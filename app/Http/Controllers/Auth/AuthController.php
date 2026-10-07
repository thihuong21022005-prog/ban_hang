<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    // Hiển thị form đăng ký
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    // Xử lý đăng ký người dùng
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            Log::info('Registering user with email: ' . $request->email);
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'customer',
            ]);
            Log::info('User registered successfully');
            return redirect()->route('login')->with('success', 'Registration successful! Please login.');
        } catch (\Exception $e) {
            Log::error('Registration failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Registration failed. Please try again.');
        }
    }

    // Hiển thị form đăng nhập
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Xử lý đăng nhập người dùng
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();
            
            $user = Auth::user();

            // 1. Ép kiểu chữ thường để tránh lỗi viết hoa/thường (Ví dụ: 'Admin' vs 'admin')
            // 2. Dùng redirect()->route() thay cho intended() để bắt buộc nhảy vào Dashboard Admin
            if (strtolower($user->role) === 'admin') {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->intended(route('welcome'));
        }

        return redirect()->back()->with('error', 'The provided credentials are incorrect.');
    }

    // Xử lý đăng xuất người dùng
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}