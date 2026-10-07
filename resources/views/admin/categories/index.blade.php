@extends('layout.admin')

@section('content')
<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="fw-bold mb-3">Thêm Danh Mục Mới</h5>
                <!-- Đã cập nhật: admin.categories.store -->
                <form action="{{ route('admin.categories.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Tên Danh Mục</label>
                        <input type="text" name="name" class="form-control" placeholder="VD: Giường Gỗ Hiện Đại" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Loại Sản Phẩm</label>
                        <select name="type" class="form-select" required>
                            <option value="Giường">Giường</option>
                            <option value="Tủ">Tủ</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Thêm Mới</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <table class="table align-middle m-0">
                    <thead class="table-light">
                        <tr>
                            <th>STT</th>
                            <th>Tên Danh Mục</th>
                            <th>Loại</th>
                            <th>Số Sản Phẩm</th>
                            <th>Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $cat)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><strong>{{ $cat->name }}</strong></td>
                            <td>
                                <span class="badge {{ $cat->type == 'Giường' ? 'bg-info text-dark' : 'bg-warning text-dark' }}">
                                    {{ $cat->type }}
                                </span>
                            </td>
                            <td>{{ $cat->products_count }} sản phẩm</td>
                            <td>
                                <!-- Đã cập nhật: admin.categories.destroy -->
                                <form action="{{ route('admin.categories.destroy', $cat->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Xóa danh mục này?')">Xóa</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Chưa có danh mục nào.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection