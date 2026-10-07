@extends('layout.app')

@section('content')
<div class="container mt-5">
    <h3 class="fw-bold mb-4 text-secondary border-bottom pb-2">🛒 GIỎ HÀNG CỦA BẠN</h3>

    @if(session('cart') && count(session('cart')) > 0)
        <form action="{{ route('user.checkout') }}" method="GET">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="5%"><input type="checkbox" id="select-all" class="form-check-input"></th>
                            <th width="40%">Sản phẩm</th>
                            <th width="15%">Đơn giá</th>
                            <th width="12%">Số lượng</th>
                            <th width="15%">Thành tiền</th>
                            <th width="10%" class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(session('cart') as $cartKey => $details)
                            <tr>
                                <td>
                                    <input type="checkbox" name="selected_items[]" value="{{ $cartKey }}" class="form-check-input item-checkbox" data-price="{{ $details['price'] * $details['quantity'] }}">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <!-- HÌNH ẢNH SẢN PHẨM -->
                                        @if(!empty($details['image']))
                                            <img src="{{ str_starts_with($details['image'], 'http') ? $details['image'] : asset('storage/' . str_replace('public/', '', $details['image'])) }}" 
                                                 alt="{{ $details['name'] }}" 
                                                 class="rounded border" 
                                                 style="width: 60px; height: 60px; object-fit: cover;">
                                        @else
                                            <img src="https://via.placeholder.com/60x60?text=No+Image" class="rounded border" style="width: 60px; height: 60px;" alt="No image">
                                        @endif
                                        
                                        <div>
                                            <div class="fw-bold text-dark">{{ $details['name'] }}</div>
                                            <!-- BỔ SUNG: HIỂN THỊ KÍCH THƯỚC / SIZE CHỌN -->
                                            <span class="badge bg-light text-primary border mt-1">
                                                Size: <strong>{{ $details['dimension'] ?? 'Mặc định' }}</strong>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ number_format($details['price'], 0, ',', '.') }} VNĐ</span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary fs-6 px-3 py-2">{{ $details['quantity'] }}</span>
                                </td>
                                <td class="text-danger fw-bold fs-6">
                                    {{ number_format($details['price'] * $details['quantity'], 0, ',', '.') }} VNĐ
                                </td>
                                <td class="text-center">
                                    <!-- NÚT XÓA SẢN PHẨM -->
                                    <a href="{{ route('user.cart.remove', $cartKey) }}" 
                                       class="btn btn-outline-danger btn-sm rounded-circle" 
                                       onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?')"
                                       title="Xóa khỏi giỏ hàng">
                                        ✕
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4 p-3 bg-light rounded-3 shadow-sm">
                <a href="{{ route('welcome') }}" class="btn btn-outline-secondary">← Tiếp tục mua sắm</a>
                <div class="d-flex align-items-center gap-3">
                    <h5 class="mb-0">Tổng chọn: <span id="total-price" class="text-danger fw-bold fs-4">0 VNĐ</span></h5>
                    <button type="submit" class="btn btn-success btn-lg fw-bold">Tiến hành thanh toán ➔</button>
                </div>
            </div>
        </form>
    @else
        <div class="text-center py-5">
            <h5 class="text-muted mb-3">Giỏ hàng của bạn đang trống!</h5>
            <a href="{{ route('welcome') }}" class="btn btn-primary fw-bold">Quay lại mua sắm ngay</a>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.item-checkbox');
    const totalPriceEl = document.getElementById('total-price');

    function calculateTotal() {
        let total = 0;
        checkboxes.forEach(cb => {
            if (cb.checked) {
                total += parseFloat(cb.getAttribute('data-price'));
            }
        });
        totalPriceEl.innerText = total.toLocaleString('vi-VN') + ' VNĐ';
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
            calculateTotal();
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', calculateTotal);
    });
});
</script>
@endsection