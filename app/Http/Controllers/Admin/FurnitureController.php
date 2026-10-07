<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Furniture;
use App\Models\Category;
use App\Models\FurnitureVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FurnitureController extends Controller
{
    public function index()
    {
        // Eager load mối quan hệ variants để hiển thị tổng số biến thể nếu cần
        $furnitures = Furniture::with(['category', 'variants'])->latest()->paginate(10);
        return view('admin.furnitures.index', compact('furnitures'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.furnitures.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'category_id'          => 'required|exists:categories,id',
            'price'                => 'required|numeric|min:0',
            'quantity'             => 'required|integer|min:0', // Số lượng tổng / mặc định
            'description'          => 'nullable|string',
            'main_image'           => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            
            // Validate mảng Kích thước / Biến thể gửi từ Form
            'variants'             => 'nullable|array',
            'variants.*.size'      => 'required_with:variants|string|max:255',
            'variants.*.quantity'  => 'required_with:variants|integer|min:0',
            'variants.*.price'     => 'nullable|numeric|min:0',
        ]);

        $validated['is_featured'] = $request->has('is_featured');

        if ($request->hasFile('main_image')) {
            $validated['main_image'] = $request->file('main_image')->store('products/main', 'public');
        }

        DB::beginTransaction();
        try {
            // 1. Tạo sản phẩm chính
            $furniture = Furniture::create($validated);

            // 2. Lưu danh sách Kích thước & Số lượng tương ứng (nếu có)
            if ($request->filled('variants')) {
                foreach ($request->variants as $variantData) {
                    if (!empty($variantData['size'])) {
                        $furniture->variants()->create([
                            'size'     => $variantData['size'],
                            'quantity' => $variantData['quantity'] ?? 0,
                            'price'    => !empty($variantData['price']) ? $variantData['price'] : $furniture->price,
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('admin.products.index')->with('success', 'Thêm sản phẩm và kích thước thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Furniture $furniture)
    {
        $furniture->load(['category', 'variants']);
        return view('admin.furnitures.show', compact('furniture'));
    }

    public function edit(Furniture $furniture)
    {
        $categories = Category::all();
        // Tải danh sách kích thước hiện có của sản phẩm
        $furniture->load('variants');
        return view('admin.furnitures.edit', compact('furniture', 'categories'));
    }

    public function update(Request $request, Furniture $furniture)
    {
        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'category_id'          => 'required|exists:categories,id',
            'price'                => 'required|numeric|min:0',
            'quantity'             => 'required|integer|min:0',
            'description'          => 'nullable|string',
            'main_image'           => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',

            // Validate mảng Kích thước
            'variants'             => 'nullable|array',
            'variants.*.id'        => 'nullable|exists:furniture_variants,id',
            'variants.*.size'      => 'required_with:variants|string|max:255',
            'variants.*.quantity'  => 'required_with:variants|integer|min:0',
            'variants.*.price'     => 'nullable|numeric|min:0',
        ]);

        $validated['is_featured'] = $request->has('is_featured');

        if ($request->hasFile('main_image')) {
            if ($furniture->main_image) {
                Storage::disk('public')->delete($furniture->main_image);
            }
            $validated['main_image'] = $request->file('main_image')->store('products/main', 'public');
        }

        DB::beginTransaction();
        try {
            // 1. Cập nhật thông tin sản phẩm gốc
            $furniture->update($validated);

            // 2. Đồng bộ danh sách Kích thước (Thêm mới / Cập nhật / Xóa bỏ)
            if ($request->has('variants')) {
                $keepVariantIds = [];

                foreach ($request->variants as $variantData) {
                    if (!empty($variantData['size'])) {
                        $variant = $furniture->variants()->updateOrCreate(
                            ['id' => $variantData['id'] ?? null],
                            [
                                'size'     => $variantData['size'],
                                'quantity' => $variantData['quantity'] ?? 0,
                                'price'    => !empty($variantData['price']) ? $variantData['price'] : $furniture->price,
                            ]
                        );
                        $keepVariantIds[] = $variant->id;
                    }
                }

                // Xóa những kích thước đã bị xóa trên giao diện Form
                $furniture->variants()->whereNotIn('id', $keepVariantIds)->delete();
            } else {
                // Nếu Admin xóa hết kích thước trên giao diện -> Xóa toàn bộ biến thể trong DB
                $furniture->variants()->delete();
            }

            DB::commit();
            return redirect()->route('admin.products.index')->with('success', 'Cập nhật sản phẩm và kích thước thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Cập nhật thất bại: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Furniture $furniture)
    {
        if ($furniture->main_image) {
            Storage::disk('public')->delete($furniture->main_image);
        }

        // Do đã cài ON DELETE CASCADE ở DB nên các biến thể kích thước sẽ tự động xóa theo
        $furniture->delete();

        return redirect()->route('admin.products.index')->with('success', 'Xóa sản phẩm thành công!');
    }
}