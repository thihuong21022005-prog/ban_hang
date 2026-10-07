@extends('layout.admin')

@section('content')
<div class="container-fluid py-3">
    <h4 class="fw-bold mb-1">Giao dịch thanh toán</h4>
    <p class="text-muted fs-7 mb-3">Tra cứu thanh toán theo đơn hàng và cập nhật trạng thái COD</p>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Tab chuyển đổi -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link fw-semibold" href="{{ route('admin.finance.index') }}">Thống kê chỉ số</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active fw-semibold" href="{{ route('admin.finance.transactions') }}">Giao dịch thanh toán</a>
        </li>
    </ul>

    <!-- Form Bộ Lọc -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.finance.transactions') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fs-7 text-muted">Tìm đơn hàng</label>
                    <input type="text" name="search" class="form-control" placeholder="Mã đơn, tên hoặc số điện thoại" value="{{ $filters['search'] ?? '' }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fs-7 text-muted">Từ ngày tạo đơn</label>
                    <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fs-7 text-muted">Đến ngày tạo đơn</label>
                    <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label fs-7 text-muted">Số tiền từ (đ)</label>
                    <input type="number" name="min_amount" class="form-control" placeholder="Không giới hạn" value="{{ $filters['min_amount'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fs-7 text-muted">Số tiền đến (đ)</label>
                    <input type="number" name="max_amount" class="form-control" placeholder="Không giới hạn" value="{{ $filters['max_amount'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fs-7 text-muted">Phương thức</label>
                    <select name="gateway" class="form-select">
                        <option value="">Tất cả</option>
                        @foreach($methods as $key => $name)
                            <option value="{{ $key }}" {{ ($filters['gateway'] ?? '') === $key ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-7 text-muted">Trạng thái thanh toán</label>
                    <select name="payment_status" class="form-select">
                        <option value="">Tất cả</option>
                        @foreach($statuses as $key => $name)
                            <option value="{{ $key }}" {{ ($filters['payment_status'] ?? '') === $key ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-7 text-muted">Sắp xếp</label>
                    <select name="sort" class="form-select">
                        <option value="newest" {{ ($filters['sort'] ?? '') === 'newest' ? 'selected' : '' }}>Mới nhất</option>
                        <option value="oldest" {{ ($filters['sort'] ?? '') === 'oldest' ? 'selected' : '' }}>Cũ nhất</option>
                        <option value="amount_desc" {{ ($filters['sort'] ?? '') === 'amount_desc' ? 'selected' : '' }}>Số tiền giảm dần</option>
                        <option value="amount_asc" {{ ($filters['sort'] ?? '') === 'amount_asc' ? 'selected' : '' }}>Số tiền tăng dần</option>
                    </select>
                </div>

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">Áp dụng bộ lọc</button>
                    <a href="{{ route('admin.finance.transactions') }}" class="btn btn-outline-secondary px-4">Xóa bộ lọc</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Danh Sách Bảng Giao Dịch -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h6 class="fw-bold m-0">Danh sách giao dịch ({{ $orders->total() }} đơn)</h6>
            <small class="text-muted d-block mt-1">COD: Xác nhận thu tiền hoặc thất bại; đơn đã thu tiền có thể chuyển sang chờ hoàn tiền với xác nhận đã hoàn tiền.</small>
        </div>
        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Đơn hàng</th>
                        <th>Khách hàng</th>
                        <th>Phương thức</th>
                        <th>Số tiền</th>
                        <th>Thanh toán</th>
                        <th width="220">Cập nhật COD</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td>
                            <strong class="text-primary">#{{ $order->id }}</strong>
                            <div class="text-muted fs-7">{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $order->name }}</div>
                            <div class="text-muted fs-7">{{ $order->phone }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                {{ $methods[$order->gateway] ?? 'Chưa xác định' }}
                            </span>
                        </td>
                        <td class="fw-bold text-dark">
                            {{ number_format($order->total_price, 0, ',', '.') }} đ
                        </td>
                        <td>
                            <span class="badge 
                                @if($order->payment_status === 'paid') bg-success 
                                @elseif(in_array($order->payment_status, ['pending', 'initiated'])) bg-warning text-dark 
                                @else bg-danger @endif">
                                {{ $statuses[$order->payment_status] ?? $order->payment_status }}
                            </span>
                        </td>
                        <td>
                            @if($order->gateway === 'cod')
                                <form action="{{ route('admin.finance.update-status', $order->id) }}" method="POST" class="d-flex gap-1">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="current_payment_status" value="{{ $order->payment_status }}">
                                    <input type="hidden" name="current_order_status" value="{{ $order->status }}">
                                    <input type="hidden" name="current_payment_id" value="{{ $order->payment_id ?? 0 }}">

                                    <select name="payment_status" class="form-select form-select-sm">
                                        @php
                                            $allowed = $codTransitions[$order->payment_status] ?? [$order->payment_status];
                                        @endphp
                                        @foreach($allowed as $statusKey)
                                            <option value="{{ $statusKey }}" {{ $order->payment_status === $statusKey ? 'selected' : '' }}>
                                                {{ $statuses[$statusKey] ?? $statusKey }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary">Lưu</button>
                                </form>
                            @else
                                <span class="text-muted fs-7">Tự động ({{ strtoupper($order->gateway) }})</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            Không có đơn hàng phù hợp với bộ lọc
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white py-3">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection