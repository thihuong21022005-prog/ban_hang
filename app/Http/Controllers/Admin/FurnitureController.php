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
            'quantity'             => 'required|integer|min:0',
            'description'          => 'nullable|string',
            'main_image'           => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',

            'variants'             => 'nullable|array',
            'variants.*.size'      => 'required_with:variants|string|max:255',
            'variants.*.quantity'  => 'required_with:variants|integer|min:0',
            'variants.*.price'     => 'nullable|numeric|min:0',
        ]);

        $validated['is_featured'] = $request->has('is_featured');

        if ($request->hasFile('main_image')) {
            $validated['main_image'] = $this->imageToBase64($request->file('main_image'));
        }

        DB::beginTransaction();
        try {
            $furniture = Furniture::create($validated);

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

            'variants'             => 'nullable|array',
            'variants.*.id'        => 'nullable|exists:furniture_variants,id',
            'variants.*.size'      => 'required_with:variants|string|max:255',
            'variants.*.quantity'  => 'required_with:variants|integer|min:0',
            'variants.*.price'     => 'nullable|numeric|min:0',
        ]);

        $validated['is_featured'] = $request->has('is_featured');

        if ($request->hasFile('main_image')) {
            $this->deleteOldImage($furniture->main_image);
            $validated['main_image'] = $this->imageToBase64($request->file('main_image'));
        }

        DB::beginTransaction();
        try {
            $furniture->update($validated);

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

                $furniture->variants()->whereNotIn('id', $keepVariantIds)->delete();
            } else {
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
        $this->deleteOldImage($furniture->main_image);

        $furniture->delete();

        return redirect()->route('admin.products.index')->with('success', 'Xóa sản phẩm thành công!');
    }

    // Chuyển ảnh upload thành chuỗi base64 để lưu thẳng vào database
    private function imageToBase64($file): string
    {
        return 'data:' . $file->getMimeType() . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
    }

    // Chỉ xóa file khi ảnh cũ là đường dẫn trong storage (không phải base64 hay URL)
    private function deleteOldImage(?string $path): void
    {
        if ($path && !str_starts_with($path, 'data:') && !str_starts_with($path, 'http')) {
            Storage::disk('public')->delete($path);
        }
    }
}