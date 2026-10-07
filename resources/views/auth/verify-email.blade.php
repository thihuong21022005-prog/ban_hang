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
                    <h3 class="font-weight-bold mb-0">✉️ XÁC THỰC EMAIL ✉️</h3>
                    <p class="small mb-0 mt-1">Hệ thống Quản Lý Giường - Tủ</p>
                </div>
                
                <div class="card-body p-4 p-sm-5 text-center">
                    <p class="text-muted mb-4">
                        Cảm ơn bạn đã đăng ký! Trước khi bắt đầu trải nghiệm hệ thống, vui lòng kiểm tra hộp thư Email của bạn và bấm vào liên kết kích hoạt tài khoản.
                    </p>

                    {{-- Thông báo thành công từ Controller --}}
                    @if (session('success'))
                        <div class="alert alert-success" style="border-radius: 10px;">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('message'))
                        <div class="alert alert-info" style="border-radius: 10px;">
                            {{ session('message') }}
                        </div>
                    @endif

                    {{-- Nút gửi lại email --}}
                    <form method="POST" action="{{ route('verification.send') }}" class="mt-4">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-auth btn-lg px-4 py-2 text-white">
                            GỬI LẠI EMAIL XÁC THỰC
                        </button>
                    </form>

                    {{-- Nút đăng xuất --}}
                    <div class="mt-4">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-link text-secondary text-decoration-none btn-sm">
                                🚪 Đăng xuất tài khoản
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card-footer text-center py-3 bg-white">
                    <small class="text-muted">Chưa nhận được mail? Hãy kiểm tra lại thư mục <b>Spam / Thư rác</b>.</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection