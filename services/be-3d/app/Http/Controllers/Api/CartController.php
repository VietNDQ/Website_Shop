<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GioHang;
use App\Models\ChiTietGioHang;
use App\Models\BienTheSanPham;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Helper to get user cart, creating one if not exists.
     */
    private function getOrCreateUserCart($userId)
    {
        return GioHang::firstOrCreate(['id_nguoi_dung' => $userId]);
    }

    /**
     * Helper to format cart items for response.
     */
    private function formatCartItems($cartId)
    {
        $items = ChiTietGioHang::where('id_gio_hang', $cartId)
            ->with(['bienThe.sanPham.hinhAnhs'])
            ->get();

        return $items->map(function ($item) {
            $bienThe = $item->bienThe;
            $sanPham = $bienThe ? $bienThe->sanPham : null;
            
            // Format attributes description (e.g., "Size: M - Màu: Đỏ")
            $attributesStr = "";
            if ($bienThe && is_array($bienThe->thuoc_tinh)) {
                $attrs = [];
                foreach ($bienThe->thuoc_tinh as $key => $value) {
                    $attrs[] = "{$key}: {$value}";
                }
                $attributesStr = implode(' - ', $attrs);
            }

            // Get product image
            $image = "";
            if ($sanPham && $sanPham->hinhAnhs) {
                $mainImg = $sanPham->hinhAnhs->where('la_anh_dai_dien', true)->first();
                if ($mainImg) {
                    $image = $mainImg->duong_dan_anh;
                } else if ($sanPham->hinhAnhs->count() > 0) {
                    $image = $sanPham->hinhAnhs->first()->duong_dan_anh;
                }
            }

            return [
                'id_bien_the' => $item->id_bien_the,
                'id_san_pham' => $sanPham ? $sanPham->id : null,
                'ten_san_pham' => $sanPham ? $sanPham->ten_san_pham : 'Sản phẩm đã xóa',
                'ten_bien_the' => $attributesStr,
                'gia_ban' => $bienThe ? (float)$bienThe->gia_ban : 0.0,
                'so_luong_ton_kho' => $bienThe ? $bienThe->so_luong_ton_kho : 0,
                'hinh_anh' => $image,
                'so_luong' => $item->so_luong,
                'duong_dan_mau_danh_muc' => ($sanPham && $sanPham->danhMuc) ? $sanPham->danhMuc->duong_dan_mau : null,
            ];
        });
    }

    /**
     * Get the authenticated user's cart.
     */
    public function getCart(Request $request)
    {
        try {
            $cart = $this->getOrCreateUserCart($request->user()->id);
            $formattedItems = $this->formatCartItems($cart->id);

            return response()->json([
                'status' => true,
                'cart' => $formattedItems,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Lỗi khi tải giỏ hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add an item to the cart.
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'id_bien_the' => 'required|exists:bien_the_san_pham,id',
            'so_luong' => 'required|integer|min:1',
        ]);

        try {
            $variant = BienTheSanPham::findOrFail($request->id_bien_the);
            $cart = $this->getOrCreateUserCart($request->user()->id);

            $cartItem = ChiTietGioHang::where('id_gio_hang', $cart->id)
                ->where('id_bien_the', $variant->id)
                ->first();

            $newQty = $request->so_luong;
            if ($cartItem) {
                $newQty += $cartItem->so_luong;
            }

            // Check variant stock
            if ($variant->so_luong_ton_kho < $newQty) {
                return response()->json([
                    'status' => false,
                    'message' => "Không đủ hàng tồn kho. Chỉ còn {$variant->so_luong_ton_kho} sản phẩm."
                ], 400);
            }

            if ($cartItem) {
                $cartItem->update(['so_luong' => $newQty]);
            } else {
                ChiTietGioHang::create([
                    'id_gio_hang' => $cart->id,
                    'id_bien_the' => $variant->id,
                    'so_luong' => $request->so_luong,
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Đã thêm sản phẩm vào giỏ hàng thành công.',
                'cart' => $this->formatCartItems($cart->id),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Lỗi khi thêm sản phẩm: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update quantity of a cart item.
     */
    public function updateQuantity(Request $request)
    {
        $request->validate([
            'id_bien_the' => 'required|exists:bien_the_san_pham,id',
            'so_luong' => 'required|integer|min:1',
        ]);

        try {
            $variant = BienTheSanPham::findOrFail($request->id_bien_the);
            $cart = $this->getOrCreateUserCart($request->user()->id);

            // Check variant stock
            if ($variant->so_luong_ton_kho < $request->so_luong) {
                return response()->json([
                    'status' => false,
                    'message' => "Không đủ hàng tồn kho. Chỉ còn {$variant->so_luong_ton_kho} sản phẩm."
                ], 400);
            }

            $cartItem = ChiTietGioHang::where('id_gio_hang', $cart->id)
                ->where('id_bien_the', $variant->id)
                ->first();

            if ($cartItem) {
                $cartItem->update(['so_luong' => $request->so_luong]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Không tìm thấy mặt hàng này trong giỏ hàng.'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Đã cập nhật số lượng thành công.',
                'cart' => $this->formatCartItems($cart->id),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Lỗi khi cập nhật giỏ hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove an item from the cart.
     */
    public function removeFromCart(Request $request)
    {
        $request->validate([
            'id_bien_the' => 'required|exists:bien_the_san_pham,id',
        ]);

        try {
            $cart = $this->getOrCreateUserCart($request->user()->id);

            $cartItem = ChiTietGioHang::where('id_gio_hang', $cart->id)
                ->where('id_bien_the', $request->id_bien_the)
                ->first();

            if ($cartItem) {
                $cartItem->delete();
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Không tìm thấy mặt hàng này trong giỏ hàng.'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Đã xóa sản phẩm khỏi giỏ hàng.',
                'cart' => $this->formatCartItems($cart->id),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Lỗi khi xóa sản phẩm: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync local guest cart items to backend cart (Merge).
     */
    public function syncCart(Request $request)
    {
        $request->validate([
            'cart_items' => 'required|array',
            'cart_items.*.id_bien_the' => 'required|exists:bien_the_san_pham,id',
            'cart_items.*.so_luong' => 'required|integer|min:1',
        ]);

        try {
            $cart = $this->getOrCreateUserCart($request->user()->id);

            DB::transaction(function () use ($cart, $request) {
                foreach ($request->cart_items as $item) {
                    $variant = BienTheSanPham::find($item['id_bien_the']);
                    if (!$variant) continue;

                    $cartItem = ChiTietGioHang::where('id_gio_hang', $cart->id)
                        ->where('id_bien_the', $item['id_bien_the'])
                        ->first();

                    $newQty = $item['so_luong'];
                    if ($cartItem) {
                        $newQty += $cartItem->so_luong;
                    }

                    // Limit quantity to available variant stock during merge
                    if ($variant->so_luong_ton_kho < $newQty) {
                        $newQty = $variant->so_luong_ton_kho;
                    }

                    if ($newQty > 0) {
                        if ($cartItem) {
                            $cartItem->update(['so_luong' => $newQty]);
                        } else {
                            ChiTietGioHang::create([
                                'id_gio_hang' => $cart->id,
                                'id_bien_the' => $item['id_bien_the'],
                                'so_luong' => $newQty,
                            ]);
                        }
                    }
                }
            });

            return response()->json([
                'status' => true,
                'message' => 'Đã đồng bộ giỏ hàng thành công.',
                'cart' => $this->formatCartItems($cart->id),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Lỗi khi đồng bộ giỏ hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear all items in the cart.
     */
    public function clearCart(Request $request)
    {
        try {
            $cart = $this->getOrCreateUserCart($request->user()->id);
            ChiTietGioHang::where('id_gio_hang', $cart->id)->delete();

            return response()->json([
                'status' => true,
                'message' => 'Giỏ hàng đã được xóa sạch.',
                'cart' => [],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Lỗi khi xóa giỏ hàng: ' . $e->getMessage()
            ], 500);
        }
    }
}
