<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DonHang;
use App\Models\SanPham;
use App\Models\NguoiDung;
use App\Models\ThongBao;
use App\Models\ThuLienHe;
use Illuminate\Http\Request;

class StaffDashboardController extends Controller
{
    /**
     * Global search for staff/admin across products, orders, and customers
     */
    public function globalSearch(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        
        if ($q === '') {
            return response()->json([
                'products' => [],
                'orders' => [],
                'customers' => []
            ]);
        }

        // 1. Search Products
        $products = SanPham::with(['hinhAnhs', 'danhMuc'])
            ->where(function($query) use ($q) {
                $query->where('ten_san_pham', 'like', "%{$q}%")
                      ->orWhere('mo_ta', 'like', "%{$q}%")
                      ->orWhere('id', $q);
            })
            ->limit(5)
            ->get()
            ->map(function($product) {
                return [
                    'id' => $product->id,
                    'title' => $product->ten_san_pham,
                    'subtitle' => $product->danhMuc->ten_danh_muc ?? 'Không có danh mục',
                    'image' => $product->hinhAnhs->where('la_anh_dai_dien', true)->first()->duong_dan_anh 
                        ?? ($product->hinhAnhs->first()->duong_dan_anh ?? null),
                    'link' => '/nhan-vien/products',
                ];
            });

        // 2. Search Orders
        $orders = DonHang::query()
            ->where(function($query) use ($q) {
                $query->where('ma_don_hang', 'like', "%{$q}%")
                      ->orWhere('dia_chi_giao_hang->ten', 'like', "%{$q}%")
                      ->orWhere('dia_chi_giao_hang->so_dien_thoai', 'like', "%{$q}%");
            })
            ->limit(5)
            ->get()
            ->map(function($order) {
                $shipping = $order->dia_chi_giao_hang;
                $customerName = is_array($shipping) ? ($shipping['ten'] ?? 'Khách') : ($shipping->ten ?? 'Khách');
                return [
                    'id' => $order->id,
                    'title' => $order->ma_don_hang,
                    'subtitle' => "Khách hàng: {$customerName} - " . number_format($order->tong_thanh_toan) . "đ",
                    'image' => null,
                    'link' => '/nhan-vien/orders',
                ];
            });

        // 3. Search Customers (users with vai_tro = 3)
        $customers = NguoiDung::query()
            ->where('vai_tro', 3)
            ->where(function($query) use ($q) {
                $query->where('ho_ten', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
            })
            ->limit(5)
            ->get()
            ->map(function($customer) {
                return [
                    'id' => $customer->id,
                    'title' => $customer->ho_ten,
                    'subtitle' => $customer->email,
                    'image' => $customer->anh_dai_dien,
                    'link' => '/nhan-vien/customers',
                ];
            });

        return response()->json([
            'products' => $products,
            'orders' => $orders,
            'customers' => $customers,
        ]);
    }

    /**
     * Get recent notifications
     */
    public function getNotifications(Request $request)
    {
        $notifications = ThongBao::orderBy('tao_luc', 'desc')
            ->limit(15)
            ->get();

        $unreadCount = ThongBao::where('da_doc', false)->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark a single notification as read
     */
    public function readNotification($id)
    {
        $notification = ThongBao::findOrFail($id);
        $notification->update(['da_doc' => true]);

        return response()->json([
            'status' => true,
            'message' => 'Đã đánh dấu đọc thông báo.',
            'unread_count' => ThongBao::where('da_doc', false)->count(),
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function readAllNotifications()
    {
        ThongBao::where('da_doc', false)->update(['da_doc' => true]);

        return response()->json([
            'status' => true,
            'message' => 'Đã đánh dấu đọc tất cả thông báo.',
            'unread_count' => 0,
        ]);
    }

    /**
     * Get recent contact letters/messages
     */
    public function getContacts(Request $request)
    {
        $contacts = ThuLienHe::orderBy('tao_luc', 'desc')
            ->paginate($request->query('limit', 15));

        $unreadCount = ThuLienHe::where('trang_thai', 0)->count();

        return response()->json([
            'contacts' => $contacts,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark a contact letter as read
     */
    public function readContact($id)
    {
        $contact = ThuLienHe::findOrFail($id);
        if ($contact->trang_thai === 0) {
            $contact->update(['trang_thai' => 1]); // Đã đọc
        }

        return response()->json([
            'status' => true,
            'message' => 'Thư liên hệ đã đánh dấu đã đọc.',
            'unread_count' => ThuLienHe::where('trang_thai', 0)->count(),
        ]);
    }

    /**
     * Reply to a contact letter
     */
    public function replyContact(Request $request, $id)
    {
        $request->validate([
            'phan_hoi' => 'required|string',
        ], [
            'phan_hoi.required' => 'Nội dung phản hồi không được để trống.',
        ]);

        $contact = ThuLienHe::findOrFail($id);
        $contact->update([
            'phan_hoi' => $request->phan_hoi,
            'trang_thai' => 2, // Đã phản hồi
        ]);

        // Ghi log hoạt động
        \App\Models\NhatKyHoatDong::ghiLog(
            $request->user()->id,
            $request->user()->ho_ten,
            "Phản hồi thư liên hệ của khách hàng: {$contact->ho_ten} (Email: {$contact->email})",
            '#8b5cf6'
        );

        return response()->json([
            'status' => true,
            'message' => 'Gửi phản hồi thư liên hệ thành công.',
            'contact' => $contact,
        ]);
    }
}
