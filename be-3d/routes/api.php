<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\ManagerController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\SanPhamController;
use App\Http\Controllers\Api\NguoiDungController;
use App\Http\Controllers\Api\ThongTinCuaHangController;
use App\Http\Controllers\Api\KhuyenMaiController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\YeuThichDaXemController;
use Illuminate\Support\Facades\Route;

// Public Routes with Rate Limiting
Route::middleware('throttle:api')->group(function () {
    Route::get('/check-token', [AuthController::class, 'checkToken']);
    Route::get('/danh-muc', [CustomerController::class, 'getCategories']);
    Route::get('/san-pham', [CustomerController::class, 'getProducts']);
    Route::get('/tim-kiem', [CustomerController::class, 'search']);
    Route::get('/tim-kiem/goi-y', [CustomerController::class, 'suggest']);
    Route::post('/tim-kiem/track', [CustomerController::class, 'trackSearchClick']);
    Route::get('/san-pham/{id}', [CustomerController::class, 'getProductDetail']);
    Route::get('/san-pham/{id}/danh-gia', [ReviewController::class, 'getProductReviews']);
    Route::get('/thong-tin-cua-hang', [ThongTinCuaHangController::class, 'getSettings']);
    Route::get('/ma-giam-gia/kiem-tra/{code}', [CustomerController::class, 'validateVoucher']);
    Route::post('/lien-he', [\App\Http\Controllers\Api\PublicContactController::class, 'sendContact']);
    Route::get('/blog', [\App\Http\Controllers\Api\BlogController::class, 'index']);
    Route::get('/blog/{id_or_slug}', [\App\Http\Controllers\Api\BlogController::class, 'show']);

    // Viettel Post Location Proxies
    Route::get('/viettelpost/provinces', [NguoiDungController::class, 'getViettelPostProvinces']);
    Route::get('/viettelpost/districts/{provinceId}', [NguoiDungController::class, 'getViettelPostDistricts']);
    Route::get('/viettelpost/wards/{districtId}', [NguoiDungController::class, 'getViettelPostWards']);
});

