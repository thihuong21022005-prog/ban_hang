@extends('layout.admin')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold m-0" style="color: #2a2265;">Danh sách người dùng</h3>
        <a href="{{ route('admin.users.create') }}" class="btn btn-success rounded-pill px-3">
            <i class="bi bi-plus-lg me-1"></i> + Thêm người dùng
        </a>
    </div>

    {{-- HIỂN THỊ THÔNG BÁO --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- BẢNG DANH SÁCH NGƯỜI DÙNG --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">ID</th>
                            <th>Tên</th>
                            <th>Email</th>
                            <th>Vai trò</th>
                            <th class="text-end pe-3">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td class="ps-3">{{ $user->id }}</td>
                            <td><strong>{{ $user->name }}</strong></td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if($user->role === 'admin')
                                    <span class="badge bg-danger px-2 py-1">Admin</span>
                                @else
                                    <span class="badge bg-secondary px-2 py-1">User</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-info btn-sm text-white">Xem</a>
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary btn-sm">Sửa</a>
                                
                                {{-- Không cho phép xóa chính tài khoản Admin đang đăng nhập --}}
                                @if($user->id !== Auth::id())
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa người dùng này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Xóa</button>
                                    </form>
                                @else
                                    <span class="badge bg-light text-muted border ms-1">Tài khoản hiện tại</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Không có người dùng nào.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        {{-- NÚT PHÂN TRANG --}}
        @if(method_exists($users, 'links'))
            <div class="card-footer bg-white border-0 py-3">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection