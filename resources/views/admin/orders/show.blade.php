@extends('layout.admin')

@section('content')
<div class="container-fluid">
    <!-- Thông báo kết quả thao tác -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

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
            <!-- 1. THÔNG TIN KHÁCH HÀNG -->
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

            <!-- 2. BỔ SUNG: VẬN ĐƠN GIAO HÀNG (GHN) -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-primary">
                        <i class="bi bi-truck me-2"></i>Vận Đơn Giao Hàng (GHN)
                    </h6>
                </div>
                <div class="card-body pt-0">
                    @if(!empty($order->shipping_code))
                        <div class="alert alert-success border-0 p-3 mb-0 rounded-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-check-circle-fill text-success fs-5 me-2"></i>
                                <span class="fw-bold text-dark">Đã phát hành vận đơn!</span>
                            </div>
                            <div class="small text-muted mb-2">
                                Mã vận đơn: <strong class="text-primary fs-6">{{ $order->shipping_code }}</strong>
                            </div>
                            <a href="https://donhang.ghn.vn/?order_code={{ $order->shipping_code }}" 
                               target="_blank" 
                               class="btn btn-sm btn-outline-primary w-100 rounded-2 fw-semibold">
                                <i class="bi bi-box-arrow-up-right me-1"></i> Tra cứu đơn trên GHN
                            </a>
                        </div>
                    @else
                        <div class="p-3 bg-light rounded-3 text-center">
                            <p class="small text-warning fw-bold mb-2">
                                <i class="bi bi-exclamation-triangle me-1"></i>Chưa tạo vận đơn GHN
                            </p>
                            <form action="{{ route('admin.orders.createGhnOrder', $order->id) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="btn btn-primary btn-sm w-100 rounded-2 fw-bold py-2"
                                        onclick="return confirm('Xác nhận gửi thông tin đơn hàng này sang GHN để tạo vận đơn?')">
                                    📦 Tạo vận đơn GHN & Giao hàng
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 3. THANH TOÁN & TRẠNG THÁI -->
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
                            <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>📦 Đang chuẩn bị hàng</option>
                            <option value="shipping" {{ in_array($order->status, ['shipping', 'delivering']) ? 'selected' : '' }}>🚚 Giao cho ĐVVC</option>
                            <option value="completed" {{ in_array($order->status, ['completed', 'cod_paid']) ? 'selected' : '' }}>✅ Hoàn thành</option>
                            <option value="cancelled" {{ in_array($order->status, ['cancelled', 'canceled']) ? 'selected' : '' }}>❌ Đã hủy</option>
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
                                            <div class="fw-semibold text-dark">
                                                {{ $item->furniture_name ?? $item->product->name ?? $item->product_name ?? 'Sản phẩm #'.$item->product_id }}
                                            </div>
                                            @if(!empty($item->variant->size) || !empty($item->dimension))
                                                <span class="badge bg-light text-primary border me-1">
                                                    Size: {{ $item->variant->size ?? $item->dimension }}
                                                </span>
                                            @endif
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