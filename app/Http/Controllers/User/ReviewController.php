<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'order_id'     => 'required|exists:orders,id',
            'furniture_id' => 'required|exists:furnitures,id',
            'rating'       => 'required|integer|min:1|max:5',
            'comment'      => 'nullable|string|max:1000',
        ]);

        // Đảm bảo đơn hàng thuộc về user đăng nhập và phải ở trạng thái completed (đã giao)
        $order = Order::where('id', $request->order_id)
            ->where('user_id', auth()->id())
            ->where('status', 'completed')
            ->firstOrFail();

        // Lưu hoặc Cập nhật đánh giá
        Review::updateOrCreate(
            [
                'user_id'      => auth()->id(),
                'order_id'     => $request->order_id,
                'furniture_id' => $request->furniture_id,
            ],
            [
                'rating'  => $request->rating,
                'comment' => $request->comment,
            ]
        );

        return back()->with('success', 'Cảm ơn bạn đã đánh giá sản phẩm!');
    }
}