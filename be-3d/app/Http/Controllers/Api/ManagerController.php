<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DanhMuc;
use App\Models\SanPham;
use App\Models\HinhAnhSanPham;
use App\Models\BienTheSanPham;
use App\Models\NhatKyTonKho;
use App\Models\DonHang;
use App\Models\LichSuTrangThaiDonHang;
use App\Models\MaGiamGia;
use App\Models\ThanhToan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManagerController extends Controller
{
    // --- Catalog ---
    public function storeCategory(Request $request)
    {
        $request->validate([
            'ten_danh_muc' => 'required|string|max:255',
            'duong_dan_mau' => 'required|string|max:255|unique:danh_muc',
            'id_danh_muc_cha' => 'nullable|exists:danh_muc,id',
        ]);
        return DanhMuc::create($request->all());
    }

    // --- Products ---
    public function storeProduct(Request $request)
    {
        $request->validate([
            'ten_san_pham' => 'required|string|max:255',
            'gia_co_ban' => 'required|numeric',
            'id_danh_muc' => 'nullable|exists:danh_muc,id',
        ]);
        return SanPham::create($request->all());
    }

    public function uploadImage(Request $request, $productId)
    {
        $request->validate([
            'duong_dan_anh' => 'required|string|max:500',
            'la_anh_dai_dien' => 'boolean',
            'thu_tu_hien_thi' => 'integer',
        ]);
        $product = SanPham::findOrFail($productId);
        return $product->hinhAnhs()->create($request->all());
    }

    public function storeVariant(Request $request, $productId)
    {
        $request->validate([
            'ma_kho' => 'required|string|max:100|unique:bien_the_san_pham',
            'thuoc_tinh' => 'nullable|array',
            'gia_ban' => 'required|numeric',
            'so_luong_ton_kho' => 'integer',
        ]);
        $product = SanPham::findOrFail($productId);
        return $product->bienThes()->create($request->all());
    }

    // --- Inventory ---
    public function adjustInventory(Request $request)
    {
        $request->validate([
            'id_bien_the' => 'required|exists:bien_the_san_pham,id',
            'so_luong_thay_doi' => 'required|integer',
            'loai_giao_dich' => 'required|in:nhap_hang,dieu_chinh',
            'ghi_chu' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $variant = BienTheSanPham::findOrFail($request->id_bien_the);
            $variant->increment('so_luong_ton_kho', $request->so_luong_thay_doi);
            
            return NhatKyTonKho::create($request->all());
        });
    }

    // --- Orders ---
    public function getOrders(Request $request)
    {
        $query = DonHang::with(['nguoiDung', 'thanhToan']);
        if ($request->trang_thai) {
            $query->where('trang_thai', $request->trang_thai);
        }
        return $query->latest('tao_luc')->paginate(20);
    }

    public function updateOrderStatus(Request $request, $orderId)
    {
        $request->validate([
            'trang_thai' => 'required|in:cho_xu_ly,dang_chuan_bi,dang_giao,da_giao,da_huy,hoan_tien',
            'ghi_chu' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request, $orderId) {
            $order = DonHang::findOrFail($orderId);
            $order->update(['trang_thai' => $request->trang_thai]);

            LichSuTrangThaiDonHang::create([
                'id_don_hang' => $order->id,
                'trang_thai' => $request->trang_thai,
                'ghi_chu' => $request->ghi_chu,
            ]);

            return $order;
        });
    }

    // --- Vouchers ---
    public function storeVoucher(Request $request)
    {
        $request->validate([
            'ma_code' => 'required|string|max:50|unique:ma_giam_gia',
            'loai_giam_gia' => 'required|in:phan_tram,tien_mat',
            'gia_tri_giam' => 'required|numeric',
            'don_hang_toi_thieu' => 'numeric',
            'ngay_bat_dau' => 'nullable|date',
            'ngay_ket_thuc' => 'nullable|date',
        ]);
        return MaGiamGia::create($request->all());
    }

    // --- Transactions ---
    public function getTransactions()
    {
        return ThanhToan::with('donHang.nguoiDung')->latest('tao_luc')->paginate(20);
    }
}
