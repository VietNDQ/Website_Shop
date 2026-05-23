<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FlashSale;
use App\Models\MaGiamGia;
use App\Models\NhatKyHoatDong;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class KhuyenMaiController extends Controller
{
    // ─────────────────────────────────────────
    // MÃ GIẢM GIÁ (COUPON)
    // ─────────────────────────────────────────

    /** Danh sách tất cả mã giảm giá */
    public function getCoupons()
    {
        $coupons = MaGiamGia::orderByDesc('tao_luc')->get();

        $data = $coupons->map(fn($c) => $this->mapCoupon($c));

        return response()->json(['status' => true, 'data' => $data]);
    }

    /** Tạo mã giảm giá mới */
    public function createCoupon(Request $request)
    {
        $request->validate([
            'code'      => 'required|string|max:50|unique:ma_giam_gia,ma_code',
            'type'      => 'required|in:Phần trăm,Số tiền cố định',
            'value'     => 'required|numeric|min:0',
            'minOrder'  => 'nullable|numeric|min:0',
            'maxDiscount' => $request->type === 'Phần trăm' ? 'required|numeric|gt:0' : 'nullable|numeric|min:0',
            'limit'     => 'nullable|integer|min:1',
            'expiry'    => 'nullable|date',
            'active'    => 'boolean',
            'budget'    => 'nullable|numeric|min:0',
            'distributeMethod' => 'required|in:public,claimable,targeted',
        ]);

        $coupon = MaGiamGia::create([
            'ma_code'          => strtoupper(trim($request->code)),
            'loai_giam_gia'    => $request->type === 'Phần trăm' ? 'phan_tram' : 'tien_mat',
            'gia_tri_giam'     => $request->value,
            'don_hang_toi_thieu' => $request->minOrder ?? 0,
            'muc_giam_toi_da'  => $request->maxDiscount,
            'ngay_ket_thuc'    => $request->expiry ? $request->expiry . ' 23:59:59' : null,
            'gioi_han_su_dung' => $request->limit,
            'so_lan_da_dung'   => 0,
            'dang_hoat_dong'   => $request->boolean('active', true),
            'ngan_sach'        => $request->budget,
            'ngan_sach_da_dung'=> 0.00,
            'hinh_thuc_phat_hanh' => $request->distributeMethod,
        ]);

        $this->logActivity('Tạo mã giảm giá mới: ' . $coupon->ma_code, '#10b981');

        return response()->json([
            'status'  => true,
            'message' => 'Tạo mã giảm giá thành công!',
            'data'    => $this->mapCoupon($coupon),
        ]);
    }

    /** Cập nhật mã giảm giá */
    public function updateCoupon(Request $request, $code)
    {
        $coupon = MaGiamGia::where('ma_code', strtoupper($code))->firstOrFail();

        $request->validate([
            'type'       => 'required|in:Phần trăm,Số tiền cố định',
            'value'      => 'required|numeric|min:0',
            'minOrder'   => 'nullable|numeric|min:0',
            'maxDiscount'=> $request->type === 'Phần trăm' ? 'required|numeric|gt:0' : 'nullable|numeric|min:0',
            'limit'      => 'nullable|integer|min:1',
            'expiry'     => 'nullable|date',
            'active'     => 'boolean',
            'budget'     => 'nullable|numeric|min:0',
            'distributeMethod' => 'required|in:public,claimable,targeted',
        ]);

        $coupon->update([
            'loai_giam_gia'    => $request->type === 'Phần trăm' ? 'phan_tram' : 'tien_mat',
            'gia_tri_giam'     => $request->value,
            'don_hang_toi_thieu' => $request->minOrder ?? 0,
            'muc_giam_toi_da'  => $request->maxDiscount,
            'ngay_ket_thuc'    => $request->expiry ? $request->expiry . ' 23:59:59' : null,
            'gioi_han_su_dung' => $request->limit,
            'dang_hoat_dong'   => $request->boolean('active', true),
            'ngan_sach'        => $request->budget,
            'hinh_thuc_phat_hanh' => $request->distributeMethod,
        ]);

        $this->logActivity('Cập nhật mã giảm giá: ' . $coupon->ma_code, '#6366f1');

        return response()->json([
            'status'  => true,
            'message' => 'Cập nhật mã giảm giá thành công!',
            'data'    => $this->mapCoupon($coupon->fresh()),
        ]);
    }

    /** Xoá mã giảm giá */
    public function deleteCoupon($code)
    {
        $coupon = MaGiamGia::where('ma_code', strtoupper($code))->firstOrFail();
        $coupon->delete();

        $this->logActivity('Xóa mã giảm giá: ' . $code, '#dc2626');

        return response()->json(['status' => true, 'message' => 'Xóa mã giảm giá thành công!']);
    }

    // ─────────────────────────────────────────
    // FLASH SALE
    // ─────────────────────────────────────────

    /** Danh sách Flash Sale */
    public function getFlashSales()
    {
        $flashSales = FlashSale::orderByDesc('created_at')->get();

        $data = $flashSales->map(fn($f) => $this->mapFlash($f));

        return response()->json(['status' => true, 'data' => $data]);
    }

    /** Tạo Flash Sale mới */
    public function createFlashSale(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'emoji'     => 'nullable|string|max:20',
            'oldPrice'  => 'required|numeric|min:0',
            'discount'  => 'required|integer|min:1|max:99',
            'timeLeft'  => 'nullable|string|max:50',
        ]);

        $newPrice = round($request->oldPrice * (1 - $request->discount / 100));

        $flash = FlashSale::create([
            'ten_san_pham'       => $request->name,
            'emoji'              => $request->emoji ?? '⚡',
            'gia_goc'            => $request->oldPrice,
            'gia_flash'          => $newPrice,
            'phan_tram_giam'     => $request->discount,
            'thoi_gian_ket_thuc' => $request->timeLeft
                ? now()->addHours((int) filter_var($request->timeLeft, FILTER_SANITIZE_NUMBER_INT))
                : null,
            'dang_hoat_dong'     => true,
        ]);

        $this->logActivity('Tạo Flash Sale: ' . $flash->ten_san_pham, '#f59e0b');

        return response()->json([
            'status'  => true,
            'message' => 'Tạo Flash Sale thành công!',
            'data'    => $this->mapFlash($flash),
        ]);
    }

    /** Cập nhật Flash Sale */
    public function updateFlashSale(Request $request, $id)
    {
        $flash = FlashSale::findOrFail($id);

        $request->validate([
            'name'     => 'required|string|max:255',
            'emoji'    => 'nullable|string|max:20',
            'oldPrice' => 'required|numeric|min:0',
            'discount' => 'required|integer|min:1|max:99',
            'timeLeft' => 'nullable|string|max:50',
        ]);

        $newPrice = round($request->oldPrice * (1 - $request->discount / 100));

        $flash->update([
            'ten_san_pham'   => $request->name,
            'emoji'          => $request->emoji ?? $flash->emoji,
            'gia_goc'        => $request->oldPrice,
            'gia_flash'      => $newPrice,
            'phan_tram_giam' => $request->discount,
        ]);

        $this->logActivity('Cập nhật Flash Sale: ' . $flash->ten_san_pham, '#6366f1');

        return response()->json([
            'status'  => true,
            'message' => 'Cập nhật Flash Sale thành công!',
            'data'    => $this->mapFlash($flash->fresh()),
        ]);
    }

    /** Dừng / Xoá Flash Sale */
    public function deleteFlashSale($id)
    {
        $flash = FlashSale::findOrFail($id);
        $name  = $flash->ten_san_pham;
        $flash->delete();

        $this->logActivity('Dừng Flash Sale: ' . $name, '#dc2626');

        return response()->json(['status' => true, 'message' => 'Đã dừng Flash Sale thành công!']);
    }

    // ─────────────────────────────────────────
    // LỊCH SỬ HOẠT ĐỘNG KHUYẾN MÃI
    // ─────────────────────────────────────────

    /** Lấy lịch sử thao tác liên quan đến Mã giảm giá & Flash Sale */
    public function getHistory()
    {
        if (!Schema::hasTable('nhat_ky_hoat_dong')) {
            return response()->json(['status' => true, 'data' => []]);
        }

        $keywords = ['mã giảm giá', 'flash sale', 'flash_sale', 'voucher', 'coupon', 'khuyến mãi'];

        $query = \App\Models\NhatKyHoatDong::query();
        $query->where(function ($q) use ($keywords) {
            foreach ($keywords as $kw) {
                $q->orWhereRaw('LOWER(hanh_dong) LIKE ?', ['%' . strtolower($kw) . '%']);
            }
        });

        $logs = $query->orderByDesc('tao_luc')->take(100)->get();

        $data = $logs->map(function ($log) {
            $action  = strtolower($log->hanh_dong ?? '');
            $isFlash = str_contains($action, 'flash sale') || str_contains($action, 'flash_sale');

            return [
                'id'     => $log->id,
                'action' => $log->hanh_dong,
                'color'  => $log->mau_sac ?? '#6366f1',
                'user'   => $log->ten_nguoi_dung,
                'kind'   => $isFlash ? 'flash' : 'coupon',
                'time'   => $log->tao_luc
                    ? \Carbon\Carbon::parse($log->tao_luc)->setTimezone('Asia/Ho_Chi_Minh')->format('H:i d/m/Y')
                    : '—',
                'time_raw' => $log->tao_luc,
            ];
        });

        return response()->json(['status' => true, 'data' => $data]);
    }

    /** Khách hàng lưu mã thu thập vào ví */
    public function claimVoucher(Request $request, $code)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Vui lòng đăng nhập.'], 401);
        }

        $voucher = MaGiamGia::where('ma_code', strtoupper(trim($code)))->first();
        if (!$voucher) {
            return response()->json(['status' => false, 'message' => 'Mã giảm giá không tồn tại.'], 404);
        }

        if ($voucher->hinh_thuc_phat_hanh !== 'claimable') {
            return response()->json(['status' => false, 'message' => 'Mã giảm giá này không hỗ trợ thu thập.'], 400);
        }

        if (!$voucher->dang_hoat_dong) {
            return response()->json(['status' => false, 'message' => 'Mã giảm giá này hiện không hoạt động.'], 400);
        }

        if ($voucher->ngay_ket_thuc && $voucher->ngay_ket_thuc->isPast()) {
            return response()->json(['status' => false, 'message' => 'Mã giảm giá đã hết hạn sử dụng.'], 400);
        }

        if ($voucher->gioi_han_su_dung !== null && $voucher->so_lan_da_dung >= $voucher->gioi_han_su_dung) {
            return response()->json(['status' => false, 'message' => 'Mã giảm giá đã hết lượt sử dụng.'], 400);
        }

        if ($voucher->ngan_sach !== null && $voucher->ngan_sach_da_dung >= $voucher->ngan_sach) {
            return response()->json(['status' => false, 'message' => 'Mã giảm giá đã hết ngân sách khuyến mãi.'], 400);
        }

        // Kiểm tra xem đã thu thập chưa
        $exists = \App\Models\NguoiDungVoucher::where('id_nguoi_dung', $user->id)
            ->where('id_ma_giam_gia', $voucher->id)
            ->first();

        if ($exists) {
            return response()->json(['status' => false, 'message' => 'Bạn đã thu thập mã giảm giá này rồi.'], 400);
        }

        \App\Models\NguoiDungVoucher::create([
            'id_nguoi_dung' => $user->id,
            'id_ma_giam_gia' => $voucher->id,
            'trang_thai' => 'unused',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Thu thập mã giảm giá thành công!'
        ]);
    }

    /** Lấy danh sách voucher trong ví của khách hàng đăng nhập */
    public function getMyVouchers(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Vui lòng đăng nhập.'], 401);
        }

        $userVouchers = \App\Models\NguoiDungVoucher::with('maGiamGia')
            ->where('id_nguoi_dung', $user->id)
            ->get();

        $data = $userVouchers->map(function ($uv) {
            $c = $uv->maGiamGia;
            if (!$c) return null;
            return [
                'id' => $uv->id,
                'id_ma_giam_gia' => $uv->id_ma_giam_gia,
                'trang_thai' => $uv->trang_thai,
                'code' => $c->ma_code,
                'type' => $c->loai_giam_gia === 'phan_tram' ? 'Phần trăm' : 'Số tiền cố định',
                'value' => (float)$c->gia_tri_giam,
                'minOrder' => (float)$c->don_hang_toi_thieu,
                'maxDiscount' => $c->muc_giam_toi_da ? (float)$c->muc_giam_toi_da : null,
                'expiry' => $c->ngay_ket_thuc ? $c->ngay_ket_thuc->format('Y-m-d') : null,
                'active' => (bool)$c->dang_hoat_dong && 
                            ($c->ngay_ket_thuc === null || $c->ngay_ket_thuc->isFuture()) &&
                            ($c->gioi_han_su_dung === null || $c->so_lan_da_dung < $c->gioi_han_su_dung) &&
                            ($c->ngan_sach === null || $c->ngan_sach_da_dung < $c->ngan_sach),
            ];
        })->filter()->values();

        return response()->json(['status' => true, 'data' => $data]);
    }

    /** Lấy danh sách các mã thuộc dạng claimable để hiển thị ở phía Client */
    public function getClaimableVouchers(Request $request)
    {
        $now = now();
        $vouchers = MaGiamGia::where('hinh_thuc_phat_hanh', 'claimable')
            ->where('dang_hoat_dong', true)
            ->where(function($q) use ($now) {
                $q->whereNull('ngay_ket_thuc')->orWhere('ngay_ket_thuc', '>', $now);
            })
            ->where(function($q) {
                $q->whereNull('gioi_han_su_dung')->orWhereColumn('so_lan_da_dung', '<', 'gioi_han_su_dung');
            })
            ->where(function($q) {
                $q->whereNull('ngan_sach')->orWhereColumn('ngan_sach_da_dung', '<', 'ngan_sach');
            })
            ->orderByDesc('tao_luc')
            ->get();

        $user = auth('sanctum')->user();
        $claimedIds = [];
        if ($user) {
            $claimedIds = \App\Models\NguoiDungVoucher::where('id_nguoi_dung', $user->id)
                ->pluck('id_ma_giam_gia')
                ->toArray();
        }

        $data = $vouchers->map(function ($c) use ($claimedIds) {
            return [
                'id' => $c->id,
                'code' => $c->ma_code,
                'type' => $c->loai_giam_gia === 'phan_tram' ? 'Phần trăm' : 'Số tiền cố định',
                'value' => (float)$c->gia_tri_giam,
                'minOrder' => (float)$c->don_hang_toi_thieu,
                'maxDiscount' => $c->muc_giam_toi_da ? (float)$c->muc_giam_toi_da : null,
                'expiry' => $c->ngay_ket_thuc ? $c->ngay_ket_thuc->format('Y-m-d') : null,
                'is_claimed' => in_array($c->id, $claimedIds),
            ];
        });

        return response()->json(['status' => true, 'data' => $data]);
    }

    /** Cho phép Admin chọn danh sách user và mã giảm giá để tặng trực tiếp (phân bổ kín) */
    public function distributeVoucher(Request $request)
    {
        $request->validate([
            'voucher_id' => 'required|exists:ma_giam_gia,id',
            'user_ids'   => 'required|array',
            'user_ids.*' => 'exists:nguoi_dung,id',
        ]);

        $voucher = MaGiamGia::findOrFail($request->voucher_id);

        if ($voucher->hinh_thuc_phat_hanh !== 'targeted') {
            return response()->json(['status' => false, 'message' => 'Mã giảm giá này không phải là loại phân bổ kín.'], 400);
        }

        $count = 0;
        foreach ($request->user_ids as $userId) {
            // Check if already assigned
            $exists = \App\Models\NguoiDungVoucher::where('id_nguoi_dung', $userId)
                ->where('id_ma_giam_gia', $voucher->id)
                ->first();

            if (!$exists) {
                \App\Models\NguoiDungVoucher::create([
                    'id_nguoi_dung' => $userId,
                    'id_ma_giam_gia' => $voucher->id,
                    'trang_thai' => 'unused',
                ]);
                $count++;
            }
        }

        $this->logActivity('Phân bổ voucher kín ' . $voucher->ma_code . ' cho ' . $count . ' người dùng', '#8b5cf6');

        return response()->json([
            'status' => true,
            'message' => "Đã phân bổ thành công mã giảm giá {$voucher->ma_code} cho {$count} khách hàng!"
        ]);
    }

    // ─────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────

    private function mapCoupon(MaGiamGia $c): array
    {
        return [
            'id'       => $c->id,
            'code'     => $c->ma_code,
            'type'     => $c->loai_giam_gia === 'phan_tram' ? 'Phần trăm' : 'Số tiền cố định',
            'value'    => (float) $c->gia_tri_giam,
            'minOrder' => (float) $c->don_hang_toi_thieu,
            'maxDiscount' => $c->muc_giam_toi_da ? (float) $c->muc_giam_toi_da : null,
            'used'     => $c->so_lan_da_dung,
            'limit'    => $c->gioi_han_su_dung,
            'expiry'   => $c->ngay_ket_thuc ? $c->ngay_ket_thuc->format('Y-m-d') : null,
            'active'   => (bool) $c->dang_hoat_dong,
            'budget'   => $c->ngan_sach !== null ? (float) $c->ngan_sach : null,
            'spentBudget' => (float) ($c->ngan_sach_da_dung ?? 0),
            'distributeMethod' => $c->hinh_thuc_phat_hanh,
        ];
    }

    private function mapFlash(FlashSale $f): array
    {
        $timeLeft = '—';
        if ($f->thoi_gian_ket_thuc && $f->thoi_gian_ket_thuc->isFuture()) {
            $diff     = now()->diff($f->thoi_gian_ket_thuc);
            $timeLeft = $diff->h . 'g ' . $diff->i . 'p';
        }

        return [
            'id'       => $f->id,
            'name'     => $f->ten_san_pham,
            'emoji'    => $f->emoji,
            'discount' => $f->phan_tram_giam,
            'oldPrice' => (float) $f->gia_goc,
            'newPrice' => (float) $f->gia_flash,
            'timeLeft' => $timeLeft,
            'active'   => (bool) $f->dang_hoat_dong,
        ];
    }

    private function logActivity(string $action, string $color = '#6366f1'): void
    {
        $user = Auth::user();
        if ($user && Schema::hasTable('nhat_ky_hoat_dong')) {
            NhatKyHoatDong::ghiLog($user->id, $user->ho_ten, $action, $color);
        }
    }
}
