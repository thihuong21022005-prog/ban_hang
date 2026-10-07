@extends('layout.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold m-0" style="color: #2a2265;">
            <i class="bi bi-receipt-cutoff me-2"></i>Chi Tiết Đơn Hàng #{{ $order->id }}
        </h4>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
        </a>
    </div>

    <div class="row g-4">
        <!-- Thông tin khách hàng & Giao hàng -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-primary">
                        <i class="bi bi-person-circle me-2"></i>Thông Tin Khách Hàng
                    </h6>
                </div>
                <div class="card-body pt-0">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-muted">Họ tên:</span>
                            <strong class="text-dark">{{ $order->user->name ?? $order->fullname ?? 'Khách lẻ' }}</strong>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-muted">Email:</span>
                            <span>{{ $order->user->email ?? $order->email ?? 'Chưa cập nhật' }}</span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-muted">Số điện thoại:</span>
                            <span>{{ $order->phone ?? $order->user->phone ?? 'N/A' }}</span>
                        </li>
                        <li class="list-group-item px-0 border-0">
                            <span class="text-muted d-block mb-1">Địa chỉ giao hàng:</span>
                            <strong>{{ $order->address ?? 'Chưa nhập địa chỉ' }}</strong>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-primary">
                        <i class="bi bi-credit-card me-2"></i>Thanh Toán & Trạng Thái
                    </h6>
                </div>
                <div class="card-body pt-0">
                    <div class="mb-3">
                        <label class="form-label text-muted small">Phương thức thanh toán:</label>
                        <div>
                            <span class="badge bg-success fs-6">{{ strtoupper($order->payment_method ?? 'COD') }}</span>
                        </div>
                    </div>

                    <!-- Form cập nhật trạng thái nhanh -->
                    <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <label class="form-label text-muted small">Cập nhật trạng thái đơn:</label>
                        <select name="status" class="form-select form-select-sm fw-bold mb-3" onchange="this.form.submit()">
                            <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>⏳ Chờ xử lý</option>
                            <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>🚚 Đang xử lý</option>
                            <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>✅ Hoàn thành</option>
                            <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>❌ Đã hủy</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>

        <!-- Danh sách sản phẩm đã đặt -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-primary">
                        <i class="bi bi-box-seam me-2"></i>Sản Phẩm Trong Đơn
                    </h6>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light small text-muted">
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th class="text-center">Đơn giá</th>
                                    <th class="text-center">Số lượng</th>
                                    <th class="text-end">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($order->items ?? [] as $item)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $item->product->name ?? $item->product_name ?? 'Sản phẩm #'.$item->product_id }}</div>
                                        </td>
                                        <td class="text-center">{{ number_format($item->price, 0, ',', '.') }} VNĐ</td>
                                        <td class="text-center fw-bold">x{{ $item->quantity }}</td>
                                        <td class="text-end fw-bold text-danger">
                                            {{ number_format($item->price * $item->quantity, 0, ',', '.') }} VNĐ
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">Không tìm thấy chi tiết sản phẩm.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="border-top">
                                <tr>
                                    <td colspan="3" class="text-end fw-bold fs-6">Tổng tiền thanh toán:</td>
                                    <td class="text-end fw-bold fs-5 text-danger">
                                        {{ number_format($order->total_price ?? $order->total ?? 0, 0, ',', '.') }} VNĐ
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection