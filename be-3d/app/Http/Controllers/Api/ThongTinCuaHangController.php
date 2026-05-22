<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ThongTinCuaHang;
use App\Models\NhatKyHoatDong;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class ThongTinCuaHangController extends Controller
{
    public function getSettings()
    {
        $settings = ThongTinCuaHang::first();
        if (!$settings) {
            $settings = ThongTinCuaHang::create([
                'ten_thuong_hieu' => 'BALAB',
                'hotline' => '1800 2097',
                'email_ho_tro' => 'support@BALAB.vn',
                'website' => 'https://BALAB.vn',
                'dia_chi_kho' => '123 Nguyễn Văn Linh, Q.7, TP.HCM',
                'mo_ta' => 'Chuyên phân phối sản phẩm chính hãng, chất lượng cao.',
                'facebook' => 'https://facebook.com/ShopBALAB',
                'instagram' => 'https://instagram.com/ShopBALAB',
            ]);
        }
        return response()->json($settings);
    }

    public function saveSettings(Request $request)
    {
        $request->validate([
            'ten_thuong_hieu' => 'required|string|max:255',
            'hotline' => 'nullable|string|max:50',
            'email_ho_tro' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'dia_chi_kho' => 'nullable|string|max:555',
            'mo_ta' => 'nullable|string',
            'facebook' => 'nullable|string|max:255',
            'instagram' => 'nullable|string|max:255',
            'bank_id' => 'nullable|string|max:50',
            'bank_account_no' => 'nullable|string|max:50',
            'bank_account_name' => 'nullable|string|max:255',
        ]);

        $settings = ThongTinCuaHang::first();
        if (!$settings) {
            $settings = new ThongTinCuaHang();
        }

        $settings->fill($request->all());
        $settings->save();

        $this->logActivity('Cập nhật thông tin cửa hàng thành ' . $settings->ten_thuong_hieu, '#ec4899'); // pink color

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật thông tin cửa hàng thành công!',
            'data' => $settings
        ]);
    }

    private function logActivity($action, $color = '#6366f1')
    {
        $user = Auth::user();
        if ($user) {
            $log = null;
            if (Schema::hasTable('nhat_ky_hoat_dong')) {
                $log = NhatKyHoatDong::create([
                    'id_nguoi_dung' => $user->id,
                    'ten_nguoi_dung' => $user->ho_ten,
                    'hanh_dong' => $action,
                    'mau_sac' => $color,
                ]);
            }

            // Gửi sự kiện Realtime qua Pusher
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

                    $eventData = [
                        'id' => $log ? $log->id : rand(1000, 9999),
                        'id_nguoi_dung' => $user->id,
                        'ten_nguoi_dung' => $user->ho_ten,
                        'hanh_dong' => $action,
                        'mau_sac' => $color,
                        'created_at' => now()->toISOString(),
                        'tao_luc' => now()->toDateTimeString()
                    ];

                    $pusher->trigger('my-channel', 'my-event', $eventData);
                }
            } catch (\Exception $e) {
                // Bỏ qua lỗi Pusher để tránh làm sập luồng chính
            }
        }
    }
}
