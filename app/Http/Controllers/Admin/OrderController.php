<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Furniture;
use App\Models\FurnitureVariant; // <<-- Import Model Kích thước biến thể
use App\Models\Order;
use App\Models\PaymentTransaction; // <<-- Import Model Giao dịch tài chính
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // Danh sách toàn bộ đơn hàng (kèm bộ lọc trạng thái)
    public function index(Request $request)
    {
        $query = Order::with('user')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
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
            'status'         => 'required|in:pending,processing,shipping,completed,canceled,cancelled',
            'payment_status' => 'nullable|string'
        ]);

        $order = Order::with('items')->findOrFail($id);
        $oldStatus = $order->status;
        $newStatus = $request->status;

        // Nếu trạng thái không đổi và không có yêu cầu cập nhật payment_status thì bỏ qua
        if ($oldStatus === $newStatus && !$request->filled('payment_status')) {
            return back()->with('info', 'Trạng thái đơn hàng không thay đổi.');
        }

        DB::beginTransaction();

        try {
            // -------------------------------------------------------------
            // TRƯỜNG HỢP 1: Chuyển sang "Đã hủy" (Canceled) -> HOÀN LẠI TỒN KHO
            // -------------------------------------------------------------
            if (in_array($newStatus, ['canceled', 'cancelled']) && !in_array($oldStatus, ['canceled', 'cancelled'])) {
                foreach ($order->items as $item) {
                    // 1. Ưu tiên hoàn lại kho của Kích thước biến thể
                    if ($item->furniture_variant_id) {
                        FurnitureVariant::where('id', $item->furniture_variant_id)->increment('quantity', $item->quantity);
                    } 
                    // 2. Nếu không có biến thể -> Hoàn lại kho sản phẩm gốc
                    else {
                        $productId = $item->product_id ?? $item->furniture_id;
                        if ($productId) {
                            Furniture::where('id', $productId)->increment('quantity', $item->quantity);
                        }
                    }
                }

                // Cập nhật giao dịch tài chính về trạng thái thất bại/hủy
                PaymentTransaction::where('order_id', $order->id)->update([
                    'status' => 'failed'
                ]);
            }

            // -------------------------------------------------------------
            // TRƯỜNG HỢP 2: Khôi phục từ "Đã hủy" sang Hoạt động -> TRỪ LẠI TỒN KHO
            // -------------------------------------------------------------
            if (in_array($oldStatus, ['canceled', 'cancelled']) && !in_array($newStatus, ['canceled', 'cancelled'])) {
                // Step 1: Kiểm tra tồn kho trước
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

                // Step 2: Tiến hành trừ kho
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
            // TRƯỜNG HỢP 3: Chuyển sang "Đã hoàn thành" -> ĐỒNG BỘ ĐÃ THANH TOÁN
            // -------------------------------------------------------------
            if ($newStatus === 'completed') {
                $order->payment_status = 'paid';

                PaymentTransaction::where('order_id', $order->id)->update([
                    'status' => 'paid'
                ]);
            }

            // -------------------------------------------------------------
            // TRƯỜNG HỢP 4: Cập nhật payment_status trực tiếp từ Form (nếu có)
            // -------------------------------------------------------------
            if ($request->filled('payment_status')) {
                $order->payment_status = $request->payment_status;

                PaymentTransaction::where('order_id', $order->id)->update([
                    'status' => $request->payment_status
                ]);
            }

            // Lưu trạng thái đơn hàng
            $order->status = $newStatus;
            $order->save();

            DB::commit();

            return back()->with('success', 'Cập nhật trạng thái đơn hàng và tồn kho thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Cập nhật thất bại: ' . $e->getMessage());
        }
    }
}