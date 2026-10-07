<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Shop Giường - Tủ' }}</title>

    <!-- Bootstrap 5 & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #f3f4f6;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: #1e293b;
        }

        .app-layout {
            display: flex;
            min-height: 100vh;
        }

        /* 1. SIDEBAR CHO KHÁCH HÀNG (STYLE TÍM ĐẬM GIỐNG ADMIN) */
        .sidebar {
            width: 240px;
            background-color: #232057; /* Màu tím đậm Admin */
            color: #ffffff;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1000;
        }

        .sidebar-brand {
            padding: 22px 20px;
            font-size: 1.1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #ffffff;
            text-decoration: none;
        }

        .sidebar-brand-icon {
            background: rgba(255, 255, 255, 0.12);
            padding: 6px 8px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar-menu {
            list-style: none;
            padding: 10px 14px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            flex-grow: 1;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 16px;
            color: #b1b4db;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            border-radius: 14px;
            transition: all 0.2s ease;
        }

        .sidebar-link:hover {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.08);
        }

        /* Active tab dạng Gradient Tím tròn */
        .sidebar-link.active {
            background: linear-gradient(135deg, #818cf8, #6366f1);
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.35);
        }

        .sidebar-divider {
            height: 1px;
            background-color: rgba(255, 255, 255, 0.12);
            margin: 12px 10px;
        }

        .badge-count {
            background-color: #ef4444;
            color: #ffffff;
            font-size: 0.7rem;
            padding: 2px 7px;
            border-radius: 10px;
            margin-left: auto;
        }

        /* 2. KHUNG NỘI DUNG VÀ HEADER TRÊN CÙNG */
        .main-wrapper {
            margin-left: 240px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .top-header {
            height: 65px;
            background-color: #ffffff;
            padding: 0 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 900;
        }

        .header-title {
            font-weight: 700;
            color: #1e293b;
            font-size: 1.1rem;
            margin: 0;
        }

        .user-info {
            font-size: 0.88rem;
            color: #475569;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Nút Đăng xuất màu tím bo tròn kiểu Admin */
        .btn-logout-purple {
            background-color: #6366f1;
            color: #ffffff;
            border: none;
            border-radius: 20px;
            padding: 6px 18px;
            font-size: 0.85rem;
            font-weight: 600;
            transition: background-color 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-logout-purple:hover {
            background-color: #4f46e5;
            color: #ffffff;
        }

        .content-body {
            padding: 24px 28px;
            flex-grow: 1;
        }

        /* CSS LIVECHAT POPUP HIỆN ĐẠI DÙNG CHUNG */
        #chat-box {
            position: fixed;
            bottom: 25px;
            right: 25px;
            z-index: 9999;
        }
        #chat-toggle {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border: none;
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
            font-size: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s ease;
        }
        #chat-toggle:hover {
            transform: scale(1.08);
        }
        #chat-popup {
            width: 340px;
            height: 450px;
            border-radius: 16px;
            border: none;
            overflow: hidden;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
        }
        #chat-messages {
            height: 320px;
            overflow-y: auto;
            padding: 12px;
            background-color: #f8fafc;
        }
        .message-row {
            margin-bottom: 10px;
            padding: 8px 14px;
            font-size: 14px;
            word-break: break-word;
        }
        .user-msg {
            background-color: #3b82f6;
            color: #ffffff;
            border-radius: 14px 14px 2px 14px;
            margin-left: 20%;
            text-align: right;
        }
        .admin-msg {
            background-color: #e2e8f0;
            color: #1e293b;
            border-radius: 14px 14px 14px 2px;
            margin-right: 20%;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="app-layout">
        <!-- Sidebar Navigation Bên Trái -->
        <aside class="sidebar">
            <a href="{{ url('/') }}" class="sidebar-brand">
                <div class="sidebar-brand-icon">
                    <i class="bi bi-shop fs-5"></i>
                </div>
                <span>Shop Giường - Tủ</span>
            </a>

            <ul class="sidebar-menu">
                <!-- 1. Trang chủ -->
                <li>
                    <a href="{{ url('/') }}" class="sidebar-link {{ request()->is('/') ? 'active' : '' }}">
                        <i class="bi bi-house-door-fill fs-5"></i>
                        <span>Trang chủ</span>
                    </a>
                </li>

                <!-- 2. Giỏ hàng -->
                <li>
                    <a href="{{ route('user.cart.index') }}" class="sidebar-link {{ request()->routeIs('user.cart.*') ? 'active' : '' }}">
                        <i class="bi bi-cart3 fs-5"></i>
                        <span>Giỏ hàng</span>
                        @if(session('cart') && count(session('cart')) > 0)
                            <span class="badge-count">{{ count(session('cart')) }}</span>
                        @endif
                    </a>
                </li>

                @auth
                <!-- 3. Đơn hàng của tôi -->
                <li>
                    <a href="{{ route('user.orders.index') }}" class="sidebar-link {{ request()->routeIs('user.orders.*') ? 'active' : '' }}">
                        <i class="bi bi-box-seam fs-5"></i>
                        <span>Đơn hàng của tôi</span>
                    </a>
                </li>

                <!-- 4. Thông tin cá nhân -->
                <li>
                    <a href="{{ route('user.profile.index') }}" class="sidebar-link {{ request()->routeIs('user.profile.*') ? 'active' : '' }}">
                        <i class="bi bi-person-circle fs-5"></i>
                        <span>Thông tin cá nhân</span>
                    </a>
                </li>

                @if(Auth::user()->role === 'admin')
                    <div class="sidebar-divider"></div>
                    <li>
                        <a href="{{ route('admin.dashboard') }}" class="sidebar-link">
                            <i class="bi bi-shield-lock fs-5 text-info"></i>
                            <span class="text-info">Trang Admin</span>
                        </a>
                    </li>
                @endif

                <div class="sidebar-divider"></div>

                <!-- 5. Tin nhắn hỗ trợ -->
                <li>
                    <a href="javascript:void(0)" id="sidebar-chat-trigger" class="sidebar-link">
                        <i class="bi bi-chat-dots-fill fs-5 text-warning"></i>
                        <span class="text-warning">Tin nhắn hỗ trợ</span>
                    </a>
                </li>
                @endauth
            </ul>
        </aside>

        <!-- Khung Nội Dung Chính Phía Bên Phải -->
        <div class="main-wrapper">
            <!-- Header góc trên bên phải -->
            <header class="top-header">
                <h5 class="header-title">Hệ Thống Bán Hàng</h5>

                <div class="d-flex align-items-center gap-3">
                    @guest
                        <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold">Đăng nhập</a>
                        <a href="{{ route('register') }}" class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold">Đăng ký</a>
                    @else
                        <!-- Xin chào + Tên Khách Hàng -->
                        <div class="user-info">
                            <i class="bi bi-person-circle fs-6"></i>
                            <span>Xin chào, <strong>{{ Auth::user()->name }}</strong></span>
                        </div>

                        <!-- Nút Đăng Xuất Bo Tròn Màu Tím -->
                        <form action="{{ route('logout') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="btn-logout-purple">
                                <i class="bi bi-box-arrow-right"></i> Đăng xuất
                            </button>
                        </form>
                    @endguest
                </div>
            </header>

            <!-- Nội dung chính trang Khách hàng -->
            <main class="content-body">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- KHU VỰC LIVECHAT POPUP HIỂN THỊ TRÊN TẤT CẢ CÁC TRANG -->
    @auth
    <div id="chat-box">
        <!-- Nút tròn mở chat góc dưới bên phải -->
        <button id="chat-toggle" class="shadow">
            <i class="bi bi-chat-dots-fill"></i>
        </button>

        <!-- Khung Popup Chat -->
        <div id="chat-popup" class="card d-none">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                <span class="fw-bold"><i class="bi bi-headset me-2"></i>Hỗ trợ trực tuyến</span>
                <button id="chat-close" class="btn btn-sm btn-light rounded-circle fw-bold py-0 px-2">✕</button>
            </div>

            <div id="chat-messages" class="card-body">
                <small class="text-muted">Đang tải lịch sử...</small>
            </div>

            <div class="card-footer bg-white border-top-0 p-2">
                <div class="input-group">
                    <input type="text" id="chat-input" class="form-control rounded-start-3 border-end-0" placeholder="Nhập tin nhắn..." autocomplete="off">
                    <button id="send-btn" class="btn btn-primary rounded-end-3 px-3">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endauth

    <!-- Script Bootstrap & Xử lý Livechat Toàn Hệ Thống -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarChatBtn = document.getElementById('sidebar-chat-trigger');
        const toggleBtn = document.getElementById("chat-toggle");
        const chatPopup = document.getElementById("chat-popup");
        const closeBtn = document.getElementById("chat-close");
        const sendBtn = document.getElementById("send-btn");
        const input = document.getElementById("chat-input");
        const chatBox = document.getElementById("chat-messages");

        // Sự kiện click nút "Tin nhắn hỗ trợ" ở Sidebar
        if (sidebarChatBtn && toggleBtn) {
            sidebarChatBtn.addEventListener('click', function() {
                if (chatPopup.classList.contains("d-none")) {
                    toggleBtn.click();
                }
            });
        }

        if (!toggleBtn) return; // Nếu chưa đăng nhập thì dừng script chat

        // Mở / Đóng popup chat
        toggleBtn.onclick = () => {
            chatPopup.classList.remove("d-none");
            toggleBtn.classList.add("d-none");
            loadMessages();
        };

        closeBtn.onclick = () => {
            chatPopup.classList.add("d-none");
            toggleBtn.classList.remove("d-none");
        };

        // Load danh sách tin nhắn từ server
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

        // Gửi tin nhắn
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
            if (e.key === "Enter") {
                sendMessage();
            }
        });

        // Tự động làm mới tin nhắn mỗi 3 giây
        setInterval(() => {
            if (!chatPopup.classList.contains("d-none")) {
                loadMessages();
            }
        }, 3000);
    });
    </script>
</body>
</html>