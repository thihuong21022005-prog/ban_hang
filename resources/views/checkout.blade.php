@extends('layout.app')

@section('content')
<div class="container mt-5">
    <h3 class="fw-bold mb-4 text-secondary border-bottom pb-2">💳 XÁC NHẬN THANH TOÁN</h3>

    <div class="row g-4">
        <div class="col-md-7">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h5 class="fw-bold mb-3">Thông tin giao hàng</h5>
                <form id="checkout-form" action="{{ route('user.payment.process') }}" method="POST">
                    @csrf

                    <!-- LƯU DANH SÁCH SẢN PHẨM CẦN MUA -->
                    @foreach($cart as $key => $item)
                        <input type="hidden" name="selected_items[]" value="{{ $item['id'] ?? $key }}">
                    @endforeach

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Họ và tên</label>
                        <input type="text" name="name" class="form-control" placeholder="Nguyễn Văn A" value="{{ old('name', Auth::user()->name ?? '') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Số điện thoại</label>
                        <input type="tel" name="phone" class="form-control" placeholder="0912345678" value="{{ old('phone') }}" required>
                    </div>

                    <!-- BỘ 3 DỮ LIỆU ĐỊA CHỈ GHN -->
                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tỉnh / Thành phố</label>
                            <select id="province_select" name="province_id" class="form-select" required>
                                <option value="">-- Chọn Tỉnh/Thành --</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Quận / Huyện</label>
                            <select id="district_select" name="to_district_id" class="form-select" disabled required>
                                <option value="">-- Chọn Quận/Huyện --</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Phường / Xã</label>
                            <select id="ward_select" name="to_ward_code" class="form-select" disabled required>
                                <option value="">-- Chọn Phường/Xã --</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Địa chỉ chi tiết</label>
                        <input type="text" name="address" class="form-control" placeholder="Số nhà, tên đường..." value="{{ old('address') }}" required>
                    </div>

                    <h5 class="fw-bold mt-4 mb-3">Hình thức thanh toán</h5>
                    <div class="border rounded p-3 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="payment_cod" value="cod" checked>
                            <label class="form-check-label fw-bold" for="payment_cod">
                                💵 Thanh toán trực tiếp (COD khi nhận hàng)
                            </label>
                        </div>
                    </div>

                    <div class="border rounded p-3 mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="payment_momo" value="momo">
                            <label class="form-check-label fw-bold text-primary" for="payment_momo">
                                📱 Thanh toán qua Ví MoMo
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100 btn-lg fw-bold">Xác nhận đặt hàng</button>
                </form>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-light">
                <h5 class="fw-bold mb-3">Tóm tắt đơn hàng</h5>
                <p class="text-muted fs-6">Đơn hàng của bạn đã sẵn sàng được xử lý ngay sau khi bấm đặt hàng.</p>
                
                <hr>
                
                <div class="d-flex justify-content-between mb-2 fs-6">
                    <span class="text-muted">Tiền hàng:</span>
                    <span class="fw-bold">{{ number_format($subtotal ?? 0, 0, ',', '.') }} VNĐ</span>
                </div>

                <div class="d-flex justify-content-between mb-2 fs-6">
                    <span class="text-muted">Phí vận chuyển (GHN):</span>
                    <span class="fw-bold text-primary" id="shipping_fee_text">0 VNĐ</span>
                </div>

                <hr>

                <div class="d-flex justify-content-between fw-bold fs-5 text-danger mb-3">
                    <span>Tổng thanh toán:</span>
                    <span id="final_total_text">{{ number_format($subtotal ?? 0, 0, ',', '.') }} VNĐ</span>
                </div>

                <input type="hidden" id="subtotal_input" value="{{ $subtotal ?? 0 }}">

                <div class="d-flex justify-content-between fw-semibold text-secondary">
                    <span>Trạng thái:</span>
                    <span class="badge bg-warning text-dark">Chờ xác nhận</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const provinceSelect = document.getElementById('province_select');
    const districtSelect = document.getElementById('district_select');
    const wardSelect = document.getElementById('ward_select');
    const shippingFeeText = document.getElementById('shipping_fee_text');
    const finalTotalText = document.getElementById('final_total_text');
    const subtotalInput = document.getElementById('subtotal_input');

    const districtsUrl = "{{ route('locations.districts', ['provinceId' => '_PROVINCE_']) }}";
    const wardsUrl = "{{ route('locations.wards', ['districtId' => '_DISTRICT_']) }}";

    const subtotal = parseInt(subtotalInput ? subtotalInput.value : 0) || 0;

    // 1. Tải Tỉnh/Thành phố (Tự động thích ứng cấu trúc mảng hoặc res.data)
    fetch("{{ route('locations.provinces') }}")
        .then(res => res.json())
        .then(res => {
            const list = Array.isArray(res) ? res : (res.data || []);
            if (list.length > 0) {
                let options = '<option value="">-- Chọn Tỉnh/Thành --</option>';
                list.forEach(p => {
                    const id = p.ProvinceID ?? p.id ?? p.ProvinceId;
                    const name = p.ProvinceName ?? p.name;
                    options += `<option value="${id}">${name}</option>`;
                });
                provinceSelect.innerHTML = options;
            } else {
                provinceSelect.innerHTML = '<option value="">-- Không tải được tỉnh --</option>';
            }
        })
        .catch(err => {
            console.error("Lỗi load tỉnh:", err);
            provinceSelect.innerHTML = '<option value="">-- Lỗi kết nối --</option>';
        });

    // 2. Tải Quận/Huyện
    provinceSelect.addEventListener('change', function () {
        districtSelect.innerHTML = '<option value="">-- Đang tải... --</option>';
        districtSelect.disabled = true;
        wardSelect.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';
        wardSelect.disabled = true;
        updateTotals(0);

        if (!this.value) return;

        fetch(districtsUrl.replace('_PROVINCE_', this.value))
            .then(res => res.json())
            .then(res => {
                const list = Array.isArray(res) ? res : (res.data || []);
                if (list.length > 0) {
                    let options = '<option value="">-- Chọn Quận/Huyện --</option>';
                    list.forEach(d => {
                        const id = d.DistrictID ?? d.id ?? d.DistrictId;
                        const name = d.DistrictName ?? d.name;
                        options += `<option value="${id}">${name}</option>`;
                    });
                    districtSelect.innerHTML = options;
                    districtSelect.disabled = false;
                } else {
                    districtSelect.innerHTML = '<option value="">-- Không có dữ liệu --</option>';
                }
            })
            .catch(err => {
                console.error("Lỗi load huyện:", err);
                districtSelect.innerHTML = '<option value="">-- Lỗi tải huyện --</option>';
            });
    });

    // 3. Tải Phường/Xã
    districtSelect.addEventListener('change', function () {
        wardSelect.innerHTML = '<option value="">-- Đang tải... --</option>';
        wardSelect.disabled = true;
        updateTotals(0);

        if (!this.value) return;

        fetch(wardsUrl.replace('_DISTRICT_', this.value))
            .then(res => res.json())
            .then(res => {
                const list = Array.isArray(res) ? res : (res.data || []);
                if (list.length > 0) {
                    let options = '<option value="">-- Chọn Phường/Xã --</option>';
                    list.forEach(w => {
                        const code = w.WardCode ?? w.code ?? w.Wardcode;
                        const name = w.WardName ?? w.name;
                        options += `<option value="${code}">${name}</option>`;
                    });
                    wardSelect.innerHTML = options;
                    wardSelect.disabled = false;
                } else {
                    wardSelect.innerHTML = '<option value="">-- Không có dữ liệu --</option>';
                }
            })
            .catch(err => {
                console.error("Lỗi load xã:", err);
                wardSelect.innerHTML = '<option value="">-- Lỗi tải xã --</option>';
            });
    });

    // 4. Tính cước vận chuyển GHN khi chọn Phường/Xã
    wardSelect.addEventListener('change', function () {
        if (!this.value || !districtSelect.value) return;

        shippingFeeText.innerText = 'Đang tính...';

        fetch("{{ route('locations.fee') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                to_district_id: districtSelect.value,
                to_ward_code: this.value
            })
        })
        .then(res => res.json())
        .then(res => {
            const fee = res.data?.total ?? res.total ?? res.data?.service_fee ?? 0;
            if (fee > 0 || res.code === 200) {
                updateTotals(parseInt(fee) || 0);
            } else {
                shippingFeeText.innerText = 'Chưa hỗ trợ';
                updateTotals(0);
            }
        })
        .catch(err => {
            console.error("Lỗi tính phí:", err);
            shippingFeeText.innerText = 'Lỗi tính phí';
            updateTotals(0);
        });
    });

    function updateTotals(fee) {
        shippingFeeText.innerText = new Intl.NumberFormat('vi-VN').format(fee) + ' VNĐ';
        const finalAmount = subtotal + fee;
        finalTotalText.innerText = new Intl.NumberFormat('vi-VN').format(finalAmount) + ' VNĐ';
    }
});
</script>
@endsection