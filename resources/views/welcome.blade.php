@extends('layout.app')

@section('content')
<style>
    /* Thẻ sản phẩm chuẩn Admin */
    .admin-style-card {
        background: #ffffff;
        border: none;
        border-radius: 12px;
        padding: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .admin-style-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
    }
    .admin-card-img {
        width: 100%;
        height: 160px;
        object-fit: cover;
        border-radius: 8px;
    }
    .admin-card-title {
        font-weight: 700;
        font-size: 0.92rem;
        color: #1e293b;
        margin-top: 8px;
        margin-bottom: 2px;
    }
    .admin-card-sub {
        font-size: 0.78rem;
        color: #64748b;
        margin-bottom: 6px;
    }
    .admin-card-price {
        color: #dc2626;
        font-weight: 700;
        font-size: 0.95rem;
    }

    /* Nút thêm giỏ hàng compact */
    .btn-cart-admin {
        background-color: #3b82f6;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        font-size: 0.82rem;
        font-weight: 600;
        padding: 6px 10px;
        transition: background-color 0.2s ease;
    }
    .btn-cart-admin:hover {
        background-color: #2563eb;
        color: #ffffff;
    }

    /* Floating Chat Button màu vàng chuẩn Admin */
    #chat-box {
        position: fixed;
        bottom: 25px;
        right: 25px;
        z-index: 9999;
    }
    #chat-toggle {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background-color: #ffc107;
        color: #1e293b;
        border: none;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        font-size: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.2s ease;
    }
    #chat-toggle:hover {
        transform: scale(1.1);
    }
    #chat-popup {
        width: 330px;
        height: 430px;
        border-radius: 14px;
        border: none;
        overflow: hidden;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        display: flex;
        flex-direction: column;
    }
    #chat-messages {
        height: 310px;
        overflow-y: auto;
        padding: 12px;
        background-color: #f8fafc;
    }
    .message-row {
        margin-bottom: 8px;
        padding: 6px 12px;
        font-size: 13px;
        word-break: break-word;
    }
    .user-msg {
        background-color: #3b82f6;
        color: #ffffff;
        border-radius: 12px 12px 2px 12px;
        margin-left: 20%;
        text-align: right;
    }
    .admin-msg {
        background-color: #e2e8f0;
        color: #1e293b;
        border-radius: 12px 12px 12px 2px;
        margin-right: 20%;
        text-align: left;
    }
</style>

<div class="container-fluid p-0">
    <!-- Notification alert -->
    <div id="cart-alert" class="alert alert-success border-0 shadow-sm rounded-3 d-none mb-3 py-2 px-3 fs-6" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        <span id="cart-alert-message"></span>
    </div>

    <!-- Header phần nội dung -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-dark m-0 d-flex align-items-center gap-2">
            <i class="bi bi-grid-fill text-primary"></i> Danh Sách Sản Phẩm
        </h5>
        <span class="text-muted small">Bấm vào sản phẩm để xem chi tiết</span>
    </div>

    <!-- Lưới sản phẩm -->
    <div class="row g-3">
        @forelse($products as $product)
            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                <div class="admin-style-card h-100 d-flex flex-column justify-content-between">
                    <a href="{{ route('user.products.show', $product->id) }}" class="text-decoration-none">
                        <img src="{{ $product->image_url ?? 'https://placehold.co/200x160?text=No+Image' }}"
                             class="admin-card-img"
                             alt="{{ $product->name }}">

                        <div class="admin-card-title text-truncate" title="{{ $product->name }}">
                            {{ $product->name }}
                        </div>
                        <div class="admin-card-sub text-truncate">
                            {{ $product->category->name ?? 'Nội thất hiện đại' }}
                        </div>
                        <div class="admin-card-price">
                            ₫{{ number_format($product->price, 0, ',', '.') }}
                        </div>
                    </a>

                    <div class="pt-2">
                        <button class="btn btn-cart-admin w-100 btn-add-cart"
                                data-id="{{ $product->id }}"
                                data-name="{{ $product->name }}"
                                data-price="{{ $product->price }}">
                            <i class="bi bi-cart-plus me-1"></i> Thêm vào giỏ
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <p class="text-muted small">Chưa có sản phẩm nào trong hệ thống.</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Nút Chat nổi màu vàng ở góc dưới bên phải -->
@auth
<div id="chat-box">
    <button id="chat-toggle" title="Hỗ trợ trực tuyến">
        <i class="bi bi-chat-dots-fill"></i>
    </button>

    <div id="chat-popup" class="card d-none">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2 px-3">
            <span class="fw-bold fs-6"><i class="bi bi-headset me-1"></i> Tin nhắn hỗ trợ</span>
            <button id="chat-close" class="btn btn-sm text-white p-0 fs-5 border-0">&times;</button>
        </div>

        <div id="chat-messages" class="card-body">
            <small class="text-muted">Đang tải lịch sử...</small>
        </div>

        <div class="card-footer bg-white p-2">
            <div class="input-group">
                <input type="text" id="chat-input" class="form-control form-control-sm rounded-start-2" placeholder="Nhập tin nhắn..." autocomplete="off">
                <button id="send-btn" class="btn btn-sm btn-primary rounded-end-2 px-3">Gửi</button>
            </div>
        </div>
    </div>
