<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class NguoiDungController extends Controller
{
    public function getProfile()
    {
        /** @var \App\Models\NguoiDung $user */
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => 0,
                'message' => 'Chưa đăng nhập.'
            ], 401);
        }

        // Lấy số điện thoại và địa chỉ từ dia_chi_nguoi_dung
        $addressRecord = $user->diaChis()->first();
        $phone = $addressRecord ? $addressRecord->so_dien_thoai : '';
        $address = $addressRecord ? $addressRecord->dia_chi_chi_tiet : '';

        // Tên vai trò
        $roleMap = [
            1 => 'Super Admin',
            2 => 'Quản lý',
            4 => 'Nhân viên kho',
            5 => 'Nhân viên bán hàng'
        ];
        $roleName = $roleMap[$user->vai_tro] ?? 'Nhân viên';

        // Check if new profile columns exist
        $dob = Schema::hasColumn('nguoi_dung', 'ngay_sinh') ? $user->ngay_sinh : '1995-06-15';
        $bio = Schema::hasColumn('nguoi_dung', 'gioi_thieu') ? $user->gioi_thieu : 'Quản trị viên hệ thống Skyline Models.';
        $avatar = Schema::hasColumn('nguoi_dung', 'anh_dai_dien') ? $user->anh_dai_dien : null;

        return response()->json([
            'status' => 1,
            'data' => [
                'id' => $user->id,
                'name' => $user->ho_ten,
                'email' => $user->email,
                'phone' => $phone,
                'dob' => $dob,
                'address' => $address,
                'bio' => $bio,
                'avatar' => $avatar ? asset($avatar) : null,
                'roleName' => $roleName
            ]
        ]);
    }

    public function updateProfile(Request $request)
    {
        /** @var \App\Models\NguoiDung $user */
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => 0,
                'message' => 'Chưa đăng nhập.'
            ], 401);
        }

        $request->validate([
            'ho_ten' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:nguoi_dung,email,' . $user->id,
            'so_dien_thoai' => 'nullable|string|max:20',
            'ngay_sinh' => 'nullable|date',
            'dia_chi' => 'nullable|string|max:255',
            'gioi_thieu' => 'nullable|string',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Cập nhật thông tin cơ bản
        $updateData = [
            'ho_ten' => $request->ho_ten,
            'email' => $request->email
        ];

        // Nếu bảng đã được migration, cập nhật thêm
        if (Schema::hasColumn('nguoi_dung', 'ngay_sinh')) {
            $updateData['ngay_sinh'] = $request->ngay_sinh;
        }
        if (Schema::hasColumn('nguoi_dung', 'gioi_thieu')) {
            $updateData['gioi_thieu'] = $request->gioi_thieu;
        }

        // Xử lý upload avatar
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $fileName = time() . '_' . $user->id . '.' . $file->getClientOriginalExtension();
            // Lưu vào thư mục public/uploads/avatars
            $file->move(public_path('uploads/avatars'), $fileName);
            $avatarPath = 'uploads/avatars/' . $fileName;

            if (Schema::hasColumn('nguoi_dung', 'anh_dai_dien')) {
                $updateData['anh_dai_dien'] = $avatarPath;
            }
        }

        $user->update($updateData);

        // Cập nhật hoặc tạo mới số điện thoại & địa chỉ
        if ($request->filled('so_dien_thoai') || $request->filled('dia_chi')) {
            $addressRecord = $user->diaChis()->first();
            $phoneVal = $request->so_dien_thoai ?? ($addressRecord ? $addressRecord->so_dien_thoai : 'Chưa cấu hình');
            $addressVal = $request->dia_chi ?? ($addressRecord ? $addressRecord->dia_chi_chi_tiet : 'Chưa cấu hình');

            if ($addressRecord) {
                $addressRecord->update([
                    'so_dien_thoai' => $phoneVal,
                    'dia_chi_chi_tiet' => $addressVal
                ]);
            } else {
                $user->diaChis()->create([
                    'so_dien_thoai' => $phoneVal,
                    'dia_chi_chi_tiet' => $addressVal,
                    'thanh_pho' => 'Chưa cấu hình',
                    'quan_huyen' => 'Chưa cấu hình',
                    'la_mac_dinh' => true
                ]);
            }
        }

        return response()->json([
            'status' => 1,
            'message' => 'Cập nhật thông tin cá nhân thành công.',
            'data' => [
                'name' => $user->ho_ten,
                'email' => $user->email,
                'phone' => $request->so_dien_thoai,
                'dob' => Schema::hasColumn('nguoi_dung', 'ngay_sinh') ? $user->ngay_sinh : $request->ngay_sinh,
                'address' => $request->dia_chi,
                'bio' => Schema::hasColumn('nguoi_dung', 'gioi_thieu') ? $user->gioi_thieu : $request->gioi_thieu,
                'avatar' => Schema::hasColumn('nguoi_dung', 'anh_dai_dien') && $user->anh_dai_dien ? asset($user->anh_dai_dien) : null
            ]
        ]);
    }

    public function updatePassword(Request $request)
    {
        /** @var \App\Models\NguoiDung $user */
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => 0,
                'message' => 'Chưa đăng nhập.'
            ], 401);
        }

        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6',
            'confirm_password' => 'required|string|same:new_password'
        ]);

        // Kiểm tra mật khẩu hiện tại
        if (!Hash::check($request->current_password, $user->mat_khau)) {
            return response()->json([
                'status' => 0,
                'message' => 'Mật khẩu hiện tại không chính xác.'
            ]);
        }

        // Cập nhật mật khẩu mới
        $user->update([
            'mat_khau' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'status' => 1,
            'message' => 'Thay đổi mật khẩu thành công.'
        ]);
    }
}
