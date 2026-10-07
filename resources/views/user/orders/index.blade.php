@extends('layout.app')

@section('content')
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold" style="color: #2a2265;">📦 Đơn hàng của tôi</h3>
        <a href="{{ route('welcome') }}" class="btn btn-outline-primary rounded-pill btn-sm fw-semibold">
            ← Tiếp tục mua sắm
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($orders->isEmpty())
        <div class="text-center py-5 bg-light rounded-4 shadow-sm">
            <i class="bi bi-bag-x text-muted display-4 d-block mb-3"></i>
            <p class="text-muted fs-5 mb-3">Bạn chưa có đơn hàng nào.</p>
            <a href="{{ route('welcome') }}" class="btn btn-primary rounded-pill px-4 fw-bold">Khám phá sản phẩm ngay</a>
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="py-3 ps-3">Mã đơn</th>
                            <th class="py-3">Người nhận</th>
                            <th class="py-3">Tổng tiền</th>
                            <th class="py-3">Trạng thái giao hàng</th>
                            <th class="py-3">Mã vận đơn GHN</th>
                            <th class="py-3 text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td class="ps-3">
                                    <span class="fw-bold text-primary">#{{ $order->id }}</span>
                                    <div class="small text-muted">{{ $order->created_at ? $order->created_at->format('d/m/Y') : '' }}</div>
                                </td>
                                <td>
                                    <strong>{{ $order->name }}</strong><br>
                                    <small class="text-muted"><i class="bi bi-telephone me-1"></i>{{ $order->phone }}</small><br>
                                    <small class="text-secondary d-inline-block text-truncate" style="max-width: 200px;" title="{{ $order->address }}">
                                        <i class="bi bi-geo-alt me-1"></i>{{ $order->address }}
                                    </small>
                                </td>
                                <td>
                                    <span class="fw-bold text-danger">
                                        {{ number_format($order->total_price ?? $order->total_amount ?? 0) }} đ
                                    </span>
                                    <div class="small text-muted">{{ strtoupper($order->payment_method ?? 'COD') }}</div>
                                </td>

                                <!-- BỘ NHÃN TRẠNG THÁI ĐỒNG BỘ STEPPER -->
                                <td>
                                    @if(in_array($order->status, ['paid', 'cod_ordered']))
                                        <span class="badge bg-warning text-dark rounded-pill px-3 py-2">
                                            <i class="bi bi-box-seam me-1"></i>1. Shop đang chuẩn bị
                                        </span>
                                    @elseif($order->status === 'shipping')
                                        <span class="badge bg-primary rounded-pill px-3 py-2">
                                            <i class="bi bi-truck me-1"></i>2. Đang giao hàng
                                        </span>
                                    @elseif($order->status === 'completed')
                                        <span class="badge bg-success rounded-pill px-3 py-2">
                                            <i class="bi bi-check-circle me-1"></i>3. Giao thành công
                                        </span>
                                    @elseif(in_array($order->status, ['cancelled', 'canceled']))
                                        <span class="badge bg-danger rounded-pill px-3 py-2">
                                            <i class="bi bi-x-circle me-1"></i>Đã hủy
                                        </span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill px-3 py-2">
                                            <i class="bi bi-clock me-1"></i>Chờ xử lý
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    @if($order->ghn_order_code)
                                        <span class="badge bg-light text-dark border font-monospace fs-6">
                                            🚚 {{ $order->ghn_order_code }}
                                        </span>
                                    @else
                                        <span class="text-muted fs-7">Chưa tạo vận đơn</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center gap-2">
                                        <!-- Nút Xem Chi Tiết Tiến Trình Stepper & Đánh Giá -->
                                        <a href="{{ route('user.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                            <i class="bi bi-eye me-1"></i>Chi tiết
                                        </a>

                                        <!-- Nút Thanh toán lại nếu đơn đang pending -->
                                        @if($order->status === 'pending')
                                            <a href="{{ route('user.orders.momo.pay', $order) }}" class="btn btn-sm btn-success rounded-pill px-3 fw-bold">
                                                Thanh toán
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection