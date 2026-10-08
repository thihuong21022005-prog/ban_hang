@extends('layout.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold m-0" style="color: #2a2265;">Chi Tiết Sản Phẩm</h3>
        <a class="btn btn-outline-secondary rounded-pill px-4" href="{{ route('welcome') }}">
            <i class="bi bi-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 p-4">
        <div class="row g-4">
            <!-- HÌNH ẢNH SẢN PHẨM -->
            <div class="col-md-5">
                <div class="bg-light rounded-3 p-2 text-center border">
                    <img src="{{ $furniture->image_url ?? 'https://placehold.co/400x300?text=No+Image' }}"
                         class="img-fluid rounded"
                         alt="{{ $furniture->name }}"
                         style="max-height: 380px; object-fit: cover;">
                </div>
            </div>

            <!-- THÔNG TIN & BỘ CHỌN MUA HÀNG -->
            <div class="col-md-7">
                <h2 class="fw-bold mb-2">{{ $furniture->name }}</h2>
                
                <div class="mb-3">
                    <span class="badge bg-light text-dark border px-3 py-2 fs-6">
                        Loại: {{ $furniture->category->name ?? $furniture->type ?? 'Nội thất' }}
                    </span>
                </div>

                <!-- HIỂN THỊ GIÁ TIỀN (TỰ ĐỘNG THAY ĐỔI THEO SIZE CHỌN) -->
                <div class="fs-3 fw-bold text-danger mb-3" id="display-price">
                    {{ number_format($furniture->price, 0, ',', '.') }} VNĐ
                </div>

                <p class="text-muted mb-4">
                    {{ $furniture->description ?? 'Chưa có mô tả cho sản phẩm này.' }}
                </p>

                <hr class="my-4">

                <!-- FORM CHỌN KÍCH THƯỚC & SỐ LƯỢNG -->
                <form action="{{ route('user.cart.add', $furniture->id) }}" method="POST">
                    @csrf

                    <!-- LƯU ID BIẾN THỂ VÀO FORM -->
                    <input type="hidden" name="variant_id" id="selected-variant-id" value="">

                    <!-- CHỌN KÍCH THƯỚC (NÚT BẤM DẠNG SHOPEE) -->
                    <div class="mb-4">
                        <label class="form-label fw-bold d-block mb-2">Chọn Kích Thước:</label>
                        
                        @if(isset($furniture->variants) && count($furniture->variants) > 0)
                            <div class="d-flex flex-wrap gap-2" id="variant-list">
                                @foreach($furniture->variants as $index => $variant)
                                    @php
                                        // Nếu variant không cài giá riêng thì lấy giá chung của sản phẩm
                                        $priceVal = $variant->price ? $variant->price : $furniture->price;
                                        $stockVal = $variant->quantity ?? 0;
                                    @endphp
                                    <input type="radio" 
                                           class="btn-check variant-radio" 
                                           name="selected_dimension" 
                                           id="size_var_{{ $variant->id }}" 
                                           value="{{ $variant->size }}"
                                           data-variant-id="{{ $variant->id }}"
                                           data-price-formatted="{{ number_format($priceVal, 0, ',', '.') }} VNĐ"
                                           data-stock="{{ $stockVal }}"
                                           {{ $index === 0 ? 'checked' : '' }} 
                                           autocomplete="off">
                                    <label class="btn btn-outline-primary rounded-pill px-3 py-2 fw-semibold" for="size_var_{{ $variant->id }}">
                                        {{ $variant->size }}
                                    </label>
                                @endforeach
                            </div>
                        @elseif(!empty($furniture->dimensions))
                            {{-- Trường hợp sản phẩm dùng chuỗi dimensions cũ --}}
                            <div class="d-flex flex-wrap gap-2">
                                @foreach(explode(',', $furniture->dimensions) as $index => $dim)
                                    @php $dimTrim = trim($dim); @endphp
                                    <input type="radio" class="btn-check" name="selected_dimension" id="size_old_{{ $index }}" value="{{ $dimTrim }}" {{ $index === 0 ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary rounded-pill px-3 py-2 fw-semibold" for="size_old_{{ $index }}">
                                        {{ $dimTrim }}
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <input type="hidden" name="selected_dimension" value="Tiêu chuẩn">
                            <span class="badge bg-secondary p-2">Kích thước tiêu chuẩn</span>
                        @endif
                    </div>

                    <!-- CHỌN SỐ LƯỢNG -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Số Lượng:</label>
                        <div class="d-flex align-items-center gap-3">
                            <div class="input-group" style="width: 140px;">
                                <button class="btn btn-outline-secondary" type="button" id="btn-minus">-</button>
                                <input type="number" id="quantity" name="quantity" class="form-control text-center fw-bold" value="1" min="1" max="{{ $furniture->quantity ?? $furniture->stock ?? 99 }}" required>
                                <button class="btn btn-outline-secondary" type="button" id="btn-plus">+</button>
                            </div>
                            <span class="text-muted small">
                                (Còn lại: <strong class="text-dark" id="stock-display">{{ $furniture->quantity ?? $furniture->stock ?? 99 }}</strong> sản phẩm)
                            </span>
                        </div>
                    </div>

                    <!-- NÚT MUA HÀNG -->
                    <button type="submit" id="add-to-cart-btn" class="btn btn-lg px-5 text-white rounded-3 shadow-sm" style="background: linear-gradient(135deg, #6f5cc3, #5a47a8); border: none;">
                        <i class="bi bi-cart-plus me-2"></i> Thêm Vào Giỏ Hàng
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPT CẬP NHẬT GIÁ TIỀN VÀ TỒN KHO TỰ ĐỘNG THEO SIZE -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const variantRadios = document.querySelectorAll('.variant-radio');
    const displayPrice = document.getElementById('display-price');
    const displayStock = document.getElementById('stock-display');
    const selectedVariantInput = document.getElementById('selected-variant-id');
    const qtyInput = document.getElementById('quantity');
    const btnPlus = document.getElementById('btn-plus');
    const btnMinus = document.getElementById('btn-minus');
    const addToCartBtn = document.getElementById('add-to-cart-btn');

    function updateVariantInformation() {
        const checkedRadio = document.querySelector('.variant-radio:checked');
        if (checkedRadio) {
            // 1. Cập nhật giá hiển thị
            displayPrice.textContent = checkedRadio.dataset.priceFormatted;
            
            // 2. Cập nhật số lượng kho hiển thị
            const stock = parseInt(checkedRadio.dataset.stock) || 0;
            displayStock.textContent = stock;
            
            // 3. Cập nhật Variant ID vào form ẩn
            selectedVariantInput.value = checkedRadio.dataset.variantId;
            
            // 4. Giới hạn max ô số lượng mua theo kho của size
            qtyInput.max = stock;

            // Kích hoạt hoặc vô hiệu hóa nút mua nếu hết hàng
            if (stock <= 0) {
                addToCartBtn.disabled = true;
                addToCartBtn.innerText = 'Hết hàng';
                qtyInput.value = 0;
            } else {
                addToCartBtn.disabled = false;
                addToCartBtn.innerHTML = '<i class="bi bi-cart-plus me-2"></i> Thêm Vào Giỏ Hàng';
                if (parseInt(qtyInput.value) <= 0 || parseInt(qtyInput.value) > stock) {
                    qtyInput.value = 1;
                }
            }
        }
    }

    // Sự kiện khi chọn Size
    variantRadios.forEach(radio => {
        radio.addEventListener('change', updateVariantInformation);
    });

    // Chạy khởi tạo ngay khi tải trang
    if (variantRadios.length > 0) {
        updateVariantInformation();
    }

    // Nút Tăng / Giảm số lượng
    btnPlus.addEventListener('click', function() {
        let maxQty = parseInt(qtyInput.getAttribute('max')) || 99;
        let currentQty = parseInt(qtyInput.value) || 1;
        if (currentQty < maxQty) {
            qtyInput.value = currentQty + 1;
        }
    });

    btnMinus.addEventListener('click', function() {
        let currentQty = parseInt(qtyInput.value) || 1;
        if (currentQty > 1) {
            qtyInput.value = currentQty - 1;
        }
    });
});
</script>
@endsection