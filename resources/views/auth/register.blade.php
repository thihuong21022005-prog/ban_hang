@extends('layout.app')

@section('content')
<style>
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .auth-card {
        border-radius: 20px; 
        border: none;
        box-shadow: 0 15px 35px rgba(0,0,0,0.2); 
        background: rgba(255, 255, 255, 0.95); 
        overflow: hidden;
    }
    .auth-header {
        background: linear-gradient(to right, #4facfe 0%, #00f2fe 100%);
        color: white;
        border-bottom: none;
    }
    .btn-auth {
        background: linear-gradient(to right, #667eea, #764ba2);
        border: none;
        border-radius: 25px;
        font-weight: bold;
        transition: all 0.3s;
    }
    .btn-auth:hover {
        transform: translateY(-2px);
        box-shadow: 0 7px 15px rgba(118, 75, 162, 0.4);
    }
</style>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card auth-card mt-4">
                <div class="card-header auth-header text-center py-4">
                    <h3 class="font-weight-bold mb-0">✨ ĐĂNG KÝ TÀI KHOẢN ✨</h3>
                    <p class="small mb-0 mt-1">Tham gia hệ thống quản lý Giường - Tủ ngay hôm nay</p>
                </div>
                <div class="card-body p-4 p-sm-5">
                    
                    {{-- Hiển thị thông báo lỗi --}}
                    @if ($errors->any())
                        <div class="alert alert-danger" style="border-radius: 10px;">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register') }}">
                        @csrf
                        
                        {{-- Ô nhập Tên --}}
                        <div class="form-group mb-3">
                            <label for="inputName" class="text-muted font-weight-bold">👤 Họ và Tên</label>
                            <input class="form-control form-control-lg" style="border-radius: 10px;" id="inputName" type="text" name="name" value="{{ old('name') }}" placeholder="Nhập họ và tên..." required autofocus />
                        </div>

                        {{-- Ô nhập Email --}}
                        <div class="form-group mb-3">
                            <label for="inputEmail" class="text-muted font-weight-bold">📧 Địa chỉ Email</label>
                            <input class="form-control form-control-lg" style="border-radius: 10px;" id="inputEmail" type="email" name="email" value="{{ old('email') }}" placeholder="Nhập email của bạn..." required />
                        </div>
                        
                        {{-- Ô nhập Password --}}
                        <div class="form-group mb-3">
                            <label for="inputPassword" class="text-muted font-weight-bold">🔒 Mật khẩu</label>
                            <input class="form-control form-control-lg" style="border-radius: 10px;" id="inputPassword" type="password" name="password" placeholder="Tối thiểu 8 ký tự..." required />
                        </div>

                        {{-- Ô Xác nhận Password --}}
                        <div class="form-group mb-4">
                            <label for="inputPasswordConfirm" class="text-muted font-weight-bold">🔐 Nhập lại Mật khẩu</label>
                            <input class="form-control form-control-lg" style="border-radius: 10px;" id="inputPasswordConfirm" type="password" name="password_confirmation" placeholder="Xác nhận lại mật khẩu..." required />
                        </div>
                        
                        <div class="d-grid gap-2 mt-4">
                            <button class="btn btn-primary btn-auth btn-lg py-2 text-white" type="submit">ĐĂNG KÝ NGAY</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center py-3 bg-white">
                    <div class="small">
                        <a href="{{ route('login') }}" class="text-decoration-none text-muted">
                            Đã có tài khoản? <span class="text-primary font-weight-bold">Đăng nhập tại đây!</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection