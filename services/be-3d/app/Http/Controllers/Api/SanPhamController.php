<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SanPham;
use App\Models\BienTheSanPham;
use App\Models\HinhAnhSanPham;
use App\Http\Requests\SanPhamRequestCreate;
use App\Http\Requests\SanPhamRequestUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SanPhamController extends Controller
{
    /**
     * Lấy danh sách sản phẩm
     */
    public function danhSach(Request $request)
    {
        $danhSach = SanPham::with(['danhMuc', 'hinhAnhs', 'bienThes', 'thuocTinhs'])->orderBy('id', 'desc')->get();

        $data = $danhSach->map(function ($sp) {
            $tongTonKho = $sp->so_luong_ton_kho !== null ? (int)$sp->so_luong_ton_kho : (int)$sp->bienThes->sum('so_luong_ton_kho');
            $sku = $sp->bienThes->first() ? $sp->bienThes->first()->ma_kho : 'N/A';
            $anhDaiDien = $sp->hinhAnhs->firstWhere('la_anh_dai_dien', true);
            $anhPhu = $sp->hinhAnhs->where('la_anh_dai_dien', false)->values();

            // Map tinh_trang từ DB sang key cho FE
            $statusMap = [1 => 'active', 0 => 'out', 2 => 'hidden'];

            return [
                'id'         => $sp->id,
                'name'       => $sp->ten_san_pham,
                'category'   => $sp->danhMuc ? $sp->danhMuc->ten_danh_muc : 'Chưa phân loại',
                'id_danh_muc' => $sp->id_danh_muc,
                'price'      => $sp->gia_co_ban,
                'priceOrig'  => $sp->gia_goc ?: null,
                'desc'       => $sp->mo_ta,
                'stock'      => $tongTonKho,
                'sku'        => $sku,
                'image'      => $anhDaiDien ? $anhDaiDien->duong_dan_anh : null,
                'gallery'    => $anhPhu->pluck('duong_dan_anh')->toArray(),
                'status'     => $statusMap[$sp->tinh_trang] ?? 'active',
                'bien_the'   => $sp->bienThes,
                'thuoc_tinh' => $sp->thuocTinhs->map(function ($tt) {
                    return [
                        'id' => $tt->id,
                        'ten_thuoc_tinh' => $tt->ten_thuoc_tinh,
                        'gia_tri' => $tt->gia_tri,
                    ];
                }),
            ];
        });

        return response()->json([
            'status' => 1,
            'data' => $data
        ]);
    }

    public function them(SanPhamRequestCreate $request)
    {
        $nhanVien = Auth::user();

        DB::beginTransaction();
        try {
            // Map status string từ FE sang tinyInteger cho DB
            $tinhTrangMap = ['active' => 1, 'out' => 0, 'hidden' => 2];

            $bienTheInput = $request->input('bien_the');
            if (is_string($bienTheInput)) {
                $bienTheInput = json_decode($bienTheInput, true);
            }

            $giaCoBan = $request->gia_co_ban;
            $soLuongTonKho = $request->so_luong_ton_kho ?: 0;
            $hasVariants = is_array($bienTheInput) && count($bienTheInput) > 0;

            if ($hasVariants) {
                // Tự động lấy min('price') của các biến thể
                $giaCoBan = collect($bienTheInput)->min('price') ?? 0;
                // Bổ sung tính toán Tổng Tồn Kho ở Backend
                $soLuongTonKho = collect($bienTheInput)->sum('stock') ?? 0;
            }

            $sanPham = SanPham::create([
                'ten_san_pham' => $request->ten_san_pham,
                'gia_co_ban' => $giaCoBan,
                'gia_goc'    => $request->gia_goc ?: null,
                'id_danh_muc' => $request->id_danh_muc,
                'mo_ta' => $request->mo_ta,
                'tinh_trang' => $tinhTrangMap[$request->tinh_trang] ?? 1,
                'so_luong_ton_kho' => $soLuongTonKho,
            ]);

            // Save attributes (thuoc_tinh)
            $thuocTinhInput = $request->input('thuoc_tinh');
            if (is_string($thuocTinhInput)) {
                $thuocTinhInput = json_decode($thuocTinhInput, true);
            }
            if (is_array($thuocTinhInput)) {
                foreach ($thuocTinhInput as $tt) {
                    if (isset($tt['ten_thuoc_tinh']) && !empty($tt['ten_thuoc_tinh'])) {
                        \App\Models\ThuocTinh::create([
                            'id_san_pham' => $sanPham->id,
                            'ten_thuoc_tinh' => $tt['ten_thuoc_tinh'],
                            'gia_tri' => $tt['gia_tri'] ?? [],
                        ]);
                    }
                }
            }

            // Save variants (bien_the)
            $bienTheImages = $request->file('bien_the_images') ?? [];
            if ($hasVariants) {
                foreach ($bienTheInput as $index => $bt) {
                    // Xử lý upload ảnh biến thể
                    $imagePath = null;
                    if (isset($bienTheImages[$index])) {
                        $file = $bienTheImages[$index];
                        $path = $file->store('uploads/bien_the', 'public');
                        $imagePath = '/storage/' . $path;
                    }
                    BienTheSanPham::create([
                        'id_san_pham' => $sanPham->id,
                        'ma_kho' => isset($bt['sku']) && !empty($bt['sku']) ? $bt['sku'] : ($request->sku ?: 'SKU-' . $sanPham->id . '-' . ($index + 1)),
                        'gia_ban' => isset($bt['price']) ? $bt['price'] : $giaCoBan,
                        'gia_goc' => isset($bt['price_orig']) && $bt['price_orig'] > 0 ? $bt['price_orig'] : null,
                        'so_luong_ton_kho' => isset($bt['stock']) ? $bt['stock'] : 0,
                        'thuoc_tinh' => isset($bt['thuoc_tinh']) ? $bt['thuoc_tinh'] : [],
                        'hinh_anh' => $imagePath,
                    ]);
                }
            } else {
                // Fallback to single variant if no variants array is provided
                BienTheSanPham::create([
                    'id_san_pham' => $sanPham->id,
                    'ma_kho' => $request->sku ?: 'SKU-' . $sanPham->id,
                    'gia_ban' => $giaCoBan,
                    'so_luong_ton_kho' => $request->so_luong_ton_kho ?: 0,
                    'thuoc_tinh' => [],
                    'hinh_anh' => null,
                ]);
            }

            // Handle ảnh đại diện
            if ($request->hasFile('hinh_anh')) {
                $file = $request->file('hinh_anh');
                $path = $file->store('uploads/san_pham', 'public');
                HinhAnhSanPham::create([
                    'id_san_pham'    => $sanPham->id,
                    'duong_dan_anh'  => '/storage/' . $path,
                    'la_anh_dai_dien' => true,
                ]);
            }

            // Handle nhiều ảnh gallery (hinh_anh_phu[])
            if ($request->hasFile('hinh_anh_phu')) {
                foreach ($request->file('hinh_anh_phu') as $anhPhu) {
                    $path = $anhPhu->store('uploads/san_pham', 'public');
                    HinhAnhSanPham::create([
                        'id_san_pham'    => $sanPham->id,
                        'duong_dan_anh'  => '/storage/' . $path,
                        'la_anh_dai_dien' => false,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 1,
                'message' => 'Nhân viên ' . $nhanVien->ho_ten . ' đã thêm sản phẩm ' . $request->ten_san_pham . ' thành công.',
                'data' => $sanPham
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 0,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function sua(SanPhamRequestUpdate $request)
    {
        $nhanVien = Auth::user();
        $sanPham = SanPham::find($request->id);

        if (!$sanPham) {
            return response()->json([
                'status' => 0,
                'message' => 'Sản phẩm không tồn tại!'
            ]);
        }

        DB::beginTransaction();
        try {
            $tinhTrangMap = ['active' => 1, 'out' => 0, 'hidden' => 2];

            $bienTheInput = $request->input('bien_the');
            if (is_string($bienTheInput)) {
                $bienTheInput = json_decode($bienTheInput, true);
            }

            $giaCoBan = $request->gia_co_ban;
            $soLuongTonKho = $request->so_luong_ton_kho ?: 0;
            $hasVariants = is_array($bienTheInput) && count($bienTheInput) > 0;

            if ($hasVariants) {
                // Tự động lấy min('price') của các biến thể
                $giaCoBan = collect($bienTheInput)->min('price') ?? 0;
                // Bổ sung tính toán Tổng Tồn Kho ở Backend
                $soLuongTonKho = collect($bienTheInput)->sum('stock') ?? 0;
            }

            $sanPham->update([
                'ten_san_pham' => $request->ten_san_pham,
                'gia_co_ban' => $giaCoBan,
                'gia_goc'    => $request->gia_goc ?: null,
                'id_danh_muc' => $request->id_danh_muc,
                'mo_ta' => $request->mo_ta,
                'tinh_trang' => $tinhTrangMap[$request->tinh_trang] ?? $sanPham->tinh_trang,
                'so_luong_ton_kho' => $soLuongTonKho,
            ]);

            // Save attributes (thuoc_tinh)
            $thuocTinhInput = $request->input('thuoc_tinh');
            if (is_string($thuocTinhInput)) {
                $thuocTinhInput = json_decode($thuocTinhInput, true);
            }
            if (is_array($thuocTinhInput)) {
                // Delete old attributes first
                \App\Models\ThuocTinh::where('id_san_pham', $sanPham->id)->delete();
                foreach ($thuocTinhInput as $tt) {
                    if (isset($tt['ten_thuoc_tinh']) && !empty($tt['ten_thuoc_tinh'])) {
                        \App\Models\ThuocTinh::create([
                            'id_san_pham' => $sanPham->id,
                            'ten_thuoc_tinh' => $tt['ten_thuoc_tinh'],
                            'gia_tri' => $tt['gia_tri'] ?? [],
                        ]);
                    }
                }
            }

            // Save/Sync variants (bien_the)
            $bienTheImages = $request->file('bien_the_images') ?? [];
            if ($hasVariants) {
                $processedVariantIds = [];

                foreach ($bienTheInput as $index => $bt) {
                    $thuocTinhMap = isset($bt['thuoc_tinh']) ? $bt['thuoc_tinh'] : [];
                    $id = isset($bt['id']) && !empty($bt['id']) ? $bt['id'] : null;

                    // Xử lý ảnh biến thể: upload mới hoặc giữ ảnh cũ
                    $imagePath = isset($bt['existing_image']) && !empty($bt['existing_image']) ? $bt['existing_image'] : null;
                    if (isset($bienTheImages[$index])) {
                        $file = $bienTheImages[$index];
                        $path = $file->store('uploads/bien_the', 'public');
                        $imagePath = '/storage/' . $path;
                    }

                    $variant = BienTheSanPham::updateOrCreate(
                        ['id' => $id, 'id_san_pham' => $sanPham->id],
                        [
                            'ma_kho' => isset($bt['sku']) && !empty($bt['sku']) ? $bt['sku'] : ($request->sku ?: 'SKU-' . $sanPham->id . '-' . time() . '-' . ($index + 1)),
                            'gia_ban' => isset($bt['price']) ? $bt['price'] : $giaCoBan,
                            'gia_goc' => isset($bt['price_orig']) && $bt['price_orig'] > 0 ? $bt['price_orig'] : null,
                            'so_luong_ton_kho' => isset($bt['stock']) ? $bt['stock'] : 0,
                            'thuoc_tinh' => $thuocTinhMap,
                            'hinh_anh' => $imagePath,
                        ]
                    );
                    $processedVariantIds[] = $variant->id;
                }

                // Delete variants that are no longer present
                BienTheSanPham::where('id_san_pham', $sanPham->id)->whereNotIn('id', $processedVariantIds)->delete();
            } else {
                // Sync to a single simple variant
                $bienTheCount = BienTheSanPham::where('id_san_pham', $sanPham->id)->count();
                if ($bienTheCount > 1) {
                    // Delete all and create one simple variant
                    BienTheSanPham::where('id_san_pham', $sanPham->id)->delete();
                    BienTheSanPham::create([
                        'id_san_pham' => $sanPham->id,
                        'ma_kho' => $request->sku ?: 'SKU-' . $sanPham->id,
                        'gia_ban' => $giaCoBan,
                        'so_luong_ton_kho' => $request->so_luong_ton_kho ?: 0,
                        'thuoc_tinh' => [],
                    ]);
                } else {
                    // Update the single variant
                    $bienThe = BienTheSanPham::where('id_san_pham', $sanPham->id)->first();
                    if ($bienThe) {
                        $bienThe->update([
                            'ma_kho' => $request->sku ?: $bienThe->ma_kho,
                            'gia_ban' => $giaCoBan,
                            'so_luong_ton_kho' => $request->so_luong_ton_kho ?: $bienThe->so_luong_ton_kho,
                            'thuoc_tinh' => [],
                        ]);
                    } else {
                        BienTheSanPham::create([
                            'id_san_pham' => $sanPham->id,
                            'ma_kho' => $request->sku ?: 'SKU-' . $sanPham->id,
                            'gia_ban' => $giaCoBan,
                            'so_luong_ton_kho' => $request->so_luong_ton_kho ?: 0,
                            'thuoc_tinh' => [],
                        ]);
                    }
                }
            }

            // Xử lý xóa ảnh đại diện (khi user bấm nút ✕ mà không upload ảnh mới)
            if ($request->filled('xoa_anh_dai_dien') && !$request->hasFile('hinh_anh')) {
                $oldHinhAnh = HinhAnhSanPham::where('id_san_pham', $sanPham->id)->where('la_anh_dai_dien', true)->first();
                if ($oldHinhAnh) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $oldHinhAnh->duong_dan_anh));
                    $oldHinhAnh->delete();
                }
            }

            // Xử lý xóa các ảnh gallery được chỉ định
            if ($request->has('xoa_hinh_anh_phu')) {
                $pathsToDelete = $request->input('xoa_hinh_anh_phu', []);
                foreach ($pathsToDelete as $path) {
                    $hinhAnh = HinhAnhSanPham::where('id_san_pham', $sanPham->id)
                        ->where('duong_dan_anh', $path)
                        ->where('la_anh_dai_dien', false)
                        ->first();
                    if ($hinhAnh) {
                        Storage::disk('public')->delete(str_replace('/storage/', '', $hinhAnh->duong_dan_anh));
                        $hinhAnh->delete();
                    }
                }
            }

            // Xử lý upload ảnh đại diện mới
            if ($request->hasFile('hinh_anh')) {
                // Xóa ảnh đại diện cũ
                $oldHinhAnh = HinhAnhSanPham::where('id_san_pham', $sanPham->id)->where('la_anh_dai_dien', true)->first();
                if ($oldHinhAnh) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $oldHinhAnh->duong_dan_anh));
                    $oldHinhAnh->delete();
                }

                $file = $request->file('hinh_anh');
                $path = $file->store('uploads/san_pham', 'public');
                HinhAnhSanPham::create([
                    'id_san_pham'    => $sanPham->id,
                    'duong_dan_anh'  => '/storage/' . $path,
                    'la_anh_dai_dien' => true,
                ]);
            }

            // Xử lý nhiều ảnh gallery mới thêm (không xóa ảnh cũ)
            if ($request->hasFile('hinh_anh_phu')) {
                foreach ($request->file('hinh_anh_phu') as $anhPhu) {
                    $path = $anhPhu->store('uploads/san_pham', 'public');
                    HinhAnhSanPham::create([
                        'id_san_pham'    => $sanPham->id,
                        'duong_dan_anh'  => '/storage/' . $path,
                        'la_anh_dai_dien' => false,
                    ]);
                }
            }
            
            DB::commit();

            return response()->json([
                'status' => 1,
                'message' => 'Nhân viên ' . $nhanVien->ho_ten . ' đã cập nhật sản phẩm thành công.',
                'data' => $sanPham
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 0,
                'message' => 'Lỗi: ' . $e->getMessage()
            ]);
        }
    }

    public function xoa(Request $request)
    {
        $nhanVien = Auth::user();
        $sanPham = SanPham::find($request->id);
        
        if (!$sanPham) {
            return response()->json([
                'status' => 0,
                'message' => 'Sản phẩm không tồn tại!'
            ]);
        }

        $sanPham->delete();

        return response()->json([
            'status' => 1,
            'message' => 'Nhân viên ' . $nhanVien->ho_ten . ' đã xóa sản phẩm thành công.',
        ]);
    }

    public function taiHinhAnh(Request $request)
    {
        $nhanVien = Auth::user();
        $sanPham = SanPham::find($request->id);
        if (!$sanPham) {
            return response()->json(['status' => 0, 'message' => 'Sản phẩm không tồn tại!']);
        }

        $request->validate([
            'image' => 'required|image|max:2048',
            'la_anh_dai_dien' => 'boolean'
        ]);

        $path = $request->file('image')->store('uploads/san_pham', 'public');

        $hinhAnh = HinhAnhSanPham::create([
            'id_san_pham' => $sanPham->id,
            'duong_dan_anh' => '/storage/' . $path,
            'la_anh_dai_dien' => $request->la_anh_dai_dien ?? false,
        ]);

        return response()->json([
            'status' => 1,
            'message' => 'Nhân viên ' . $nhanVien->ho_ten . ' tải ảnh thành công.',
            'data' => $hinhAnh
        ]);
    }

    public function themBienThe(Request $request)
    {
        $nhanVien = Auth::user();
        $sanPham = SanPham::find($request->id);
        if (!$sanPham) {
            return response()->json(['status' => 0, 'message' => 'Sản phẩm không tồn tại!']);
        }

        $request->validate([
            'ma_kho' => 'required|string|unique:bien_the_san_pham,ma_kho',
            'gia_ban' => 'required|numeric',
            'so_luong_ton_kho' => 'required|integer'
        ]);

        $bienThe = BienTheSanPham::create([
            'id_san_pham' => $sanPham->id,
            'ma_kho' => $request->ma_kho,
            'gia_ban' => $request->gia_ban,
            'so_luong_ton_kho' => $request->so_luong_ton_kho,
            'thuoc_tinh' => json_encode($request->thuoc_tinh ?? [])
        ]);

        return response()->json([
            'status' => 1,
            'message' => 'Nhân viên ' . $nhanVien->ho_ten . ' đã thêm biến thể thành công.',
            'data' => $bienThe
        ]);
    }
}
