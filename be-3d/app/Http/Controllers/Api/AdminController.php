<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use App\Models\DonHang;
use App\Models\ThanhToan;
use App\Models\BienTheSanPham;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function getUsers()
    {
        return NguoiDung::paginate(20);
    }

    public function updateUserRole(Request $request, $userId)
    {
        $request->validate([
            'vai_tro' => 'required|in:1,2,3',
        ]);
        $user = NguoiDung::findOrFail($userId);
        $user->update(['vai_tro' => $request->vai_tro]);
        return $user;
    }

    public function toggleUserStatus(Request $request, $userId)
    {
        $user = NguoiDung::findOrFail($userId);
        $user->update(['dang_hoat_dong' => !$user->dang_hoat_dong]);
        return $user;
    }

    public function getStatistics()
    {
        $totalRevenue = ThanhToan::where('trang_thai', 'da_thanh_toan')->sum('so_tien');
        $totalOrders = DonHang::count();
        $totalCustomers = NguoiDung::where('vai_tro', 3)->count();
        $lowStockVariants = BienTheSanPham::with('sanPham')
            ->where('so_luong_ton_kho', '<', 10)
            ->get();

        $monthlyRevenue = ThanhToan::where('trang_thai', 'da_thanh_toan')
            ->select(DB::raw('SUM(so_tien) as revenue'), DB::raw("DATE_FORMAT(tao_luc, '%Y-%m') as month"))
            ->groupBy('month')
            ->get();

        return response()->json([
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_orders' => $totalOrders,
                'total_customers' => $totalCustomers,
            ],
            'low_stock' => $lowStockVariants,
            'monthly_revenue' => $monthlyRevenue,
        ]);
    }
}
