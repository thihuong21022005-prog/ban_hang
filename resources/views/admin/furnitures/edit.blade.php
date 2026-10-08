@extends('layout.admin')

@php
    $variants = old('variants', $furniture->variants ?? []);
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0">Sửa Sản Phẩm: <span class="text-primary">{{ $furniture->name }}</span></h4>
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
        <form action="{{ route('admin.products.update', $furniture->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tên sản phẩm <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $furniture->name) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Danh mục <span class="text-danger">*</span></label>
                    <select name="category_id" class="form-select" required>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $furniture->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->type ?? 'DM' }} - {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Giá bán chung (đ) <span class="text-danger">*</span></label>
                    <input type="number" name="price" class="form-control" value="{{ old('price', $furniture->price) }}" min="0" step="any" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Số lượng tồn kho mặc định <span class="text-danger">*</span></label>
                    <input type="number" name="quantity" id="default-quantity" class="form-control" value="{{ old('quantity', $furniture->quantity ?? 0) }}" min="0" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Ảnh đại diện mới (nếu muốn đổi)</label>
                    <input type="file" name="main_image" class="form-control" accept="image/*">
                    @if ($furniture->image_url)
                        <div class="mt-2">
                            <img src="{{ $furniture->image_url }}"
                                 class="rounded border" width="60" height="60" style="object-fit: cover;" alt="{{ $furniture->name }}">
                        </div>
                    @endif
                </div>

                <!-- KHỐI BIẾN THỂ KÍCH THƯỚC -->
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
                                * Xóa dòng kích thước nếu không muốn giữ lại. Để trống giá riêng nếu muốn tự động lấy theo "Giá bán chung".
                            </small>

                            <div id="variant-container">
                                @foreach ($variants as $index => $variant)
                                    @php
                                        $vId = is_array($variant) ? ($variant['id'] ?? '') : $variant->id;
                                        $vSize = is_array($variant) ? ($variant['size'] ?? '') : $variant->size;
                                        $vQty = is_array($variant) ? ($variant['quantity'] ?? 0) : $variant->quantity;
                                        $vPrice = is_array($variant) ? ($variant['price'] ?? '') : $variant->price;
                                    @endphp
                                    <div class="row g-2 mb-2 variant-row align-items-center">
                                        @if ($vId)
                                            <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $vId }}">
                                        @endif
                                        <div class="col-md-4">
                                            <input type="text" name="variants[{{ $index }}][size]" class="form-control" placeholder="Tên kích thước (VD: 1m6 x 2m)" value="{{ $vSize }}" required>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="variants[{{ $index }}][quantity]" class="form-control variant-quantity" placeholder="Số lượng kho" value="{{ $vQty }}" min="0" required>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="number" name="variants[{{ $index }}][price]" class="form-control" placeholder="Giá riêng (bỏ trống nếu dùng giá chung)" value="{{ $vPrice }}" min="0" step="any">
                                        </div>
                                        <div class="col-md-1 text-end">
                                            <button type="button" class="btn btn-outline-danger remove-variant-btn">Xóa</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 mt-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="isFeatured" {{ old('is_featured', $furniture->is_featured) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="isFeatured">Sản phẩm nổi bật (Trang chủ)</label>
                    </div>
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-semibold">Mô tả sản phẩm</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Nhập mô tả chi tiết sản phẩm...">{{ old('description', $furniture->description ?? '') }}</textarea>
                </div>
            </div>

            <div class="text-end mt-4">
                <button type="submit" class="btn btn-primary px-4">Cập Nhật Sản Phẩm</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let variantIndex = {{ count($variants) }};
    const container = document.getElementById('variant-container');
    const addBtn = document.getElementById('add-variant-btn');
    const defaultQuantityInput = document.getElementById('default-quantity');

    // Tự động cập nhật số lượng tồn kho mặc định & khóa/mở ô nhập
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

    // Thêm kích thước mới
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
                    <input type="number" name="variants[${variantIndex}][price]" class="form-control" placeholder="Giá riêng (bỏ trống nếu dùng giá chung)" min="0" step="any">
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

    // Xóa kích thước
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