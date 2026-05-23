<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CheckoutRequest;
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
use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
        $query = SanPham::with(['hinhAnhs', 'danhMuc', 'bienThes'])
            ->whereIn('tinh_trang', [0, 1]); // Chỉ lấy sản phẩm đang bán (1) hoặc hết hàng (0)

        if ($request->id_danh_muc) {
            $query->where('id_danh_muc', $request->id_danh_muc);
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ten_san_pham', 'like', "%{$search}%")
                  ->orWhere('mo_ta', 'like', "%{$search}%");
            });
        }

        if ($request->scale) {
            $scale = $request->scale;
            $query->where(function($q) use ($scale) {
                $q->where('ten_san_pham', 'like', "%{$scale}%")
                  ->orWhere('mo_ta', 'like', "%{$scale}%");
            });
        }

        if ($request->min_price) {
            $query->where('gia_co_ban', '>=', $request->min_price);
        }
        if ($request->max_price) {
            $query->where('gia_co_ban', '<=', $request->max_price);
        }

        // Ưu tiên sản phẩm đang bán (1) lên đầu, sau đó sắp xếp theo tiêu chí lọc
        $query->orderBy('tinh_trang', 'desc');

        if ($request->sort) {
            if ($request->sort === 'price_asc') {
                $query->orderBy('gia_co_ban', 'asc');
            } elseif ($request->sort === 'price_desc') {
                $query->orderBy('gia_co_ban', 'desc');
            } elseif ($request->sort === 'newest') {
                $query->orderBy('tao_luc', 'desc');
            }
        } else {
            $query->orderBy('tao_luc', 'desc');
        }

        $perPage = (int) $request->input('per_page', 12);
        $perPage = max(1, min($perPage, 100));

        return response()->json($query->paginate($perPage));
    }

    public function search(Request $request)
    {
        $keyword = trim((string) $request->query('q', ''));
        $categoryId = $request->query('id_danh_muc');
        $limit = max(1, min((int) $request->query('limit', 8), 20));

        $productsQuery = SanPham::with(['hinhAnhs', 'danhMuc'])
            ->whereIn('tinh_trang', [0, 1]);

        if ($categoryId) {
            $productsQuery->where('id_danh_muc', $categoryId);
        }

        if ($keyword !== '') {
            $productsQuery->where(function ($q) use ($keyword) {
                $q->where('ten_san_pham', 'like', "%{$keyword}%")
                    ->orWhere('mo_ta', 'like', "%{$keyword}%");
            });
        }

        $products = $productsQuery
            ->orderBy('tinh_trang', 'desc')
            ->orderBy('tao_luc', 'desc')
            ->limit($limit)
            ->get();

        $categoriesQuery = DanhMuc::query()->where('trang_thai', 1);
        if ($keyword !== '') {
            $categoriesQuery->where('ten_danh_muc', 'like', "%{$keyword}%");
        }
        $categories = $categoriesQuery
            ->orderBy('thu_tu_hien_thi', 'asc')
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get(['id', 'ten_danh_muc', 'emoji']);

        return response()->json([
            'keyword' => $keyword,
            'category_id' => $categoryId ? (int) $categoryId : null,
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    public function suggest(Request $request)
    {
        $keyword = trim((string) $request->query('q', ''));
        $categoryId = $request->query('id_danh_muc');
        $limit = max(1, min((int) $request->query('limit', 8), 20));

        if (mb_strlen($keyword) < 2) {
            return response()->json([
                'keyword' => $keyword,
                'suggestions' => [],
            ]);
        }

        $cacheKey = 'search:suggest:' . md5($keyword . '|' . $categoryId . '|' . $limit);
        $cached = Cache::get($cacheKey);
        if ($cached) {
            return response()->json($cached);
        }

        $query = SanPham::query()
            ->with(['hinhAnhs', 'danhMuc'])
            ->whereIn('tinh_trang', [0, 1]);

        if ($categoryId) {
            $query->where('id_danh_muc', $categoryId);
        }

        $query->where(function ($q) use ($keyword) {
            $q->where('ten_san_pham', 'like', "%{$keyword}%")
                ->orWhere('mo_ta', 'like', "%{$keyword}%");
        });

        $products = $query->orderBy('tinh_trang', 'desc')
            ->orderBy('tao_luc', 'desc')
            ->limit($limit)
            ->get();

        $suggestions = $products->map(function ($p) {
            return [
                'id' => $p->id,
                'label' => $p->ten_san_pham,
                'type' => 'product',
                'id_danh_muc' => $p->id_danh_muc,
            ];
        })->values();

        $response = [
            'keyword' => $keyword,
            'suggestions' => $suggestions,
        ];

        Cache::put($cacheKey, $response, now()->addMinutes(10));
        return response()->json($response);
    }

    public function trackSearchClick(Request $request)
    {
        $data = $request->validate([
            'keyword' => 'nullable|string|max:255',
            'suggestion' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:30',
            'id_san_pham' => 'nullable|integer',
            'id_danh_muc' => 'nullable|integer',
        ]);

        $key = 'search:track:' . strtolower(trim((string) ($data['keyword'] ?? '')));
        Cache::increment($key);

        return response()->json([
            'status' => true,
            'message' => 'tracked',
        ]);
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
            $user = auth('sanctum')->user();
            
            // Lấy hạng thành viên của user
            $tier = 'dong';
            if ($user) {
                // Lock for Update khi đọc user để giải quyết Race Condition
                $user = \App\Models\NguoiDung::where('id', $user->id)->lockForUpdate()->first();
                $tier = $user->hang_thanh_vien ?? 'dong';
            }

            if ($user && $request->id_dia_chi) {
                $address = DiaChiNguoiDung::findOrFail($request->id_dia_chi);
                $ten = $user->ho_ten;
                $soDienThoai = $address->so_dien_thoai;
                $diaChi = "{$address->dia_chi_chi_tiet}, {$address->phuong_xa}, {$address->quan_huyen}, {$address->thanh_pho}";
            } else {
                $ten = $request->ho_ten;
                $soDienThoai = $request->so_dien_thoai;
                $diaChi = "{$request->dia_chi_chi_tiet}, {$request->phuong_xa}, {$request->quan_huyen}, {$request->thanh_pho}";
            }

            $tongTienHang = 0;
            $orderItems = [];

            foreach ($request->items as $item) {
                $variant = BienTheSanPham::with('sanPham.danhMuc')->findOrFail($item['id_bien_the']);

                if ($variant->so_luong_ton_kho < $item['so_luong']) {
                    throw new \Exception("Sản phẩm {$variant->sanPham->ten_san_pham} không đủ tồn kho.");
                }

                $donGia = (float) $variant->gia_ban;

                // Kiểm tra xem có phải phụ kiện không
                $slug = $variant->sanPham->danhMuc->duong_dan_mau ?? '';
                $isPhuKien = in_array($slug, ['dung-cu-lap-rap-cat-got', 'son-va-hoa-chat-mo-hinh', 'dung-cu-ca-nhan']);

                // Giảm giá phụ kiện theo hạng
                $giamGiaTier = 0;
                if ($isPhuKien) {
                    if ($tier === 'bac') {
                        $giamGiaTier = 0.01; // Giảm 1%
                    } elseif ($tier === 'vang') {
                        $giamGiaTier = 0.05; // Giảm 5%
                    }
                }

                if ($giamGiaTier > 0) {
                    $donGia = $donGia * (1 - $giamGiaTier);
                }

                $subTotal = $donGia * $item['so_luong'];
                $tongTienHang += $subTotal;

                $orderItems[] = [
                    'id_bien_the' => $variant->id,
                    'ten_bien_the_luc_mua' => $variant->sanPham->ten_san_pham . ' (' . collect($variant->thuoc_tinh)->map(fn($v, $k) => "$k: $v")->implode(' - ') . ')',
                    'so_luong' => $item['so_luong'],
                    'don_gia' => $donGia,
                    'thanh_tien' => $subTotal,
                ];

                // Update inventory
                $variant->decrement('so_luong_ton_kho', $item['so_luong']);
                $variant->refresh();
                if ($variant->so_luong_ton_kho < 5) {
                    try {
                        \App\Models\ThongBao::taoThongBao(
                            "Sản phẩm sắp hết hàng",
                            "Biến thể " . $variant->sanPham->ten_san_pham . " (" . collect($variant->thuoc_tinh)->map(fn($v, $k) => "$k: $v")->implode(' - ') . ") chỉ còn " . $variant->so_luong_ton_kho . " sản phẩm.",
                            'het_hang',
                            '/nhan-vien/products'
                        );
                    } catch (\Exception $ex) {
                        \Illuminate\Support\Facades\Log::error('Notification error in low stock warning: ' . $ex->getMessage());
                    }
                }
                NhatKyTonKho::create([
                    'id_bien_the' => $variant->id,
                    'so_luong_thay_doi' => -$item['so_luong'],
                    'loai_giao_dich' => 'khach_dat',
                    'ghi_chu' => 'Khách đặt hàng',
                ]);
            }

            $tienGiamVoucher = 0;
            $voucherId = null;
            if ($request->ma_giam_gia) {
                $voucher = MaGiamGia::where('ma_code', $request->ma_giam_gia)->lockForUpdate()->first();
                if (!$voucher) {
                    throw new \Exception("Mã giảm giá không tồn tại.");
                }
                if (!$voucher->dang_hoat_dong) {
                    throw new \Exception("Mã giảm giá này hiện không hoạt động.");
                }
                $now = now();
                if ($voucher->ngay_bat_dau && $voucher->ngay_bat_dau->isFuture()) {
                    throw new \Exception("Chương trình giảm giá chưa bắt đầu.");
                }
                if ($voucher->ngay_ket_thuc && $voucher->ngay_ket_thuc->isPast()) {
                    throw new \Exception("Mã giảm giá đã hết hạn sử dụng.");
                }
                if ($voucher->gioi_han_su_dung !== null && $voucher->so_lan_da_dung >= $voucher->gioi_han_su_dung) {
                    throw new \Exception("Mã giảm giá đã hết lượt sử dụng.");
                }
                if ($voucher->ngan_sach !== null && $voucher->ngan_sach_da_dung >= $voucher->ngan_sach) {
                    throw new \Exception("Mã giảm giá đã hết ngân sách khuyến mãi.");
                }
                if ($tongTienHang < $voucher->don_hang_toi_thieu) {
                    throw new \Exception("Đơn hàng chưa đạt giá trị tối thiểu " . number_format($voucher->don_hang_toi_thieu) . "đ để áp dụng mã này.");
                }

                // Kiểm tra ví voucher và chống trục lợi
                if ($voucher->hinh_thuc_phat_hanh === 'claimable' || $voucher->hinh_thuc_phat_hanh === 'targeted') {
                    if (!$user) {
                        throw new \Exception("Mã giảm giá này yêu cầu đăng nhập.");
                    }
                    $userVoucher = \App\Models\NguoiDungVoucher::where('id_nguoi_dung', $user->id)
                        ->where('id_ma_giam_gia', $voucher->id)
                        ->where('trang_thai', 'unused')
                        ->first();
                    if (!$userVoucher) {
                        throw new \Exception("Mã giảm giá này phải được thu thập hoặc tặng vào ví trước khi dùng.");
                    }
                } elseif ($voucher->hinh_thuc_phat_hanh === 'public') {
                    if ($user) {
                        $usedVoucher = \App\Models\NguoiDungVoucher::where('id_nguoi_dung', $user->id)
                            ->where('id_ma_giam_gia', $voucher->id)
                            ->where('trang_thai', 'used')
                            ->first();
                        if ($usedVoucher) {
                            throw new \Exception("Bạn đã sử dụng mã giảm giá này rồi.");
                        }
                    }
                }

                // Tính số tiền giảm
                if ($voucher->loai_giam_gia === 'phan_tram') {
                    $tienGiamVoucher = ($tongTienHang * $voucher->gia_tri_giam) / 100;
                    if ($voucher->muc_giam_toi_da) {
                        $tienGiamVoucher = min($tienGiamVoucher, $voucher->muc_giam_toi_da);
                    }
                } else {
                    $tienGiamVoucher = $voucher->gia_tri_giam;
                }

                // Cập nhật số lần dùng và ngân sách
                $voucher->increment('so_lan_da_dung');
                if ($voucher->ngan_sach !== null) {
                    $voucher->increment('ngan_sach_da_dung', $tienGiamVoucher);
                }
                $voucherId = $voucher->id;

                // Cập nhật ví voucher của khách hàng thành 'used'
                if ($user) {
                    if ($voucher->hinh_thuc_phat_hanh === 'public') {
                        \App\Models\NguoiDungVoucher::create([
                            'id_nguoi_dung' => $user->id,
                            'id_ma_giam_gia' => $voucher->id,
                            'trang_thai' => 'used'
                        ]);
                    } else { // claimable hoặc targeted
                        $userVoucher->update(['trang_thai' => 'used']);
                    }
                }
            }

            // Coin discount integration (1 coin = 1.000 VND discount)
            $tienGiamXu = 0;
            $soXuDung = 0;
            if ($user && $request->dung_xu) {
                $userPoints = (int) $user->diem_thanh_vien;
                if ($userPoints >= 10) { // Tối thiểu 10 xu mới được áp dụng
                    $soTienSauVoucher = max(0, $tongTienHang - $tienGiamVoucher);
                    $soTienToiDaDuocGiamBangXu = $soTienSauVoucher * 0.5; // Tối đa 50%
                    $soXuToiDaTuongUng = (int) floor($soTienToiDaDuocGiamBangXu / 1000);
                    $soXuToiDaTheoQuyDinh = min(500, $soXuToiDaTuongUng); // Tối đa 500 xu

                    $soXuDung = min($userPoints, $soXuToiDaTheoQuyDinh);
                    if ($soXuDung >= 10) {
                        $user->decrement('diem_thanh_vien', $soXuDung);
                        $tienGiamXu = $soXuDung * 1000;
                    } else {
                        $soXuDung = 0;
                    }
                }
            }

            $tienGiam = $tienGiamVoucher + $tienGiamXu;

            // Freeship cho hạng Vàng (S-VIP)
            $phiGiaoHang = ($tier === 'vang') ? 0 : 30000;
            $tongThanhToan = max(0, $tongTienHang - $tienGiam + $phiGiaoHang);

            // Tính điểm tích lũy (sau khi đã trừ voucher và trừ xu) và không bao gồm phí ship
            // Điểm tích lũy = Math.floor((Tổng tiền thực tế khách trả / 100000) * Tỷ lệ tích xu)
            $tienThucTeKhachTra = max(0, $tongTienHang - $tienGiamVoucher - $tienGiamXu);
            $tiLeTichXu = ($tier === 'vang') ? 1.5 : (($tier === 'bac') ? 1.2 : 1.0);
            $diemTichLuy = (int) floor(($tienThucTeKhachTra * $tiLeTichXu) / 100000);

            // Điểm này nằm ở trạng thái Chờ duyệt (Pending Points)
            if ($user && $diemTichLuy > 0) {
                $user->increment('diem_cho_duyet', $diemTichLuy);
            }

            $order = DonHang::create([
                'id_nguoi_dung' => $user ? $user->id : null,
                'ma_don_hang' => 'ORD-' . strtoupper(Str::random(10)),
                'id_ma_giam_gia' => $voucherId,
                'tong_tien_hang' => $tongTienHang,
                'tien_duoc_giam' => $tienGiam,
                'phi_giao_hang' => $phiGiaoHang,
                'tong_thanh_toan' => $tongThanhToan,
                'so_xu_dung' => $soXuDung,
                'diem_tich_luy' => $diemTichLuy,
                'trang_thai' => 'cho_xu_ly',
                'dia_chi_giao_hang' => [
                    'ten' => $ten,
                    'so_dien_thoai' => $soDienThoai,
                    'dia_chi' => $diaChi,
                ],
                'ghi_chu_khach_hang' => $request->ghi_chu_khach_hang,
            ]);

            foreach ($orderItems as $item) {
                $item['id_don_hang'] = $order->id;
                ChiTietDonHang::create($item);
            }

            // Tạo thông báo đơn hàng mới
            try {
                \App\Models\ThongBao::taoThongBao(
                    "Đơn hàng mới {$order->ma_don_hang}",
                    "Khách hàng {$ten} vừa đặt đơn hàng {$order->ma_don_hang} với tổng thanh toán " . number_format($tongThanhToan) . "đ.",
                    'don_hang_moi',
                    '/nhan-vien/orders'
                );
            } catch (\Exception $ex) {
                \Illuminate\Support\Facades\Log::error('Notification error in checkout: ' . $ex->getMessage());
            }

            // Ghi log đặt hàng thành công
            \App\Models\NhatKyHoatDong::ghiLog(
                $user ? $user->id : null,
                $user ? $user->ho_ten : 'Khách vãng lai (' . $ten . ')',
                "Đặt hàng thành công. Mã đơn hàng: {$order->ma_don_hang}, tổng thanh toán: " . number_format($tongThanhToan) . "đ",
                '#3b82f6'
            );

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

            // Create VietQR URL if payment method is bank transfer
            $qrUrl = null;
            $bankInfo = null;
            if ($request->phuong_thuc_thanh_toan === 'chuyen_khoan') {
                $activeAccount = \App\Models\TaiKhoanNganHang::where('is_active', true)->first();
                if ($activeAccount) {
                    // Format: https://img.vietqr.io/image/<BANK_ID>-<ACCOUNT_NO>-<TEMPLATE>.png?amount=<AMOUNT>&addInfo=<DESCRIPTION>&accountName=<ACCOUNT_NAME>
                    $qrUrl = "https://img.vietqr.io/image/{$activeAccount->bank_id}-{$activeAccount->bank_account_no}-compact.png" .
                        "?amount={$tongThanhToan}" .
                        "&addInfo=" . urlencode($order->ma_don_hang) .
                        "&accountName=" . urlencode($activeAccount->bank_account_name);

                    $bankInfo = [
                        'bank_id' => $activeAccount->bank_id,
                        'bank_account_no' => $activeAccount->bank_account_no,
                        'bank_account_name' => $activeAccount->bank_account_name,
                    ];
                }
            }

            // Gửi thông báo Realtime qua Pusher khi có đơn hàng mới
            try {
                if (class_exists('Pusher\Pusher')) {
                    $options = [
                        'cluster' => env('PUSHER_APP_CLUSTER', 'ap1'),
                        'useTLS' => true
                    ];
                    $pusher = new \Pusher\Pusher(
                        env('PUSHER_APP_KEY', '794a0b225fca675fc9a7'),
                        env('PUSHER_APP_SECRET', 'cee10ba3a6fe8c8db26f'),
                        env('PUSHER_APP_ID', '2156596'),
                        $options
                    );
                    $pusher->trigger('my-channel', 'new-order', [
                        'id' => $order->id,
                        'ma_don_hang' => $order->ma_don_hang,
                        'customer' => $ten,
                        'total' => number_format($tongThanhToan, 0, ',', '.') . ' ₫'
                    ]);
                }
            } catch (\Exception $e) {
                // Bỏ qua lỗi Pusher để không làm gián đoạn đặt hàng
                \Illuminate\Support\Facades\Log::error('Pusher error in Customer Checkout: ' . $e->getMessage());
            }

            return response()->json([
                'message' => 'Đặt hàng thành công',
                'order' => array_merge($order->load('chiTiets')->toArray(), [
                    'qr_url' => $qrUrl,
                    'bank_info' => $bankInfo
                ]),
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

    public function validateVoucher($code)
    {
        $voucher = MaGiamGia::where('ma_code', strtoupper(trim($code)))->first();

        if (!$voucher) {
            return response()->json([
                'status' => false,
                'message' => 'Mã giảm giá không tồn tại.'
            ], 404);
        }

        if (!$voucher->dang_hoat_dong) {
            return response()->json([
                'status' => false,
                'message' => 'Mã giảm giá này hiện không hoạt động.'
            ], 400);
        }

        $now = now();
        if ($voucher->ngay_bat_dau && $voucher->ngay_bat_dau->isFuture()) {
            return response()->json([
                'status' => false,
                'message' => 'Chương trình giảm giá chưa bắt đầu.'
            ], 400);
        }

        if ($voucher->ngay_ket_thuc && $voucher->ngay_ket_thuc->isPast()) {
            return response()->json([
                'status' => false,
                'message' => 'Mã giảm giá đã hết hạn sử dụng.'
            ], 400);
        }

        if ($voucher->gioi_han_su_dung !== null && $voucher->so_lan_da_dung >= $voucher->gioi_han_su_dung) {
            return response()->json([
                'status' => false,
                'message' => 'Mã giảm giá đã hết lượt sử dụng.'
            ], 400);
        }

        if ($voucher->ngan_sach !== null && $voucher->ngan_sach_da_dung >= $voucher->ngan_sach) {
            return response()->json([
                'status' => false,
                'message' => 'Mã giảm giá đã hết ngân sách khuyến mãi.'
            ], 400);
        }

        // Kiểm tra ví voucher và chống trục lợi nếu có user đăng nhập
        $user = auth('sanctum')->user();
        if ($voucher->hinh_thuc_phat_hanh === 'claimable' || $voucher->hinh_thuc_phat_hanh === 'targeted') {
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Mã giảm giá này yêu cầu đăng nhập.'
                ], 400);
            }
            $userVoucher = \App\Models\NguoiDungVoucher::where('id_nguoi_dung', $user->id)
                ->where('id_ma_giam_gia', $voucher->id)
                ->where('trang_thai', 'unused')
                ->first();
            if (!$userVoucher) {
                return response()->json([
                    'status' => false,
                    'message' => 'Mã giảm giá này phải được thu thập hoặc được tặng vào ví trước khi sử dụng.'
                ], 400);
            }
        } elseif ($voucher->hinh_thuc_phat_hanh === 'public') {
            if ($user) {
                $usedVoucher = \App\Models\NguoiDungVoucher::where('id_nguoi_dung', $user->id)
                    ->where('id_ma_giam_gia', $voucher->id)
                    ->where('trang_thai', 'used')
                    ->first();
                if ($usedVoucher) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Bạn đã sử dụng mã giảm giá này rồi.'
                    ], 400);
                }
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Mã giảm giá hợp lệ.',
            'voucher' => [
                'id' => $voucher->id,
                'ma_code' => $voucher->ma_code,
                'loai_giam_gia' => $voucher->loai_giam_gia,
                'gia_tri_giam' => (float)$voucher->gia_tri_giam,
                'don_hang_toi_thieu' => (float)$voucher->don_hang_toi_thieu,
                'muc_giam_toi_da' => $voucher->muc_giam_toi_da ? (float)$voucher->muc_giam_toi_da : null,
            ]
        ]);
    }
}
