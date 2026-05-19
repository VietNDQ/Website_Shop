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
            'maxDiscount' => 'nullable|numeric|min:0',
            'limit'     => 'nullable|integer|min:1',
            'expiry'    => 'nullable|date',
            'active'    => 'boolean',
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
            'maxDiscount'=> 'nullable|numeric|min:0',
            'limit'      => 'nullable|integer|min:1',
            'expiry'     => 'nullable|date',
            'active'     => 'boolean',
        ]);

        $coupon->update([
            'loai_giam_gia'    => $request->type === 'Phần trăm' ? 'phan_tram' : 'tien_mat',
            'gia_tri_giam'     => $request->value,
            'don_hang_toi_thieu' => $request->minOrder ?? 0,
            'muc_giam_toi_da'  => $request->maxDiscount,
            'ngay_ket_thuc'    => $request->expiry ? $request->expiry . ' 23:59:59' : null,
            'gioi_han_su_dung' => $request->limit,
            'dang_hoat_dong'   => $request->boolean('active', true),
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

    // ─────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────

    private function mapCoupon(MaGiamGia $c): array
    {
        return [
            'code'     => $c->ma_code,
            'type'     => $c->loai_giam_gia === 'phan_tram' ? 'Phần trăm' : 'Số tiền cố định',
            'value'    => (float) $c->gia_tri_giam,
            'minOrder' => (float) $c->don_hang_toi_thieu,
            'maxDiscount' => $c->muc_giam_toi_da ? (float) $c->muc_giam_toi_da : null,
            'used'     => $c->so_lan_da_dung,
            'limit'    => $c->gioi_han_su_dung,
            'expiry'   => $c->ngay_ket_thuc ? $c->ngay_ket_thuc->format('Y-m-d') : null,
            'active'   => (bool) $c->dang_hoat_dong,
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
        if (!$user) return;

        $log = null;
        if (Schema::hasTable('nhat_ky_hoat_dong')) {
            $log = NhatKyHoatDong::create([
                'id_nguoi_dung'  => $user->id,
                'ten_nguoi_dung' => $user->ho_ten,
                'hanh_dong'      => $action,
                'mau_sac'        => $color,
            ]);
        }

        try {
            if (class_exists('Pusher\Pusher')) {
                $pusher = new \Pusher\Pusher(
                    env('PUSHER_APP_KEY', '794a0b225fca675fc9a7'),
                    env('PUSHER_APP_SECRET', 'cee10ba3a6fe8c8db26f'),
                    env('PUSHER_APP_ID', '2156596'),
                    ['cluster' => env('PUSHER_APP_CLUSTER', 'ap1'), 'useTLS' => true]
                );
                $pusher->trigger('my-channel', 'my-event', [
                    'id'             => $log ? $log->id : rand(1000, 9999),
                    'id_nguoi_dung'  => $user->id,
                    'ten_nguoi_dung' => $user->ho_ten,
                    'hanh_dong'      => $action,
                    'mau_sac'        => $color,
                    'created_at'     => now()->toISOString(),
                    'tao_luc'        => now()->toDateTimeString(),
                ]);
            }
        } catch (\Exception $e) {
            // Bỏ qua lỗi Pusher
        }
    }
}
