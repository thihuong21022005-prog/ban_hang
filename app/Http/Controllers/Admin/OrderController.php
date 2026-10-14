<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Furniture;
use App\Models\FurnitureVariant; 
use App\Models\Order;
use App\Models\PaymentTransaction; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class OrderController extends Controller
{
    // Danh sách toàn bộ đơn hàng (kèm bộ lọc trạng thái)
    public function index(Request $request)
    {
        $query = Order::with('user')->latest();

        if ($request->filled('status')) {
            $status = $request->status;
            $query->where(function($q) use ($status) {
                if ($status === 'paid') {
                    $q->whereIn('status', ['paid', 'cod_ordered', 'pending']);
                } elseif ($status === 'shipping') {
                    $q->where('status', 'shipping')
                      ->orWhereIn('shipping_status', ['delivering', 'picking', 'transporting']);
                } elseif ($status === 'completed') {
                    $q->whereIn('status', ['completed', 'cod_paid'])
                      ->orWhere('shipping_status', 'delivered');
                } elseif (in_array($status, ['canceled', 'cancelled'])) {
                    $q->whereIn('status', ['canceled', 'cancelled'])
                      ->orWhere('shipping_status', 'cancelled');
                }
            });
        }

        $orders = $query->paginate(10);
        return view('admin.orders.index', compact('orders'));
    }

    // Xem chi tiết đơn hàng
    public function show($id)
    {
        $order = Order::with(['user', 'items.furniture', 'items.variant'])->findOrFail($id);
        return view('admin.orders.show', compact('order'));
    }

    // Cập nhật trạng thái đơn hàng
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status'         => 'required|in:paid,pending,processing,shipping,completed,canceled,cancelled',
            'payment_status' => 'nullable|string'
        ]);

        $order = Order::with('items')->findOrFail($id);
        $oldStatus = $order->status;
        $newStatus = $request->status;

        DB::beginTransaction();

        try {
            // -------------------------------------------------------------
            // ĐỒNG BỘ CỘT SHIPPING_STATUS THEO STATUS MỚI
            // -------------------------------------------------------------
            if ($newStatus === 'paid') {
                $order->shipping_status = 'ready_to_pick';
            } elseif ($newStatus === 'shipping') {
                $order->shipping_status = 'delivering';
            } elseif ($newStatus === 'completed') {
                $order->shipping_status = 'delivered';

                // Nếu là đơn COD thì đổi status chuẩn thành cod_paid
                if ($order->payment_method === 'cod') {
                    $newStatus = 'cod_paid';
                }

                // Cập nhật trạng thái tài chính ở bảng payment_transactions
                PaymentTransaction::where('order_id', $order->id)->update([
                    'status' => 'paid'
                ]);
            } elseif (in_array($newStatus, ['canceled', 'cancelled'])) {
                $order->shipping_status = 'cancelled';
            }

            // -------------------------------------------------------------
            // TRƯỜNG HỢP 1: Chuyển sang "Đã hủy" (Canceled) -> HOÀN LẠI TỒN KHO
            // -------------------------------------------------------------
            if (in_array($newStatus, ['canceled', 'cancelled']) && !in_array($oldStatus, ['canceled', 'cancelled'])) {
                foreach ($order->items as $item) {
                    if ($item->furniture_variant_id) {
                        FurnitureVariant::where('id', $item->furniture_variant_id)->increment('quantity', $item->quantity);
                    } else {
                        $productId = $item->product_id ?? $item->furniture_id;
                        if ($productId) {
                            Furniture::where('id', $productId)->increment('quantity', $item->quantity);
                        }
                    }
                }

                PaymentTransaction::where('order_id', $order->id)->update([
                    'status' => 'failed'
                ]);
            }

            // -------------------------------------------------------------
            // TRƯỜNG HỢP 2: Khôi phục từ "Đã hủy" sang Hoạt động -> TRỪ LẠI TỒN KHO
            // -------------------------------------------------------------
            if (in_array($oldStatus, ['canceled', 'cancelled']) && !in_array($newStatus, ['canceled', 'cancelled'])) {
                foreach ($order->items as $item) {
                    if ($item->furniture_variant_id) {
                        $variant = FurnitureVariant::find($item->furniture_variant_id);
                        if (!$variant || $variant->quantity < $item->quantity) {
                            $sizeName = $variant->size ?? 'Kích thước chọn';
                            return back()->with('error', "Không thể khôi phục! Biến thể kích thước \"{$sizeName}\" hiện không đủ tồn kho.");
                        }
                    } else {
                        $productId = $item->product_id ?? $item->furniture_id;
                        $furniture = Furniture::find($productId);
                        if (!$furniture || $furniture->quantity < $item->quantity) {
                            $name = $furniture->name ?? 'Sản phẩm';
                            return back()->with('error', "Không thể khôi phục! Sản phẩm \"{$name}\" hiện không đủ tồn kho.");
                        }
                    }
                }

                foreach ($order->items as $item) {
                    if ($item->furniture_variant_id) {
                        FurnitureVariant::where('id', $item->furniture_variant_id)->decrement('quantity', $item->quantity);
                    } else {
                        $productId = $item->product_id ?? $item->furniture_id;
                        if ($productId) {
                            Furniture::where('id', $productId)->decrement('quantity', $item->quantity);
                        }
                    }
                }
            }

            // -------------------------------------------------------------
            // TRƯỜNG HỢP 3: Cập nhật giao dịch tài chính nếu có gửi payment_status
            // -------------------------------------------------------------
            if ($request->filled('payment_status')) {
                PaymentTransaction::where('order_id', $order->id)->update([
                    'status' => $request->payment_status
                ]);
            }

            // Lưu trạng thái đơn hàng (Đã loại bỏ hoàn toàn payment_status)
            $order->status = $newStatus;
            $order->save();

            DB::commit();

            return back()->with('success', 'Cập nhật trạng thái đơn hàng và tồn kho thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Cập nhật thất bại: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------
    // TẠO VẬN ĐƠN GHN TỰ ĐỘNG & CHUYỂN TRẠNG THÁI SANG ĐANG GIAO HÀNG
    // -------------------------------------------------------------
    public function createGhnOrder($id)
    {
        $order = Order::with(['items', 'user'])->findOrFail($id);

        // 1. Kiểm tra nếu đơn hàng đã có mã vận đơn GHN
        if (!empty($order->shipping_code)) {
            return back()->with('error', "Đơn hàng này đã có mã vận đơn GHN: {$order->shipping_code}");
        }

        try {
            // 2. Lấy GHN Token & ShopID từ file .env (hoặc mặc định Sandbox GHN)
            $ghnToken = env('GHN_TOKEN', 'YOUR_GHN_TOKEN'); 
            $ghnShopId = env('GHN_SHOP_ID', 'YOUR_GHN_SHOP_ID');

            // 3. Chuẩn bị danh sách sản phẩm gửi sang GHN
            $items = [];
            foreach ($order->items as $item) {
                $items[] = [
                    "name"     => $item->furniture_name ?? $item->name ?? "Sản phẩm nội thất",
                    "quantity" => (int) $item->quantity,
                    "price"    => (int) $item->price,
                    "length"   => 20,
                    "width"    => 20,
                    "height"   => 20,
                    "weight"   => 1000
                ];
            }

            // 4. Dữ liệu Payload gửi sang GHN API
            $payload = [
                "payment_type_id"   => 2, // Người nhận trả cước phí vận chuyển
                "note"              => "Đơn hàng nội thất - Giao giờ hành chính",
                "required_note"     => "KHONGCHOXEMHANG",
                "to_name"           => $order->name ?? optional($order->user)->name ?? 'Khách hàng',
                "to_phone"          => $order->phone ?? optional($order->user)->phone ?? '0987654321',
                "to_address"        => $order->address ?? 'Địa chỉ giao hàng',
                "to_ward_code"      => (string) ($order->ward_id ?? "20109"),
                "to_district_id"    => (int) ($order->district_id ?? 1442),
                "cod_amount"        => ($order->payment_method === 'cod') ? (int) $order->total_price : 0, // Tiền thu hộ COD
                "weight"            => 2000,
                "length"            => 30,
                "width"             => 30,
                "height"            => 30,
                "service_type_id"   => 2,
                "items"             => $items
            ];

            // 5. Gọi API GHN Create Shipping Order
            $response = Http::withHeaders([
                'Token'        => $ghnToken,
                'ShopId'       => $ghnShopId,
                'Content-Type' => 'application/json'
            ])->post('https://dev-online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/create', $payload);

            $result = $response->json();

            if (isset($result['code']) && $result['code'] == 200) {
                $orderCode = $result['data']['order_code'];

                // Cập nhật mã vận đơn GHN & TỰ ĐỘNG CHUYỂN TRẠNG THÁI SANG GIAO CHO ĐVVC
                $order->shipping_code = $orderCode;
                $order->status = 'shipping';
                $order->shipping_status = 'delivering';
                $order->save();

                return back()->with('success', "Tạo vận đơn GHN thành công! Mã vận đơn: {$orderCode}");
            } else {
                $errorMsg = $result['message'] ?? 'Lỗi không xác định từ API GHN';
                return back()->with('error', "Không thể tạo vận đơn GHN: {$errorMsg}");
            }

        } catch (\Exception $e) {
            return back()->with('error', "Lỗi kết nối GHN: " . $e->getMessage());
        }
    }
}