</div>
@endauth

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Thêm sản phẩm vào giỏ hàng AJAX
    const alertBox = document.getElementById('cart-alert');
    const alertMsg = document.getElementById('cart-alert-message');

    document.querySelectorAll('.btn-add-cart').forEach(button => {
        button.addEventListener('click', function (e) {
            e.stopPropagation();
            let id = this.getAttribute('data-id');
            let name = this.getAttribute('data-name');
            let price = this.getAttribute('data-price');

            fetch(`/cart/add/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ name: name, price: price, quantity: 1 })
            })
            .then(res => res.json())
            .then(data => {
                showAlert(`Đã thêm "${name}" vào giỏ hàng!`);
            })
            .catch(() => {
                showAlert(`Đã thêm "${name}" vào giỏ hàng!`);
            });
        });
    });

    function showAlert(msg) {
        alertMsg.innerText = msg;
        alertBox.classList.remove('d-none');
        setTimeout(() => { alertBox.classList.add('d-none'); }, 3000);
    }

    // 2. Chat Livechat
    const toggleBtn = document.getElementById("chat-toggle");
    const chatPopup = document.getElementById("chat-popup");
    const closeBtn = document.getElementById("chat-close");
    const sendBtn = document.getElementById("send-btn");
    const input = document.getElementById("chat-input");
    const chatBox = document.getElementById("chat-messages");

    if (!toggleBtn) return;

    toggleBtn.onclick = () => {
        chatPopup.classList.remove("d-none");
        toggleBtn.classList.add("d-none");
        loadMessages();
    };

    closeBtn.onclick = () => {
        chatPopup.classList.add("d-none");
        toggleBtn.classList.remove("d-none");
    };

    function loadMessages() {
        fetch("{{ route('chat.messages') }}")
            .then(res => res.json())
            .then(messages => {
                let html = "";
                if (messages.length === 0) {
                    html = "<div class='text-center text-muted mt-3'><small>Bắt đầu cuộc trò chuyện với Admin</small></div>";
                } else {
                    messages.forEach(msg => {
                        const isMe = msg.sender_id == "{{ Auth::id() }}";
                        html += `
                            <div class="message-row ${isMe ? 'user-msg' : 'admin-msg'}">
                                <strong>${isMe ? 'Bạn' : 'Admin'}:</strong> ${msg.content}
                            </div>
                        `;
                    });
                }
                chatBox.innerHTML = html;
                chatBox.scrollTop = chatBox.scrollHeight;
            })
            .catch(err => console.error("Lỗi tải tin nhắn:", err));
    }

    function sendMessage() {
        let message = input.value.trim();
        if (message === "") return;

        input.disabled = true;
        sendBtn.disabled = true;

        fetch("{{ route('user.chat.send') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": '{{ csrf_token() }}',
                "Content-Type": "application/json",
                "Accept": "application/json"
            },
            body: JSON.stringify({ message: message })
        })
        .then(res => res.json())
        .then(data => {
            input.value = "";
            input.disabled = false;
            sendBtn.disabled = false;
            input.focus();
            loadMessages();
        })
        .catch(err => {
            console.error("Lỗi gửi tin:", err);
            input.disabled = false;
            sendBtn.disabled = false;
        });
    }

    sendBtn.onclick = sendMessage;
    input.addEventListener("keypress", function(e) {
        if (e.key === "Enter") sendMessage();
    });

    setInterval(() => {
        if (!chatPopup.classList.contains("d-none")) {
            loadMessages();
        }
    }, 3000);
});
</script>
@endsection