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
    public function getCategoriesAdmin()
    {
        $categories = DanhMuc::withCount('sanPhams')
            ->orderBy('thu_tu_hien_thi', 'asc')
            ->get();
        
        $data = $categories->map(function ($cat) {
            return [
                'id' => $cat->id,
                'name' => $cat->ten_danh_muc,
                'emoji' => $cat->emoji ?? '📁',
                'slug' => $cat->duong_dan_mau,
                'desc' => $cat->mo_ta,
                'orderIndex' => $cat->thu_tu_hien_thi,
                'productCount' => $cat->san_phams_count,
                'status' => $cat->trang_thai === 1 ? 'active' : 'hidden',
                'id_danh_muc_cha' => $cat->id_danh_muc_cha
            ];
        });

        return response()->json([
            'status' => 1,
            'data' => $data
        ]);
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'ten_danh_muc' => 'required|string|max:255',
            'duong_dan_mau' => 'required|string|max:255|unique:danh_muc,duong_dan_mau',
            'emoji' => 'nullable|string|max:50',
            'mo_ta' => 'nullable|string',
            'thu_tu_hien_thi' => 'nullable|integer',
            'trang_thai' => 'nullable|string|in:active,hidden',
            'id_danh_muc_cha' => 'nullable|exists:danh_muc,id',
        ]);

        $statusMap = ['active' => 1, 'hidden' => 0];

        $category = DanhMuc::create([
            'ten_danh_muc' => $request->ten_danh_muc,
            'duong_dan_mau' => $request->duong_dan_mau,
            'emoji' => $request->emoji ?? '📁',
            'mo_ta' => $request->mo_ta,
            'thu_tu_hien_thi' => $request->thu_tu_hien_thi ?? 1,
            'trang_thai' => $statusMap[$request->trang_thai] ?? 1,
            'id_danh_muc_cha' => $request->id_danh_muc_cha,
        ]);

        return response()->json([
            'status' => 1,
            'message' => 'Thêm danh mục thành công.',
            'data' => $category
        ]);
    }

    public function updateCategory(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:danh_muc,id',
            'ten_danh_muc' => 'required|string|max:255',
            'duong_dan_mau' => 'required|string|max:255|unique:danh_muc,duong_dan_mau,' . $request->id,
            'emoji' => 'nullable|string|max:50',
            'mo_ta' => 'nullable|string',
            'thu_tu_hien_thi' => 'nullable|integer',
            'trang_thai' => 'nullable|string|in:active,hidden',
            'id_danh_muc_cha' => 'nullable|exists:danh_muc,id',
        ]);

        $category = DanhMuc::findOrFail($request->id);
        
        $statusMap = ['active' => 1, 'hidden' => 0];

        $category->update([
            'ten_danh_muc' => $request->ten_danh_muc,
            'duong_dan_mau' => $request->duong_dan_mau,
            'emoji' => $request->emoji ?? $category->emoji,
            'mo_ta' => $request->mo_ta,
            'thu_tu_hien_thi' => $request->thu_tu_hien_thi ?? $category->thu_tu_hien_thi,
            'trang_thai' => $statusMap[$request->trang_thai] ?? $category->trang_thai,
            'id_danh_muc_cha' => $request->id_danh_muc_cha,
        ]);

        return response()->json([
            'status' => 1,
            'message' => 'Cập nhật danh mục thành công.',
            'data' => $category
        ]);
    }

    public function deleteCategory(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:danh_muc,id'
        ]);

        $category = DanhMuc::findOrFail($request->id);
        
        // Cập nhật các sản phẩm thuộc danh mục này thành null
        SanPham::where('id_danh_muc', $category->id)->update(['id_danh_muc' => null]);
        
        $category->delete();

        return response()->json([
            'status' => 1,
            'message' => 'Xóa danh mục thành công.'
        ]);
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

    // --- Customers ---
    public function getCustomersAdmin()
    {
        $customers = \App\Models\NguoiDung::where('vai_tro', 3)
            ->with(['diaChis', 'donHangs.chiTiets'])
            ->get();

        $gradients = [
            'linear-gradient(135deg, #D70018, #7c3aed)',
            'linear-gradient(135deg, #6366f1, #0ea5e9)',
            'linear-gradient(135deg, #f59e0b, #22c55e)',
            'linear-gradient(135deg, #0ea5e9, #6366f1)',
            'linear-gradient(135deg, #ec4899, #f43f5e)',
            'linear-gradient(135deg, #10b981, #059669)',
            'linear-gradient(135deg, #8b5cf6, #d946ef)'
        ];

        $data = $customers->map(function ($c) use ($gradients) {
            $phone = $c->diaChis->firstWhere('la_mac_dinh', true)?->so_dien_thoai 
                ?? $c->diaChis->first()?->so_dien_thoai 
                ?? 'N/A';

            $totalSpent = $c->donHangs->where('trang_thai', 'da_giao')->sum('tong_thanh_toan');
            $ordersCount = $c->donHangs->count();

            // Determine Group based on spending
            if ($totalSpent >= 20000000) {
                $group = 'Khách VIP';
            } elseif ($totalSpent >= 5000000) {
                $group = 'Khách thân thiết';
            } else {
                $group = 'Khách mới';
            }

            // Map orders history
            $orderHistory = $c->donHangs->map(function ($o) {
                $productNames = $o->chiTiets->pluck('ten_bien_the_luc_mua')->implode(', ');
                if (strlen($productNames) > 40) {
                    $productNames = mb_substr($productNames, 0, 37, 'UTF-8') . '...';
                }

                // Map status label
                $statusLabels = [
                    'cho_xu_ly' => 'Chờ xử lý',
                    'dang_chuan_bi' => 'Đang chuẩn bị',
                    'dang_giao' => 'Đang giao',
                    'da_giao' => 'Đã giao',
                    'da_huy' => 'Đã hủy',
                    'hoan_tien' => 'Hoàn tiền'
                ];

                return [
                    'code' => '#' . $o->ma_don_hang,
                    'products' => $productNames ?: 'N/A',
                    'total' => number_format($o->tong_thanh_toan, 0, ',', '.') . ' ₫',
                    'date' => $o->tao_luc ? $o->tao_luc->format('d/m/Y H:i') : 'N/A',
                    'status' => $o->trang_thai,
                    'statusLabel' => $statusLabels[$o->trang_thai] ?? 'Không xác định'
                ];
            });

            return [
                'id' => $c->id,
                'name' => $c->ho_ten,
                'email' => $c->email,
                'phone' => $phone,
                'orders' => $ordersCount,
                'spent' => number_format($totalSpent, 0, ',', '.') . ' ₫',
                'group' => $group,
                'joinDate' => $c->tao_luc ? $c->tao_luc->format('d/m/Y H:i') : 'N/A',
                'avatarBg' => $gradients[$c->id % count($gradients)],
                'orderHistory' => $orderHistory
            ];
        });

        return response()->json([
            'status' => 1,
            'data' => $data
        ]);
    }

    // --- Quản lý đơn hàng ---
    public function getOrders(Request $request)
    {
        $orders = DonHang::with(['nguoiDung', 'chiTiets', 'thanhToan', 'lichSuTrangThais'])
            ->orderByDesc('tao_luc')
            ->get();
            
        $data = $orders->map(function ($order) {
            $productNames = $order->chiTiets->pluck('ten_bien_the_luc_mua')->implode(', ');
            $paymentMethod = $order->thanhToan ? $order->thanhToan->phuong_thuc : 'COD';
            
            // Map payment method to display name
            $paymentMap = [
                'cod' => 'Tiền mặt',
                'tien_mat' => 'Tiền mặt',
                'vnpay' => 'VNPAY',
                'momo' => 'MoMo',
                'chuyen_khoan' => 'Chuyển khoản'
            ];
            
            $phone = 'N/A';
            $address = 'N/A';
            if (is_array($order->dia_chi_giao_hang)) {
                $phone = $order->dia_chi_giao_hang['so_dien_thoai'] ?? $order->dia_chi_giao_hang['phone'] ?? 'N/A';
                if (isset($order->dia_chi_giao_hang['dia_chi'])) {
                    $address = $order->dia_chi_giao_hang['dia_chi'];
                } elseif (isset($order->dia_chi_giao_hang['address'])) {
                    $address = $order->dia_chi_giao_hang['address'];
                } else {
                    $addressParts = [];
                    if (isset($order->dia_chi_giao_hang['dia_chi_chi_tiet'])) $addressParts[] = $order->dia_chi_giao_hang['dia_chi_chi_tiet'];
                    if (isset($order->dia_chi_giao_hang['quan_huyen'])) $addressParts[] = $order->dia_chi_giao_hang['quan_huyen'];
                    if (isset($order->dia_chi_giao_hang['thanh_pho'])) $addressParts[] = $order->dia_chi_giao_hang['thanh_pho'];
                    if (count($addressParts) > 0) $address = implode(', ', $addressParts);
                }
            }

            // Xác định người hủy đơn
            $nguoiHuy = null;
            if ($order->trang_thai === 'da_huy') {
                $huyLog = $order->lichSuTrangThais->where('trang_thai', 'da_huy')->first();
                if ($huyLog) {
                    $ghiChu = $huyLog->ghi_chu ?? '';
                    if (str_contains($ghiChu, 'Khách hàng hủy') || str_contains($ghiChu, 'khach')) {
                        $nguoiHuy = 'khach';
                    } else {
                        $nguoiHuy = 'admin';
                    }
                }
            }

            // Map payment status to display label
            $paymentStatusLabels = [
                'chua_thanh_toan' => 'Chưa thanh toán',
                'cho_thanh_toan'  => 'Chưa thanh toán',
                'da_thanh_toan'   => 'Đã thanh toán',
                'hoan_tien'       => 'Đã hoàn tiền',
                'that_bai'        => 'Thất bại',
                'cho_xu_ly'       => 'Chưa thanh toán',
            ];
            $paymentStatus = $order->thanhToan ? $order->thanhToan->trang_thai : 'cho_thanh_toan';

            return [
                'id' => $order->id,
                'code' => '#' . $order->ma_don_hang,
                'customer' => $order->nguoiDung->ho_ten ?? ($order->dia_chi_giao_hang['ten'] ?? 'Khách lẻ'),
                'phone' => $phone,
                'address' => $address,
                'product' => $productNames,
                'total' => number_format($order->tong_thanh_toan, 0, ',', '.') . ' ₫',
                'raw_total' => $order->tong_thanh_toan,
                'payment' => $paymentMap[$paymentMethod] ?? $paymentMethod,
                'payment_status' => $paymentStatus,
                'payment_status_label' => $paymentStatusLabels[$paymentStatus] ?? $paymentStatus,
                'date' => $order->tao_luc ? $order->tao_luc->format('d/m/Y H:i') : 'N/A',
                'status' => $order->trang_thai,
                'ly_do_huy' => $order->ly_do_huy,
                'nguoi_huy' => $nguoiHuy,
                'chi_tiets' => $order->chiTiets->map(function ($ct) {
                    return [
                        'ten' => $ct->ten_bien_the_luc_mua,
                        'gia' => number_format($ct->don_gia, 0, ',', '.') . ' ₫',
                        'sl' => $ct->so_luong,
                        'thanh_tien' => number_format($ct->thanh_tien, 0, ',', '.') . ' ₫'
                    ];
                }),
                'lich_su' => $order->lichSuTrangThais->sortBy('tao_luc')->values()->map(function ($ls) {
                    return [
                        'trang_thai' => $ls->trang_thai,
                        'thoi_gian' => $ls->tao_luc ? $ls->tao_luc->format('d/m/Y H:i') : 'N/A',
                        'ghi_chu' => $ls->ghi_chu
                    ];
                })
            ];
        });

        return response()->json([
            'status' => 1,
            'data' => $data
        ]);
    }

    public function updateOrderStatus(Request $request, $id)
    {
        $request->validate([
            'trang_thai' => 'required|in:cho_xu_ly,dang_chuan_bi,dang_giao,da_giao,da_huy,hoan_tien',
            'ly_do' => 'nullable|string|max:500',
        ]);

        $order = DonHang::with(['chiTiets', 'thanhToan'])->find($id);
        if (!$order) {
            return response()->json(['status' => 0, 'message' => 'Không tìm thấy đơn hàng'], 404);
        }

        $oldStatus = $order->trang_thai;
        $newStatus = $request->trang_thai;
        $lyDo = $request->input('ly_do', '');

        if ($oldStatus === $newStatus) {
            return response()->json(['status' => 1, 'message' => 'Trạng thái không thay đổi']);
        }

        // Quy tắc chuyển trạng thái hợp lệ
        $allowedTransitions = [
            'cho_xu_ly'    => ['dang_chuan_bi', 'da_huy'],
            'dang_chuan_bi'=> ['dang_giao', 'da_huy'],
            'dang_giao'    => ['da_giao', 'da_huy'],
            'da_giao'      => ['hoan_tien'],  // Chỉ cho phép chuyển sang hoàn tiền
            'da_huy'       => [],             // Không thể thay đổi
            'hoan_tien'    => [],             // Không thể thay đổi
        ];

        $validNext = $allowedTransitions[$oldStatus] ?? [];
        if (!in_array($newStatus, $validNext)) {
            return response()->json([
                'status' => 0,
                'message' => 'Không thể chuyển từ trạng thái "' . $oldStatus . '" sang "' . $newStatus . '"'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $order->trang_thai = $newStatus;

            // Lưu lý do hủy nếu là hủy đơn
            if ($newStatus === 'da_huy' && $lyDo) {
                $order->ly_do_huy = $lyDo;
            }

            $order->save();

            $user = null;
            if ($order->id_nguoi_dung) {
                $user = \App\Models\NguoiDung::where('id', $order->id_nguoi_dung)->lockForUpdate()->first();
            }

            // Xử lý khi hủy đơn
            if ($newStatus === 'da_huy') {
                // Hoàn lại số lượng tồn kho
                foreach ($order->chiTiets as $ct) {
                    $variant = BienTheSanPham::find($ct->id_bien_the);
                    if ($variant) {
                        $variant->increment('so_luong_ton_kho', $ct->so_luong);
                    }
                }

                // Cập nhật thanh toán
                if ($order->thanhToan) {
                    if ($order->thanhToan->trang_thai === 'da_thanh_toan') {
                        $order->thanhToan->trang_thai = 'hoan_tien';
                    } else {
                        $order->thanhToan->trang_thai = 'that_bai';
                    }
                    $order->thanhToan->save();
                }

                // Hoàn trả xu và hủy điểm chờ duyệt
                if ($user) {
                    if ($oldStatus === 'da_giao') {
                        // Trừ điểm khả dụng
                        if ($order->diem_tich_luy > 0) {
                            $user->diem_thanh_vien = max(0, $user->diem_thanh_vien - $order->diem_tich_luy);
                        }
                        // Trừ chi tiêu tích lũy
                        $tienThucTeKhachTra = max(0, $order->tong_tien_hang - $order->tien_duoc_giam);
                        $user->tong_chi_tieu = max(0, $user->tong_chi_tieu - $tienThucTeKhachTra);
                    } else {
                        // Trừ điểm chờ duyệt
                        if ($order->diem_tich_luy > 0) {
                            $user->diem_cho_duyet = max(0, $user->diem_cho_duyet - $order->diem_tich_luy);
                        }
                    }

                    // Hoàn xu đã dùng về ví khả dụng
                    if ($order->so_xu_dung > 0) {
                        $user->increment('diem_thanh_vien', $order->so_xu_dung);
                    }
                    $user->save();

                    // Cập nhật lại hạng
                    if ($oldStatus === 'da_giao') {
                        \App\Models\NguoiDung::updateMembership($user->id);
                    }
                }

                $ghiChuLs = 'Admin hủy đơn hàng' . ($lyDo ? '. Lý do: ' . $lyDo : '');
            }

            // Xử lý khi xác nhận hoàn tiền (da_giao → hoan_tien)
            if ($newStatus === 'hoan_tien') {
                // Cập nhật thanh toán sang hoàn tiền
                if ($order->thanhToan) {
                    $order->thanhToan->trang_thai = 'hoan_tien';
                    $order->thanhToan->save();
                }

                // Hoàn lại tồn kho vì hàng được trả về
                foreach ($order->chiTiets as $ct) {
                    $variant = BienTheSanPham::find($ct->id_bien_the);
                    if ($variant) {
                        $variant->increment('so_luong_ton_kho', $ct->so_luong);
                    }
                }

                // Đơn hàng đã ở trạng thái da_giao, nên thu hồi điểm khả dụng, hoàn xu và trừ chi tiêu
                if ($user) {
                    // Trừ điểm khả dụng
                    if ($order->diem_tich_luy > 0) {
                        $user->diem_thanh_vien = max(0, $user->diem_thanh_vien - $order->diem_tich_luy);
                    }
                    // Hoàn xu
                    if ($order->so_xu_dung > 0) {
                        $user->increment('diem_thanh_vien', $order->so_xu_dung);
                    }
                    // Trừ chi tiêu tích lũy
                    $tienThucTeKhachTra = max(0, $order->tong_tien_hang - $order->tien_duoc_giam);
                    $user->tong_chi_tieu = max(0, $user->tong_chi_tieu - $tienThucTeKhachTra);
                    $user->save();

                    // Cập nhật lại hạng thành viên
                    \App\Models\NguoiDung::updateMembership($user->id);
                }

                $ghiChuLs = 'Admin xác nhận hoàn tiền' . ($lyDo ? '. Lý do: ' . $lyDo : '');
            }

            // Hoàn lại voucher nếu có khi hủy đơn hoặc hoàn tiền
            if (($newStatus === 'da_huy' || $newStatus === 'hoan_tien') && $order->id_ma_giam_gia) {
                $voucher = MaGiamGia::find($order->id_ma_giam_gia);
                if ($voucher) {
                    $voucher->decrement('so_lan_da_dung');
                    $tienGiamVoucher = max(0.0, (float) $order->tien_duoc_giam - ((float) $order->so_xu_dung * 1000));
                    if ($voucher->ngan_sach !== null) {
                        $voucher->ngan_sach_da_dung = max(0.0, $voucher->ngan_sach_da_dung - $tienGiamVoucher);
                        $voucher->save();
                    }
                }
                if ($order->id_nguoi_dung) {
                    $userVoucher = \App\Models\NguoiDungVoucher::where('id_nguoi_dung', $order->id_nguoi_dung)
                        ->where('id_ma_giam_gia', $order->id_ma_giam_gia)
                        ->where('trang_thai', 'used')
                        ->first();
                    if ($userVoucher) {
                        $userVoucher->update(['trang_thai' => 'unused']);
                    }
                }
            }

            // Xử lý khi đã giao thành công và là COD
            if ($newStatus === 'da_giao') {
                if ($order->thanhToan && in_array($order->thanhToan->phuong_thuc, ['cod', 'tien_mat'])) {
                    $order->thanhToan->trang_thai = 'da_thanh_toan';
                    $order->thanhToan->save();
                }

                // Cộng điểm tích lũy khả dụng & cộng chi tiêu để thăng hạng
                if ($user) {
                    // Trừ điểm chờ duyệt
                    if ($order->diem_tich_luy > 0) {
                        $user->diem_cho_duyet = max(0, $user->diem_cho_duyet - $order->diem_tich_luy);
                    }
                    // Cộng điểm khả dụng
                    if ($order->diem_tich_luy > 0) {
                        $user->increment('diem_thanh_vien', $order->diem_tich_luy);
                    }
                    // Cộng dồn chi tiêu
                    $tienThucTeKhachTra = max(0, $order->tong_tien_hang - $order->tien_duoc_giam);
                    $user->increment('tong_chi_tieu', $tienThucTeKhachTra);
                    $user->save();

                    // Xét thăng hạng
                    \App\Models\NguoiDung::updateMembership($user->id);
                }
            }

            // Ghi lịch sử trạng thái
            $statusLabels = [
                'cho_xu_ly'     => 'Chờ xác nhận',
                'dang_chuan_bi' => 'Đang chuẩn bị',
                'dang_giao'     => 'Đang giao hàng',
                'da_giao'       => 'Đã giao',
                'da_huy'        => 'Đã hủy',
                'hoan_tien'     => 'Hoàn tiền',
            ];

            $defaultGhiChu = 'Admin cập nhật: ' . ($statusLabels[$newStatus] ?? $newStatus);
            LichSuTrangThaiDonHang::create([
                'id_don_hang' => $order->id,
                'trang_thai'  => $newStatus,
                'ghi_chu'     => $ghiChuLs ?? $defaultGhiChu,
            ]);

            // Ghi nhật ký hoạt động
            \App\Models\NhatKyHoatDong::create([
                'hanh_dong'      => 'Cập nhật đơn hàng',
                'mo_ta'          => 'Cập nhật trạng thái đơn #' . $order->ma_don_hang . ': ' . ($statusLabels[$oldStatus] ?? $oldStatus) . ' → ' . ($statusLabels[$newStatus] ?? $newStatus) . ($lyDo ? ' | Lý do: ' . $lyDo : ''),
                'loai_doi_tuong' => 'don_hang',
                'id_doi_tuong'   => $order->id,
            ]);

            DB::commit();

            $messages = [
                'da_huy'    => 'Đã hủy đơn hàng thành công',
                'hoan_tien' => 'Đã xác nhận hoàn tiền thành công',
                'da_giao'   => 'Đã xác nhận giao hàng thành công',
            ];

            return response()->json([
                'status'  => 1,
                'message' => $messages[$newStatus] ?? 'Cập nhật trạng thái thành công'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 0, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
        }
    }

    public function getSidebarStats()
    {
        try {
            $productsCount = \App\Models\SanPham::count();
            // Lấy số đơn hàng chưa xử lý để hiển thị thông báo đỏ
            $pendingOrdersCount = \App\Models\DonHang::where('trang_thai', 'cho_xu_ly')->count();
            // Lấy tổng số đơn hàng
            $totalOrdersCount = \App\Models\DonHang::count();

            return response()->json([
                'status' => 1,
                'data' => [
                    'products_count' => $productsCount,
                    'pending_orders_count' => $pendingOrdersCount,
                    'total_orders_count' => $totalOrdersCount
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => $e->getMessage()], 500);
        }
    }

    public function updatePaymentStatus(Request $request, $id)
    {
        $request->validate([
            'trang_thai' => 'required|in:chua_thanh_toan,da_thanh_toan,hoan_tien,that_bai'
        ]);

        $order = DonHang::with('thanhToan')->findOrFail($id);
        if (!$order->thanhToan) {
            return response()->json(['status' => 0, 'message' => 'Đơn hàng không có thông tin thanh toán!'], 400);
        }

        DB::beginTransaction();
        try {
            $order->thanhToan->update([
                'trang_thai' => $request->trang_thai,
                'ngay_thanh_toan' => $request->trang_thai === 'da_thanh_toan' ? now() : $order->thanhToan->ngay_thanh_toan,
            ]);

            // Tự động chuyển trạng thái đơn hàng sang 'dang_chuan_bi' nếu được xác nhận thanh toán khi đang 'cho_xu_ly'
            if ($request->trang_thai === 'da_thanh_toan' && $order->trang_thai === 'cho_xu_ly') {
                $order->update(['trang_thai' => 'dang_chuan_bi']);
                
                \App\Models\LichSuTrangThaiDonHang::create([
                    'id_don_hang' => $order->id,
                    'trang_thai'  => 'dang_chuan_bi',
                    'ghi_chu'     => 'Nhân viên xác nhận đã nhận chuyển khoản. Đơn hàng tự động chuyển sang chuẩn bị hàng.',
                ]);
            }

            // Ghi nhật ký hoạt động
            \App\Models\NhatKyHoatDong::create([
                'hanh_dong'      => 'Xác nhận thanh toán',
                'mo_ta'          => 'Nhân viên xác nhận thanh toán cho đơn hàng #' . $order->ma_don_hang . ': ' . $request->trang_thai,
                'loai_doi_tuong' => 'don_hang',
                'id_doi_tuong'   => $order->id,
            ]);

            DB::commit();

            return response()->json([
                'status' => 1,
                'message' => 'Cập nhật trạng thái thanh toán thành công.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 0, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
        }
    }
}