// Sensitive Auth Routes (Strict Rate Limiting)
Route::middleware('throttle:auth_limiter')->group(function () {
    Route::post('/dang-ky', [AuthController::class, 'register']);
    Route::post('/dang-nhap', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Webhook (Public - No throttle or separate setup to avoid payment drops)
Route::post('/webhooks/thanh-toan', [WebhookController::class, 'handlePaymentWebhook']);

// Authenticated Routes
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {

    //-----------------------------------------------Check Login---------------------------------------
    Route::post('/dang-xuat', [AuthController::class, 'logout']);
    Route::get('/thong-tin-ca-nhan', [AuthController::class, 'me']);

    // API Profile cá nhân (Super Admin, Quản lý, Nhân viên, Khách hàng)
    Route::get('/thong-tin-ca-nhan/profile', [NguoiDungController::class, 'getProfile']);
    Route::post('/thong-tin-ca-nhan/update', [NguoiDungController::class, 'updateProfile']);
    Route::post('/thong-tin-ca-nhan/update-password', [NguoiDungController::class, 'updatePassword']);
    Route::post('/thong-tin-ca-nhan/link-google', [NguoiDungController::class, 'linkGoogle']);
    Route::post('/thong-tin-ca-nhan/unlink-google', [NguoiDungController::class, 'unlinkGoogle']);
    Route::post('/thong-tin-ca-nhan/link-zalo', [NguoiDungController::class, 'linkZalo']);
    Route::post('/thong-tin-ca-nhan/unlink-zalo', [NguoiDungController::class, 'unlinkZalo']);
    Route::post('/thong-tin-cua-hang', [ThongTinCuaHangController::class, 'saveSettings']);

    //------------------------------------------------Nhân viên-------------------------------------------

    // Manager Routes (Quản lý)
    Route::middleware('role:quan_ly,quan_tri')->prefix('quan-ly')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'getDashboardData']);
        Route::get('/analytics', [DashboardController::class, 'getAnalyticsData']);
        Route::get('/sidebar-stats', [ManagerController::class, 'getSidebarStats']);

        Route::get('/danh-muc/data', [ManagerController::class, 'getCategoriesAdmin']);
        Route::post('/danh-muc/create', [ManagerController::class, 'storeCategory']);
        Route::post('/danh-muc/update', [ManagerController::class, 'updateCategory']);
        Route::post('/danh-muc/delete', [ManagerController::class, 'deleteCategory']);

        // San Pham routes
        Route::get('/san-pham/data', [SanPhamController::class, 'danhSach']);
        Route::post('/san-pham/create', [SanPhamController::class, 'them']);
        Route::post('/san-pham/update', [SanPhamController::class, 'sua']);
        Route::post('/san-pham/delete', [SanPhamController::class, 'xoa']);
        Route::post('/san-pham/upload-image', [SanPhamController::class, 'taiHinhAnh']);
        Route::post('/san-pham/add-variant', [SanPhamController::class, 'themBienThe']);

        Route::post('/kho/dieu-chinh', [ManagerController::class, 'adjustInventory']);
        Route::get('/don-hang', [ManagerController::class, 'getOrders']);
        Route::patch('/don-hang/{id}/trang-thai', [ManagerController::class, 'updateOrderStatus']);
        Route::post('/ma-giam-gia', [ManagerController::class, 'storeVoucher']);
        Route::get('/giao-dich', [ManagerController::class, 'getTransactions']);
        Route::get('/khach-hang/data', [ManagerController::class, 'getCustomersAdmin']);
        Route::get('/danh-gia', [ReviewController::class, 'getReviewsAdmin']);
        Route::post('/danh-gia/{id}/trang-thai', [ReviewController::class, 'toggleVisibility']);
        Route::post('/danh-gia/{id}/phan-hoi', [ReviewController::class, 'replyReview']);

        // Khuyến mãi & Flash Sale
        Route::get('/khuyen-mai/coupon', [KhuyenMaiController::class, 'getCoupons']);
        Route::post('/khuyen-mai/coupon/create', [KhuyenMaiController::class, 'createCoupon']);
        Route::post('/khuyen-mai/coupon/{code}/update', [KhuyenMaiController::class, 'updateCoupon']);
        Route::delete('/khuyen-mai/coupon/{code}', [KhuyenMaiController::class, 'deleteCoupon']);

        Route::get('/khuyen-mai/flash-sale', [KhuyenMaiController::class, 'getFlashSales']);
        Route::post('/khuyen-mai/flash-sale/create', [KhuyenMaiController::class, 'createFlashSale']);
        Route::post('/khuyen-mai/flash-sale/{id}/update', [KhuyenMaiController::class, 'updateFlashSale']);
        Route::delete('/khuyen-mai/flash-sale/{id}', [KhuyenMaiController::class, 'deleteFlashSale']);

        Route::get('/khuyen-mai/lich-su', [KhuyenMaiController::class, 'getHistory']);

        // Quản lý Tài khoản Ngân hàng
        Route::get('/tai-khoan-ngan-hang', [\App\Http\Controllers\Api\TaiKhoanNganHangController::class, 'index']);
        Route::post('/tai-khoan-ngan-hang', [\App\Http\Controllers\Api\TaiKhoanNganHangController::class, 'store']);
        Route::post('/tai-khoan-ngan-hang/{id}/kich-hoat', [\App\Http\Controllers\Api\TaiKhoanNganHangController::class, 'activate']);
        Route::delete('/tai-khoan-ngan-hang/{id}', [\App\Http\Controllers\Api\TaiKhoanNganHangController::class, 'destroy']);
    });

    // Admin Routes (Quản trị)
    Route::middleware('role:quan_tri')->prefix('quan-tri')->group(function () {
        Route::get('/nguoi-dung', [AdminController::class, 'getUsers']);
        Route::patch('/nguoi-dung/{id}/vai-tro', [AdminController::class, 'updateUserRole']);
        Route::patch('/nguoi-dung/{id}/trang-thai', [AdminController::class, 'toggleUserStatus']);
        Route::get('/thong-ke', [AdminController::class, 'getStatistics']);

        // Quản lý Nhân viên & Nhật ký
        Route::get('/nhan-vien/data', [\App\Http\Controllers\Api\NhanVienController::class, 'listStaff']);
        Route::post('/nhan-vien/create', [\App\Http\Controllers\Api\NhanVienController::class, 'createStaff']);
        Route::post('/nhan-vien/update', [\App\Http\Controllers\Api\NhanVienController::class, 'updateStaff']);
        Route::post('/nhan-vien/{id}/toggle-lock', [\App\Http\Controllers\Api\NhanVienController::class, 'toggleLockStaff']);
    });

    // Staff Shared Routes (Search, notifications, contacts)
    Route::middleware('role:quan_tri,quan_ly,nhan_vien_kho,nhan_vien_ban_hang')->prefix('staff')->group(function () {
        Route::get('/search', [\App\Http\Controllers\Api\StaffDashboardController::class, 'globalSearch']);
        Route::get('/notifications', [\App\Http\Controllers\Api\StaffDashboardController::class, 'getNotifications']);
        Route::post('/notifications/{id}/read', [\App\Http\Controllers\Api\StaffDashboardController::class, 'readNotification']);
        Route::post('/notifications/read-all', [\App\Http\Controllers\Api\StaffDashboardController::class, 'readAllNotifications']);
        Route::get('/contacts', [\App\Http\Controllers\Api\StaffDashboardController::class, 'getContacts']);
        Route::post('/contacts/{id}/read', [\App\Http\Controllers\Api\StaffDashboardController::class, 'readContact']);
        Route::post('/contacts/{id}/reply', [\App\Http\Controllers\Api\StaffDashboardController::class, 'replyContact']);

        // Blog management
        Route::get('/blog', [\App\Http\Controllers\Api\BlogController::class, 'adminIndex']);
        Route::post('/blog', [\App\Http\Controllers\Api\BlogController::class, 'store']);
        Route::post('/blog/{id}/update', [\App\Http\Controllers\Api\BlogController::class, 'update']);
        Route::delete('/blog/{id}', [\App\Http\Controllers\Api\BlogController::class, 'destroy']);
    });
});


