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
        
        return response()->json($query->paginate(12));
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
                $variant = BienTheSanPham::with('sanPham')->findOrFail($item['id_bien_the']);

                if ($variant->so_luong_ton_kho < $item['so_luong']) {
                    throw new \Exception("Sản phẩm {$variant->sanPham->ten_san_pham} không đủ tồn kho.");
                }

                $subTotal = $variant->gia_ban * $item['so_luong'];
                $tongTienHang += $subTotal;

                $orderItems[] = [
                    'id_bien_the' => $variant->id,
                    'ten_bien_the_luc_mua' => $variant->sanPham->ten_san_pham . ' (' . collect($variant->thuoc_tinh)->map(fn($v, $k) => "$k: $v")->implode(' - ') . ')',
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

            // Coin discount integration (1 coin = 1 VND discount)
            if ($user && $request->dung_xu) {
                $userPoints = (int) $user->diem_thanh_vien;
                if ($userPoints > 0) {
                    $maxDiscountFromCoins = $userPoints;
                    $remainingPayableBeforeCoins = max(0, $tongTienHang - $tienGiam);
                    $coinDiscount = min($maxDiscountFromCoins, $remainingPayableBeforeCoins);
                    
                    $pointsUsed = (int) $coinDiscount;
                    if ($pointsUsed > 0) {
                        $user->decrement('diem_thanh_vien', $pointsUsed);
                    }
                    
                    $tienGiam += $coinDiscount;
                }
            }

            $phiGiaoHang = 30000;
            $tongThanhToan = max(0, $tongTienHang - $tienGiam + $phiGiaoHang);

            $order = DonHang::create([
                'id_nguoi_dung' => $user ? $user->id : null,
                'ma_don_hang' => 'ORD-' . strtoupper(Str::random(10)),
                'id_ma_giam_gia' => $voucherId,
                'tong_tien_hang' => $tongTienHang,
                'tien_duoc_giam' => $tienGiam,
                'phi_giao_hang' => $phiGiaoHang,
                'tong_thanh_toan' => $tongThanhToan,
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
                    $qrUrl = "https://img.vietqr.io/image/{$activeAccount->bank_id}-{$activeAccount->bank_account_no}-compact2.png" .
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
        $voucher = MaGiamGia::where('ma_code', $code)->first();

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
