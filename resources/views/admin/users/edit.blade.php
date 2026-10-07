@extends('layout.admin')
@section('content')
<div class="container">
    <h2 class="mb-4">Chỉnh sửa người dùng</h2>
    <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group mb-3">
            <label>Tên</label>
            <input type="text" name="name" value="{{ $user->name }}" class="form-control" required>
        </div>
        <div class="form-group mb-3">
            <label>Email</label>
            <input type="email" name="email" value="{{ $user->email }}" class="form-control" required>
        </div>
        <div class="form-group mb-3">
            <label>Vai trò</label>
            <select name="role" class="form-control" required>
                <option value="user" @selected($user->role === 'user')>Người dùng</option>
                <option value="admin" @selected($user->role === 'admin')>Quản trị</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Cập nhật</button>
    </form>
</div>
@endsection