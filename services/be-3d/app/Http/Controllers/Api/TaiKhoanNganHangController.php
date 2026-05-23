<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TaiKhoanNganHang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaiKhoanNganHangController extends Controller
{
    public function index()
    {
        $accounts = TaiKhoanNganHang::orderBy('created_at', 'desc')->get();
        return response()->json($accounts);
    }

    public function store(Request $request)
    {
        $request->validate([
            'bank_id' => 'required|string|max:50',
            'bank_account_no' => 'required|string|max:50|unique:tai_khoan_ngan_hang,bank_account_no',
            'bank_account_name' => 'required|string|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        return DB::transaction(function () use ($request) {
            $isActive = $request->boolean('is_active', false);

            // If there are no accounts yet, make this one active
            if (TaiKhoanNganHang::count() === 0) {
                $isActive = true;
            }

            if ($isActive) {
                // Deactivate all other accounts
                TaiKhoanNganHang::query()->update(['is_active' => false]);
            }

            $account = TaiKhoanNganHang::create([
                'bank_id' => $request->bank_id,
                'bank_account_no' => $request->bank_account_no,
                'bank_account_name' => $request->bank_account_name,
                'is_active' => $isActive,
            ]);

            return response()->json([
                'message' => 'Thêm tài khoản ngân hàng thành công',
                'account' => $account,
            ], 201);
        });
    }

    public function activate($id)
    {
        $account = TaiKhoanNganHang::findOrFail($id);

        return DB::transaction(function () use ($account) {
            // Deactivate all other accounts
            TaiKhoanNganHang::query()->update(['is_active' => false]);

            // Activate this account
            $account->update(['is_active' => true]);

            return response()->json([
                'message' => 'Kích hoạt tài khoản ngân hàng thành công',
                'account' => $account,
            ]);
        });
    }

    public function destroy($id)
    {
        $account = TaiKhoanNganHang::findOrFail($id);

        return DB::transaction(function () use ($account) {
            $wasActive = $account->is_active;
            $account->delete();

            // If the deleted account was active, activate another one if available
            if ($wasActive) {
                $nextAccount = TaiKhoanNganHang::first();
                if ($nextAccount) {
                    $nextAccount->update(['is_active' => true]);
                }
            }

            return response()->json([
                'message' => 'Xóa tài khoản ngân hàng thành công',
            ]);
        });
    }
}
