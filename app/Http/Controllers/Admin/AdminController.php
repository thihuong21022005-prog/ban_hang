<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Furniture;
use App\Models\Category;
use App\Models\User;
use App\Models\Order;
use App\Models\Message;

class AdminController extends Controller
{
    public function dashboard()
    {
        // 1. Tải danh sách sản phẩm mới nhất kèm Category & Variants
        $furnitures = Furniture::with(['category', 'variants'])->latest()->take(10)->get();

        // 2. Thống kê tổng quan số lượng
        $totalProducts   = Furniture::count(); 
        $totalCategories = Category::count();
        $totalUsers      = User::count();
        $totalOrders     = Order::count();

        // 3. Tính tổng doanh thu (Bao gồm các đơn Đã thanh toán, Đang giao và Hoàn thành)
        // Lưu ý: Đổi 'total_price' thành 'total_amount' nếu cột DB của bạn tên là total_amount
        $totalRevenue    = Order::whereIn('status', ['paid', 'shipping', 'completed'])
                                ->sum('total_price');

        // 4. Thống kê sản phẩm Giường (Lọc theo cả Type và Name danh mục)
        $totalBeds = Furniture::whereHas('category', function ($query) {
            $query->where('type', 'Giường')
                  ->orWhere('name', 'LIKE', '%Giường%');
        })->count();

        // 5. Thống kê sản phẩm Tủ (Lọc theo cả Type và Name danh mục)
        $totalWardrobes = Furniture::whereHas('category', function ($query) {
            $query->where('type', 'Tủ')
                  ->orWhere('name', 'LIKE', '%Tủ%');
        })->count();

        // 6. Đếm số tin nhắn chưa đọc
        $unreadMessages = Message::where('is_read', false)->count();

        // 7. Truyền dữ liệu sang View Dashboard
        return view('admin.dashboard', compact(
            'furnitures',
            'totalProducts', 
            'totalCategories', 
            'totalUsers', 
            'totalOrders',
            'totalRevenue',
            'totalBeds', 
            'totalWardrobes',
            'unreadMessages'
        ));
    }
}