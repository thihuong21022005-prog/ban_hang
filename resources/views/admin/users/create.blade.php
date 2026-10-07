@extends('layout.admin')
@section('content')
<div class="container">
    <h2 class="mb-4">Thêm người dùng</h2>
    <form action="{{ route('admin.users.store') }}" method="POST">
        @csrf
        <div class="form-group mb-3">
            <label>Tên</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="form-group mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="form-group mb-3">
            <label>Mật khẩu</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="form-group mb-3">
            <label>Vai trò</label>
            <select name="role" class="form-control" required>
                <option value="user">Người dùng</option>
                <option value="admin">Quản trị</option>
            </select>
        </div>
        <button type="submit" class="btn btn-success">Lưu</button>
    </form>
</div>
@endsection