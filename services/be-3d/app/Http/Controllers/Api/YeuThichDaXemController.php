<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\YeuThich;
use App\Models\DaXem;
use App\Models\SanPham;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class YeuThichDaXemController extends Controller
{
    /**
     * Helper to format product details for response.
     */
    private function formatProducts($productsCollection)
    {
        return $productsCollection->map(function ($item) {
            $sanPham = $item->sanPham;
            if (!$sanPham) return null;

            // Get product image
            $image = "";
            if ($sanPham->hinhAnhs) {
                $mainImg = $sanPham->hinhAnhs->where('la_anh_dai_dien', true)->first();
                if ($mainImg) {
                    $image = $mainImg->duong_dan_anh;
                } else if ($sanPham->hinhAnhs->count() > 0) {
                    $image = $sanPham->hinhAnhs->first()->duong_dan_anh;
                }
            }

            return [
                'id' => $sanPham->id,
                'ten_san_pham' => $sanPham->ten_san_pham,
                'gia_co_ban' => (float)$sanPham->gia_co_ban,
                'gia_goc' => $sanPham->gia_goc ? (float)$sanPham->gia_goc : null,
                'hinh_anh' => $image,
            ];
        })->filter()->values();
    }

    /**
     * Get the authenticated user's wishlist.
     */
    public function getYeuThich(Request $request)
    {
        try {
            $user = $request->user();
            $items = YeuThich::where('id_nguoi_dung', $user->id)
                ->with(['sanPham.hinhAnhs'])
                ->orderBy('tao_luc', 'desc')
                ->get();

            return response()->json([
                'status' => true,
                'wishlist' => $this->formatProducts($items),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Lỗi khi tải danh sách yêu thích: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle a product in the wishlist (add if not exists, remove if exists).
     */
    public function toggleYeuThich(Request $request)
    {
        $request->validate([
            'id_san_pham' => 'required|exists:san_pham,id',
        ]);

        try {
            $user = $request->user();
            $productId = $request->id_san_pham;

            $item = YeuThich::where('id_nguoi_dung', $user->id)
                ->where('id_san_pham', $productId)
                ->first();

            if ($item) {
                $item->delete();
                $message = 'Đã xóa sản phẩm khỏi danh sách yêu thích.';
                $isWished = false;
            } else {
                YeuThich::create([
                    'id_nguoi_dung' => $user->id,
                    'id_san_pham' => $productId,
                ]);
                $message = 'Đã thêm sản phẩm vào danh sách yêu thích.';
                $isWished = true;
            }

            // Get updated list
            $items = YeuThich::where('id_nguoi_dung', $user->id)
                ->with(['sanPham.hinhAnhs'])
                ->orderBy('tao_luc', 'desc')
                ->get();

            return response()->json([
                'status' => true,
                'message' => $message,
                'is_wished' => $isWished,
                'wishlist' => $this->formatProducts($items),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Lỗi khi thay đổi trạng thái yêu thích: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync local guest wishlist to backend database.
     */
    public function syncYeuThich(Request $request)
    {
        $request->validate([
            'id_san_phams' => 'required|array',
            'id_san_phams.*' => 'exists:san_pham,id',
        ]);

        try {
            $user = $request->user();

            DB::transaction(function () use ($user, $request) {
                foreach ($request->id_san_phams as $productId) {
                    YeuThich::firstOrCreate([
                        'id_nguoi_dung' => $user->id,
                        'id_san_pham' => $productId,
                    ]);
                }
            });

            $items = YeuThich::where('id_nguoi_dung', $user->id)
                ->with(['sanPham.hinhAnhs'])
                ->orderBy('tao_luc', 'desc')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Đã đồng bộ danh sách yêu thích thành công.',
                'wishlist' => $this->formatProducts($items),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Lỗi khi đồng bộ danh sách yêu thích: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the authenticated user's recently viewed products.
     */
    public function getDaXem(Request $request)
    {
        try {
            $user = $request->user();
            $items = DaXem::where('id_nguoi_dung', $user->id)
                ->with(['sanPham.hinhAnhs'])
                ->orderBy('cap_nhat_luc', 'desc')
                ->orderBy('id', 'desc')
                ->limit(20)
                ->get();

            return response()->json([
                'status' => true,
                'recentlyViewed' => $this->formatProducts($items),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Lỗi khi tải danh sách đã xem: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add a product to the recently viewed history.
     */
    public function addDaXem(Request $request)
    {
        $request->validate([
            'id_san_pham' => 'required|exists:san_pham,id',
        ]);

        try {
            $user = $request->user();
            $productId = $request->id_san_pham;

            // Use updateOrCreate to refresh updated_at (cap_nhat_luc)
            DaXem::updateOrCreate(
                ['id_nguoi_dung' => $user->id, 'id_san_pham' => $productId],
                ['cap_nhat_luc' => now()]
            );

            // Prune history: keep only 20
            $count = DaXem::where('id_nguoi_dung', $user->id)->count();
            if ($count > 20) {
                $keepIds = DaXem::where('id_nguoi_dung', $user->id)
                    ->orderBy('cap_nhat_luc', 'desc')
                    ->orderBy('id', 'desc')
                    ->limit(20)
                    ->pluck('id');
                
                DaXem::where('id_nguoi_dung', $user->id)
                    ->whereNotIn('id', $keepIds)
                    ->delete();
            }

            $items = DaXem::where('id_nguoi_dung', $user->id)
                ->with(['sanPham.hinhAnhs'])
                ->orderBy('cap_nhat_luc', 'desc')
                ->orderBy('id', 'desc')
                ->limit(20)
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Đã cập nhật danh sách đã xem.',
                'recentlyViewed' => $this->formatProducts($items),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Lỗi khi cập nhật danh sách đã xem: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync local guest recently viewed history to backend database.
     */
    public function syncDaXem(Request $request)
    {
        $request->validate([
            'id_san_phams' => 'required|array',
            'id_san_phams.*' => 'exists:san_pham,id',
        ]);

        try {
            $user = $request->user();

            DB::transaction(function () use ($user, $request) {
                // Reverse the array to maintain order (oldest to newest, so the latest is updated last)
                $reversedIds = array_reverse($request->id_san_phams);
                foreach ($reversedIds as $productId) {
                    DaXem::updateOrCreate(
                        ['id_nguoi_dung' => $user->id, 'id_san_pham' => $productId],
                        ['cap_nhat_luc' => now()]
                    );
                }
            });

            // Prune history: keep only 20
            $count = DaXem::where('id_nguoi_dung', $user->id)->count();
            if ($count > 20) {
                $keepIds = DaXem::where('id_nguoi_dung', $user->id)
                    ->orderBy('cap_nhat_luc', 'desc')
                    ->orderBy('id', 'desc')
                    ->limit(20)
                    ->pluck('id');
                
                DaXem::where('id_nguoi_dung', $user->id)
                    ->whereNotIn('id', $keepIds)
                    ->delete();
            }

            $items = DaXem::where('id_nguoi_dung', $user->id)
                ->with(['sanPham.hinhAnhs'])
                ->orderBy('cap_nhat_luc', 'desc')
                ->orderBy('id', 'desc')
                ->limit(20)
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Đã đồng bộ danh sách đã xem thành công.',
                'recentlyViewed' => $this->formatProducts($items),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Lỗi khi đồng bộ danh sách đã xem: ' . $e->getMessage()
            ], 500);
        }
    }
}
