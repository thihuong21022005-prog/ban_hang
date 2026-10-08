@extends('layout.app')

@section('content')
<div class="container my-5">
    <!-- Nút quay lại & Mã đơn hàng -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('user.orders.index') }}" class="text-decoration-none text-secondary fw-semibold">
                ← Quay lại danh sách đơn hàng
            </a>
            <h3 class="fw-bold mt-2" style="color: #2a2265;">Chi tiết đơn hàng #{{ $order->id }}</h3>
        </div>
        <div class="text-end">
            <span class="text-muted d-block small">Ngày đặt: {{ $order->created_at ? $order->created_at->format('d/m/Y H:i') : 'N/A' }}</span>
            @if($order->ghn_order_code)
                <span class="badge bg-light text-dark border font-monospace mt-1 fs-6">
                    🚚 GHN: {{ $order->ghn_order_code }}
                </span>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- KHỐI STEPPER TIẾN TRÌNH ĐƠN HÀNG (CHUẨN SHOPEE) -->
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
        <h5 class="fw-bold mb-4" style="color: #2a2265;"><i class="bi bi-truck me-2"></i>Trạng thái vận chuyển</h5>
        
        @if(in_array($order->status, ['cancelled', 'canceled']))
            <div class="alert alert-danger mb-0 rounded-3 text-center py-3">
                <i class="bi bi-x-circle-fill fs-4 d-block mb-1"></i>
                <span class="fw-bold fs-5">Đơn hàng này đã bị hủy.</span>
            </div>
        @else
            @php
                // Xác định bước hiện tại của Stepper
                $step = 1;
                if ($order->status === 'shipping') { $step = 2; }
                elseif ($order->status === 'completed') { $step = 3; }
            @endphp

            <div class="row text-center position-relative my-2">
                <!-- Bước 1: Chuẩn bị hàng -->
                <div class="col-4">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold mb-2 {{ $step >= 1 ? 'bg-success' : 'bg-secondary' }}" style="width: 45px; height: 45px;">
                        <i class="bi bi-box-seam fs-5"></i>
                    </div>
                    <div class="fw-bold {{ $step >= 1 ? 'text-success' : 'text-muted' }}">1. Shop chuẩn bị hàng</div>
                    <small class="text-muted d-block">Đã nhận đơn & đóng gói</small>
                </div>

                <!-- Bước 2: Đang giao hàng -->
                <div class="col-4">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold mb-2 {{ $step >= 2 ? 'bg-success' : 'bg-secondary' }}" style="width: 45px; height: 45px;">
                        <i class="bi bi-truck fs-5"></i>
                    </div>
                    <div class="fw-bold {{ $step >= 2 ? 'text-success' : 'text-muted' }}">2. Đang giao hàng</div>
                    <small class="text-muted d-block">Đang trên đường vận chuyển</small>
                </div>

                <!-- Bước 3: Giao thành công -->
                <div class="col-4">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold mb-2 {{ $step >= 3 ? 'bg-success' : 'bg-secondary' }}" style="width: 45px; height: 45px;">
                        <i class="bi bi-check-circle fs-5"></i>
                    </div>
                    <div class="fw-bold {{ $step >= 3 ? 'text-success' : 'text-muted' }}">3. Hoàn thành</div>
                    <small class="text-muted d-block">Đã nhận hàng thành công</small>
                </div>
            </div>

            <!-- NÚT XÁC NHẬN ĐÃ NHẬN HÀNG -->
            @if($order->status !== 'completed')
                <div class="text-end mt-4 pt-3 border-top">
                    <form action="{{ route('user.orders.confirm', $order->id) }}" method="POST" onsubmit="return confirm('Bạn xác nhận đã nhận đủ hàng và sản phẩm nguyên vẹn?');">
                        @csrf
                        <button type="submit" class="btn btn-success fw-bold rounded-pill px-4">
                            <i class="bi bi-box2-heart me-1"></i> Tôi đã nhận được hàng
                        </button>
                    </form>
                </div>
            @endif
        @endif
    </div>

    <!-- KHỐI THÔNG TIN NGƯỜI NHẬN & SẢN PHẨM -->
    <div class="row g-4">
        <!-- Thông tin người nhận -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <h5 class="fw-bold mb-3 text-secondary"><i class="bi bi-geo-alt me-1"></i>Địa chỉ nhận hàng</h5>
                <p class="mb-1 fw-bold text-dark fs-6">{{ $order->name }}</p>
                <p class="mb-1 text-muted"><i class="bi bi-telephone me-1"></i>{{ $order->phone }}</p>
                <p class="mb-3 text-muted"><i class="bi bi-house-door me-1"></i>{{ $order->address }}</p>
                
                <hr>
                
                <h6 class="fw-bold text-secondary mb-2"><i class="bi bi-credit-card me-1"></i>Thanh toán</h6>
                <p class="mb-0 text-uppercase fw-bold text-primary">
                    {{ $order->payment_method ?? 'COD (Thanh toán khi nhận hàng)' }}
                </p>
            </div>
        </div>

        <!-- Danh sách sản phẩm trong đơn hàng -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden p-4">
                <h5 class="fw-bold mb-3 text-secondary"><i class="bi bi-bag me-1"></i>Danh sách sản phẩm</h5>
                
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Sản phẩm</th>
                                <th class="text-center">Đơn giá</th>
                                <th class="text-center">SL</th>
                                <th class="text-end">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                @php
                                    // Lấy linh hoạt ID sản phẩm (tránh null nếu đặt tên cột là product_id hoặc furniture_id)
                                    $productId = $item->furniture_id ?? $item->product_id ?? $item->furniture?->id;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            @php
                                                $imgSrc = optional($item->furniture)->image_url ?? 'https://placehold.co/80';
                                            @endphp
                                            <img src="{{ $imgSrc }}" class="rounded-3 border" style="width: 60px; height: 60px; object-fit: cover;" alt="{{ $item->furniture->name ?? 'Sản phẩm' }}">
                                            <div>
                                                @if($productId)
                                                    <a href="{{ route('user.products.show', $productId) }}" class="fw-bold text-dark text-decoration-none d-block">
                                                        {{ $item->furniture->name ?? 'Sản phẩm' }}
                                                    </a>
                                                @else
                                                    <span class="fw-bold text-dark d-block">{{ $item->furniture->name ?? 'Sản phẩm' }}</span>
                                                @endif

                                                @if($item->dimension)
                                                    <small class="text-muted bg-light px-2 py-1 rounded border d-inline-block mt-1">Phân loại: {{ $item->dimension }}</small>
                                                @endif

                                                <!-- NÚT ĐÁNH GIÁ SẢN PHẨM NẾU ĐƠN HÀNG ĐÃ HOÀN THÀNH -->
                                                @if($order->status === 'completed' && $productId)
                                                    @php
                                                        $existingReview = \App\Models\Review::where('order_id', $order->id)
                                                            ->where('furniture_id', $productId)
                                                            ->where('user_id', auth()->id())
                                                            ->first();
                                                    @endphp
                                                    <div class="mt-2">
                                                        <button type="button" 
                                                                class="btn btn-xs {{ $existingReview ? 'btn-outline-success' : 'btn-warning text-dark fw-bold' }} rounded-pill px-3 py-1" 
                                                                style="font-size: 0.8rem;"
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#reviewModal{{ $item->id }}">
                                                            <i class="bi bi-star-fill me-1"></i>
                                                            {{ $existingReview ? 'Sửa đánh giá (' . $existingReview->rating . '⭐)' : 'Viết đánh giá' }}
                                                        </button>
                                                    </div>

                                                    <!-- Modal Form Đánh Giá -->
                                                    <div class="modal fade" id="reviewModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered">
                                                            <div class="modal-content border-0 shadow rounded-4">
                                                                <form action="{{ route('user.reviews.store') }}" method="POST">
                                                                    @csrf
                                                                    <input type="hidden" name="order_id" value="{{ $order->id }}">
                                                                    <input type="hidden" name="furniture_id" value="{{ $productId }}">

                                                                    <div class="modal-header border-0 pb-0">
                                                                        <h5 class="modal-title fw-bold">Đánh giá: {{ $item->furniture->name ?? 'Sản phẩm' }}</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div>

                                                                    <div class="modal-body py-3">
                                                                        <div class="mb-3 text-center">
                                                                            <label class="form-label d-block text-muted small fw-bold">CHỌN MỨC ĐỘ HÀI LÒNG</label>
                                                                            <select name="rating" class="form-select text-center fw-bold text-warning fs-5 rounded-3">
                                                                                <option value="5" {{ ($existingReview->rating ?? 5) == 5 ? 'selected' : '' }}>⭐⭐⭐⭐⭐ (5/5 - Rất tốt)</option>
                                                                                <option value="4" {{ ($existingReview->rating ?? 5) == 4 ? 'selected' : '' }}>⭐⭐⭐⭐ (4/5 - Tốt)</option>
                                                                                <option value="3" {{ ($existingReview->rating ?? 5) == 3 ? 'selected' : '' }}>⭐⭐⭐ (3/5 - Bình thường)</option>
                                                                                <option value="2" {{ ($existingReview->rating ?? 5) == 2 ? 'selected' : '' }}>⭐⭐ (2/5 - Tệ)</option>
                                                                                <option value="1" {{ ($existingReview->rating ?? 5) == 1 ? 'selected' : '' }}>⭐ (1/5 - Rất tệ)</option>
                                                                            </select>
                                                                        </div>

                                                                        <div class="mb-3">
                                                                            <label class="form-label fw-semibold">Nhận xét chi tiết</label>
                                                                            <textarea name="comment" rows="3" class="form-control rounded-3" placeholder="Chất liệu gỗ thế nào? Đóng gói chắc chắn chứ?">{{ $existingReview->comment ?? '' }}</textarea>
                                                                        </div>
                                                                    </div>

                                                                    <div class="modal-footer border-0 pt-0">
                                                                        <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Hủy</button>
                                                                        <button type="submit" class="btn btn-warning text-dark fw-bold rounded-pill px-4">Gửi đánh giá</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center fw-semibold">{{ number_format($item->price) }} đ</td>
                                    <td class="text-center fw-bold">x{{ $item->quantity }}</td>
                                    <td class="text-end fw-bold text-danger">{{ number_format($item->price * $item->quantity) }} đ</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- TỔNG CỘNG TIỀN ĐƠN HÀNG -->
                <div class="border-top pt-3 mt-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Phí vận chuyển GHN:</span>
                        <span class="fw-semibold">{{ number_format($order->ghn_total_fee ?? 0) }} đ</span>
                    </div>
                    <div class="d-flex justify-content-between fs-5 fw-bold text-danger mt-2">
                        <span>Tổng thanh toán:</span>
                        <span>{{ number_format($order->total_price) }} đ</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection