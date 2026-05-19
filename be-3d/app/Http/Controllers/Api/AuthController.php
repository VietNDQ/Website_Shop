<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

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
}
