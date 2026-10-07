<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Tạo sẵn tài khoản Admin (Đã tự động xác thực Email)
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'], // Kiểm tra nếu chưa có email này thì tạo mới
            [
                'name' => 'Quan Tri Vien',
                'password' => Hash::make('12345678'), // Mật khẩu đăng nhập
                'role' => 'admin', // Gán thẳng quyền admin
                'email_verified_at' => now(), // Tự động xác thực email để không bị hỏi lại
            ]
        );
    }
}