<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Furniture;
use App\Models\FurnitureVariant; // <<-- BỔ SUNG MODEL VARIANT
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentTransaction;
use App\Services\GHNOrderService;
use App\Services\GHNService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    // 1. Hiển thị trang giao diện đặt hàng (Thanh toán)
    public function index(Request $request)
    {
        $cart = session('cart', []);

        // Lấy danh sách key sản phẩm người dùng đã tích chọn từ giỏ hàng
        $selectedIds = $request->input('selected_items', []);

        if (!empty($selectedIds)) {
            // Lọc giỏ hàng chỉ giữ lại các sản phẩm/size được chọn
            $selectedIdsStr = array_map('strval', $selectedIds);
            $cart = array_filter($cart, function ($item, $key) use ($selectedIdsStr) {
                return in_array((string)$key, $selectedIdsStr, true);
            }, ARRAY_FILTER_USE_BOTH);
        }

        if (empty($cart)) {
            return redirect()->route('user.cart.index')->with('error', 'Vui lòng chọn ít nhất 1 sản phẩm để thanh toán!');
        }

        // Tính tổng tiền hàng
        $subtotal = collect($cart)->sum(function ($item) {
            $price = $item['price'] ?? $item['product']['price'] ?? 0;
            $quantity = $item['quantity'] ?? 1;
            return $price * $quantity;
        });

        return view('checkout', compact('cart', 'subtotal'));
    }

    // 2. Xử lý đặt hàng (Phân luồng COD hoặc MoMo)
    public function processPayment(Request $request, GHNService $ghn, GHNOrderService $ghnOrders)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'phone' => ['required', 'regex:/^0\d{9}$/'],
            'address' => 'required|string|max:255',
            'to_district_id' => 'required|integer',
            'to_ward_code' => 'required|string',
            'payment_method' => 'required|in:cod,momo',
        ]);

        $cart = session('cart', []);

        // Lọc lại các sản phẩm được chọn thanh toán nếu có gửi qua POST
        $selectedIds = $request->input('selected_items', []);
        if (!empty($selectedIds)) {
            $selectedIdsStr = array_map('strval', $selectedIds);
            $cart = array_filter($cart, function ($item, $key) use ($selectedIdsStr) {
                return in_array((string)$key, $selectedIdsStr, true);
            }, ARRAY_FILTER_USE_BOTH);
        }

        if (empty($cart)) {
            return redirect()->route('user.cart.index')->with('error', 'Không thể thanh toán vì giỏ hàng trống.');
        }

        // =========================================================================
        // BƯỚC BỔ SUNG: KIỂM TRA TỒN KHO THEO SIZE TRƯỚC KHU TẠO ĐƠN HÀNG
        // =========================================================================
        foreach ($cart as $cartKey => $item) {
            $realProductId = $item['product_id'] ?? $item['id'] ?? null;
            $variantId = $item['variant_id'] ?? null;
            $requestedQty = (int) ($item['quantity'] ?? 1);
            $dimensionName = $item['dimension'] ?? 'Mặc định';

            // 1. Nếu mua theo Biến thể Size
            if ($variantId) {
                $variant = FurnitureVariant::find($variantId);
                if (!$variant || $variant->quantity < $requestedQty) {
                    $stockAvailable = $variant ? $variant->quantity : 0;
                    return redirect()->route('user.cart.index')->with(
                        'error',
                        "Sản phẩm \"{$item['name']}\" (Size: {$dimensionName}) hiện chỉ còn {$stockAvailable} cái trong kho, không đủ số lượng bạn yêu cầu ({$requestedQty})!"
                    );
                }
            } else {
                // 2. Nếu mua sản phẩm thông thường
                $furniture = Furniture::find($realProductId);
                if (!$furniture || ($furniture->quantity ?? 0) < $requestedQty) {
                    $stockAvailable = $furniture ? $furniture->quantity : 0;
                    return redirect()->route('user.cart.index')->with(
                        'error',
                        "Sản phẩm \"{$item['name']}\" hiện chỉ còn {$stockAvailable} cái trong kho, không đủ số lượng bạn yêu cầu ({$requestedQty})!"
                    );
                }
            }
        }

        // Tính tổng tiền hàng và tổng khối lượng sản phẩm
        $subtotal = collect($cart)->sum(function ($item) {
            $price = $item['price'] ?? $item['product']['price'] ?? 0;
            return $price * ($item['quantity'] ?? 1);
        });

        $totalWeight = collect($cart)->sum(function ($item) {
            $weight = $item['weight'] ?? $item['product']['weight'] ?? 200;
            return ((int) $weight) * (int) ($item['quantity'] ?? 1);
        });

        // Tính lại phí ship chuẩn xác từ GHN trên server
        $feeResponse = $ghn->calculateFee([
            'service_type_id' => 2,
            'from_district_id' => (int) config('services.ghn.from_district_id'),
            'to_district_id' => (int) $request->to_district_id,
            'to_ward_code' => (string) $request->to_ward_code,
            'weight' => $totalWeight > 0 ? $totalWeight : 300,
            'length' => 15,
            'width' => 15,
            'height' => 10,
        ]);

        $shippingFee = (isset($feeResponse['code']) && $feeResponse['code'] == 200)
            ? (int) ($feeResponse['data']['total'] ?? 0)
            : 0;

        $finalTotal = $subtotal + $shippingFee;

        // Tạo đơn hàng, chi tiết đơn hàng và TRỪ TỒN KHO trong Database
        $order = DB::transaction(function () use ($request, $shippingFee, $finalTotal, $cart) {
            $order = Order::create([
                'user_id' => Auth::id(),
                'name' => $request->name,
                'address' => $request->address,
                'phone' => $request->phone,
                'total_price' => $finalTotal,
                'status' => 'pending',
                'to_district_id' => (int) $request->to_district_id,
                'to_ward_code' => (string) $request->to_ward_code,
                'ghn_total_fee' => $shippingFee,
                'shipping_status' => 'pending',
            ]);

            foreach ($cart as $cartKey => $item) {
                $realProductId = $item['product_id'] ?? $item['id'] ?? null;
                $variantId     = $item['variant_id'] ?? null;
                $price         = $item['price'] ?? $item['product']['price'] ?? 0;
                $quantity      = (int) ($item['quantity'] ?? 1);
                $dimension     = $item['dimension'] ?? 'Tiêu chuẩn';

                // LƯU CHI TIẾT ĐƠN HÀNG (KÈM KÍCH THƯỚC / SIZE)
                OrderItem::create([
                    'order_id'   => $order->id,
                    'furniture_id' => $realProductId,
                    'variant_id' => $variantId,
                    'quantity'   => $quantity,
                    'price'      => $price,
                    'dimension'  => $dimension, // Lưu Size vào CSDL
                ]);

                // TRỪ SỐ LƯỢNG TỒN KHO CỦA BIẾN THỂ SIZE (NẾU CÓ)
                if ($variantId) {
                    FurnitureVariant::where('id', $variantId)->decrement('quantity', $quantity);
                }

                // TRỪ SỐ LƯỢNG TỒN KHO TỔNG CỦA SẢN PHẨM
                Furniture::where('id', $realProductId)->decrement('quantity', $quantity);
            }

            return $order;
        });

        // Xóa session giỏ hàng
        session()->forget('cart');

        // Phân luồng thanh toán MoMo vs COD
        if ($request->payment_method === 'momo') {
            PaymentTransaction::create([
                'order_id' => $order->id,
                'gateway' => 'momo',
                'amount' => $order->total_price,
                'status' => 'pending',
            ]);

            return redirect()->route('user.orders.momo.start', $order);
        }

        // NHÁNH COD: TẠO VẬN ĐƠN GHN NGAY LẬP TỨC
        PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'cod',
            'amount' => $order->total_price,
            'status' => 'pending',
            'message' => 'Thanh toán khi nhận hàng',
        ]);

        $order->load('items.furniture');
        $ghnOrderResponse = $ghnOrders->create($order);

        if (($ghnOrderResponse['code'] ?? null) == 200 && !empty($ghnOrderResponse['data']['order_code'])) {
            $order->update([
                'status' => 'cod_ordered',
                'ghn_order_code' => $ghnOrderResponse['data']['order_code'],
                'shipping_status' => 'ready_to_pick',
            ]);

            return redirect()->route('user.orders.index')
                ->with('success', 'Đặt hàng thành công! Mã vận đơn GHN: ' . $ghnOrderResponse['data']['order_code']);
        }

        Log::error('GHN COD Order Failed: ', $ghnOrderResponse ?? []);
        $order->update(['status' => 'cod_ordered']);

        return redirect()->route('user.orders.index')
            ->with('warning', 'Đặt hàng thành công nhưng chưa thể tạo vận đơn GHN tự động.');
    }

    // 3. Các hàm AJAX lấy Tỉnh/Thành, Quận/Huyện, Phường/Xã và tính phí ship GHN
    public function getProvinces(GHNService $ghn)
    {
        return response()->json($ghn->getProvinces());
    }

    public function getDistricts(int $provinceId, GHNService $ghn)
    {
        return response()->json($ghn->getDistricts($provinceId));
    }

    public function getWards(int $districtId, GHNService $ghn)
    {
        return response()->json($ghn->getWards($districtId));
    }

    public function getShippingFee(Request $request, GHNService $ghn)
    {
        $cart = session('cart', []);
        $totalWeight = 0;

        foreach ($cart as $item) {
            $weight = $item['weight'] ?? $item['product']['weight'] ?? 200;
            $totalWeight += ((int) $weight) * (int) ($item['quantity'] ?? 1);
        }

        $res = $ghn->calculateFee([
            'service_type_id' => 2,
            'from_district_id' => (int) config('services.ghn.from_district_id'),
            'to_district_id' => (int) $request->to_district_id,
            'to_ward_code' => (string) $request->to_ward_code,
            'weight' => $totalWeight > 0 ? $totalWeight : 300,
            'length' => 15,
            'width' => 15,
            'height' => 10,
        ]);

        return response()->json($res);
    }

    public function ordersIndex(GHNOrderService $ghnOrders)
    {
        $orders = Order::where('user_id', Auth::id())
            ->latest()
            ->get();

        foreach ($orders as $order) {
            if (in_array($order->status, ['paid', 'cod_ordered']) && empty($order->ghn_order_code)) {
                $order->load('items'); 
                
                $ghnOrderResponse = $ghnOrders->create($order);

                if (($ghnOrderResponse['code'] ?? null) == 200 && !empty($ghnOrderResponse['data']['order_code'])) {
                    $order->update([
                        'ghn_order_code' => $ghnOrderResponse['data']['order_code'],
                        'shipping_status' => 'ready_to_pick',
                    ]);
                }
            }
        }

        return view('user.orders.index', compact('orders'));
    }

    /**
     * 4. Xem chi tiết đơn hàng & Tiến trình Stepper giao hàng
     */
    public function show($id)
{
    $order = Order::with(['items.furniture'])
        ->where('user_id', Auth::id())
        ->findOrFail($id);

    return view('user.orders.show', compact('order')); // ✅ Đổi thành user.orders.show
}

    /**
     * 5. Khách hàng bấm "Đã nhận được hàng" (Xác nhận hoàn thành đơn hàng)
     */
    public function confirmReceived($id)
    {
        $order = Order::where('user_id', Auth::id())->findOrFail($id);

        // Cho phép xác nhận nếu đơn đang giao hoặc đã chuyển sang trạng thái shipping/cod_ordered
        if (in_array($order->status, ['shipping', 'paid', 'cod_ordered'])) {
            $order->update([
                'status' => 'completed'
            ]);

            return back()->with('success', 'Xác nhận thành công! Cảm ơn bạn đã mua sắm. Hãy để lại đánh giá cho sản phẩm nhé!');
        }

        return back()->with('error', 'Đơn hàng chưa ở trạng thái có thể xác nhận đã nhận hàng.');
    }
}