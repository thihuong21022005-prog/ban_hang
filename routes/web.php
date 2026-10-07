<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\FurnitureController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\Admin\ChatController as AdminChatController;

use App\Http\Controllers\CartController;
use App\Http\Controllers\User\OrderController;
use App\Http\Controllers\User\MomoController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\ChatController as UserChatController;
use App\Http\Controllers\User\ReviewController; // Bổ sung import ReviewController
use App\Models\Furniture;

/*
|--------------------------------------------------------------------------
| 1. PUBLIC ROUTES (Không cần đăng nhập)
|--------------------------------------------------------------------------
*/
// Webhook & Callback bên thứ 3 (MoMo / GHN)
Route::post('/payment/momo/ipn', [MomoController::class, 'ipn'])->name('payment.momo.ipn');
Route::get('/payment/momo/callback', [MomoController::class, 'callback'])->name('user.payment.momo.callback');

// Xem trang chủ & sản phẩm công khai
Route::get('/', function () {
    if (auth()->check() && strtolower(auth()->user()->role) === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    $products = Furniture::all(); 
    return view('welcome', compact('products'));
})->name('welcome');

// Chi tiết sản phẩm
Route::get('/products/{id}', function ($id) {
    $product = Furniture::with('variants')->findOrFail($id);
    return view('products.show', compact('product'));
})->name('user.products.show');


/*
|--------------------------------------------------------------------------
| 2. GUEST ROUTES (Chỉ dành cho người CHƯA đăng nhập)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [AuthController::class, 'register']);

    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
});

Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');


/*
|--------------------------------------------------------------------------
| 3. AUTH & EMAIL VERIFICATION
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect()->route('welcome');
    })->middleware('signed')->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', 'Đã gửi lại link xác thực!');
    })->middleware('throttle:6,1')->name('verification.send');
});


/*
|--------------------------------------------------------------------------
| 4. NHÓM KHÁCH HÀNG (Yêu cầu Đăng nhập + Đã xác thực Email)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('user.profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('user.profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('user.profile.password');

    // Giỏ hàng
    Route::get('/cart', [CartController::class, 'index'])->name('user.cart.index');
    Route::post('/cart/add/{id}', [CartController::class, 'add'])->name('user.cart.add');
    Route::post('/cart/update/{id}', [CartController::class, 'update'])->name('user.cart.update');
    Route::post('/cart/remove/{id}', [CartController::class, 'remove'])->name('user.cart.remove');

    // Thanh toán & Đơn hàng
    Route::get('/checkout', [OrderController::class, 'index'])->name('user.checkout');
    Route::post('/payment/process', [OrderController::class, 'processPayment'])->name('user.payment.process');
    Route::get('/orders', [OrderController::class, 'ordersIndex'])->name('user.orders.index');
    
    // Xem tiến trình Stepper & Khách xác nhận nhận hàng
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('user.orders.show');
    Route::post('/orders/{id}/confirm', [OrderController::class, 'confirmReceived'])->name('user.orders.confirm');

    // Đánh giá sản phẩm
    Route::post('/reviews', [ReviewController::class, 'store'])->name('user.reviews.store');

    // MoMo Payment
    Route::get('/orders/{order}/start-momo', [MomoController::class, 'start'])->name('user.orders.momo.start');
    Route::get('/orders/{order}/pay/momo', [MomoController::class, 'payAgain'])->name('user.orders.momo.pay');

    // Livechat
    Route::post('/chat/send', [UserChatController::class, 'send'])->name('user.chat.send');
    Route::get('/chat/messages', [UserChatController::class, 'getMessages'])->name('chat.messages');

    // Location API & GHN
    Route::prefix('locations')->name('locations.')->group(function () {
        Route::get('/provinces', [OrderController::class, 'getProvinces'])->name('provinces');
        Route::get('/districts/{provinceId}', [OrderController::class, 'getDistricts'])->name('districts');
        Route::get('/wards/{districtId}', [OrderController::class, 'getWards'])->name('wards');
        Route::post('/calculate-fee', [OrderController::class, 'getShippingFee'])->name('fee');
    });
});


/*
|--------------------------------------------------------------------------
| 5. NHÓM ADMIN
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // CRUD Danh mục & Sản phẩm
    Route::resource('categories', CategoryController::class);
    Route::resource('furnitures', FurnitureController::class)->names('products');

    // Đơn hàng
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [AdminOrderController::class, 'show'])->name('orders.show');
    
    // Cập nhật trạng thái đơn hàng
    Route::put('/orders/{id}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.updateStatus');

    // Tài chính
    Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');
    Route::get('/finance/transactions', [FinanceController::class, 'transactions'])->name('finance.transactions');
    Route::patch('/finance/{order}/status', [FinanceController::class, 'updateStatus'])->name('finance.update-status');

    // Quản lý Người dùng
    Route::resource('users', AdminUserController::class);

    // Báo cáo
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/charts', [ReportController::class, 'charts'])->name('reports.charts');

    // Livechat Admin
    Route::get('/chat/users', [AdminChatController::class, 'getUsers'])->name('chat.users');
    Route::get('/chat/messages/{userId}', [AdminChatController::class, 'getMessages'])->name('chat.messages');
    Route::post('/chat/send', [AdminChatController::class, 'send'])->name('chat.send');
});