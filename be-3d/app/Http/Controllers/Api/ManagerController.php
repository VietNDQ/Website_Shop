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
                    'date' => $o->tao_luc ? $o->tao_luc->format('d/m/Y') : 'N/A',
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
                'joinDate' => $c->tao_luc ? $c->tao_luc->format('d/m/Y') : 'N/A',
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
                'cod' => 'COD',
                'vnpay' => 'VNPAY',
                'momo' => 'MoMo',
                'chuyen_khoan' => 'Chuyển khoản'
            ];
            
            $phone = 'N/A';
            $address = 'N/A';
            if (is_array($order->dia_chi_giao_hang)) {
                $phone = $order->dia_chi_giao_hang['so_dien_thoai'] ?? 'N/A';
                if (isset($order->dia_chi_giao_hang['dia_chi'])) {
                    $address = $order->dia_chi_giao_hang['dia_chi'];
                } else {
                    $addressParts = [];
                    if (isset($order->dia_chi_giao_hang['dia_chi_chi_tiet'])) $addressParts[] = $order->dia_chi_giao_hang['dia_chi_chi_tiet'];
                    if (isset($order->dia_chi_giao_hang['quan_huyen'])) $addressParts[] = $order->dia_chi_giao_hang['quan_huyen'];
                    if (isset($order->dia_chi_giao_hang['thanh_pho'])) $addressParts[] = $order->dia_chi_giao_hang['thanh_pho'];
                    if (count($addressParts) > 0) $address = implode(', ', $addressParts);
                }
            }

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
                'payment_status' => $order->thanhToan ? $order->thanhToan->trang_thai : 'cho_thanh_toan',
                'date' => $order->tao_luc ? $order->tao_luc->format('d/m/Y H:i') : 'N/A',
                'status' => $order->trang_thai,
                'chi_tiets' => $order->chiTiets->map(function ($ct) {
                    return [
                        'ten' => $ct->ten_bien_the_luc_mua,
                        'gia' => number_format($ct->don_gia, 0, ',', '.') . ' ₫',
                        'sl' => $ct->so_luong,
                        'thanh_tien' => number_format($ct->thanh_tien, 0, ',', '.') . ' ₫'
                    ];
                }),
                'lich_su' => $order->lichSuTrangThais->map(function ($ls) {
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
            'trang_thai' => 'required|in:cho_xu_ly,dang_chuan_bi,dang_giao,da_giao,da_huy'
        ]);

        $order = DonHang::with(['chiTiets', 'thanhToan'])->find($id);
        if (!$order) {
            return response()->json(['status' => 0, 'message' => 'Không tìm thấy đơn hàng'], 404);
        }

        $oldStatus = $order->trang_thai;
        $newStatus = $request->trang_thai;
        
        if ($oldStatus === $newStatus) {
            return response()->json(['status' => 1, 'message' => 'Trạng thái không thay đổi']);
        }

        if ($oldStatus === 'da_huy' || $oldStatus === 'da_giao') {
            return response()->json(['status' => 0, 'message' => 'Không thể thay đổi trạng thái của đơn hàng đã hoàn tất hoặc đã hủy'], 400);
        }

        DB::beginTransaction();
        try {
            $order->trang_thai = $newStatus;
            $order->save();

            // Nếu đơn hàng bị hủy, hoàn lại số lượng tồn kho
            if ($newStatus === 'da_huy') {
                foreach ($order->chiTiets as $ct) {
                    $variant = BienTheSanPham::find($ct->id_bien_the);
                    if ($variant) {
                        $variant->increment('so_luong_ton_kho', $ct->so_luong);
                    }
                }
                
                // Cập nhật thanh toán
                if ($order->thanhToan && $order->thanhToan->trang_thai === 'da_thanh_toan') {
                    $order->thanhToan->trang_thai = 'hoan_tien';
                    $order->thanhToan->save();
                } else if ($order->thanhToan) {
                    $order->thanhToan->trang_thai = 'that_bai';
                    $order->thanhToan->save();
                }
            }
            
            // Nếu đơn hàng đã giao thành công và là COD, cập nhật thanh toán
            if ($newStatus === 'da_giao' && $order->thanhToan) {
                if ($order->thanhToan->phuong_thuc === 'cod') {
                    $order->thanhToan->trang_thai = 'da_thanh_toan';
                    $order->thanhToan->save();
                }
            }

            // Ghi lịch sử trạng thái
            LichSuTrangThaiDonHang::create([
                'id_don_hang' => $order->id,
                'trang_thai' => $newStatus,
                'ghi_chu' => 'Admin cập nhật trạng thái',
            ]);

            // Ghi nhật ký hoạt động
            \App\Models\NhatKyHoatDong::create([
                'hanh_dong' => 'Cập nhật đơn hàng',
                'mo_ta' => 'Cập nhật trạng thái đơn hàng #' . $order->ma_don_hang . ' thành ' . $newStatus,
                'loai_doi_tuong' => 'don_hang',
                'id_doi_tuong' => $order->id,
            ]);

            DB::commit();
            return response()->json(['status' => 1, 'message' => 'Cập nhật trạng thái thành công']);
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
}

