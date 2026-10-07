<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->role == 'admin') {
            // Tự động kích hoạt email cho Admin nếu chưa có ngày xác thực
            if (is_null(Auth::user()->email_verified_at)) {
                Auth::user()->update(['email_verified_at' => now()]);
            }

            return $next($request);
        }

        return redirect()->route('welcome');
    }
}