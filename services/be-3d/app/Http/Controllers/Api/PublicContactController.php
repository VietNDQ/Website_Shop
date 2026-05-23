<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ThuLienHe;
use App\Models\ThongBao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublicContactController extends Controller
{
    /**
     * Send contact letter/message
     */
    public function sendContact(Request $request)
    {
        $request->validate([
            'ho_ten' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'so_dien_thoai' => 'nullable|string|max:20',
            'tieu_de' => 'required|string|max:255',
            'noi_dung' => 'required|string|max:2000',
        ], [
            'ho_ten.required' => 'Họ tên là bắt buộc.',
            'email.required' => 'Email là bắt buộc.',
            'email.email' => 'Định dạng email không hợp lệ.',
            'tieu_de.required' => 'Tiêu đề thư là bắt buộc.',
            'noi_dung.required' => 'Nội dung thư là bắt buộc.',
        ]);

        $user = Auth::guard('sanctum')->user();

        $contact = ThuLienHe::create([
            'id_nguoi_dung' => $user ? $user->id : null,
            'ho_ten' => $request->ho_ten,
            'email' => $request->email,
            'so_dien_thoai' => $request->so_dien_thoai,
            'tieu_de' => $request->tieu_de,
            'noi_dung' => $request->noi_dung,
            'trang_thai' => 0, // chưa đọc
        ]);

        // Tạo chuông thông báo cho Admin
        ThongBao::taoThongBao(
            "Thư liên hệ mới",
            "Bạn có thư liên hệ mới từ khách hàng {$request->ho_ten} với tiêu đề \"{$request->tieu_de}\".",
            'thu_lien_he',
            '/nhan-vien/dashboard'
        );

        // Gửi Realtime qua Pusher nếu có
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
                $pusher->trigger('my-channel', 'new-contact', [
                    'id' => $contact->id,
                    'ho_ten' => $contact->ho_ten,
                    'tieu_de' => $contact->tieu_de,
                ]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Pusher error in Public Contact: ' . $e->getMessage());
        }

        return response()->json([
            'status' => true,
            'message' => 'Gửi thư liên hệ thành công! Chúng tôi sẽ phản hồi sớm nhất.',
            'contact' => $contact,
        ], 201);
    }
}
