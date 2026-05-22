<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DonHang;
use App\Models\NguoiDung;
use App\Models\SanPham;
use App\Models\ThanhToan;
use App\Models\NhatKyHoatDong;
use App\Models\ChiTietDonHang;
use App\Models\BienTheSanPham;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function getDashboardData()
    {
        Carbon::setLocale('vi');
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
                'dang_chuan_bi' => 'preparing',
                'dang_giao' => 'shipping',
                'da_giao' => 'delivered',
                'da_huy' => 'cancelled',
                'hoan_tien' => 'refunded',
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

        $topProducts = $topSelling->map(function($item, $index) use ($maxSold, $colors) {
            $image = null;
            $bienThe = BienTheSanPham::with(['sanPham.hinhAnhs'])->find($item->id_bien_the);
            if ($bienThe) {
                if ($bienThe->hinh_anh) {
                    $image = $bienThe->hinh_anh;
                } else if ($bienThe->sanPham) {
                    $anhDaiDien = $bienThe->sanPham->hinhAnhs->firstWhere('la_anh_dai_dien', true);
                    if ($anhDaiDien) {
                        $image = $anhDaiDien->duong_dan_anh;
                    } else {
                        $firstAnh = $bienThe->sanPham->hinhAnhs->first();
                        if ($firstAnh) {
                            $image = $firstAnh->duong_dan_anh;
                        }
                    }
                }
            }

            return [
                'name' => $item->ten_bien_the_luc_mua,
                'image' => $image,
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

    public function getAnalyticsData(Request $request)
    {
        Carbon::setLocale('vi');
        $period = $request->query('period', 'month');
        $now = Carbon::now();

        // 1. Date ranges calculation
        switch ($period) {
            case 'today':
                $start = $now->copy()->startOfDay();
                $end = $now->copy()->endOfDay();
                $prevStart = $now->copy()->subDay()->startOfDay();
                $prevEnd = $now->copy()->subDay()->endOfDay();
                break;
            case 'week':
                $start = $now->copy()->startOfWeek();
                $end = $now->copy()->endOfWeek();
                $prevStart = $now->copy()->subWeek()->startOfWeek();
                $prevEnd = $now->copy()->subWeek()->endOfWeek();
                break;
            case 'year':
                $start = $now->copy()->startOfYear();
                $end = $now->copy()->endOfYear();
                $prevStart = $now->copy()->subYear()->startOfYear();
                $prevEnd = $now->copy()->subYear()->endOfYear();
                break;
            case 'month':
            default:
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                $prevStart = $now->copy()->subMonth()->startOfMonth();
                $prevEnd = $now->copy()->subMonth()->endOfMonth();
                break;
        }

        // 2. KPI Metrics & Trends calculation
        // Doanh thu
        $currRevenue = ThanhToan::where('trang_thai', 'da_thanh_toan')
            ->whereBetween('tao_luc', [$start, $end])
            ->sum('so_tien');
        $prevRevenue = ThanhToan::where('trang_thai', 'da_thanh_toan')
            ->whereBetween('tao_luc', [$prevStart, $prevEnd])
            ->sum('so_tien');
        $revenueTrend = $this->calculateTrend($currRevenue, $prevRevenue);

        // Số đơn hàng
        $currOrders = DonHang::whereBetween('tao_luc', [$start, $end])->count();
        $prevOrders = DonHang::whereBetween('tao_luc', [$prevStart, $prevEnd])->count();
        $ordersTrend = $this->calculateTrend($currOrders, $prevOrders);

        // Tỷ lệ hủy đơn (cancelled or refunded vs total)
        $currTotalOrders = $currOrders;
        $currCancelledOrders = DonHang::whereIn('trang_thai', ['da_huy', 'hoan_tien'])
            ->whereBetween('tao_luc', [$start, $end])
            ->count();
        $currRate = $currTotalOrders > 0 ? ($currCancelledOrders / $currTotalOrders) * 100 : 0;

        $prevTotalOrders = $prevOrders;
        $prevCancelledOrders = DonHang::whereIn('trang_thai', ['da_huy', 'hoan_tien'])
            ->whereBetween('tao_luc', [$prevStart, $prevEnd])
            ->count();
        $prevRate = $prevTotalOrders > 0 ? ($prevCancelledOrders / $prevTotalOrders) * 100 : 0;

        $rateDiff = $currRate - $prevRate;
        $rateTrendVal = ($rateDiff >= 0 ? '+' : '') . number_format($rateDiff, 1) . '%';
        $rateTrendUp = ($rateDiff <= 0); // Tiêu chí là tỷ lệ hủy đơn giảm là tốt (trendUp = true)

        // Khách hàng mới (vai_tro = 3)
        $currCustomers = NguoiDung::where('vai_tro', 3)
            ->whereBetween('tao_luc', [$start, $end])
            ->count();
        $prevCustomers = NguoiDung::where('vai_tro', 3)
            ->whereBetween('tao_luc', [$prevStart, $prevEnd])
            ->count();
        $customersTrend = $this->calculateTrend($currCustomers, $prevCustomers);

        $kpis = [
            [
                'label' => 'Doanh thu',
                'val' => $this->formatCurrency($currRevenue),
                'trend' => $revenueTrend['value'],
                'trendUp' => $revenueTrend['isUp'],
                'color' => '#D70018',
                'iconBg' => 'rgba(215,0,24,0.1)',
                'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#D70018" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>'
            ],
            [
                'label' => 'Số đơn hàng',
                'val' => number_format($currOrders),
                'trend' => $ordersTrend['value'],
                'trendUp' => $ordersTrend['isUp'],
                'color' => '#6366f1',
                'iconBg' => 'rgba(99,102,241,0.1)',
                'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg>'
            ],
            [
                'label' => 'Tỷ lệ hủy đơn',
                'val' => number_format($currRate, 1) . '%',
                'trend' => $rateTrendVal,
                'trendUp' => $rateTrendUp,
                'color' => '#22c55e',
                'iconBg' => 'rgba(34,197,94,0.1)',
                'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>'
            ],
            [
                'label' => 'Khách hàng mới',
                'val' => number_format($currCustomers),
                'trend' => $customersTrend['value'],
                'trendUp' => $customersTrend['isUp'],
                'color' => '#f59e0b',
                'iconBg' => 'rgba(245,158,11,0.1)',
                'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="23" y1="11" x2="17" y2="11"/><line x1="20" y1="8" x2="20" y2="14"/></svg>'
            ],
        ];

        // 3. Bar Chart Data (Revenue Over Time)
        $barData = [];
        if ($period === 'today') {
            // Group by 4 hours
            $hourlyRevenue = ThanhToan::where('trang_thai', 'da_thanh_toan')
                ->whereBetween('tao_luc', [$start, $end])
                ->select(DB::raw('HOUR(tao_luc) as hour_num'), DB::raw('SUM(so_tien) as total'))
                ->groupBy('hour_num')
                ->pluck('total', 'hour_num')
                ->toArray();

            $slots = [
                ['label' => '0h-4h', 'start' => 0, 'end' => 3],
                ['label' => '4h-8h', 'start' => 4, 'end' => 7],
                ['label' => '8h-12h', 'start' => 8, 'end' => 11],
                ['label' => '12h-16h', 'start' => 12, 'end' => 15],
                ['label' => '16h-20h', 'start' => 16, 'end' => 19],
                ['label' => '20h-24h', 'start' => 20, 'end' => 23],
            ];

            $currentHour = $now->hour;
            $maxVal = 0;
            foreach ($slots as $slot) {
                $sum = 0;
                for ($h = $slot['start']; $h <= $slot['end']; $h++) {
                    $sum += $hourlyRevenue[$h] ?? 0;
                }
                if ($sum > $maxVal) {
                    $maxVal = $sum;
                }
                $isCurrent = ($currentHour >= $slot['start'] && $currentHour <= $slot['end']);
                $barData[] = [
                    'label' => $slot['label'],
                    'value_num' => $sum,
                    'isCurrent' => $isCurrent,
                ];
            }

            foreach ($barData as &$bar) {
                $bar['val'] = $this->formatCurrency($bar['value_num']);
                $bar['pct'] = $maxVal > 0 ? round(($bar['value_num'] / $maxVal) * 100) : 0;
                $bar['color'] = $bar['isCurrent'] ? '#D70018' : '#e2e8f0';
            }
        } elseif ($period === 'week') {
            // Group by weekday (Monday-Sunday)
            $weekdayRevenue = ThanhToan::where('trang_thai', 'da_thanh_toan')
                ->whereBetween('tao_luc', [$start, $end])
                ->select(DB::raw('WEEKDAY(tao_luc) as weekday_index'), DB::raw('SUM(so_tien) as total'))
                ->groupBy('weekday_index')
                ->pluck('total', 'weekday_index')
                ->toArray();

            $weekdayLabels = ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'];
            // Carbon weekday index: Monday is 0, Sunday is 6. Matches MySQL WEEKDAY()
            $currentWeekdayIndex = $now->dayOfWeekIso - 1; // Carbon dayOfWeekIso is 1 (Mon) - 7 (Sun)
            
            $maxVal = 0;
            for ($i = 0; $i < 7; $i++) {
                $val = $weekdayRevenue[$i] ?? 0;
                if ($val > $maxVal) {
                    $maxVal = $val;
                }
                $barData[] = [
                    'label' => $weekdayLabels[$i],
                    'value_num' => $val,
                    'isCurrent' => ($i === $currentWeekdayIndex),
                ];
            }

            foreach ($barData as &$bar) {
                $bar['val'] = $this->formatCurrency($bar['value_num']);
                $bar['pct'] = $maxVal > 0 ? round(($bar['value_num'] / $maxVal) * 100) : 0;
                $bar['color'] = $bar['isCurrent'] ? '#D70018' : '#e2e8f0';
            }
        } elseif ($period === 'year') {
            // Group by month
            $monthlyRevenue = ThanhToan::where('trang_thai', 'da_thanh_toan')
                ->whereBetween('tao_luc', [$start, $end])
                ->select(DB::raw('MONTH(tao_luc) as month_num'), DB::raw('SUM(so_tien) as total'))
                ->groupBy('month_num')
                ->pluck('total', 'month_num')
                ->toArray();

            $currentMonth = $now->month;
            $maxVal = 0;
            for ($m = 1; $m <= 12; $m++) {
                $val = $monthlyRevenue[$m] ?? 0;
                if ($val > $maxVal) {
                    $maxVal = $val;
                }
                $barData[] = [
                    'label' => 'T' . $m,
                    'value_num' => $val,
                    'isCurrent' => ($m === $currentMonth),
                ];
            }

            foreach ($barData as &$bar) {
                $bar['val'] = $this->formatCurrency($bar['value_num']);
                $bar['pct'] = $maxVal > 0 ? round(($bar['value_num'] / $maxVal) * 100) : 0;
                $bar['color'] = $bar['isCurrent'] ? '#D70018' : '#e2e8f0';
            }
        } else {
            // Month period - group by 5-day intervals: days 1-5, 6-10, 11-15, 16-20, 21-25, 26+
            $dailyRevenue = ThanhToan::where('trang_thai', 'da_thanh_toan')
                ->whereBetween('tao_luc', [$start, $end])
                ->select(DB::raw('DAY(tao_luc) as day_num'), DB::raw('SUM(so_tien) as total'))
                ->groupBy('day_num')
                ->pluck('total', 'day_num')
                ->toArray();

            $slots = [
                ['label' => 'N1-5', 'start' => 1, 'end' => 5],
                ['label' => 'N6-10', 'start' => 6, 'end' => 10],
                ['label' => 'N11-15', 'start' => 11, 'end' => 15],
                ['label' => 'N16-20', 'start' => 16, 'end' => 20],
                ['label' => 'N21-25', 'start' => 21, 'end' => 25],
                ['label' => 'N26+', 'start' => 26, 'end' => 31],
            ];

            $currentDay = $now->day;
            $maxVal = 0;
            foreach ($slots as $slot) {
                $sum = 0;
                for ($d = $slot['start']; $d <= $slot['end']; $d++) {
                    $sum += $dailyRevenue[$d] ?? 0;
                }
                if ($sum > $maxVal) {
                    $maxVal = $sum;
                }
                $isCurrent = ($currentDay >= $slot['start'] && $currentDay <= $slot['end']);
                $barData[] = [
                    'label' => $slot['label'],
                    'value_num' => $sum,
                    'isCurrent' => $isCurrent,
                ];
            }

            foreach ($barData as &$bar) {
                $bar['val'] = $this->formatCurrency($bar['value_num']);
                $bar['pct'] = $maxVal > 0 ? round(($bar['value_num'] / $maxVal) * 100) : 0;
                $bar['color'] = $bar['isCurrent'] ? '#D70018' : '#e2e8f0';
            }
        }

        // 4. Order Status Ratio (Donut Chart)
        $statusCounts = DonHang::whereBetween('tao_luc', [$start, $end])
            ->select('trang_thai', DB::raw('COUNT(*) as count'))
            ->groupBy('trang_thai')
            ->pluck('count', 'trang_thai')
            ->toArray();

        $daGiao = $statusCounts['da_giao'] ?? 0;
        $dangGiao = ($statusCounts['dang_giao'] ?? 0) + ($statusCounts['dang_chuan_bi'] ?? 0);
        $choXacNhan = $statusCounts['cho_xu_ly'] ?? 0;
        $daHuy = ($statusCounts['da_huy'] ?? 0) + ($statusCounts['hoan_tien'] ?? 0);
        
        $totalStatusOrders = $daGiao + $dangGiao + $choXacNhan + $daHuy;

        $legendData = [
            [
                'label' => 'Đã giao',
                'color' => '#22c55e',
                'count' => $daGiao,
                'pct' => $totalStatusOrders > 0 ? round(($daGiao / $totalStatusOrders) * 100) : 0
            ],
            [
                'label' => 'Đang giao',
                'color' => '#6366f1',
                'count' => $dangGiao,
                'pct' => $totalStatusOrders > 0 ? round(($dangGiao / $totalStatusOrders) * 100) : 0
            ],
            [
                'label' => 'Chờ xác nhận',
                'color' => '#f59e0b',
                'count' => $choXacNhan,
                'pct' => $totalStatusOrders > 0 ? round(($choXacNhan / $totalStatusOrders) * 100) : 0
            ],
            [
                'label' => 'Đã huỷ',
                'color' => '#D70018',
                'count' => $daHuy,
                'pct' => $totalStatusOrders > 0 ? round(($daHuy / $totalStatusOrders) * 100) : 0
            ],
        ];

        // 5. Top Selling Products
        $topSelling = ChiTietDonHang::whereBetween('tao_luc', [$start, $end])
            ->select('id_bien_the', 'ten_bien_the_luc_mua', DB::raw('SUM(so_luong) as total_sold'), DB::raw('SUM(thanh_tien) as total_revenue'))
            ->groupBy('id_bien_the', 'ten_bien_the_luc_mua')
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();

        $maxSold = $topSelling->max('total_sold') ?: 1;
        $colors = ['#D70018', '#6366f1', '#f59e0b', '#0ea5e9', '#22c55e'];
        $emojis = ['🤖', '🦾', '🐉', '🏎️', '⚓'];

        $topProductsList = $topSelling->map(function($item, $index) use ($maxSold, $colors, $emojis) {
            $image = null;
            $bienThe = BienTheSanPham::with(['sanPham.hinhAnhs'])->find($item->id_bien_the);
            if ($bienThe) {
                if ($bienThe->hinh_anh) {
                    $image = $bienThe->hinh_anh;
                } else if ($bienThe->sanPham) {
                    $anhDaiDien = $bienThe->sanPham->hinhAnhs->firstWhere('la_anh_dai_dien', true);
                    if ($anhDaiDien) {
                        $image = $anhDaiDien->duong_dan_anh;
                    } else {
                        $firstAnh = $bienThe->sanPham->hinhAnhs->first();
                        if ($firstAnh) {
                            $image = $firstAnh->duong_dan_anh;
                        }
                    }
                }
            }

            return [
                'name' => $item->ten_bien_the_luc_mua,
                'image' => $image,
                'emoji' => $emojis[$index % count($emojis)],
                'sold' => (int)$item->total_sold,
                'revenue' => $this->formatCurrency($item->total_revenue),
                'pct' => round(($item->total_sold / $maxSold) * 100),
                'color' => $colors[$index % count($colors)],
            ];
        });

        // 6. Payment Methods
        $payments = ThanhToan::where('trang_thai', 'da_thanh_toan')
            ->whereBetween('tao_luc', [$start, $end])
            ->select('phuong_thuc', DB::raw('SUM(so_tien) as total_amount'))
            ->groupBy('phuong_thuc')
            ->get();

        $totalPaymentAmount = $payments->sum('total_amount') ?: 1;

        $paymentMethodsConfig = [
            'tien_mat' => ['name' => 'COD', 'color' => '#f59e0b', 'bg' => 'rgba(245,158,11,0.1)', 'icon' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>'],
            'cod' => ['name' => 'COD', 'color' => '#f59e0b', 'bg' => 'rgba(245,158,11,0.1)', 'icon' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>'],
            'vnpay' => ['name' => 'VNPAY', 'color' => '#D70018', 'bg' => 'rgba(215,0,24,0.1)', 'icon' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#D70018" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>'],
            'chuyen_khoan' => ['name' => 'Chuyển khoản', 'color' => '#6366f1', 'bg' => 'rgba(99,102,241,0.1)', 'icon' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 014-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 01-4 4H3"/></svg>'],
            'momo' => ['name' => 'Momo', 'color' => '#ec4899', 'bg' => 'rgba(236,72,153,0.1)', 'icon' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ec4899" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>'],
            'the_tin_dung' => ['name' => 'Thẻ tín dụng', 'color' => '#0ea5e9', 'bg' => 'rgba(14,165,233,0.1)', 'icon' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0ea5e9" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>'],
        ];

        $paymentMethodsList = [];
        foreach ($payments as $payment) {
            $key = strtolower($payment->phuong_thuc);
            $cfg = $paymentMethodsConfig[$key] ?? [
                'name' => ucfirst($payment->phuong_thuc),
                'color' => '#94a3b8',
                'bg' => 'rgba(148,163,184,0.1)',
                'icon' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/></svg>'
            ];

            $pct = round(($payment->total_amount / $totalPaymentAmount) * 100);
            $paymentMethodsList[] = [
                'name' => $cfg['name'],
                'pct' => $pct,
                'amount' => $this->formatCurrency($payment->total_amount),
                'color' => $cfg['color'],
                'bg' => $cfg['bg'],
                'icon' => $cfg['icon']
            ];
        }

        // If empty, return placeholder COD/VNPAY/Transfer with 0
        if (empty($paymentMethodsList)) {
            $paymentMethodsList = [
                [
                    'name' => 'COD',
                    'pct' => 0,
                    'amount' => '0 ₫',
                    'color' => '#f59e0b',
                    'bg' => 'rgba(245,158,11,0.1)',
                    'icon' => $paymentMethodsConfig['tien_mat']['icon']
                ],
                [
                    'name' => 'VNPAY',
                    'pct' => 0,
                    'amount' => '0 ₫',
                    'color' => '#D70018',
                    'bg' => 'rgba(215,0,24,0.1)',
                    'icon' => $paymentMethodsConfig['vnpay']['icon']
                ],
                [
                    'name' => 'Chuyển khoản',
                    'pct' => 0,
                    'amount' => '0 ₫',
                    'color' => '#6366f1',
                    'bg' => 'rgba(99,102,241,0.1)',
                    'icon' => $paymentMethodsConfig['chuyen_khoan']['icon']
                ],
            ];
        }

        return response()->json([
            'status' => true,
            'data' => [
                'stats' => $kpis,
                'barData' => $barData,
                'totalStatusOrders' => $totalStatusOrders,
                'legendData' => $legendData,
                'topProducts' => $topProductsList,
                'paymentMethods' => $paymentMethodsList
            ]
        ]);
    }

    private function calculateTrend($current, $previous)
    {
        if ($previous == 0) {
            if ($current == 0) {
                return ['value' => '0%', 'isUp' => true];
            }
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
