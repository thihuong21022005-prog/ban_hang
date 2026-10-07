<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Furniture;
use App\Models\FurnitureVariant;
use Illuminate\Support\Str;

class CartController extends Controller
{
    // Hiển thị danh sách sản phẩm trong giỏ
    public function index()
    {
        $cart = session()->get('cart', []);
        return view('cart.index', compact('cart'));
    }

    // Thêm sản phẩm vào giỏ hàng (Hỗ trợ cả Fetch API JSON & Form Redirect)
    public function add(Request $request, $id)
    {
        $product = Furniture::findOrFail($id);
        $quantityToAdd = max(1, (int) $request->input('quantity', 1));
        
        // 1. Nhận thông tin Size & Variant ID (Khớp với cả fetch JS và Form HTML)
        $variantId = $request->input('furniture_variant_id', $request->input('variant_id'));
        $selectedDimension = $request->input('selected_dimension', 'Tiêu chuẩn');

        $variant = null;
        if ($variantId) {
            $variant = FurnitureVariant::find($variantId);
        }

        // 2. Tự động xác định Tồn kho & Giá bán chuẩn theo Size
        if ($variant) {
            $selectedDimension = $variant->size;
            $stock = $variant->quantity ?? 0;
            $price = $variant->price ?: $product->price;
        } else {
            $stock = $product->quantity ?? $product->stock ?? 0;
            $price = $product->price;
        }

        // 3. Tạo Khóa định danh (Cart Key) riêng cho từng Sản phẩm + Size
        $cartKey = $variantId ? "{$id}_v{$variantId}" : "{$id}_" . Str::slug($selectedDimension);

        $cart = session()->get('cart', []);

        // Lấy số lượng đã có của đúng Size này trong giỏ
        $currentInCart = isset($cart[$cartKey]) ? (int) $cart[$cartKey]['quantity'] : 0;
        $totalRequested = $currentInCart + $quantityToAdd;

        // 4. Kiểm tra tồn kho của Size chọn
        if ($stock <= 0) {
            $errorMsg = "Sản phẩm \"{$product->name}\" (Kích thước: {$selectedDimension}) hiện đã hết hàng!";
            
            if ($request->wantsJson() || $request->ajax() || $request->isJson()) {
                return response()->json(['error' => $errorMsg], 400);
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        if ($totalRequested > $stock) {
            $msg = $currentInCart > 0 
                ? "Sản phẩm \"{$product->name}\" (Kích thước: {$selectedDimension}) chỉ còn {$stock} cái trong kho (Bạn đã có {$currentInCart} cái trong giỏ)."
                : "Sản phẩm \"{$product->name}\" (Kích thước: {$selectedDimension}) chỉ còn {$stock} cái trong kho.";

            if ($request->wantsJson() || $request->ajax() || $request->isJson()) {
                return response()->json(['error' => $msg], 400);
            }
            return redirect()->back()->with('error', $msg);
        }

        // 5. Lưu thông tin vào Session Giỏ hàng
        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] = $totalRequested;
        } else {
            $cart[$cartKey] = [
                "product_id" => $product->id,
                "variant_id" => $variantId,
                "name"       => $product->name,
                "dimension"  => $selectedDimension,
                "quantity"   => $quantityToAdd,
                "price"      => $price,
                "image"      => $product->main_image ?? $product->image ?? null,
            ];
        }

        session()->put('cart', $cart);
        $successMsg = "Đã thêm \"{$product->name}\" (Size: {$selectedDimension}) vào giỏ hàng!";

        // Trả về kết quả phù hợp với phương thức request
        if ($request->wantsJson() || $request->ajax() || $request->isJson()) {
            return response()->json([
                'success' => $successMsg,
                'cart_count' => count($cart)
            ]);
        }

        return redirect()->route('user.cart.index')->with('success', $successMsg);
    }

    // Cập nhật số lượng trong giỏ hàng ($id ở đây đóng vai trò là $cartKey)
    public function update(Request $request, $id)
    {
        $cartKey = $id;
        $cart = session()->get('cart', []);

        if (!isset($cart[$cartKey])) {
            $errorMsg = 'Sản phẩm không tồn tại trong giỏ hàng!';
            if ($request->wantsJson() || $request->ajax() || $request->isJson()) {
                return response()->json(['error' => $errorMsg], 404);
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        $newQuantity = max(1, (int) $request->quantity);
        $item = $cart[$cartKey];
        
        // Kiểm tra tồn kho thực tế của Size đó
        $stock = 99;
        if (!empty($item['variant_id'])) {
            $variant = FurnitureVariant::find($item['variant_id']);
            $stock = $variant ? ($variant->quantity ?? 0) : 0;
        } else {
            $product = Furniture::find($item['product_id'] ?? null);
            $stock = $product ? ($product->quantity ?? $product->stock ?? 0) : 0;
        }

        if ($newQuantity > $stock) {
            $errorMsg = "Rất tiếc, sản phẩm \"{$item['name']}\" (Size: {$item['dimension']}) chỉ còn {$stock} cái trong kho!";
            if ($request->wantsJson() || $request->ajax() || $request->isJson()) {
                return response()->json(['error' => $errorMsg], 400);
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        $cart[$cartKey]['quantity'] = $newQuantity;
        session()->put('cart', $cart);

        if ($request->wantsJson() || $request->ajax() || $request->isJson()) {
            return response()->json(['success' => 'Đã cập nhật số lượng thành công!']);
        }

        return redirect()->back()->with('success', 'Đã cập nhật số lượng thành công!');
    }

    // Xóa sản phẩm khỏi giỏ ($id ở đây đóng vai trò là $cartKey)
    public function remove(Request $request, $id)
    {
        $cartKey = $id;
        $cart = session()->get('cart', []);
        
        if (isset($cart[$cartKey])) {
            unset($cart[$cartKey]);
            session()->put('cart', $cart);
        }
        
        if ($request->wantsJson() || $request->ajax() || $request->isJson()) {
            return response()->json(['success' => 'Đã xóa sản phẩm khỏi giỏ hàng!']);
        }

        return redirect()->back()->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng!');
    }
}