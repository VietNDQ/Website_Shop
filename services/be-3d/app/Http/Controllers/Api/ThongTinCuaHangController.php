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
        if ($user && Schema::hasTable('nhat_ky_hoat_dong')) {
            NhatKyHoatDong::ghiLog($user->id, $user->ho_ten, $action, $color);
        }
    }
}
