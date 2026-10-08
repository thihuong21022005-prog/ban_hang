@extends('layout.admin')

@section('content')
<h4 class="fw-bold mb-4">Tổng Quan</h4>

<!-- CSS Hiệu ứng Hover cho các thẻ Thống Kê -->
<style>
    .stat-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        cursor: pointer;
    }
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
</style>

<!-- KHỐI THỐNG KÊ (ĐÃ THÊM LINK BẤM CHUYỂN TRANG) -->
<div class="row g-3 mb-4">
    <!-- 1. Tổng Sản Phẩm -->
    <div class="col-md-3">
        <a href="{{ route('admin.products.index') }}" class="text-decoration-none d-block">
            <div class="card border-0 shadow-sm bg-primary text-white p-3 stat-card">
                <div class="text-white-50">Tổng Sản Phẩm</div>
                <h2 class="fw-bold m-0 mt-2 text-white">{{ $totalProducts }}</h2>
            </div>
        </a>
    </div>

    <!-- 2. Sản Phẩm Giường -->
    <div class="col-md-3">
        <a href="{{ route('admin.products.index', ['category' => 'Giường']) }}" class="text-decoration-none d-block">
            <div class="card border-0 shadow-sm bg-info text-dark p-3 stat-card">
                <div class="text-dark">Sản Phẩm Giường</div>
                <h2 class="fw-bold m-0 mt-2 text-dark">{{ $totalBeds }}</h2>
            </div>
        </a>
    </div>

    <!-- 3. Sản Phẩm Tủ -->
    <div class="col-md-3">
        <a href="{{ route('admin.products.index', ['category' => 'Tủ']) }}" class="text-decoration-none d-block">
            <div class="card border-0 shadow-sm bg-warning text-dark p-3 stat-card">
                <div class="text-dark">Sản Phẩm Tủ</div>
                <h2 class="fw-bold m-0 mt-2 text-dark">{{ $totalWardrobes }}</h2>
            </div>
        </a>
    </div>

    <!-- 4. Danh Mục Phân Loại -->
    <div class="col-md-3">
        <a href="{{ route('admin.categories.index') }}" class="text-decoration-none d-block">
            <div class="card border-0 shadow-sm bg-success text-white p-3 stat-card">
                <div class="text-white-50">Danh Mục Phân Loại</div>
                <h2 class="fw-bold m-0 mt-2 text-white">{{ $totalCategories }}</h2>
            </div>
        </a>
    </div>
</div>

<!-- TỰA ĐỀ DANH SÁCH SẢN PHẨM -->
<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="fw-bold m-0"><i class="bi bi-grid-fill text-primary me-2"></i>Danh Sách Sản Phẩm</h5>
    <span class="text-muted small">Bấm vào sản phẩm để xem chi tiết</span>
</div>

