<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ Thống Quản Lý Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --purple-dark: #201b4e;
            --purple-sidebar: #2a2265;
            --purple-accent: #6f5cc3;
            --cyan-bright: #38ef7d;
            --cyan-gradient: linear-gradient(135deg, #11998e, #38ef7d);
            --card-gradient: linear-gradient(135deg, #614385 0%, #516395 100%);
        }

        body { 
            margin: 0; 
            padding: 0; 
            background-color: #f1f3f9; 
            font-family: system-ui, -apple-system, sans-serif; 
        }

        .admin-wrapper { display: flex; min-height: 100vh; }
        
        .admin-sidebar { 
            width: 250px; 
            min-width: 250px; 
            background: linear-gradient(180deg, var(--purple-dark) 0%, var(--purple-sidebar) 100%); 
            color: #ffffff; 
            padding: 20px 15px; 
            display: flex; 
            flex-direction: column; 
            box-shadow: 4px 0 15px rgba(0,0,0,0.05);
        }

        .admin-sidebar .brand { 
            font-size: 1.25rem; 
            font-weight: 700; 
            color: #ffffff; 
            margin-bottom: 30px; 
            padding-left: 10px; 
            display: flex;
            align-items: center;
        }

        .admin-sidebar .nav-link { 
            color: #b7b5d9; 
            padding: 12px 18px; 
            border-radius: 10px; 
            margin-bottom: 8px; 
            font-weight: 500; 
            display: flex; 
            align-items: center; 
            text-decoration: none; 
            transition: all 0.25s ease; 
        }

        .admin-sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }

        .admin-sidebar .nav-link.active { 
            background: linear-gradient(135deg, #6f5cc3 0%, #8e74ec 100%); 
            color: #ffffff; 
            box-shadow: 0 4px 12px rgba(111, 92, 195, 0.35);
        }

        .admin-content { flex-grow: 1; background-color: #f1f3f9; overflow-x: hidden; }
        
        .admin-topbar { 
            background-color: #ffffff; 
            padding: 16px 30px; 
            border-bottom: 1px solid #e3e8f0; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 25px; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        }

        .btn-purple-logout {
            background: linear-gradient(135deg, #6f5cc3, #5a47a8);
            color: #fff;
            border: none;
            transition: all 0.2s;
        }

        .btn-purple-logout:hover {
            background: linear-gradient(135deg, #5a47a8, #48378b);
            color: #fff;
            box-shadow: 0 4px 10px rgba(111, 92, 195, 0.3);
        }

        /* STYLES KHUNG ADMIN LIVECHAT (LAB 07) */
        #admin-chat-box {
            position: fixed;
            bottom: 25px;
            right: 25px;
            z-index: 9999;
        }
        #admin-chat-popup {
            width: 420px;
            height: 500px;
            display: flex;
            flex-direction: column;
            border-radius: 12px;
            overflow: hidden;
        }
        #admin-chat-messages {
            height: 320px;
            overflow-y: auto;
            padding: 12px;
            background-color: #f8f9fa;
        }
        .admin-msg-row {
            margin-bottom: 8px;
            padding: 8px 12px;
            border-radius: 10px;
            font-size: 13.5px;
            word-break: break-word;
        }
        .admin-sent {
            background-color: #6f5cc3;
            color: #ffffff;
            text-align: right;
            margin-left: 20%;
        }
        .user-sent {
            background-color: #e9ecef;
            color: #212529;
            text-align: left;
            margin-right: 20%;
        }
    </style>
</head>
<body>

<div class="admin-wrapper">
    <aside class="admin-sidebar">
        <div class="brand">
            <i class="bi bi-shop text-info me-2 fs-4"></i> Admin Portal
        </div>
        <nav class="nav flex-column">
            <!-- 1. Tổng quan -->
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2 me-2 fs-5"></i> Tổng quan
            </a>

           <!-- 2. Danh mục -->
            <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                <i class="bi bi-folder2-open me-2 fs-5"></i> Danh mục
            </a>

            <!-- 3. Sản phẩm -->
            <a href="{{ route('admin.products.index') }}" class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                <i class="bi bi-box-seam me-2 fs-5"></i> Sản phẩm
            </a>
            
            <!-- 4. Đơn hàng -->
            <a href="{{ route('admin.orders.index') }}" class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                <i class="bi bi-receipt me-2 fs-5"></i> Đơn hàng
            </a>

            <!-- Thống kê tài chính -->
            <a href="{{ route('admin.finance.index') }}" 
            class="nav-link {{ request()->routeIs('admin.finance.index') ? 'active' : '' }}">
                <i class="bi bi-graph-up-arrow"></i>
                <span>Thống kê tài chính</span>
            </a>

            <!-- Giao dịch thanh toán -->
            <a href="{{ route('admin.finance.transactions') }}" 
            class="nav-link {{ request()->routeIs('admin.finance.transactions') ? 'active' : '' }}">
                <i class="bi bi-credit-card"></i>
                <span>Giao dịch thanh toán</span>
            </a>

            <!-- 5. Người dùng (MỚI BỔ SUNG) -->
            <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="bi bi-people me-2 fs-5"></i> Người dùng
            </a>

            <!-- 6. Báo cáo doanh thu (MỚI BỔ SUNG) -->
            <a href="{{ route('admin.reports.index') }}" class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-line me-2 fs-5"></i> Báo cáo
            </a>

            <!-- 7. Livechat Hỗ trợ -->
            <a href="javascript:void(0)" id="admin-chat-menu-btn" class="nav-link">
                <i class="bi bi-chat-dots-fill me-2 fs-5 text-warning"></i> Tin nhắn hỗ trợ
            </a>
        </nav>
    </aside>

    <main class="admin-content">
        <header class="admin-topbar">
            <h5 class="m-0 fw-bold" style="color: #2a2265;">Hệ Thống Quản Lý</h5>
            
            <div class="d-flex align-items-center gap-3">
                <span class="text-secondary small">
                    <i class="bi bi-person-circle me-1" style="color: #6f5cc3;"></i> Admin: <strong>{{ Auth::user()->name ?? 'Quản trị viên' }}</strong>
                </span>
                
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-purple-logout btn-sm px-3 rounded-pill d-flex align-items-center gap-1">
                        <i class="bi bi-box-arrow-right"></i> Đăng xuất
                    </button>
                </form>
            </div>
        </header>

        <div class="px-4 pb-4">
            @yield('content')
        </div>
    </main>
</div>

<!-- ========================================== -->
<!-- LAB 07: KHU VỰC LIVECHAT POPUP DÀNH CHO ADMIN -->
<!-- ========================================== -->
<div id="admin-chat-box">
    <!-- Nút hình tròn bật popup -->
    <button id="admin-chat-toggle" class="btn btn-warning rounded-circle shadow-lg p-3 fw-bold fs-5 text-dark">
        💬
    </button>

    <!-- Khung Popup Chat Admin -->
    <div id="admin-chat-popup" class="card shadow-lg d-none">
        <div class="card-header text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #201b4e, #2a2265);">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-headset fs-5"></i>
                <span class="fw-bold">Hỗ trợ Khách hàng</span>
            </div>
            <button id="admin-chat-close" class="btn btn-sm btn-outline-light fw-bold">X</button>
        </div>

        <!-- Chọn khách hàng để trò chuyện -->
        <div class="p-2 bg-light border-bottom">
            <select id="user-select" class="form-select form-select-sm fw-semibold">
                <option value="">-- Chọn khách hàng cần tư vấn --</option>
            </select>
        </div>

        <!-- Khung chứa tin nhắn -->
        <div id="admin-chat-messages" class="card-body">
            <div class="text-center text-muted mt-4">
                <small>Vui lòng chọn một khách hàng từ danh sách trên để xem cuộc trò chuyện.</small>
            </div>
        </div>

        <!-- Khung nhập tin nhắn trả lời -->
        <div class="card-footer bg-white">
            <div class="input-group">
                <input type="text" id="admin-chat-input" class="form-control form-control-sm" placeholder="Nhập câu trả lời..." disabled>
                <button id="admin-send-btn" class="btn btn-primary btn-sm" disabled>Gửi</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- JAVASCRIPT XỬ LÝ LIVECHAT ADMIN (LAB 07) -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('admin-chat-toggle');
    const menuBtn = document.getElementById('admin-chat-menu-btn');
    const chatPopup = document.getElementById('admin-chat-popup');
    const closeBtn = document.getElementById('admin-chat-close');
    const userSelect = document.getElementById('user-select');
    const chatMessages = document.getElementById('admin-chat-messages');
    const chatInput = document.getElementById('admin-chat-input');
    const sendBtn = document.getElementById('admin-send-btn');

    let currentUserId = null;

    // Toggle hiển thị Khung Chat
    function openChat() {
        chatPopup.classList.remove('d-none');
        toggleBtn.classList.add('d-none');
        loadUsers();
    }

    function closeChat() {
        chatPopup.classList.add('d-none');
        toggleBtn.classList.remove('d-none');
    }

    toggleBtn.onclick = openChat;
    menuBtn.onclick = openChat;
    closeBtn.onclick = closeChat;

    // 1. Tải danh sách Khách hàng đã gửi tin
    function loadUsers() {
        fetch("{{ route('admin.chat.users') }}")
            .then(res => res.json())
            .then(users => {
                let options = '<option value="">-- Chọn khách hàng cần tư vấn --</option>';
                users.forEach(u => {
                    const isSelected = u.id == currentUserId ? 'selected' : '';
                    options += `<option value="${u.id}" ${isSelected}>👤 ${u.name} (${u.email})</option>`;
                });
                userSelect.innerHTML = options;
            })
            .catch(err => console.error("Lỗi tải danh sách người dùng:", err));
    }

    // 2. Chọn khách hàng & Tải lịch sử nhắn tin
    userSelect.addEventListener('change', function () {
        currentUserId = this.value;
        if (!currentUserId) {
            chatMessages.innerHTML = `<div class="text-center text-muted mt-4"><small>Vui lòng chọn một khách hàng để trò chuyện.</small></div>`;
            chatInput.disabled = true;
            sendBtn.disabled = true;
            return;
        }

        chatInput.disabled = false;
        sendBtn.disabled = false;
        loadMessages(currentUserId);
    });

    function loadMessages(userId) {
        if (!userId) return;

        let url = "{{ route('admin.chat.messages', ':id') }}".replace(':id', userId);

        fetch(url)
            .then(res => res.json())
            .then(messages => {
                let html = "";
                if (messages.length === 0) {
                    html = "<div class='text-center text-muted mt-3'><small>Chưa có tin nhắn nào từ khách hàng này.</small></div>";
                } else {
                    messages.forEach(msg => {
                        const isAdmin = msg.sender_id == "{{ Auth::id() }}";
                        html += `
                            <div class="admin-msg-row ${isAdmin ? 'admin-sent' : 'user-sent'}">
                                <strong>${isAdmin ? 'Bạn (Admin)' : 'Khách hàng'}:</strong> ${msg.content}
                            </div>
                        `;
                    });
                }
                chatMessages.innerHTML = html;
                chatMessages.scrollTop = chatMessages.scrollHeight;
            })
            .catch(err => console.error("Lỗi tải tin nhắn:", err));
    }

    // 3. Gửi tin nhắn phản hồi Khách hàng
    function sendMessage() {
        let message = chatInput.value.trim();
        if (!message || !currentUserId) return;

        chatInput.disabled = true;
        sendBtn.disabled = true;

        fetch("{{ route('admin.chat.send') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": '{{ csrf_token() }}',
                "Content-Type": "application/json",
                "Accept": "application/json"
            },
            body: JSON.stringify({
                user_id: currentUserId,
                message: message
            })
        })
        .then(res => res.json())
        .then(data => {
            chatInput.value = "";
            chatInput.disabled = false;
            sendBtn.disabled = false;
            chatInput.focus();
            loadMessages(currentUserId);
        })
        .catch(err => {
            console.error("Lỗi gửi tin nhắn Admin:", err);
            chatInput.disabled = false;
            sendBtn.disabled = false;
        });
    }

    sendBtn.onclick = sendMessage;
    chatInput.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });

    // 4. Tự động kiểm tra tin nhắn mới mỗi 3 giây
    setInterval(() => {
        if (!chatPopup.classList.contains('d-none') && currentUserId) {
            loadMessages(currentUserId);
        }
    }, 3000);
});
</script>

</body>
</html>