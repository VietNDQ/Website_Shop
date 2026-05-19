<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\ManagerController;
use App\Http\Controllers\Api\WebhookController;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::post('/dang-ky', [AuthController::class, 'register']);
Route::post('/dang-nhap', [AuthController::class, 'login']);
Route::get('/danh-muc', [CustomerController::class, 'getCategories']);
Route::get('/san-pham', [CustomerController::class, 'getProducts']);
Route::get('/san-pham/{id}', [CustomerController::class, 'getProductDetail']);

// Webhook (Public)
Route::post('/webhooks/thanh-toan', [WebhookController::class, 'handlePaymentWebhook']);

// Authenticated Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/dang-xuat', [AuthController::class, 'logout']);
    Route::get('/thong-tin-ca-nhan', [AuthController::class, 'me']);

    // Customer Routes (Khách hàng)
    Route::prefix('khach-hang')->group(function () {
        Route::put('/ho-so', [CustomerController::class, 'updateProfile']);
        Route::get('/dia-chi', [CustomerController::class, 'getAddresses']);
        Route::post('/dia-chi', [CustomerController::class, 'addAddress']);
        Route::post('/dat-hang', [CustomerController::class, 'checkout']);
        Route::get('/don-hang', [CustomerController::class, 'getOrderHistory']);
        Route::get('/don-hang/{id}/theo-doi', [CustomerController::class, 'getOrderTracking']);
    });

    // Manager Routes (Quản lý)
    Route::middleware('role:quan_ly,quan_tri')->prefix('quan-ly')->group(function () {
        Route::post('/danh-muc', [ManagerController::class, 'storeCategory']);
        Route::post('/san-pham', [ManagerController::class, 'storeProduct']);
        Route::post('/san-pham/{id}/hinh-anh', [ManagerController::class, 'uploadImage']);
        Route::post('/san-pham/{id}/bien-the', [ManagerController::class, 'storeVariant']);
        Route::post('/kho/dieu-chinh', [ManagerController::class, 'adjustInventory']);
        Route::get('/don-hang', [ManagerController::class, 'getOrders']);
        Route::patch('/don-hang/{id}/trang-thai', [ManagerController::class, 'updateOrderStatus']);
        Route::post('/ma-giam-gia', [ManagerController::class, 'storeVoucher']);
        Route::get('/giao-dich', [ManagerController::class, 'getTransactions']);
    });

    // Admin Routes (Quản trị)
    Route::middleware('role:quan_tri')->prefix('quan-tri')->group(function () {
        Route::get('/nguoi-dung', [AdminController::class, 'getUsers']);
        Route::patch('/nguoi-dung/{id}/vai-tro', [AdminController::class, 'updateUserRole']);
        Route::patch('/nguoi-dung/{id}/trang-thai', [AdminController::class, 'toggleUserStatus']);
        Route::get('/thong-ke', [AdminController::class, 'getStatistics']);
    });
});
