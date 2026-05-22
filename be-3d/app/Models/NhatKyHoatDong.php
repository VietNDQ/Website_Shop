<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NhatKyHoatDong extends Model
{
    use HasFactory;

    protected $table = 'nhat_ky_hoat_dong';

    const CREATED_AT = 'tao_luc';
    const UPDATED_AT = null; // Bảng này chỉ log, không update

    protected $fillable = [
        'id_nguoi_dung',
        'ten_nguoi_dung',
        'hanh_dong',
        'mau_sac',
    ];

    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_dung');
    }

    public static function ghiLog($idNguoiDung, $tenNguoiDung, $hanhDong, $mauSac = '#6366f1')
    {
        $ip = request()->ip();
        $userAgent = request()->userAgent();
        $chiTietHanhDong = $hanhDong . " [IP: $ip, UA: $userAgent]";

        // 1. Ghi log file text (Laravel system log)
        \Illuminate\Support\Facades\Log::info("ActivityLog - User ID: " . ($idNguoiDung ?? 'Khách') . " - Name: $tenNguoiDung - Action: $chiTietHanhDong");

        // 2. Ghi database
        $log = self::create([
            'id_nguoi_dung' => $idNguoiDung,
            'ten_nguoi_dung' => $tenNguoiDung,
            'hanh_dong' => $chiTietHanhDong,
            'mau_sac' => $mauSac,
        ]);

        // 3. Gửi Realtime Pusher nếu có
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
                    'id' => $log->id,
                    'user' => $tenNguoiDung,
                    'action' => $chiTietHanhDong,
                    'time' => 'Vừa xong',
                    'color' => $mauSac
                ];

                $pusher->trigger('my-channel', 'my-event', $eventData);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Pusher error in Activity Log: ' . $e->getMessage());
        }

        return $log;
    }
}
