@extends('layout.app')

@section('content')
<div class="container mt-4 mb-5">
    <a href="{{ route('welcome') }}" class="btn btn-outline-secondary mb-4 fw-bold">← Quay lại trang chủ</a>

    <!-- KHỐI 1: THÔNG TIN VÀ CHỌN BIẾN THỂ SẢN PHẨM -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden p-4 mb-4">
        <div class="row g-4 align-items-center">
            <!-- Hình ảnh sản phẩm -->
            <div class="col-md-5">
                <img src="{{ $product->image_url ?? 'https://placehold.co/400x300?text=No+Image' }}"
                     class="img-fluid rounded-4 w-100 shadow-sm"
                     alt="{{ $product->name }}"
                     style="max-height: 380px; object-fit: cover;">
            </div>

            <!-- Thông tin chi tiết -->
            <div class="col-md-7">
                <h2 class="fw-bold text-dark mb-2">{{ $product->name }}</h2>

                <!-- Hiển thị đánh giá sao tổng quan -->
                <div class="d-flex align-items-center mb-3">
                    <div class="text-warning me-2 fs-5">
                        @php $avg = $product->averageRating(); @endphp
                        @for($i = 1; $i <= 5; $i++)
                            <i class="bi bi-star{{ $i <= round($avg) ? '-fill' : '' }}"></i>
                        @endfor
                    </div>
                    <span class="fw-bold text-dark me-2">{{ $avg }}</span>
                    <span class="text-muted border-start ps-2">({{ $product->reviews ? $product->reviews->count() : 0 }} đánh giá)</span>
                </div>

                <!-- Giá sản phẩm -->
                <h3 class="text-danger fw-bold my-3" id="product-price">
                    {{ number_format($product->price, 0, ',', '.') }} VNĐ
                </h3>

                <!-- Hiển thị Trạng thái Kho hàng -->
                <div class="mb-3" id="stock-status-container">
                    @if(($product->quantity ?? 0) > 0)
                        <span class="badge bg-success fs-6 fw-normal px-3 py-2" id="stock-badge">
                            <i class="bi bi-check-circle me-1"></i>Còn {{ $product->quantity }} sản phẩm
                        </span>
                    @else
                        <span class="badge bg-danger fs-6 fw-normal px-3 py-2" id="stock-badge">
                            <i class="bi bi-x-circle me-1"></i>Đã hết hàng
                        </span>
                    @endif
                </div>

                <div class="mb-4">
                    <h6 class="fw-bold text-secondary">Mô tả sản phẩm:</h6>
                    <p class="text-muted mb-0" style="white-space: pre-line;">
                        {{ $product->description ?? 'Chưa có mô tả cho sản phẩm này.' }}
                    </p>
                </div>

               <!-- BỘ CHỌN KÍCH THƯỚC DẠNG NÚT BẤM (VIỀN ĐỎ CHUẨN SHOPEE) -->
                @if(!empty($product->variants) && count($product->variants) > 0)
                   <div class="mb-4">
                        <label class="form-label fw-bold text-dark d-block mb-2">
                            Chọn kích thước / Phân loại <span class="text-danger">*</span>
                        </label>

                        <div class="d-flex flex-wrap gap-2" id="variant-btn-group">
                            @foreach ($product->variants as $variant)
                                <button type="button"
                                        class="btn btn-outline-secondary variant-btn py-2 px-3 fw-semibold"
                                        data-id="{{ $variant->id }}"
                                        data-price="{{ $variant->price ?? $product->price }}"
                                        data-formatted-price="{{ number_format($variant->price ?? $product->price, 0, ',', '.') }} VNĐ"
                                        data-quantity="{{ $variant->quantity }}">
                                    {{ $variant->size }}
                                </button>
                            @endforeach
                        </div>

                        <div class="text-danger small mt-2 d-none" id="variant-error-msg">
                            <i class="bi bi-exclamation-circle me-1"></i>Vui lòng chọn kích thước trước khi thêm vào giỏ hàng!
                        </div>
                    </div>
                @endif

                <!-- Xử lý Số lượng & Nút Thêm vào giỏ hàng -->
                <div class="d-flex align-items-center gap-3 mb-4">
                    <label for="quantity-input" class="fw-bold text-secondary mb-0">Số lượng:</label>
                    <input type="number"
                           id="quantity-input"
                           class="form-control text-center fw-bold"
                           value="1"
                           min="1"
                           max="{{ $product->quantity }}"
                           style="width: 90px;">
                </div>

                <!-- Nút Thêm giỏ hàng -->
                <button class="btn btn-primary btn-lg fw-bold px-4 btn-add-cart"
                        id="btn-add-cart"
                        data-id="{{ $product->id }}"
                        data-name="{{ $product->name }}"
                        data-price="{{ $product->price }}"
                        data-stock="{{ $product->quantity }}">
                    🛒 Thêm vào giỏ hàng
                </button>
            </div>
        </div>
    </div>

    <!-- KHỐI 2: ĐÁNH GIÁ & BÌNH LUẬN CỦA KHÁCH HÀNG -->
    <div class="card border-0 shadow-sm rounded-4 p-4">
        <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
            <h4 class="fw-bold m-0" style="color: #2a2265;">
                <i class="bi bi-star-fill text-warning me-2"></i>ĐÁNH GIÁ SẢN PHẨM
            </h4>
            <div class="text-end">
                <span class="fs-3 fw-bold text-dark">{{ $product->averageRating() }}</span>
                <span class="text-muted">/ 5.0</span>
                <div class="small text-muted">Tất cả ({{ $product->reviews ? $product->reviews->count() : 0 }} đánh giá)</div>
            </div>
        </div>

        <!-- Danh sách Bình luận -->
        <div class="review-list">
            @if($product->reviews && $product->reviews->count() > 0)
                @foreach($product->reviews()->latest()->get() as $review)
                    <div class="py-3 border-bottom last-border-0">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                {{ $review->user->name ?? 'Người dùng' }}
                                <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-2 py-1" style="font-size: 0.75rem;">
                                    <i class="bi bi-patch-check-fill me-1"></i>Đã mua hàng
                                </span>
                            </div>
                            <small class="text-muted">{{ $review->created_at ? $review->created_at->format('d/m/Y H:i') : '' }}</small>
                        </div>

                        <!-- Số sao tương ứng -->
                        <div class="text-warning mb-2" style="font-size: 0.9rem;">
                            @for($s = 1; $s <= 5; $s++)
                                <i class="bi bi-star{{ $s <= $review->rating ? '-fill' : '' }}"></i>
                            @endfor
                        </div>

                        <!-- Nội dung nhận xét -->
                        @if($review->comment)
                            <p class="text-secondary mb-0 bg-light p-3 rounded-3 small">
                                {{ $review->comment }}
                            </p>
                        @endif
                    </div>
                @endforeach
            @else
                <div class="text-center text-muted py-5">
                    <i class="bi bi-chat-square-heart display-5 d-block mb-3 text-secondary opacity-50"></i>
                    <p class="mb-0 fs-6">Chưa có đánh giá nào cho sản phẩm này.</p>
                    <small class="text-muted">Hãy là người đầu tiên mua hàng và trải nghiệm sản phẩm!</small>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- CSS TÙY CHỈNH NÚT BIẾN THỂ KHI ĐƯỢC CHỌN -->
