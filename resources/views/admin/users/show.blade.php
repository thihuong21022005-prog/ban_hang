@extends('layout.admin')

@section('content')
<div class="container-fluid py-3">
    <!-- Nút quay lại & Tiêu đề -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold m-0" style="color: #2a2265;">
            <i class="bi bi-person-lines-fill me-2"></i>Chi Tiết Người Dùng
        </h4>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary rounded-pill px-3 fw-bold">
            <i class="bi bi-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    <div class="row g-4">
        <!-- Card Thông tin cá nhân -->
        <div class="col-md-5 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 text-center p-4">
                <div class="mb-3 position-relative d-inline-block mx-auto">
                    <!-- Avatar mặc định dạng Vòng tròn chữ cái đầu -->
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-sm mx-auto" 
                         style="width: 90px; height: 90px; font-size: 2.2rem; background: linear-gradient(135deg, #4e73df, #224abe);">
                        {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                    </div>
                </div>

                <h5 class="fw-bold text-dark mb-1">{{ $user->name }}</h5>
                <p class="text-muted small mb-3">{{ $user->email }}</p>

                <div>
                    @if(in_array(strtolower($user->role ?? ''), ['admin', 'quản trị', '1']))
                        <span class="badge bg-danger px-3 py-2 rounded-pill fw-semibold">
                            <i class="bi bi-shield-lock-fill me-1"></i> Quản trị viên (Admin)
                        </span>
                    @else
                        <span class="badge bg-info text-dark px-3 py-2 rounded-pill fw-semibold">
                            <i class="bi bi-person-fill me-1"></i> Khách hàng
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Card Chi tiết thuộc tính -->
        <div class="col-md-7 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <h6 class="fw-bold text-secondary mb-3 border-bottom pb-2">
                    <i class="bi bi-info-circle me-1"></i> Thông tin hệ thống
                </h6>

                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="text-muted extra-small d-block fw-semibold">MÃ NGƯỜI DÙNG (ID)</label>
                        <span class="fw-bold text-dark">#{{ $user->id }}</span>
                    </div>

                    <div class="col-sm-6">
                        <label class="text-muted extra-small d-block fw-semibold">NGÀY THAM GIA</label>
                        <span class="fw-semibold text-dark">
                            {{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : 'Chưa cập nhật' }}
                        </span>
                    </div>

                    <div class="col-sm-6">
                        <label class="text-muted extra-small d-block fw-semibold">SỐ ĐIỆN THOẠI</label>
                        <span class="fw-semibold text-dark">{{ $user->phone ?? 'Chưa cập nhật' }}</span>
                    </div>

                    <div class="col-sm-6">
                        <label class="text-muted extra-small d-block fw-semibold">ĐỊA CHỈ MẶC ĐỊNH</label>
                        <span class="fw-semibold text-dark">{{ $user->address ?? 'Chưa cập nhật' }}</span>
                    </div>
                </div>

                <!-- Lịch sử đơn hàng của User (Tùy chọn) -->
                @if(isset($user->orders) && $user->orders->count() > 0)
                    <h6 class="fw-bold text-secondary mt-4 mb-3 border-bottom pb-2">
                        <i class="bi bi-bag-check me-1"></i> Đơn hàng gần đây ({{ $user->orders->count() }})
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr class="text-muted small">
                                    <th>Mã Đơn</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày đặt</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->orders->take(5) as $order)
                                <tr>
                                    <td class="fw-bold">#{{ $order->id }}</td>
                                    <td class="text-danger fw-bold">{{ number_format($order->total_price ?? $order->total_amount ?? 0, 0, ',', '.') }} đ</td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $order->status }}</span>
                                    </td>
                                    <td class="small text-muted">{{ $order->created_at->format('d/m/Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .extra-small {
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }
</style>
@endsection