//---------------------------------------------- Khách Hàng-----------------------------------------

Route::middleware('throttle:api')->post('/khach-hang/dat-hang', [CustomerController::class, 'checkout']);
Route::middleware('throttle:auth_limiter')->post('/khach-hang/login-google', [NguoiDungController::class, 'loginGoogle']);

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    // Customer Routes (Khách hàng)
    Route::prefix('khach-hang')->group(function () {
        Route::put('/ho-so', [NguoiDungController::class, 'updateProfile']);
        Route::get('/dia-chi', [NguoiDungController::class, 'getAddresses']);
        Route::post('/dia-chi', [NguoiDungController::class, 'addAddress']);
        Route::put('/dia-chi/{id}', [NguoiDungController::class, 'updateAddress']);
        Route::delete('/dia-chi/{id}', [NguoiDungController::class, 'deleteAddress']);
        Route::get('/don-hang', [NguoiDungController::class, 'getOrderHistory']);
        Route::post('/don-hang/{id}/huy', [NguoiDungController::class, 'cancelOrder']);
        Route::get('/don-hang/{id}/theo-doi', [CustomerController::class, 'getOrderTracking']);
        Route::post('/danh-gia', [ReviewController::class, 'submitReview']);

        // Giỏ hàng (Cart)
        Route::get('/gio-hang', [CartController::class, 'getCart']);
        Route::post('/gio-hang/sync', [CartController::class, 'syncCart']);
        Route::post('/gio-hang/add', [CartController::class, 'addToCart']);
        Route::post('/gio-hang/update', [CartController::class, 'updateQuantity']);
        Route::post('/gio-hang/remove', [CartController::class, 'removeFromCart']);
        Route::post('/gio-hang/clear', [CartController::class, 'clearCart']);

        // Yêu thích & Đã xem (Wishlist & Recently Viewed)
        Route::get('/yeu-thich', [YeuThichDaXemController::class, 'getYeuThich']);
        Route::post('/yeu-thich/toggle', [YeuThichDaXemController::class, 'toggleYeuThich']);
        Route::post('/yeu-thich/sync', [YeuThichDaXemController::class, 'syncYeuThich']);
        Route::get('/da-xem', [YeuThichDaXemController::class, 'getDaXem']);
        Route::post('/da-xem/add', [YeuThichDaXemController::class, 'addDaXem']);
        Route::post('/da-xem/sync', [YeuThichDaXemController::class, 'syncDaXem']);
    });
});
