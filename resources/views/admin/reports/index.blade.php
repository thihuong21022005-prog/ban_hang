@extends('layout.admin')
@section('title', 'Báo cáo doanh thu')

@section('content')
<style>
    /* Card thống kê đơn giản, viền màu nhấn nhẹ */
    .card-stat {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        transition: all 0.2s ease;
    }
    .card-stat:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05) !important;
    }
    .icon-box {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
    }
    
    /* Bảng dữ liệu viền bo tròn */
    .card-table {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
    }
</style>

<div class="container-fluid px-4 py-3">
    <!-- Tiêu đề & Điều hướng -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1 text-dark">📊 Báo cáo doanh thu</h3>
            <p class="text-muted small mb-0">Doanh thu tính theo đơn hàng đã thanh toán, không tính đơn bị hủy/hoàn.</p>
        </div>
        <div class="btn-group" role="group">
            <a href="{{ route('admin.reports.index') }}" class="btn btn-primary active btn-sm px-3 fw-semibold">
                <i class="bi bi-table me-1"></i> Bảng số liệu
            </a>
            <a href="{{ route('admin.reports.charts') }}" class="btn btn-outline-primary btn-sm px-3 fw-semibold">
                <i class="bi bi-bar-chart-line me-1"></i> Biểu đồ
            </a>
        </div>
    </div>

    <!-- 3 Thẻ Thống Kê Tổng Quan (Viền màu Accent) -->
    <div class="row g-3 mb-4">
        <!-- Đơn hàng -->
        <div class="col-md-4">
            <div class="card card-stat bg-white p-3 shadow-sm border-start border-4 border-primary h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase">Tổng số đơn hàng</span>
                        <h3 class="fw-bold mb-0 mt-1 text-dark">{{ number_format($totalOrders) }}</h3>
                    </div>
                    <div class="icon-box bg-primary-subtle text-primary">
                        <i class="bi bi-bag-check-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Khách hàng -->
        <div class="col-md-4">
            <div class="card card-stat bg-white p-3 shadow-sm border-start border-4 border-info h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase">Tổng số khách hàng</span>
                        <h3 class="fw-bold mb-0 mt-1 text-dark">{{ number_format($totalCustomers) }}</h3>
                    </div>
                    <div class="icon-box bg-info-subtle text-info">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Doanh thu -->
        <div class="col-md-4">
            <div class="card card-stat bg-white p-3 shadow-sm border-start border-4 border-success h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase">Tổng doanh thu</span>
                        <h3 class="fw-bold mb-0 mt-1 text-success">{{ number_format($totalRevenue, 0, ',', '.') }} <small class="fs-6">đ</small></h3>
                    </div>
                    <div class="icon-box bg-success-subtle text-success">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bảng: Doanh thu theo danh mục -->
    <div class="card card-table bg-white shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-folder2-open me-2 text-primary"></i>Doanh thu theo danh mục
            </h6>
            <span class="text-muted small">Không gồm phí vận chuyển</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Danh mục</th>
                        <th class="text-end">Số lượng bán</th>
                        <th class="text-end pe-3">Doanh thu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categoryRevenue as $revenue)
                    <tr>
                        <td class="ps-3 fw-medium text-dark">
                            {{ $revenue->category_name ?? ('Danh mục #'.$revenue->category_id) }}
                        </td>
                        <td class="text-end">
                            <span class="badge bg-light text-dark border px-2 py-1">{{ number_format($revenue->total_qty) }}</span>
                        </td>
                        <td class="text-end pe-3 fw-bold text-success">
                            {{ number_format($revenue->total_revenue, 0, ',', '.') }} đ
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">Chưa có dữ liệu.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bảng: Theo Ngày / Tháng / Năm -->
    @foreach([
        ['Doanh thu theo ngày', 'Ngày', 'date', $revenueByDate, 'd/m/Y', 'bi-calendar-event', 'text-primary'],
        ['Doanh thu theo tháng', 'Tháng', 'month', $revenueByMonth, 'm/Y', 'bi-calendar-month', 'text-warning'],
        ['Doanh thu theo năm', 'Năm', 'year', $revenueByYear, null, 'bi-calendar-range', 'text-info'],
    ] as [$title, $label, $field, $rows, $format, $icon, $iconColor])
    <div class="card card-table bg-white shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi {{ $icon }} me-2 {{ $iconColor }}"></i>{{ $title }}
            </h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">{{ $label }}</th>
                        <th class="text-end">Số đơn đã thanh toán</th>
                        <th class="text-end pe-3">Doanh thu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $revenue)
                    <tr>
                        <td class="ps-3 text-secondary fw-medium">
                            {{ $format ? \Carbon\Carbon::parse($revenue->{$field}.($field === 'month' ? '-01' : ''))->format($format) : $revenue->{$field} }}
                        </td>
                        <td class="text-end">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                {{ number_format($revenue->order_count) }} đơn
                            </span>
                        </td>
                        <td class="text-end pe-3 fw-bold text-success">
                            {{ number_format($revenue->total_revenue, 0, ',', '.') }} đ
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">Chưa có dữ liệu.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
</div>
@endsection