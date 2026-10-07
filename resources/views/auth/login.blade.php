@extends('layout.app')

@section('content')
<style>
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .login-card {
        border-radius: 20px;
        border: none;
        box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        background: rgba(255, 255, 255, 0.95);
        overflow: hidden;
    }
    .login-header {
        background: linear-gradient(to right, #4facfe 0%, #00f2fe 100%);
        color: white;
        border-bottom: none;
    }
    .btn-login {
        background: linear-gradient(to right, #667eea, #764ba2);
        border: none;
        border-radius: 25px;
        font-weight: bold;
        transition: all 0.3s;
    }
    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 7px 15px rgba(118, 75, 162, 0.4);
    }
    .navbar { background-color: rgba(255, 255, 255, 0.9) !important; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
</style>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card login-card mt-4">
                <div class="card-header login-header text-center py-4">
                    <h3 class="font-weight-bold mb-0">✨ CHÀO MỪNG TRỞ LẠI ✨</h3>
                    <p class="small mb-0 mt-1">Vui lòng đăng nhập để quản lý Giường - Tủ</p>
                </div>
                <div class="card-body p-4 p-sm-5">
                    
                    @if (session('error'))
                        <div class="alert alert-danger text-center" style="border-radius: 10px;">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        
                        <div class="form-group mb-3">
                            <label for="inputEmail" class="text-muted font-weight-bold">📧 Địa chỉ Email</label>
                            <input class="form-control form-control-lg" style="border-radius: 10px;" id="inputEmail" type="email" name="email" value="{{ old('email') }}" placeholder="Nhập email của bạn..." required autofocus />
                        </div>
                        
                        <div class="form-group mb-4">
                            <label for="inputPassword" class="text-muted font-weight-bold">🔒 Mật khẩu</label>
                            <input class="form-control form-control-lg" style="border-radius: 10px;" id="inputPassword" type="password" name="password" placeholder="Nhập mật khẩu..." required />
                        </div>
                        
                        <div class="d-grid gap-2 mt-4">
                            <button class="btn btn-primary btn-login btn-lg py-2 text-white" type="submit">ĐĂNG NHẬP NGAY</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center py-3 bg-white">
                    <div class="small">
                        <a href="{{ route('register') }}" class="text-decoration-none text-muted">
                            Chưa có tài khoản? <span class="text-primary font-weight-bold">Đăng ký tại đây!</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection