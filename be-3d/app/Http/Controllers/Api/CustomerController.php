<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DanhMuc;
use App\Models\DiaChiNguoiDung;
use App\Models\DonHang;
use App\Models\ChiTietDonHang;
use App\Models\SanPham;
use App\Models\BienTheSanPham;
use App\Models\MaGiamGia;
use App\Models\LichSuTrangThaiDonHang;
use App\Models\ThanhToan;
use App\Models\NhatKyTonKho;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    // --- Account & Profile ---
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'ho_ten' => 'string|max:255',
        ]);
        $user->update($request->only('ho_ten'));
        return response()->json($user);
    }

    // --- Addresses ---
    public function getAddresses(Request $request)
    {
        return response()->json($request->user()->diaChis);
    }

    public function addAddress(Request $request)
    {
        $request->validate([
            'so_dien_thoai' => 'required|string|max:20',
            'dia_chi_chi_tiet' => 'required|string|max:255',
            'thanh_pho' => 'required|string|max:100',
            'quan_huyen' => 'required|string|max:100',
            'la_mac_dinh' => 'boolean',
        ]);

        if ($request->la_mac_dinh) {
            $request->user()->diaChis()->update(['la_mac_dinh' => false]);
        }

        $address = $request->user()->diaChis()->create($request->all());
        return response()->json($address, 201);
    }

    // --- Catalog ---
    public function getCategories()
    {
        return response()->json(DanhMuc::with('children')->whereNull('id_danh_muc_cha')->get());
    }

    public function getProducts(Request $request)
    {
        $query = SanPham::with(['hinhAnhs', 'danhMuc']);
        if ($request->id_danh_muc) {
            $query->where('id_danh_muc', $request->id_danh_muc);
        }
        return response()->json($query->paginate(10));
    }

    public function getProductDetail($id)
    {
        $product = SanPham::with(['hinhAnhs', 'bienThes', 'danhMuc'])->findOrFail($id);
        return response()->json($product);
    }

    // --- Orders ---
    public function checkout(CheckoutRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $user = $request->user();
            $address = DiaChiNguoiDung::findOrFail($request->id_dia_chi);

            $tongTienHang = 0;
            $orderItems = [];

            foreach ($request->items as $item) {
                $variant = BienTheSanPham::with('sanPham')->findOrFail($item['id_bien_the']);

                if ($variant->so_luong_ton_kho < $item['so_luong']) {
                    throw new \Exception("Sản phẩm {$variant->sanPham->ten_san_pham} không đủ tồn kho.");
                }

                $subTotal = $variant->gia_ban * $item['so_luong'];
                $tongTienHang += $subTotal;

                $orderItems[] = [
                    'id_bien_the' => $variant->id,
                    'ten_bien_the_luc_mua' => $variant->sanPham->ten_san_pham . ' (' . json_encode($variant->thuoc_tinh) . ')',
                    'so_luong' => $item['so_luong'],
                    'don_gia' => $variant->gia_ban,
                    'thanh_tien' => $subTotal,
                ];

                // Update inventory
                $variant->decrement('so_luong_ton_kho', $item['so_luong']);
                NhatKyTonKho::create([
                    'id_bien_the' => $variant->id,
                    'so_luong_thay_doi' => -$item['so_luong'],
                    'loai_giao_dich' => 'khach_dat',
                    'ghi_chu' => 'Khách đặt hàng',
                ]);
            }

            $tienGiam = 0;
            $voucherId = null;
            if ($request->ma_giam_gia) {
                $voucher = MaGiamGia::where('ma_code', $request->ma_giam_gia)->first();
                if ($voucher && $tongTienHang >= $voucher->don_hang_toi_thieu) {
                    if ($voucher->loai_giam_gia === 'phan_tram') {
                        $tienGiam = ($tongTienHang * $voucher->gia_tri_giam) / 100;
                        if ($voucher->muc_giam_toi_da) {
                            $tienGiam = min($tienGiam, $voucher->muc_giam_toi_da);
                        }
                    } else {
                        $tienGiam = $voucher->gia_tri_giam;
                    }
                    $voucher->increment('so_lan_da_dung');
                    $voucherId = $voucher->id;
                }
            }

            $phiGiaoHang = 30000;
            $tongThanhToan = max(0, $tongTienHang - $tienGiam + $phiGiaoHang);

            $order = DonHang::create([
                'id_nguoi_dung' => $user->id,
                'ma_don_hang' => 'ORD-' . strtoupper(Str::random(10)),
                'id_ma_giam_gia' => $voucherId,
                'tong_tien_hang' => $tongTienHang,
                'tien_duoc_giam' => $tienGiam,
                'phi_giao_hang' => $phiGiaoHang,
                'tong_thanh_toan' => $tongThanhToan,
                'trang_thai' => 'cho_xu_ly',
                'dia_chi_giao_hang' => [
                    'ten' => $user->ho_ten,
                    'so_dien_thoai' => $address->so_dien_thoai,
                    'dia_chi' => "{$address->dia_chi_chi_tiet}, {$address->quan_huyen}, {$address->thanh_pho}",
                ],
                'ghi_chu_khach_hang' => $request->ghi_chu_khach_hang,
            ]);

            foreach ($orderItems as $item) {
                $item['id_don_hang'] = $order->id;
                ChiTietDonHang::create($item);
            }

            LichSuTrangThaiDonHang::create([
                'id_don_hang' => $order->id,
                'trang_thai' => 'cho_xu_ly',
                'ghi_chu' => 'Đơn hàng mới được tạo',
            ]);

            ThanhToan::create([
                'id_don_hang' => $order->id,
                'phuong_thuc' => $request->phuong_thuc_thanh_toan,
                'so_tien' => $tongThanhToan,
                'trang_thai' => 'chua_thanh_toan',
            ]);

            return response()->json([
                'message' => 'Đặt hàng thành công',
                'order' => $order->load('chiTiets'),
            ], 201);
        });
    }

    public function getOrderHistory(Request $request)
    {
        $orders = DonHang::with(['chiTiets', 'thanhToan'])
            ->where('id_nguoi_dung', $request->user()->id)
            ->latest('tao_luc')
            ->get();
        return response()->json($orders);
    }

    public function getOrderTracking($id, Request $request)
    {
        $order = DonHang::with(['lichSuTrangThais', 'thanhToan'])
            ->where('id_nguoi_dung', $request->user()->id)
            ->findOrFail($id);
        return response()->json($order);
    }
}
