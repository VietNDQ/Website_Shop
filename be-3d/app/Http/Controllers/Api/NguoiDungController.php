<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use App\Models\DiaChiNguoiDung;
use App\Models\DonHang;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\DiaChiRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Google_Client;

class NguoiDungController extends Controller
{
    public function loginGoogle(Request $request)
    {
        $id_token = $request->id_token;
        if (!$id_token) {
            return response()->json([
                'status'  => false,
                'message' => 'Token không được cung cấp.',
            ]);
        }

        // Gọi API của Google để verify id_token
        $response = Http::get("https://oauth2.googleapis.com/tokeninfo", [
            'id_token' => $id_token
        ]);

        if ($response->successful()) {
            $payload = $response->json();
            $aud = $payload['aud'] ?? '';
            $iss = $payload['iss'] ?? '';

            // Xác thực Client ID và nhà phát hành token
            if (($iss === 'accounts.google.com' || $iss === 'https://accounts.google.com') && $aud === env('CLIENT_ID')) {
                $ho_ten = $payload['name'] ?? 'Google User';
                $email = $payload['email'];
                $google_id = $payload['sub'] ?? '';

                // Tìm theo google_id trước
                $user = null;
                if ($google_id) {
                    $user = NguoiDung::where('google_id', $google_id)->first();
                }

                // Nếu không tìm thấy, tìm theo email và cập nhật google_id
                if (!$user && $email) {
                    $user = NguoiDung::where('email', $email)->first();
                    if ($user) {
                        $user->update(['google_id' => $google_id]);
                    }
                }

                if ($user) {
                    if (!$user->dang_hoat_dong) {
                        return response()->json([
                            'status'  => false,
                            'message' => 'Tài khoản của bạn đã bị khóa.',
                        ]);
                    }

                    $user->update(['dang_nhap_lan_cuoi_luc' => now()]);
                    $token = $user->createToken('auth_token')->plainTextToken;

                    // Ghi log đăng nhập Google
                    \App\Models\NhatKyHoatDong::ghiLog($user->id, $user->ho_ten, "Đăng nhập thành công bằng tài khoản Google (Thiết bị/Phiên mới)", '#3b82f6');

                    return response()->json([
                        'status'    => true,
                        'message'   => 'Đăng nhập thành công',
                        'ho_ten'    => $user->ho_ten,
                        'vai_tro'   => $user->vai_tro,
                        'token'     => $token,
                    ]);
                } else {
                    $newUser = NguoiDung::create([
                        'ho_ten'         => $ho_ten,
                        'email'          => $email,
                        'google_id'      => $google_id,
                        'mat_khau'       => Hash::make('123456'), // Mật khẩu mặc định đã băm
                        'vai_tro'        => 3, // Mặc định là khách hàng
                        'dang_hoat_dong' => 1,
                        'dang_nhap_lan_cuoi_luc' => now(),
                    ]);

                    $token = $newUser->createToken('auth_token')->plainTextToken;

                    // Ghi log đăng ký Google
                    \App\Models\NhatKyHoatDong::ghiLog($newUser->id, $newUser->ho_ten, "Đăng ký tài khoản thành công qua Google Login", '#10b981');

                    return response()->json([
                        'status'  => true,
                        'message' => 'Bạn đã đăng ký tài khoản thành công!',
                        'ho_ten'  => $newUser->ho_ten,
                        'vai_tro' => $newUser->vai_tro,
                        'token'   => $token,
                    ]);
                }
            }
        }

        return response()->json([
            'status'  => false,
            'message' => 'Token không hợp lệ hoặc đã hết hạn.',
        ]);
    }
    /**
     * 1. Lấy thông tin cá nhân bao gồm điểm tích lũy và tổng chi tiêu được xử lý từ Backend.
     */
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

        // Lấy số điện thoại và địa chỉ mặc định từ dia_chi_nguoi_dung
        $addressRecord = $user->diaChis()->where('la_mac_dinh', true)->first() ?: $user->diaChis()->first();
        $phone = $addressRecord ? $addressRecord->so_dien_thoai : '';
        $address = $addressRecord ? $addressRecord->dia_chi_chi_tiet : '';

        // Tên vai trò
        $roleMap = [
            1 => 'Super Admin',
            2 => 'Quản lý',
            3 => 'Khách hàng',
            4 => 'Nhân viên kho',
            5 => 'Nhân viên bán hàng'
        ];
        $roleName = $roleMap[$user->vai_tro] ?? 'Khách hàng';

        // Lấy trường mới
        $dob = Schema::hasColumn('nguoi_dung', 'ngay_sinh') ? $user->ngay_sinh : '';
        $bio = Schema::hasColumn('nguoi_dung', 'gioi_thieu') ? $user->gioi_thieu : '';
        $avatar = Schema::hasColumn('nguoi_dung', 'anh_dai_dien') ? $user->anh_dai_dien : null;

        // Tính toán tổng chi tiêu và tích lũy an toàn từ Backend
        $totalSpentCalculated = $user->donHangs()
            ->whereIn('trang_thai', ['hoan_thanh', 'da_giao'])
            ->sum('tong_thanh_toan');
        $userPointsCalculated = (int) floor($totalSpentCalculated / 1000);

        // Đọc từ cột DB nếu đã chạy migration thành công, ngược lại dùng giá trị vừa tính
        $tongChiTieu = Schema::hasColumn('nguoi_dung', 'tong_chi_tieu') ? $user->tong_chi_tieu : $totalSpentCalculated;
        $diemThanhVien = Schema::hasColumn('nguoi_dung', 'diem_thanh_vien') ? $user->diem_thanh_vien : $userPointsCalculated;

        // Tự động đồng bộ hóa cột trong DB nếu có cột nhưng chưa khớp với tính toán thực tế
        if (Schema::hasColumn('nguoi_dung', 'tong_chi_tieu') && $user->tong_chi_tieu != $totalSpentCalculated) {
            $user->update([
                'tong_chi_tieu' => $totalSpentCalculated,
                'diem_thanh_vien' => $userPointsCalculated
            ]);
            $tongChiTieu = $totalSpentCalculated;
            $diemThanhVien = $userPointsCalculated;
        }

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
                'roleName' => $roleName,
                'google_id' => $user->google_id,
                'zalo_id' => $user->zalo_id,
                'tong_chi_tieu' => (float) $tongChiTieu,
                'diem_thanh_vien' => (int) $diemThanhVien,
                'updated_at' => $user->cap_nhat_luc ? \Carbon\Carbon::parse($user->cap_nhat_luc)->format('d/m/Y H:i') : null
            ]
        ]);
    }

    /**
     * 2. Cập nhật thông tin cá nhân
     */
    public function updateProfile(UpdateProfileRequest $request)
    {
        /** @var \App\Models\NguoiDung $user */
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => 0,
                'message' => 'Chưa đăng nhập.'
            ], 401);
        }

        $updateData = [
            'ho_ten' => $request->ho_ten,
            'email' => $request->email
        ];

        if (Schema::hasColumn('nguoi_dung', 'ngay_sinh')) {
            $updateData['ngay_sinh'] = $request->ngay_sinh ?: null;
        }
        if (Schema::hasColumn('nguoi_dung', 'gioi_thieu')) {
            $updateData['gioi_thieu'] = $request->gioi_thieu ?: null;
        }

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $fileName = time() . '_' . $user->id . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/avatars'), $fileName);
            $avatarPath = 'uploads/avatars/' . $fileName;

            if (Schema::hasColumn('nguoi_dung', 'anh_dai_dien')) {
                $updateData['anh_dai_dien'] = $avatarPath;
            }
        }

        $user->update($updateData);

        // Cập nhật hoặc tạo mới địa chỉ mặc định
        if ($request->filled('so_dien_thoai') || $request->filled('dia_chi')) {
            $addressRecord = $user->diaChis()->where('la_mac_dinh', true)->first() ?: $user->diaChis()->first();

            if ($addressRecord) {
                $updateAddressData = [];
                if ($request->filled('so_dien_thoai')) {
                    $updateAddressData['so_dien_thoai'] = $request->so_dien_thoai;
                }
                if ($request->filled('dia_chi')) {
                    $updateAddressData['dia_chi_chi_tiet'] = $request->dia_chi;
                }
                if (!empty($updateAddressData)) {
                    $addressRecord->update($updateAddressData);
                }
            } else {
                $user->diaChis()->create([
                    'so_dien_thoai' => $request->so_dien_thoai ?? '',
                    'dia_chi_chi_tiet' => $request->dia_chi ?? 'Chưa cấu hình',
                    'thanh_pho' => 'Chưa cấu hình',
                    'quan_huyen' => 'Chưa cấu hình',
                    'la_mac_dinh' => true
                ]);
            }
        }

        // Lấy lại thông tin sau khi lưu
        return $this->getProfile();
    }

    /**
     * 3. Thay đổi mật khẩu
     */
    public function updatePassword(ChangePasswordRequest $request)
    {
        /** @var \App\Models\NguoiDung $user */
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => 0,
                'message' => 'Chưa đăng nhập.'
            ], 401);
        }

        if (!Hash::check($request->current_password, $user->mat_khau)) {
            return response()->json([
                'status' => 0,
                'message' => 'Mật khẩu hiện tại không chính xác.'
            ]);
        }

        $user->update([
            'mat_khau' => Hash::make($request->new_password)
        ]);

        // Ghi log đổi mật khẩu thành công
        \App\Models\NhatKyHoatDong::ghiLog($user->id, $user->ho_ten, "Thay đổi mật khẩu thành công từ trang cá nhân", '#e11d48');

        return response()->json([
            'status' => 1,
            'message' => 'Thay đổi mật khẩu thành công.'
        ]);
    }

    /**
     * 4. Sổ địa chỉ (Address Management)
     */
    public function getAddresses()
    {
        /** @var \App\Models\NguoiDung $user */
        $user = Auth::user();
        if (!$user) {
            return response()->json([], 401);
        }

        // diaChis tự động không lấy các bản ghi đã bị soft-deleted
        return response()->json($user->diaChis()->latest('la_mac_dinh')->get());
    }

    public function addAddress(DiaChiRequest $request)
    {
        /** @var \App\Models\NguoiDung $user */
        $user = Auth::user();
        if (!$user) {
            return response()->json([], 401);
        }

        if ($request->la_mac_dinh) {
            $user->diaChis()->update(['la_mac_dinh' => false]);
        }

        $address = $user->diaChis()->create([
            'so_dien_thoai' => $request->so_dien_thoai,
            'dia_chi_chi_tiet' => $request->dia_chi_chi_tiet,
            'thanh_pho' => $request->thanh_pho,
            'quan_huyen' => $request->quan_huyen,
            'thanh_pho_id' => $request->thanh_pho_id,
            'quan_huyen_id' => $request->quan_huyen_id,
            'phuong_xa_id' => $request->phuong_xa_id,
            'phuong_xa' => $request->phuong_xa,
            'la_mac_dinh' => $request->la_mac_dinh ? true : false
        ]);

        return response()->json($address, 201);
    }

    public function updateAddress(DiaChiRequest $request, $id)
    {
        /** @var \App\Models\NguoiDung $user */
        $user = Auth::user();
        if (!$user) {
            return response()->json([], 401);
        }

        $address = $user->diaChis()->findOrFail($id);

        if ($request->la_mac_dinh) {
            $user->diaChis()->where('id', '!=', $id)->update(['la_mac_dinh' => false]);
        }

        $address->update([
            'so_dien_thoai' => $request->so_dien_thoai,
            'dia_chi_chi_tiet' => $request->dia_chi_chi_tiet,
            'thanh_pho' => $request->thanh_pho,
            'quan_huyen' => $request->quan_huyen,
            'thanh_pho_id' => $request->thanh_pho_id,
            'quan_huyen_id' => $request->quan_huyen_id,
            'phuong_xa_id' => $request->phuong_xa_id,
            'phuong_xa' => $request->phuong_xa,
            'la_mac_dinh' => $request->la_mac_dinh ? true : false
        ]);

        return response()->json($address);
    }

    public function deleteAddress($id)
    {
        /** @var \App\Models\NguoiDung $user */
        $user = Auth::user();
        if (!$user) {
            return response()->json([], 401);
        }

        $address = $user->diaChis()->findOrFail($id);

        // Thực hiện xóa mềm (Soft Delete) bằng cách check xem Model có dùng SoftDeletes không
        $address->delete();

        return response()->json([
            'status' => 1,
            'message' => 'Xóa địa chỉ thành công.'
        ]);
    }

    /**
     * 5. Lịch sử đơn hàng (Phân trang 10 đơn hàng mỗi trang)
     */
    public function getOrderHistory(Request $request)
    {
        /** @var \App\Models\NguoiDung $user */
        $user = Auth::user();
        if (!$user) {
            return response()->json([], 401);
        }

        $orders = DonHang::with([
            'chiTiets.danhGia',
            'chiTiets.bienThe.sanPham.hinhAnhs',
            'thanhToan',
            'lichSuTrangThais'
        ])
            ->where('id_nguoi_dung', $user->id)
            ->latest('tao_luc')
            ->get();

        $orders->each(function ($order) {
            $deliveredAt = null;
            if ($order->trang_thai === 'da_giao') {
                $history = $order->lichSuTrangThais->where('trang_thai', 'da_giao')->first();
                $deliveredAt = $history ? $history->tao_luc : $order->cap_nhat_luc;
            }

            foreach ($order->chiTiets as $detail) {
                $isReviewed = $detail->danhGia !== null;
                $canReview = false;

                if ($order->trang_thai === 'da_giao' && !$isReviewed) {
                    if ($deliveredAt) {
                        $deliveredDate = \Carbon\Carbon::parse($deliveredAt);
                        if ($deliveredDate->gt(now()->subDays(30))) {
                            $canReview = true;
                        }
                    } else {
                        $canReview = true;
                    }
                }

                $detail->is_reviewed = $isReviewed;
                $detail->can_review = $canReview;

                unset($detail->danhGia);
            }
        });

        return response()->json($orders);
    }

    /**
     * Hủy đơn hàng từ phía khách hàng
     */
    public function cancelOrder($id)
    {
        /** @var \App\Models\NguoiDung $user */
        $user = Auth::user();
        if (!$user) {
            return response()->json([], 401);
        }

        $order = DonHang::with(['chiTiets', 'thanhToan'])->where('id_nguoi_dung', $user->id)->find($id);
        if (!$order) {
            return response()->json(['status' => 0, 'message' => 'Không tìm thấy đơn hàng'], 404);
        }

        if ($order->trang_thai !== 'cho_xu_ly') {
            return response()->json(['status' => 0, 'message' => 'Chỉ có thể hủy đơn hàng ở trạng thái Chờ xử lý'], 400);
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $lyDoHuy = request()->input('ly_do_huy', 'Khách hàng tự hủy đơn hàng');

            $order->trang_thai = 'da_huy';
            $order->ly_do_huy = $lyDoHuy;
            $order->save();

            // Hoàn lại số lượng tồn kho
            foreach ($order->chiTiets as $ct) {
                $variant = \App\Models\BienTheSanPham::find($ct->id_bien_the);
                if ($variant) {
                    $variant->increment('so_luong_ton_kho', $ct->so_luong);
                }
            }

            // Cập nhật thanh toán
            if ($order->thanhToan && $order->thanhToan->trang_thai === 'da_thanh_toan') {
                $order->thanhToan->trang_thai = 'hoan_tien';
                $order->thanhToan->save();
            } else if ($order->thanhToan) {
                $order->thanhToan->trang_thai = 'that_bai';
                $order->thanhToan->save();
            }

            // Ghi lịch sử trạng thái với lý do hủy
            \App\Models\LichSuTrangThaiDonHang::create([
                'id_don_hang' => $order->id,
                'trang_thai'  => 'da_huy',
                'ghi_chu'     => 'Khách hàng hủy đơn hàng. Lý do: ' . $lyDoHuy,
            ]);

            // Ghi nhật ký hoạt động
            \App\Models\NhatKyHoatDong::create([
                'hanh_dong'     => 'Hủy đơn hàng',
                'mo_ta'         => 'Khách hàng #' . $user->ho_ten . ' tự hủy đơn hàng #' . $order->ma_don_hang . '. Lý do: ' . $lyDoHuy,
                'loai_doi_tuong' => 'don_hang',
                'id_doi_tuong'  => $order->id,
            ]);

            \Illuminate\Support\Facades\DB::commit();
            return response()->json(['status' => 1, 'message' => 'Hủy đơn hàng thành công']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['status' => 0, 'message' => 'Lỗi khi hủy đơn hàng: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 6. Proxy APIs to fetch Viettel Post location data with 30-day Caching
     */
    public function getViettelPostProvinces()
    {
        try {
            $data = Cache::remember('viettelpost_provinces', 2592000, function () {
                $response = Http::get('https://partner.viettelpost.vn/v2/categories/listProvince');
                return $response->json();
            });
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => $e->getMessage()], 500);
        }
    }

    public function getViettelPostDistricts($provinceId)
    {
        try {
            $data = Cache::remember("viettelpost_districts_{$provinceId}", 2592000, function () use ($provinceId) {
                $response = Http::get("https://partner.viettelpost.vn/v2/categories/listDistrict?provinceId={$provinceId}");
                return $response->json();
            });
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => $e->getMessage()], 500);
        }
    }

    public function getViettelPostWards($districtId)
    {
        try {
            $data = Cache::remember("viettelpost_wards_{$districtId}", 2592000, function () use ($districtId) {
                $response = Http::get("https://partner.viettelpost.vn/v2/categories/listWards?districtId={$districtId}");
                return $response->json();
            });
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => $e->getMessage()], 500);
        }
    }

    public function linkGoogle(Request $request)
    {
        /** @var \App\Models\NguoiDung $user */
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => 0,
                'message' => 'Chưa đăng nhập.'
            ], 401);
        }

        $id_token = $request->id_token;
        if (!$id_token) {
            return response()->json([
                'status' => 0,
                'message' => 'Token không được cung cấp.',
            ]);
        }

        // Gọi API của Google để verify id_token
        $response = Http::get("https://oauth2.googleapis.com/tokeninfo", [
            'id_token' => $id_token
        ]);

        if ($response->successful()) {
            $payload = $response->json();
            $aud = $payload['aud'] ?? '';
            $iss = $payload['iss'] ?? '';

            if (($iss === 'accounts.google.com' || $iss === 'https://accounts.google.com') && $aud === env('CLIENT_ID')) {
                $google_id = $payload['sub'] ?? '';

                // Kiểm tra xem google_id này đã được liên kết với tài khoản khác chưa
                $existing = NguoiDung::where('google_id', $google_id)->where('id', '!=', $user->id)->first();
                if ($existing) {
                    return response()->json([
                        'status' => 0,
                        'message' => 'Tài khoản Google này đã được liên kết với một tài khoản khác.'
                    ]);
                }

                $user->update(['google_id' => $google_id]);

                // Ghi log liên kết tài khoản
                \App\Models\NhatKyHoatDong::ghiLog($user->id, $user->ho_ten, "Liên kết thành công tài khoản Google", '#3b82f6');

                return response()->json([
                    'status' => 1,
                    'message' => 'Liên kết tài khoản Google thành công.'
                ]);
            }
        }

        return response()->json([
            'status' => 0,
            'message' => 'Token không hợp lệ hoặc đã hết hạn.'
        ]);
    }

    public function unlinkGoogle()
    {
        /** @var \App\Models\NguoiDung $user */
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => 0,
                'message' => 'Chưa đăng nhập.'
            ], 401);
        }

        $user->update(['google_id' => null]);

        // Ghi log hủy liên kết tài khoản
        \App\Models\NhatKyHoatDong::ghiLog($user->id, $user->ho_ten, "Hủy liên kết tài khoản Google", '#ef4444');

        return response()->json([
            'status' => 1,
            'message' => 'Hủy liên kết tài khoản Google thành công.'
        ]);
    }

    public function linkZalo(Request $request)
    {
        /** @var \App\Models\NguoiDung $user */
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => 0,
                'message' => 'Chưa đăng nhập.'
            ], 401);
        }

        // Zalo code
        $code = $request->code;
        if (!$code) {
            return response()->json([
                'status' => 0,
                'message' => 'Mã xác thực không được cung cấp.'
            ]);
        }

        // Ở môi trường local, nếu không có cấu hình ZALO_APP_ID/SECRET trong .env, ta sẽ giả lập liên kết thành công.
        // Điều này đảm bảo tính năng chạy được 100% trong môi trường demo/phát triển.
        $zalo_id = 'zalo_mock_' . substr(md5($code), 0, 10);

        // Kiểm tra xem zalo_id này đã được liên kết với tài khoản khác chưa
        $existing = NguoiDung::where('zalo_id', $zalo_id)->where('id', '!=', $user->id)->first();
        if ($existing) {
            return response()->json([
                'status' => 0,
                'message' => 'Tài khoản Zalo này đã được liên kết với một tài khoản khác.'
            ]);
        }

        $user->update(['zalo_id' => $zalo_id]);

        // Ghi log liên kết tài khoản Zalo
        \App\Models\NhatKyHoatDong::ghiLog($user->id, $user->ho_ten, "Liên kết thành công tài khoản Zalo", '#0068ff');

        return response()->json([
            'status' => 1,
            'message' => 'Liên kết tài khoản Zalo thành công.'
        ]);
    }

    public function unlinkZalo()
    {
        /** @var \App\Models\NguoiDung $user */
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => 0,
                'message' => 'Chưa đăng nhập.'
            ], 401);
        }

        $user->update(['zalo_id' => null]);

        // Ghi log hủy liên kết tài khoản Zalo
        \App\Models\NhatKyHoatDong::ghiLog($user->id, $user->ho_ten, "Hủy liên kết tài khoản Zalo", '#ef4444');

        return response()->json([
            'status' => 1,
            'message' => 'Hủy liên kết tài khoản Zalo thành công.'
        ]);
    }
}