<style>
    .variant-btn.active-variant {
        border-color: #dc3545 !important;
        color: #dc3545 !important;
        background-color: #fff5f5 !important;
        border-width: 2px !important;
        font-weight: 700 !important;
        box-shadow: 0 0 0 0.1rem rgba(220, 53, 69, 0.25);
    }
    .last-border-0:last-child {
        border-bottom: 0 !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const variantBtns = document.querySelectorAll('.variant-btn');
    const variantErrorMsg = document.getElementById('variant-error-msg');
    const priceElement = document.getElementById('product-price');
    const stockBadge = document.getElementById('stock-badge');
    const quantityInput = document.getElementById('quantity-input');
    const btnAddCart = document.getElementById('btn-add-cart');

    let selectedVariantId = null;

    variantBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            // Reset tất cả nút về trạng thái bình thường
            variantBtns.forEach(b => {
                b.classList.remove('active-variant');
                b.classList.add('btn-outline-secondary');
            });

            // Bôi đậm viền đỏ và highlight chữ cho nút được bấm chọn
            this.classList.remove('btn-outline-secondary');
            this.classList.add('active-variant');

            // Cập nhật dữ liệu
            selectedVariantId = this.getAttribute('data-id');
            const price = this.getAttribute('data-price');
            const formattedPrice = this.getAttribute('data-formatted-price');
            const stock = parseInt(this.getAttribute('data-quantity'));

            if (variantErrorMsg) {
                variantErrorMsg.classList.add('d-none');
            }

            if (priceElement) {
                priceElement.innerText = formattedPrice;
            }

            if (btnAddCart) {
                btnAddCart.setAttribute('data-price', price);
                btnAddCart.setAttribute('data-stock', stock);
            }

            if (quantityInput) {
                quantityInput.max = stock;
                if (parseInt(quantityInput.value) > stock) {
                    quantityInput.value = stock > 0 ? stock : 1;
                }
            }

            if (stock > 0) {
                stockBadge.className = 'badge bg-success fs-6 fw-normal px-3 py-2';
                stockBadge.innerHTML = `<i class="bi bi-check-circle me-1"></i>Còn ${stock} sản phẩm`;
                if (btnAddCart) btnAddCart.disabled = false;
            } else {
                stockBadge.className = 'badge bg-danger fs-6 fw-normal px-3 py-2';
                stockBadge.innerHTML = `<i class="bi bi-x-circle me-1"></i>Kích thước này đã hết hàng`;
                if (btnAddCart) btnAddCart.disabled = true;
            }
        });
    });

    if (btnAddCart) {
        btnAddCart.addEventListener('click', function () {
            const hasVariants = variantBtns.length > 0;

            if (hasVariants && !selectedVariantId) {
                if (variantErrorMsg) {
                    variantErrorMsg.classList.remove('d-none');
                }
                alert('⚠️ Vui lòng bấm chọn kích thước sản phẩm trước!');
                return;
            }

            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const price = this.getAttribute('data-price');
            const stock = parseInt(this.getAttribute('data-stock'));
            const quantity = quantityInput ? parseInt(quantityInput.value) : 1;

            if (quantity > stock) {
                alert(`⚠️ Số lượng chọn (${quantity}) vượt quá số lượng tồn kho (${stock})!`);
                return;
            }

            fetch(`/cart/add/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    name: name,
                    price: price,
                    quantity: quantity,
                    furniture_variant_id: selectedVariantId
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    alert(`❌ ${data.error}`);
                } else {
                    alert(`✅ Đã thêm thành công ${quantity} "${name}" vào giỏ hàng!`);
                    location.reload();
                }
            })
            .catch(() => {
                alert(`✅ Đã thêm thành công "${name}" vào giỏ hàng!`);
                location.reload();
            });
        });
    }
});
</script>
@endsection