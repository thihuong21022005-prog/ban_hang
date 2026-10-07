@extends('layout.admin')
@section('title', 'Biểu đồ báo cáo doanh thu')

@section('content')
<style>
    .chart-wrap {
        position: relative;
        height: 300px;
        width: 100%;
    }
</style>

<div class="container-fluid py-3">
    <!-- Header & Chuyển đổi Bảng / Biểu đồ -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">Biểu đồ báo cáo doanh thu</h4>
            <p class="text-muted small mb-0">Thống kê trực quan doanh thu theo các tiêu chí</p>
        </div>
        <div class="btn-group bg-white p-1 rounded-3 border shadow-sm">
            <a class="btn btn-sm btn-light text-secondary border-0 fw-semibold px-3 rounded-2" href="{{ route('admin.reports.index') }}">
                <i class="bi bi-table me-1"></i> Bảng số liệu
            </a>
            <a class="btn btn-sm btn-primary border-0 fw-semibold px-3 rounded-2 active shadow-sm" href="{{ route('admin.reports.charts') }}">
                <i class="bi bi-bar-chart-fill me-1"></i> Biểu đồ
            </a>
        </div>
    </div>

    <div id="report-chart-error" class="alert alert-warning d-none" role="alert">
        Không tải được thư viện biểu đồ. Xem tại <a href="{{ route('admin.reports.index') }}" class="alert-link">Bảng số liệu</a>.
    </div>

    <!-- Lưới biểu đồ -->
    <div class="row g-4">
        <!-- 1. Doanh thu theo danh mục -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-0 fw-semibold text-dark">
                    <i class="bi bi-folder2-open me-2 text-primary"></i>Doanh thu theo danh mục
                </div>
                <div class="card-body chart-wrap">
                    <canvas id="categoryRevenueChart"></canvas>
                </div>
            </div>
        </div>

        <!-- 2. Doanh thu theo 30 ngày -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-0 fw-semibold text-dark">
                    <i class="bi bi-calendar-event me-2 text-primary"></i>Doanh thu theo ngày (30 ngày)
                </div>
                <div class="card-body chart-wrap">
                    <canvas id="revenueByDateChart"></canvas>
                </div>
            </div>
        </div>

        <!-- 3. Doanh thu theo 12 tháng -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-0 fw-semibold text-dark">
                    <i class="bi bi-calendar-month me-2 text-primary"></i>Doanh thu theo tháng (12 tháng)
                </div>
                <div class="card-body chart-wrap">
                    <canvas id="revenueByMonthChart"></canvas>
                </div>
            </div>
        </div>

        <!-- 4. Doanh thu theo năm -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-0 fw-semibold text-dark">
                    <i class="bi bi-calendar3 me-2 text-primary"></i>Doanh thu theo năm
                </div>
                <div class="card-body chart-wrap">
                    <canvas id="revenueByYearChart"></canvas>
                </div>
            </div>
        </div>

        <!-- 5. Doanh thu theo phương thức thanh toán -->
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0 fw-semibold text-dark">
                    <i class="bi bi-credit-card me-2 text-primary"></i>Doanh thu theo phương thức thanh toán
                </div>
                <div class="card-body chart-wrap" style="height: 320px;">
                    <canvas id="revenueByPaymentMethodChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="report-chart-data" hidden data-chart-data="{{ json_encode([
    'catLabels' => $catLabels ?? [],
    'catRevenue' => $catRevenue ?? [],
    'revDateLabels' => $revDateLabels ?? [],
    'revDateData' => $revDateData ?? [],
    'revMonthLabels' => $revMonthLabels ?? [],
    'revMonthData' => $revMonthData ?? [],
    'revYearLabels' => $revYearLabels ?? [],
    'revYearData' => $revYearData ?? [],
    'paymentMethodLabels' => $paymentMethodLabels ?? [],
    'paymentMethodRevenue' => $paymentMethodRevenue ?? [],
]) }}"></div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
window.addEventListener('DOMContentLoaded', () => {
    if (typeof Chart === 'undefined') {
        document.getElementById('report-chart-error').classList.remove('d-none');
        return;
    }

    const reportData = JSON.parse(document.getElementById('report-chart-data').dataset.chartData);
    
    const primaryColor = '#4f46e5';
    const primaryBg = 'rgba(79, 70, 229, 0.12)';
    const formatVND = (val) => new Intl.NumberFormat('vi-VN').format(val) + ' đ';

    const defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            x: { grid: { display: false } },
            y: { 
                beginAtZero: true,
                ticks: { callback: (val) => formatVND(val) }
            }
        }
    };
    
    // Hàm tạo biểu đồ cột / đường đơn giản
    const mk = (el, type, labels, data, label) => new Chart(el, {
        type, 
        data: { 
            labels, 
            datasets: [{ 
                label, 
                data, 
                backgroundColor: type === 'line' ? primaryBg : primaryColor,
                borderColor: primaryColor,
                borderWidth: 2,
                fill: type === 'line', 
                tension: 0.35,
                borderRadius: type === 'bar' ? 4 : 0
            }] 
        },
        options: defaultOptions
    });

    mk(document.getElementById('categoryRevenueChart'), 'bar', reportData.catLabels, reportData.catRevenue.map(Number), 'Doanh thu (VNĐ)');
    mk(document.getElementById('revenueByDateChart'), 'line', reportData.revDateLabels, reportData.revDateData.map(Number), 'Doanh thu (VNĐ)');
    mk(document.getElementById('revenueByMonthChart'), 'bar', reportData.revMonthLabels, reportData.revMonthData.map(Number), 'Doanh thu (VNĐ)');
    mk(document.getElementById('revenueByYearChart'), 'bar', reportData.revYearLabels, reportData.revYearData.map(Number), 'Doanh thu (VNĐ)');

    // Biểu đồ tròn cho phương thức thanh toán
    new Chart(document.getElementById('revenueByPaymentMethodChart'), {
        type: 'doughnut',
        data: {
            labels: reportData.paymentMethodLabels,
            datasets: [{ 
                label: 'Doanh thu (VNĐ)', 
                data: reportData.paymentMethodRevenue.map(Number),
                backgroundColor: ['#d946ef', '#10b981', '#3b82f6', '#f59e0b']
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { padding: 15 } }
            },
            cutout: '65%'
        }
    });
});
</script>
@endsection