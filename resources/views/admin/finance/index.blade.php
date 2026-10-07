@extends('layout.admin') {{-- Thay bằng layout admin tương ứng của dự án --}}

@section('content')
<div class="container-fluid py-3">
    <h4 class="fw-bold mb-1">Thống kê tài chính</h4>
    <p class="text-muted fs-7 mb-3">Tổng hợp giá trị thanh toán theo trạng thái và phương thức</p>

    <!-- Tab chuyển đổi -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link active fw-semibold" href="{{ route('admin.finance.index') }}">Thống kê chỉ số</a>
        </li>
        <li class="nav-item">
            <a class="nav-link fw-semibold" href="{{ route('admin.finance.transactions') }}">Giao dịch thanh toán</a>
        </li>
    </ul>

    <!-- Form Bộ lọc -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.finance.index') }}" method="GET" class="row g-3">
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

                <div class="col-md-3">
                    <label class="form-label fs-7 text-muted">Số tiền từ (đ)</label>
                    <input type="number" name="min_amount" class="form-control" placeholder="Không giới hạn" value="{{ $filters['min_amount'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-7 text-muted">Số tiền đến (đ)</label>
                    <input type="number" name="max_amount" class="form-control" placeholder="Không giới hạn" value="{{ $filters['max_amount'] ?? '' }}">
                </div>
                <div class="col-md-3">
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

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">Áp dụng bộ lọc</button>
                    <a href="{{ route('admin.finance.index') }}" class="btn btn-outline-secondary px-4">Xóa bộ lọc</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Thông báo kết quả -->
    <p class="text-muted fs-7 mb-3">
        Có <strong>{{ $summary->order_count ?? 0 }}</strong> đơn phù hợp. Số tiền bao gồm phí vận chuyển, thống kê theo ngày tạo đơn trên toàn bộ kết quả lọc.
    </p>

    <!-- Grid Thống kê các trạng thái -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 p-3">
                <span class="text-muted fs-7">Tổng giá trị đơn hàng</span>
                <h4 class="fw-bold text-dark mt-2 mb-1">{{ number_format($summary->total_amount ?? 0, 0, ',', '.') }} đ</h4>
                <small class="text-muted">{{ $summary->order_count ?? 0 }} đơn (bao gồm đơn đã hủy)</small>
            </div>
        </div>

        @foreach($statuses as $key => $name)
        @php
            $item = $statusTotals->get($key);
        @endphp
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 p-3">
                <span class="text-muted fs-7">{{ $name }}</span>
                <h4 class="fw-bold text-dark mt-2 mb-1">{{ number_format($item->total_amount ?? 0, 0, ',', '.') }} đ</h4>
                <small class="text-muted">{{ $item->order_count ?? 0 }} đơn</small>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Bảng Thống kê theo phương thức -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h6 class="fw-bold m-0">Thống kê theo phương thức</h6>
        </div>
        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Phương thức</th>
                        <th>Số đơn</th>
                        <th>Tổng giá trị</th>
                        <th>Đã thanh toán</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($methods as $key => $name)
                    @php
                        $mItem = $methodTotals->get($key);
                    @endphp
                    <tr>
                        <td class="fw-semibold">{{ $name }}</td>
                        <td>{{ $mItem->order_count ?? 0 }}</td>
                        <td>{{ number_format($mItem->total_amount ?? 0, 0, ',', '.') }} đ</td>
                        <td class="text-success fw-semibold">{{ number_format($mItem->paid_amount ?? 0, 0, ',', '.') }} đ</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection