<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\ResetPasswordMail;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function checkToken()
    {
        $userLogin = Auth::guard('sanctum')->user();
        if ($userLogin) {
            if (!$userLogin->dang_hoat_dong) {
                return response()->json([
                    'status'    => false,
                    'message'   => 'Tài khoản của bạn đã bị khóa.',
                ], 403);
            }

            return response()->json([
                'status'    => true,
                'id'        => $userLogin->id,
                'ho_ten'    => $userLogin->ho_ten,
                'email'     => $userLogin->email,
                'vai_tro'   => $userLogin->vai_tro,
            ]);
        } else {
            return response()->json([
                'status'    => false,
                'message'   => 'Token không hợp lệ'
            ], 401);
        }
    }
    
    public function register(Request $request)
    {
        $request->validate([
            'ho_ten' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:nguoi_dung',
            'mat_khau' => 'required|string|min:8',
        ]);

        $user = NguoiDung::create([
            'ho_ten' => $request->ho_ten,
            'email' => $request->email,
            'mat_khau' => Hash::make($request->mat_khau),
            'vai_tro' => 3,
            'dang_hoat_dong' => true,
        ]);

        return response()->json([
            'message' => 'Đăng ký thành công',
            'user' => $user,
            'token' => $user->createToken('auth_token')->plainTextToken,
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'mat_khau' => 'required',
        ]);

        $user = NguoiDung::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->mat_khau, $user->mat_khau)) {
            throw ValidationException::withMessages([
                'email' => ['Thông tin đăng nhập không chính xác.'],
            ]);
        }

        if (!$user->dang_hoat_dong) {
            return response()->json(['message' => 'Tài khoản của bạn đã bị khóa.'], 403);
        }

        $user->update(['dang_nhap_lan_cuoi_luc' => now()]);

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('auth_token')->plainTextToken,
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Đăng xuất thành công']);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:nguoi_dung,email',
        ], [
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Định dạng email không hợp lệ.',
            'email.exists' => 'Email này không tồn tại trong hệ thống.',
        ]);

        $email = $request->email;
        $user = NguoiDung::where('email', $email)->first();

        // Generate token
        $token = Str::random(60);

        // Save token to password_reset_tokens
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => $token,
                'created_at' => now()
            ]
        );

        // Create reset URL
        $resetUrl = "http://localhost:5173/reset-password?token=" . $token . "&email=" . urlencode($email);

        // Send email
        Mail::to($email)->send(new ResetPasswordMail($user->ho_ten, $resetUrl));

        return response()->json([
            'status' => true,
            'message' => 'Yêu cầu khôi phục mật khẩu đã được gửi đến email của bạn.'
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:nguoi_dung,email',
            'token' => 'required|string',
            'mat_khau' => 'required|string|min:8|confirmed',
        ], [
            'email.required' => 'Vui lòng cung cấp email.',
            'email.email' => 'Định dạng email không hợp lệ.',
            'email.exists' => 'Email không tồn tại trong hệ thống.',
            'token.required' => 'Thiếu token xác nhận.',
            'mat_khau.required' => 'Vui lòng nhập mật khẩu mới.',
            'mat_khau.min' => 'Mật khẩu phải chứa ít nhất 8 ký tự.',
            'mat_khau.confirmed' => 'Mật khẩu xác nhận không trùng khớp.',
        ]);

        $email = $request->email;
        $token = $request->token;

        // Check if token exists and is valid
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('token', $token)
            ->first();

        if (!$resetRecord) {
            return response()->json([
                'status' => false,
                'message' => 'Yêu cầu khôi phục mật khẩu không hợp lệ hoặc token đã hết hạn.'
            ], 400);
        }

        // Check token expiration (e.g. 60 minutes)
        $createdAt = Carbon::parse($resetRecord->created_at);
        if ($createdAt->addMinutes(60)->isPast()) {
            // Delete expired token
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return response()->json([
                'status' => false,
                'message' => 'Mã khôi phục đã hết hạn. Vui lòng yêu cầu mã mới.'
            ], 400);
        }

        // Update password in nguoi_dung
        $user = NguoiDung::where('email', $email)->first();
        $user->mat_khau = Hash::make($request->mat_khau);
        $user->save();

        // Delete the token
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Mật khẩu của bạn đã được cập nhật thành công!'
        ]);
    }
}