<!-- LƯỚI SẢN PHẨM Ô VUÔNG -->
<div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-6 g-3">
    @forelse ($furnitures as $item)
    <div class="col">
        <div class="card h-100 border-0 shadow-sm overflow-hidden position-relative" 
             style="transition: transform 0.2s; cursor: pointer;"
             data-bs-toggle="modal" 
             data-bs-target="#productModal{{ $item->id }}"
             onmouseover="this.style.transform='scale(1.03)'" 
             onmouseout="this.style.transform='scale(1)'">
            
            @if ($item->is_featured)
                <span class="badge bg-danger position-absolute top-0 start-0 m-2 z-1" style="font-size: 10px;">Yêu thích</span>
            @endif

            <div class="w-100 bg-light position-relative" style="aspect-ratio: 1 / 1; overflow: hidden;">
                @if ($item->image_url)
                    <img src="{{ $item->image_url }}" class="w-100 h-100" style="object-fit: cover;" alt="{{ $item->name }}">
                @else
                    <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted">
                        <i class="bi bi-image fs-3"></i>
                    </div>
                @endif
            </div>

            <div class="card-body p-2 d-flex flex-column justify-content-between">
                <div>
                    <div class="fw-semibold text-dark text-truncate mb-1" title="{{ $item->name }}" style="font-size: 14px;">
                        {{ $item->name }}
                    </div>
                    <span class="badge bg-light text-secondary border mb-2" style="font-size: 10px;">
                        {{ optional($item->category)->name ?? 'Chưa rõ' }}
                    </span>
                </div>

                <div class="text-danger fw-bold" style="font-size: 15px;">
                    <span style="font-size: 11px; text-decoration: underline;">đ</span>{{ number_format($item->price ?? 0, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <!-- POPUP CHI TIẾT SẢN PHẨM -->
        <div class="modal fade" id="productModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold text-primary"><i class="bi bi-info-circle me-2"></i>Thông Tin Chi Tiết</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-4">
                            <div class="col-md-5">
                                <div class="bg-light rounded border overflow-hidden" style="aspect-ratio: 1 / 1;">
                                    @if ($item->image_url)
                                        <img src="{{ $item->image_url }}" class="w-100 h-100" style="object-fit: cover;" alt="{{ $item->name }}">
                                    @else
                                        <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted">
                                            <i class="bi bi-image fs-1"></i>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-7 d-flex flex-column justify-content-between">
                                <div>
                                    <span class="badge bg-info text-dark mb-2">
                                        {{ optional($item->category)->type }} - {{ optional($item->category)->name }}
                                    </span>
                                    <h4 class="fw-bold mb-2">{{ $item->name }}</h4>
                                    
                                    <div class="p-3 bg-light rounded-3 mb-3 border">
                                        <div class="text-muted small">Giá bán chính thức:</div>
                                        <div class="text-danger fw-bold fs-3">
                                            {{ number_format($item->price ?? 0, 0, ',', '.') }} <span class="fs-6">VNĐ</span>
                                        </div>
                                    </div>

                                    <!-- KHỐI KÍCH THƯỚC / PHÂN LOẠI SẢN PHẨM -->
                                    <div class="mb-3">
                                        <label class="fw-bold text-secondary small mb-1"><i class="bi bi-layers me-1"></i>Danh sách kích thước & Phân loại:</label>
                                        @if ($item->variants && $item->variants->count() > 0)
                                            <div class="table-responsive border rounded">
                                                <table class="table table-sm table-striped align-middle text-center mb-0 small">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Kích thước</th>
                                                            <th>Giá bán</th>
                                                            <th>Tồn kho</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($item->variants as $variant)
                                                            <tr>
                                                                <td><span class="badge bg-info text-dark">{{ $variant->size }}</span></td>
                                                                <td class="fw-bold text-danger">{{ number_format($variant->price ?? $item->price, 0, ',', '.') }} đ</td>
                                                                <td><span class="badge bg-secondary">{{ $variant->quantity }}</span></td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="p-2 bg-light rounded text-muted small border">
                                                <em>Chưa có phân loại kích thước riêng (Áp dụng giá và tồn kho chung).</em>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- KHỐI MÔ TẢ SẢN PHẨM -->
                                    <div class="mb-3">
                                        <label class="fw-bold text-secondary small mb-1"><i class="bi bi-file-text me-1"></i>Mô tả sản phẩm:</label>
                                        <div class="p-2 bg-light rounded text-dark small border" style="max-height: 100px; overflow-y: auto; white-space: pre-line;">
                                            {{ $item->description ?? 'Chưa có mô tả cho sản phẩm này.' }}
                                        </div>
                                    </div>

                                    <ul class="list-group list-group-flush border-top border-bottom my-3">
                                        <li class="list-group-item px-0 d-flex justify-content-between bg-transparent">
                                            <span class="text-muted">Mã ID:</span>
                                            <span class="fw-semibold">#{{ $item->id }}</span>
                                        </li>
                                        <li class="list-group-item px-0 d-flex justify-content-between bg-transparent">
                                            <span class="text-muted">Nổi bật:</span>
                                            <span>
                                                @if ($item->is_featured)
                                                    <span class="badge bg-danger">Yêu thích</span>
                                                @else
                                                    <span class="badge bg-secondary">Thường</span>
                                                @endif
                                            </span>
                                        </li>
                                        <li class="list-group-item px-0 d-flex justify-content-between bg-transparent">
                                            <span class="text-muted">Trạng thái:</span>
                                            <span>
                                                @if (($item->quantity ?? $item->stock ?? 1) > 0)
                                                    <span class="badge bg-success">Còn hàng</span>
                                                @else
                                                    <span class="badge bg-danger">Hết hàng</span>
                                                @endif
                                            </span>
                                        </li>
                                    </ul>
                                </div>

                                <div class="text-end">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng cửa sổ</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    @empty
    <div class="col-12 w-100 text-center text-muted py-5">
        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
        Chưa có sản phẩm nào.
    </div>
    @endforelse
</div>
@endsection