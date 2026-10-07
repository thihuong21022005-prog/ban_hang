@extends('layout.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0">Danh Sách Sản Phẩm</h4>
    <!-- Đã cập nhật: admin.products.create -->
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">+ Thêm Sản Phẩm Mới</a>
</div>

@if ($message = Session::get('success'))
    <div class="alert alert-success border-0 shadow-sm mb-3">{{ $message }}</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">STT</th>
                        <th>Ảnh Đại Diện</th>
                        <th>Tên Sản Phẩm</th>
                        <th>Danh Mục</th>
                        <th>Giá Bán</th>
                        <th class="text-center">Số Lượng Kho</th>
                        <th class="text-center">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($furnitures as $item)
                    <tr>
                        <td class="ps-3">{{ $loop->iteration }}</td>
                        <td>
                            @if($item->main_image)
                                <img src="{{ asset('storage/' . $item->main_image) }}" class="rounded" width="50" height="50" style="object-fit: cover;">
                            @else
                                <span class="badge bg-secondary">Không ảnh</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $item->name }}</strong>
                            @if($item->is_featured)
                                <span class="badge bg-danger ms-1">Nổi bật</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ optional($item->category)->type == 'Giường' ? 'bg-info text-dark' : 'bg-warning text-dark' }}">
                                {{ optional($item->category)->type }} - {{ optional($item->category)->name }}
                            </span>
                        </td>
                        <td class="text-primary fw-bold">
                            {{ number_format($item->price ?? 0, 0, ',', '.') }}đ
                        </td>

                        <!-- HIỂN THỊ SỐ LƯỢNG KHO -->
                        <td class="text-center">
                            @if(($item->quantity ?? 0) > 10)
                                <span class="badge bg-success px-2 py-1">{{ $item->quantity }}</span>
                            @elseif(($item->quantity ?? 0) > 0)
                                <span class="badge bg-warning text-dark px-2 py-1">Còn {{ $item->quantity }}</span>
                            @else
                                <span class="badge bg-danger px-2 py-1">Hết hàng</span>
                            @endif
                        </td>

                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <!-- Đã cập nhật: admin.products.edit -->
                                <a href="{{ route('admin.products.edit', $item->id) }}" class="btn btn-sm btn-outline-primary">Sửa</a>
                                
                                <!-- Đã cập nhật: admin.products.destroy -->
                                <form action="{{ route('admin.products.destroy', $item->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Xóa sản phẩm này?')">Xóa</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Chưa có sản phẩm nào.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection