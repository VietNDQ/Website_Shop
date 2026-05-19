<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ThanhToan;
use App\Models\DonHang;
use App\Models\LichSuTrangThaiDonHang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebhookController extends Controller
{
    public function handlePaymentWebhook(Request $request)
    {
        // This is a simplified webhook handler
        // In a real app, you would verify signatures from VNPay/Momo
        $request->validate([
            'order_id' => 'required',
            'transaction_id' => 'required',
            'status' => 'required|in:success,failed',
        ]);

        return DB::transaction(function () use ($request) {
            $order = DonHang::where('ma_don_hang', $request->order_id)->firstOrFail();
            $payment = ThanhToan::where('id_don_hang', $order->id)->firstOrFail();

            if ($request->status === 'success') {
                $payment->update([
                    'trang_thai' => 'da_thanh_toan',
                    'ma_giao_dich' => $request->transaction_id,
                    'ngay_thanh_toan' => now(),
                ]);

                // Automatically update order status if needed
                $order->update(['trang_thai' => 'dang_chuan_bi']);
                LichSuTrangThaiDonHang::create([
                    'id_don_hang' => $order->id,
                    'trang_thai' => 'dang_chuan_bi',
                    'ghi_chu' => 'Thanh toán thành công. Đơn hàng đang được chuẩn bị.',
                ]);
            } else {
                $payment->update(['trang_thai' => 'that_bai']);
            }

            return response()->json(['message' => 'Webhook processed']);
        });
    }
}
