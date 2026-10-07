@extends('layout.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0">Thêm Sản Phẩm Mới</h4>
    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary btn-sm">Quay lại danh sách</a>
</div>

@if ($errors->any())
    <div class="alert alert-danger border-0 shadow-sm mb-3">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tên sản phẩm <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="Nhập tên sản phẩm">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Danh mục <span class="text-danger">*</span></label>
                    <select name="category_id" class="form-select" required>
                        <option value="">-- Chọn danh mục --</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->type }} - {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Giá bán chung (đ) <span class="text-danger">*</span></label>
                    <input type="number" name="price" class="form-control" value="{{ old('price') }}" min="0" required placeholder="0">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Số lượng tồn kho mặc định <span class="text-danger">*</span></label>
                    <input type="number" name="quantity" id="default-quantity" class="form-control" value="{{ old('quantity', 1) }}" min="0" required placeholder="Nhập số lượng">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Ảnh đại diện</label>
                    <input type="file" name="main_image" class="form-control" accept="image/*">
                </div>

                <!-- PHẦN THÊM BIẾN THỂ KÍCH THƯỚC (GIỐNG SHOPEE) -->
                <div class="col-md-12 mt-4">
                    <div class="card bg-light border">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold m-0 text-primary">
                                <i class="bi bi-layers-fill me-1"></i> Danh sách kích thước / Phân loại (Tùy chọn)
                            </h6>
                            <button type="button" id="add-variant-btn" class="btn btn-sm btn-outline-primary">
                                + Thêm kích thước
                            </button>
                        </div>
                        <div class="card-body">
                            <small class="text-muted d-block mb-3">
                                * Nếu sản phẩm có nhiều kích thước (VD: 1m6x2m, 1m8x2m), bấm "+ Thêm kích thước". Nếu bỏ trống giá riêng, hệ thống sẽ tự lấy theo "Giá bán chung".
                            </small>

                            <div id="variant-container">
                                @if (old('variants'))
                                    @foreach (old('variants') as $index => $variant)
                                        <div class="row g-2 mb-2 variant-row align-items-center">
                                            <div class="col-md-4">
                                                <input type="text" name="variants[{{ $index }}][size]" class="form-control" placeholder="Tên kích thước (VD: 1m6 x 2m)" value="{{ $variant['size'] ?? '' }}" required>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="number" name="variants[{{ $index }}][quantity]" class="form-control variant-quantity" placeholder="Số lượng kho" value="{{ $variant['quantity'] ?? 0 }}" min="0" required>
                                            </div>
                                            <div class="col-md-4">
                                                <input type="number" name="variants[{{ $index }}][price]" class="form-control" placeholder="Giá riêng (bỏ trống nếu dùng giá chung)" value="{{ $variant['price'] ?? '' }}" min="0">
                                            </div>
                                            <div class="col-md-1 text-end">
                                                <button type="button" class="btn btn-outline-danger remove-variant-btn">Xóa</button>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 mt-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="isFeatured" {{ old('is_featured') ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="isFeatured">Sản phẩm nổi bật (Trang chủ)</label>
                    </div>
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-semibold">Mô tả sản phẩm</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Nhập mô tả chi tiết sản phẩm...">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="text-end mt-4">
                <button type="submit" class="btn btn-primary px-4">Lưu Sản Phẩm</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let variantIndex = {{ old('variants') ? count(old('variants')) : 0 }};
    const container = document.getElementById('variant-container');
    const addBtn = document.getElementById('add-variant-btn');
    const defaultQuantityInput = document.getElementById('default-quantity');

    // Tự động cập nhật tổng tồn kho & khóa/mở ô nhập mặc định
    function updateStockLogic() {
        const variantQuantityInputs = document.querySelectorAll('.variant-quantity');

        if (variantQuantityInputs.length > 0) {
            let totalStock = 0;
            variantQuantityInputs.forEach(input => {
                totalStock += parseInt(input.value) || 0;
            });

            if (defaultQuantityInput) {
                defaultQuantityInput.value = totalStock;
                defaultQuantityInput.readOnly = true;
                defaultQuantityInput.style.backgroundColor = '#e9ecef';
                defaultQuantityInput.style.cursor = 'not-allowed';
            }
        } else {
            if (defaultQuantityInput) {
                defaultQuantityInput.readOnly = false;
                defaultQuantityInput.style.backgroundColor = '#ffffff';
                defaultQuantityInput.style.cursor = 'text';
            }
        }
    }

    updateStockLogic();

    // Thêm dòng kích thước mới
    if (addBtn) {
        addBtn.addEventListener('click', function () {
            const row = document.createElement('div');
            row.className = 'row g-2 mb-2 variant-row align-items-center';
            row.innerHTML = `
                <div class="col-md-4">
                    <input type="text" name="variants[${variantIndex}][size]" class="form-control" placeholder="Tên kích thước (VD: 1m6 x 2m)" required>
                </div>
                <div class="col-md-3">
                    <input type="number" name="variants[${variantIndex}][quantity]" class="form-control variant-quantity" placeholder="Số lượng kho" value="10" min="0" required>
                </div>
                <div class="col-md-4">
                    <input type="number" name="variants[${variantIndex}][price]" class="form-control" placeholder="Giá riêng (bỏ trống nếu dùng giá chung)" min="0">
                </div>
                <div class="col-md-1 text-end">
                    <button type="button" class="btn btn-outline-danger remove-variant-btn">Xóa</button>
                </div>
            `;
            container.appendChild(row);
            variantIndex++;
            updateStockLogic();
        });
    }

    // Lắng nghe gõ số lượng ở từng dòng kích thước
    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('variant-quantity')) {
            updateStockLogic();
        }
    });

    // Xóa dòng kích thước
    if (container) {
        container.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-variant-btn') || e.target.closest('.remove-variant-btn')) {
                const row = e.target.closest('.variant-row');
                if (row) {
                    row.remove();
                    updateStockLogic();
                }
            }
        });
    }
});
</script>
@endsection