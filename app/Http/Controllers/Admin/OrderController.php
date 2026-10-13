<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Furniture;
use App\Models\FurnitureVariant; 
use App\Models\Order;
use App\Models\PaymentTransaction; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        // 1. Thêm 'paid' vào Validation để khớp với value gửi từ View
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
                $order->payment_status = 'paid'; // Đơn đã giao thành công thì tự động đánh dấu đã thanh toán

                // Nếu là đơn COD thì đổi status chuẩn thành cod_paid
                if ($order->payment_method === 'cod') {
                    $newStatus = 'cod_paid';
                }

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
            // TRƯỜNG HỢP 3: Cập nhật payment_status trực tiếp từ Form (nếu có)
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