<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use App\Models\NhatKyHoatDong;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class NhanVienController extends Controller
{
    private function logActivity($action, $color = '#6366f1')
    {
        $user = Auth::user();
        if ($user && Schema::hasTable('nhat_ky_hoat_dong')) {
            NhatKyHoatDong::ghiLog($user->id, $user->ho_ten, $action, $color);
        }
    }

    public function listStaff()
    {
        // Lấy tất cả người dùng không phải khách hàng (vai_tro != 3)
        $staffMembers = NguoiDung::where('vai_tro', '!=', 3)->get();

        $gradients = [
            'linear-gradient(135deg, #D70018, #7c3aed)',
            'linear-gradient(135deg, #0ea5e9, #6366f1)',
            'linear-gradient(135deg, #22c55e, #0ea5e9)',
            'linear-gradient(135deg, #f59e0b, #22c55e)',
            'linear-gradient(135deg, #ec4899, #8b5cf6)',
        ];

        $roleMap = [
            1 => 'Super Admin',
            2 => 'Quản lý',
            4 => 'Nhân viên kho',
            5 => 'Nhân viên bán hàng'
        ];

        $roleKeys = [
            1 => 'super',
            2 => 'manager',
            4 => 'warehouse',
            5 => 'sales'
        ];

        // Lấy số điện thoại từ địa chỉ mặc định hoặc đầu tiên nếu có
        $data = $staffMembers->map(function ($s) use ($gradients, $roleMap, $roleKeys) {
            $phone = $s->diaChis()->firstWhere('la_mac_dinh', true)?->so_dien_thoai 
                ?? $s->diaChis()->first()?->so_dien_thoai 
                ?? 'Chưa cấu hình';

            return [
                'id' => $s->id,
                'name' => $s->ho_ten,
                'email' => $s->email,
                'phone' => $phone,
                'role' => $roleMap[$s->vai_tro] ?? 'Nhân viên bán hàng',
                'roleKey' => $roleKeys[$s->vai_tro] ?? 'sales',
                'active' => (bool)$s->dang_hoat_dong,
                'lastLogin' => $s->dang_nhap_lan_cuoi_luc ? $s->dang_nhap_lan_cuoi_luc->format('d/m/Y H:i') : 'Chưa đăng nhập',
                'avatarBg' => $gradients[$s->id % count($gradients)]
            ];
        });

        // Tải nhật ký hoạt động
        $logs = [];
        if (Schema::hasTable('nhat_ky_hoat_dong')) {
            $dbLogs = NhatKyHoatDong::latest('tao_luc')->take(30)->get();
            $logs = $dbLogs->map(function ($l) {
                $actionText = $l->hanh_dong;
                $ipAddress = null;
                $userAgent = null;

                if (preg_match('/^(.*?)\s*\[IP:\s*([^,]+),\s*UA:\s*(.*?)\]$/s', $l->hanh_dong, $matches)) {
                    $actionText = trim($matches[1]);
                    $ipAddress = trim($matches[2]);
                    $userAgent = trim($matches[3]);
                }

                $timeStr = $l->tao_luc ? $l->tao_luc->format('H:i - d/m/Y') : 'Vừa xong';

                return [
                    'id' => $l->id,
                    'user' => $l->ten_nguoi_dung ?? 'Hệ thống',
                    'action' => $actionText,
                    'ip' => $ipAddress,
                    'ua' => $userAgent,
                    'time' => $timeStr,
                    'color' => $l->mau_sac
                ];
            })->toArray();
        }

        // Fallback nhật ký giả lập nếu bảng trống
        if (empty($logs)) {
            $logs = [
                ['id' => 1, 'user' => 'Admin Việt', 'action' => 'Thay đổi giá sản phẩm Gundam RX-78', 'time' => '5 phút trước', 'color' => '#6366f1'],
                ['id' => 2, 'user' => 'Trần Văn Kho', 'action' => 'Cập nhật tồn kho: +50 Iron Man MK50', 'time' => '22 phút trước', 'color' => '#0ea5e9'],
                ['id' => 3, 'user' => 'Lê Thị Sales', 'action' => 'Xử lý đơn hàng #DH8821 → Đang giao', 'time' => '1 giờ trước', 'color' => '#22c55e'],
                ['id' => 4, 'user' => 'Admin Việt', 'action' => 'Tạo mã giảm giá SALE20', 'time' => '2 giờ trước', 'color' => '#f59e0b'],
                ['id' => 5, 'user' => 'Lê Thị Sales', 'action' => 'Huỷ đơn hàng #DH8817 theo yêu cầu KH', 'time' => '3 giờ trước', 'color' => '#D70018'],
                ['id' => 6, 'user' => 'Trần Văn Kho', 'action' => 'Thêm sản phẩm mới: Gundam SEED', 'time' => '5 giờ trước', 'color' => '#94a3b8'],
            ];
        }

        return response()->json([
            'status' => 1,
            'data' => [
                'staff' => $data,
                'logs' => $logs
            ]
        ]);
    }

    public function createStaff(Request $request)
    {
        $request->validate([
            'ho_ten' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:nguoi_dung,email',
            'so_dien_thoai' => 'nullable|string|max:20',
            'vai_tro' => 'required|in:Super Admin,Quản lý,Nhân viên kho,Nhân viên bán hàng',
            'mat_khau' => 'required|string|min:6',
        ]);

        $roleValues = [
            'Super Admin' => 1,
            'Quản lý' => 2,
            'Nhân viên kho' => 4,
            'Nhân viên bán hàng' => 5
        ];

        $roleVal = $roleValues[$request->vai_tro] ?? 5;

        $staff = NguoiDung::create([
            'ho_ten' => $request->ho_ten,
            'email' => $request->email,
            'mat_khau' => Hash::make($request->mat_khau),
            'vai_tro' => $roleVal,
            'dang_hoat_dong' => true,
        ]);

        // Tạo số điện thoại liên kết trong dia_chi_nguoi_dung
        if ($request->so_dien_thoai) {
            $staff->diaChis()->create([
                'so_dien_thoai' => $request->so_dien_thoai,
                'dia_chi_chi_tiet' => 'Chưa cấu hình',
                'thanh_pho' => 'Chưa cấu hình',
                'quan_huyen' => 'Chưa cấu hình',
                'la_mac_dinh' => true
            ]);
        }

        $this->logActivity("Tạo tài khoản nhân viên mới: {$request->ho_ten}", '#10b981');

        return response()->json([
            'status' => 1,
            'message' => 'Tạo tài khoản nhân viên thành công.',
            'data' => $staff
        ]);
    }

    public function updateStaff(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:nguoi_dung,id',
            'ho_ten' => 'required|string|max:255',
            'so_dien_thoai' => 'nullable|string|max:20',
            'vai_tro' => 'required|in:Super Admin,Quản lý,Nhân viên kho,Nhân viên bán hàng',
        ]);

        $staff = NguoiDung::findOrFail($request->id);
        $currentUser = Auth::user();

        $roleValues = [
            'Super Admin' => 1,
            'Quản lý' => 2,
            'Nhân viên kho' => 4,
            'Nhân viên bán hàng' => 5
        ];

        $roleVal = $roleValues[$request->vai_tro] ?? $staff->vai_tro;

        // Ràng buộc: không thể tự thay đổi vai trò của chính mình
        if ($currentUser && $currentUser->id === $staff->id) {
            if ($roleVal !== $staff->vai_tro) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Bạn không thể tự thay đổi vai trò/quyền hạn của chính mình!'
                ]);
            }
        }

        $staff->update([
            'ho_ten' => $request->ho_ten,
            'vai_tro' => $roleVal,
        ]);

        // Cập nhật số điện thoại nếu được điền
        if ($request->filled('so_dien_thoai')) {
            $address = $staff->diaChis()->first();
            if ($address) {
                $address->update(['so_dien_thoai' => $request->so_dien_thoai]);
            } else {
                $staff->diaChis()->create([
                    'so_dien_thoai' => $request->so_dien_thoai,
                    'dia_chi_chi_tiet' => 'Chưa cấu hình',
                    'thanh_pho' => 'Chưa cấu hình',
                    'quan_huyen' => 'Chưa cấu hình',
                    'la_mac_dinh' => true
                ]);
            }
        }

        $this->logActivity("Cập nhật thông tin nhân viên: {$request->ho_ten}", '#6366f1');

        return response()->json([
            'status' => 1,
            'message' => 'Cập nhật thông tin nhân viên thành công.',
            'data' => $staff
        ]);
    }

    public function toggleLockStaff($id)
    {
        $staff = NguoiDung::findOrFail($id);
        $currentUser = Auth::user();

        if ($currentUser && $currentUser->id === $staff->id) {
            return response()->json([
                'status' => 0,
                'message' => 'Không thể tự khóa tài khoản của chính mình!'
            ]);
        }

        $staff->update([
            'dang_hoat_dong' => !$staff->dang_hoat_dong
        ]);

        $statusStr = $staff->dang_hoat_dong ? 'Mở khóa' : 'Khóa';
        $logColor = $staff->dang_hoat_dong ? '#10b981' : '#D70018';

        $this->logActivity("{$statusStr} tài khoản nhân viên: {$staff->ho_ten}", $logColor);

        return response()->json([
            'status' => 1,
            'message' => "Đã {$statusStr} tài khoản nhân viên thành công.",
            'data' => $staff
        ]);
    }
}
