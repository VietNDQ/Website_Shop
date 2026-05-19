<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DonHang;
use App\Models\NguoiDung;
use App\Models\SanPham;
use App\Models\ThanhToan;
use App\Models\NhatKyHoatDong;
use App\Models\ChiTietDonHang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function getDashboardData()
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        // 1. Stats
        // Doanh thu tháng này
        $revenueThisMonth = ThanhToan::where('trang_thai', 'da_thanh_toan')
            ->where('tao_luc', '>=', $startOfMonth)
            ->sum('so_tien');
        
        $revenueLastMonth = ThanhToan::where('trang_thai', 'da_thanh_toan')
            ->whereBetween('tao_luc', [$startOfLastMonth, $endOfLastMonth])
            ->sum('so_tien');
            
        $revenueTrend = $this->calculateTrend($revenueThisMonth, $revenueLastMonth);

        // Đơn hàng tháng này
        $ordersThisMonth = DonHang::where('tao_luc', '>=', $startOfMonth)->count();
        $ordersLastMonth = DonHang::whereBetween('tao_luc', [$startOfLastMonth, $endOfLastMonth])->count();
        $ordersTrend = $this->calculateTrend($ordersThisMonth, $ordersLastMonth);

        // Tổng Sản phẩm
        $totalProducts = SanPham::count();
        $productsThisMonth = SanPham::where('tao_luc', '>=', $startOfMonth)->count();
        $productsTrend = [
            'value' => '+' . $productsThisMonth . ' mới',
            'isUp' => true
        ];

        // Tổng khách hàng
        $totalCustomers = NguoiDung::where('vai_tro', 3)->count();
        $customersThisMonth = NguoiDung::where('vai_tro', 3)->where('tao_luc', '>=', $startOfMonth)->count();
        $customersLastMonth = NguoiDung::where('vai_tro', 3)->whereBetween('tao_luc', [$startOfLastMonth, $endOfLastMonth])->count();
        $customersTrend = $this->calculateTrend($customersThisMonth, $customersLastMonth);

        $stats = [
            [
                'label' => 'Doanh thu tháng',
                'value' => $this->formatCurrency($revenueThisMonth),
                'trend' => $revenueTrend['value'],
                'trendUp' => $revenueTrend['isUp'],
                'type' => 'revenue',
                'bg' => 'linear-gradient(135deg,#D70018,#ff4d5e)',
                'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>',
            ],
            [
                'label' => 'Đơn hàng',
                'value' => number_format($ordersThisMonth),
                'trend' => $ordersTrend['value'],
                'trendUp' => $ordersTrend['isUp'],
                'type' => 'orders',
                'bg' => 'linear-gradient(135deg,#6366f1,#8b5cf6)',
                'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>',
            ],
            [
                'label' => 'Sản phẩm',
                'value' => number_format($totalProducts),
                'trend' => $productsTrend['value'],
                'trendUp' => $productsTrend['isUp'],
                'type' => 'products',
                'bg' => 'linear-gradient(135deg,#0ea5e9,#38bdf8)',
                'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>',
            ],
            [
                'label' => 'Khách hàng',
                'value' => number_format($totalCustomers),
                'trend' => $customersTrend['value'],
                'trendUp' => $customersTrend['isUp'],
                'type' => 'customers',
                'bg' => 'linear-gradient(135deg,#f59e0b,#fbbf24)',
                'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>',
            ],
        ];

        // 2. Recent Orders
        $orders = DonHang::with(['nguoiDung', 'chiTiets'])
            ->orderByDesc('tao_luc')
            ->take(5)
            ->get();

        $recentOrders = $orders->map(function($order) {
            $productName = 'Chưa có sản phẩm';
            if ($order->chiTiets->count() > 0) {
                $productName = $order->chiTiets->first()->ten_bien_the_luc_mua;
                if ($order->chiTiets->count() > 1) {
                    $productName .= ' (+' . ($order->chiTiets->count() - 1) . ' sp khác)';
                }
            }

            // Map status
            $statusMap = [
                'cho_xu_ly' => 'pending',
                'dang_chuan_bi' => 'pending',
                'dang_giao' => 'shipping',
                'da_giao' => 'delivered',
                'da_huy' => 'cancelled',
                'hoan_tien' => 'cancelled',
            ];

            return [
                'id' => '#' . $order->ma_don_hang,
                'customer' => $order->nguoiDung->ho_ten ?? 'Khách lẻ',
                'product' => $productName,
                'price' => number_format($order->tong_thanh_toan, 0, ',', '.') . ' ₫',
                'status' => $statusMap[$order->trang_thai] ?? 'pending',
            ];
        });

        // 3. Top Products
        $topSelling = ChiTietDonHang::select('id_bien_the', 'ten_bien_the_luc_mua', DB::raw('SUM(so_luong) as total_sold'))
            ->groupBy('id_bien_the', 'ten_bien_the_luc_mua')
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();

        $maxSold = $topSelling->max('total_sold') ?: 1;
        $colors = ['#D70018', '#6366f1', '#f59e0b', '#0ea5e9', '#22c55e'];
        $emojis = ['🤖', '🦾', '🐉', '🏎️', '⚓'];

        $topProducts = $topSelling->map(function($item, $index) use ($maxSold, $colors, $emojis) {
            return [
                'name' => $item->ten_bien_the_luc_mua,
                'emoji' => $emojis[$index % count($emojis)],
                'sold' => (int)$item->total_sold,
                'pct' => round(($item->total_sold / $maxSold) * 100),
                'color' => $colors[$index % count($colors)],
            ];
        });

        // 4. Activities
        $activities = [];
        if (Schema::hasTable('nhat_ky_hoat_dong')) {
            $logs = NhatKyHoatDong::orderByDesc('tao_luc')->take(6)->get();
            $activities = $logs->map(function($log) {
                return [
                    'id' => $log->id,
                    'text' => $log->hanh_dong,
                    'time' => Carbon::parse($log->tao_luc)->diffForHumans(),
                    'color' => $log->mau_sac ?? '#94a3b8',
                ];
            });
        }

        return response()->json([
            'status' => true,
            'data' => [
                'stats' => $stats,
                'recentOrders' => $recentOrders,
                'topProducts' => $topProducts,
                'activities' => $activities,
            ]
        ]);
    }

    private function calculateTrend($current, $previous)
    {
        if ($previous == 0) {
            return ['value' => '+100%', 'isUp' => true];
        }
        
        $percent = (($current - $previous) / $previous) * 100;
        $formatted = number_format(abs($percent), 1) . '%';
        
        if ($percent > 0) {
            return ['value' => '+' . $formatted, 'isUp' => true];
        } else if ($percent < 0) {
            return ['value' => '-' . $formatted, 'isUp' => false];
        }
        
        return ['value' => '0%', 'isUp' => true];
    }

    private function formatCurrency($value)
    {
        if ($value >= 1000000000) {
            return number_format($value / 1000000000, 1) . 'T ₫';
        }
        if ($value >= 1000000) {
            return number_format($value / 1000000, 1) . 'Tr ₫';
        }
        if ($value >= 1000) {
            return number_format($value / 1000, 1) . 'K ₫';
        }
        return number_format($value, 0, ',', '.') . ' ₫';
    }
}
