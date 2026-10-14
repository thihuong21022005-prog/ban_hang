@extends('layout.admin')

@section('content')
<div class="card shadow-sm border-0 rounded-3">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold m-0" style="color: #2a2265;">
                <i class="bi bi-receipt me-2"></i>QUẢN LÝ ĐƠN HÀNG
            </h4>

            <!-- Lọc trạng thái -->
            <form action="{{ route('admin.orders.index') }}" method="GET" class="d-flex align-items-center gap-2">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>📦 Shop đang chuẩn bị hàng</option>
                    <option value="shipping" {{ request('status') == 'shipping' ? 'selected' : '' }}>🚚 Đã giao cho ĐVVC</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>✅ Giao thành công</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>❌ Đã hủy</option>
                </select>
            </form>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Mã Đơn</th>
                        <th>Khách Hàng</th>
                        <th>Tổng Tiền</th>
                        <th>Thanh Toán</th>
                        <th>Trạng Thái Giao Hàng</th>
                        <th>Ngày Đặt</th>
                        <th class="text-center">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        @php
                            // Bắt chuẩn xác 100% các trạng thái bao gồm cả canceled (1 chữ l) và cancelled (2 chữ l)
                            $isCancelled = in_array($order->status, ['canceled', 'cancelled']) || $order->shipping_status === 'cancelled';
                            $isCompleted = !$isCancelled && (in_array($order->status, ['completed', 'cod_paid']) || $order->shipping_status === 'delivered');
                            $isShipping = !$isCancelled && !$isCompleted && ($order->status === 'shipping' || in_array($order->shipping_status, ['delivering', 'picking', 'transporting']));
                            $isPending = !$isCancelled && !$isCompleted && !$isShipping && $order->status === 'pending';
                            $isPaid = !$isCancelled && !$isCompleted && !$isShipping && !$isPending;
                        @endphp
                        <tr class="{{ $isCancelled ? 'table-light' : '' }}">
                            <td class="fw-bold">#{{ $order->id }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $order->user->name ?? $order->name ?? 'Khách hàng' }}</div>
                                @if($order->phone ?? optional($order->user)->phone)
                                    <small class="text-muted d-block">{{ $order->phone ?? optional($order->user)->phone }}</small>
                                @endif
                            </td>
                            <td class="text-danger fw-bold">{{ number_format($order->total_price ?? $order->total ?? 0, 0, ',', '.') }} VNĐ</td>
                            
                            <!-- CỘT THANH TOÁN -->
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    <span class="badge bg-secondary w-auto me-auto">
                                        {{ strtoupper($order->payment_method ?? 'COD') }}
                                    </span>
                                    
                                    @if($isCancelled)
                                        <span class="badge bg-danger w-auto me-auto">
                                            <i class="bi bi-x-circle me-1"></i>Đã hủy
                                        </span>
                                    @elseif(in_array($order->status, ['paid', 'shipping', 'completed', 'cod_paid', 'paid_momo']) || ($order->payment_status ?? '') == 'paid' || $order->shipping_status == 'delivered')
                                        <span class="badge bg-success w-auto me-auto">
                                            <i class="bi bi-check-circle me-1"></i>Đã thanh toán
                                        </span>
                                    @else
                                        <span class="badge bg-warning text-dark w-auto me-auto">
                                            <i class="bi bi-clock me-1"></i>Chờ thanh toán
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- CỘT CẬP NHẬT TRẠNG THÁI -->
                            <td>
                                <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <select name="status" 
                                            class="form-select form-select-sm fw-semibold 
                                            @if($isCancelled) border-danger text-danger bg-light
                                            @elseif($isPending) border-secondary text-secondary
                                            @elseif($isPaid) border-warning text-dark
                                            @elseif($isShipping) border-primary text-primary
                                            @elseif($isCompleted) border-success text-success
                                            @endif" 
                                            onchange="this.form.submit()">
                                        @if($isPending)
                                            <option value="pending" selected>⏳ Chờ xử lý</option>
                                        @endif
                                        <option value="paid" {{ $isPaid ? 'selected' : '' }}>📦 1. Chuẩn bị hàng</option>
                                        <option value="shipping" {{ $isShipping ? 'selected' : '' }}>🚚 2. Giao cho ĐVVC</option>
                                        <option value="completed" {{ $isCompleted ? 'selected' : '' }}>✅ 3. Giao thành công</option>
                                        <option value="cancelled" {{ $isCancelled ? 'selected' : '' }}>❌ Đã hủy</option>
                                    </select>
                                </form>
                            </td>
                            
                            <td class="small text-muted">{{ $order->created_at ? $order->created_at->format('d/m/Y H:i') : '' }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                    <i class="bi bi-eye me-1"></i>Chi tiết
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Chưa có đơn hàng nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($orders, 'links'))
            <div class="mt-3">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection