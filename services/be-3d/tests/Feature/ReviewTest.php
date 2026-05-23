<?php

use App\Models\NguoiDung;
use App\Models\SanPham;
use App\Models\BienTheSanPham;
use App\Models\DonHang;
use App\Models\ChiTietDonHang;
use App\Models\DanhGia;
use App\Models\LichSuTrangThaiDonHang;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('verified purchase validation checks', function () {
    // 1. Create a user
    $user = NguoiDung::create([
        'ho_ten' => 'Nguyen Quoc Viet',
        'email' => 'viet@gmail.com',
        'mat_khau' => bcrypt('password123'),
        'vai_tro' => 3,
        'dang_hoat_dong' => true,
    ]);

    // 2. Create a product and a variant
    $product = SanPham::create([
        'ten_san_pham' => 'Deadpool Resin Figure',
        'mo_ta' => 'Deadpool 1/6 Scale Resin Figure',
        'hinh_anh' => 'deadpool.jpg',
        'gia_co_ban' => 1500000,
        'tinh_trang' => 1,
    ]);

    $variant = BienTheSanPham::create([
        'id_san_pham' => $product->id,
        'ma_kho' => 'DP-RESIN-01',
        'thuoc_tinh' => json_encode(['mau_sac' => 'Resin Red']),
        'gia_goc' => 1600000,
        'gia_ban' => 1500000,
        'so_luong_ton_kho' => 10,
    ]);

    // 3. Create an order not owned by this user
    $otherUser = NguoiDung::create([
        'ho_ten' => 'Other User',
        'email' => 'other@gmail.com',
        'mat_khau' => bcrypt('password123'),
        'vai_tro' => 3,
        'dang_hoat_dong' => true,
    ]);

    $orderOther = DonHang::create([
        'id_nguoi_dung' => $otherUser->id,
        'ma_don_hang' => 'ORD-OTHER-123',
        'tong_tien_hang' => 1500000,
        'tien_duoc_giam' => 0,
        'phi_giao_hang' => 30000,
        'tong_thanh_toan' => 1530000,
        'trang_thai' => 'da_giao',
        'dia_chi_giao_hang' => ['name' => 'Other'],
    ]);

    $detailOther = ChiTietDonHang::create([
        'id_don_hang' => $orderOther->id,
        'id_bien_the' => $variant->id,
        'ten_bien_the_luc_mua' => 'Deadpool Resin Figure - Resin Red',
        'so_luong' => 1,
        'don_gia' => 1500000,
        'thanh_tien' => 1500000,
    ]);

    // Test: Try to review an order owned by someone else -> Forbidden (403)
    $response = $this->actingAs($user)->postJson('/api/khach-hang/danh-gia', [
        'id_chi_tiet_don_hang' => $detailOther->id,
        'so_sao' => 5,
        'binh_luan' => 'Nice figure!',
    ]);
    $response->assertStatus(403);

    // 4. Create an order owned by user, but status is "cho_xac_nhan" (not "da_giao")
    $orderPending = DonHang::create([
        'id_nguoi_dung' => $user->id,
        'ma_don_hang' => 'ORD-PENDING-123',
        'tong_tien_hang' => 1500000,
        'tien_duoc_giam' => 0,
        'phi_giao_hang' => 30000,
        'tong_thanh_toan' => 1530000,
        'trang_thai' => 'cho_xac_nhan',
        'dia_chi_giao_hang' => ['name' => 'Viet'],
    ]);

    $detailPending = ChiTietDonHang::create([
        'id_don_hang' => $orderPending->id,
        'id_bien_the' => $variant->id,
        'ten_bien_the_luc_mua' => 'Deadpool Resin Figure - Resin Red',
        'so_luong' => 1,
        'don_gia' => 1500000,
        'thanh_tien' => 1500000,
    ]);

    $response = $this->actingAs($user)->postJson('/api/khach-hang/danh-gia', [
        'id_chi_tiet_don_hang' => $detailPending->id,
        'so_sao' => 5,
        'binh_luan' => 'Nice!',
    ]);
    $response->assertStatus(400);
    $response->assertJsonFragment(['message' => 'Bạn chỉ có thể đánh giá khi đơn hàng đã giao thành công.']);

    // 5. Create a delivered order but delivered 35 days ago (expired)
    $orderExpired = DonHang::create([
        'id_nguoi_dung' => $user->id,
        'ma_don_hang' => 'ORD-EXPIRED-123',
        'tong_tien_hang' => 1500000,
        'tien_duoc_giam' => 0,
        'phi_giao_hang' => 30000,
        'tong_thanh_toan' => 1530000,
        'trang_thai' => 'da_giao',
        'dia_chi_giao_hang' => ['name' => 'Viet'],
    ]);

    $detailExpired = ChiTietDonHang::create([
        'id_don_hang' => $orderExpired->id,
        'id_bien_the' => $variant->id,
        'ten_bien_the_luc_mua' => 'Deadpool Resin Figure - Resin Red',
        'so_luong' => 1,
        'don_gia' => 1500000,
        'thanh_tien' => 1500000,
    ]);

    LichSuTrangThaiDonHang::unguard();
    LichSuTrangThaiDonHang::create([
        'id_don_hang' => $orderExpired->id,
        'trang_thai' => 'da_giao',
        'tao_luc' => now()->subDays(31),
    ]);
    LichSuTrangThaiDonHang::reguard();

    $response = $this->actingAs($user)->postJson('/api/khach-hang/danh-gia', [
        'id_chi_tiet_don_hang' => $detailExpired->id,
        'so_sao' => 5,
        'binh_luan' => 'Expired!',
    ]);
    $response->assertStatus(400);
    $response->assertJsonFragment(['message' => 'Thời hạn đánh giá cho sản phẩm này đã hết (tối đa 30 ngày từ khi nhận hàng).']);
});

test('successful review submission with profanity filter', function () {
    $user = NguoiDung::create([
        'ho_ten' => 'Nguyen Quoc Viet',
        'email' => 'viet@gmail.com',
        'mat_khau' => bcrypt('password123'),
        'vai_tro' => 3,
        'dang_hoat_dong' => true,
    ]);

    $product = SanPham::create([
        'ten_san_pham' => 'Deadpool Resin Figure',
        'mo_ta' => 'Deadpool 1/6 Scale Resin Figure',
        'hinh_anh' => 'deadpool.jpg',
        'gia_co_ban' => 1500000,
        'tinh_trang' => 1,
    ]);

    $variant = BienTheSanPham::create([
        'id_san_pham' => $product->id,
        'ma_kho' => 'DP-RESIN-01',
        'thuoc_tinh' => json_encode(['mau_sac' => 'Resin Red']),
        'gia_goc' => 1600000,
        'gia_ban' => 1500000,
        'so_luong_ton_kho' => 10,
    ]);

    $order = DonHang::create([
        'id_nguoi_dung' => $user->id,
        'ma_don_hang' => 'ORD-SUCCESS-123',
        'tong_tien_hang' => 1500000,
        'tien_duoc_giam' => 0,
        'phi_giao_hang' => 30000,
        'tong_thanh_toan' => 1530000,
        'trang_thai' => 'da_giao',
        'dia_chi_giao_hang' => ['name' => 'Viet'],
    ]);

    $detail = ChiTietDonHang::create([
        'id_don_hang' => $order->id,
        'id_bien_the' => $variant->id,
        'ten_bien_the_luc_mua' => 'Deadpool Resin Figure - Resin Red',
        'so_luong' => 1,
        'don_gia' => 1500000,
        'thanh_tien' => 1500000,
    ]);

    LichSuTrangThaiDonHang::unguard();
    LichSuTrangThaiDonHang::create([
        'id_don_hang' => $order->id,
        'trang_thai' => 'da_giao',
        'tao_luc' => now()->subDays(5),
    ]);
    LichSuTrangThaiDonHang::reguard();

    // Submit a review with bad word "vcl"
    $response = $this->actingAs($user)->postJson('/api/khach-hang/danh-gia', [
        'id_chi_tiet_don_hang' => $detail->id,
        'so_sao' => 5,
        'binh_luan' => 'Mô hình vcl đẹp thế!',
    ]);

    $response->assertStatus(200);
    $response->assertJsonFragment(['status' => 1]);

    // Check that review comment is filtered
    $this->assertDatabaseHas('danh_gia', [
        'id_chi_tiet_don_hang' => $detail->id,
        'binh_luan' => 'Mô hình *** đẹp thế!',
        'trang_thai' => 'hien_thi'
    ]);

    // Try to review again -> Duplicate (400)
    $responseDuplicate = $this->actingAs($user)->postJson('/api/khach-hang/danh-gia', [
        'id_chi_tiet_don_hang' => $detail->id,
        'so_sao' => 4,
    ]);
    $responseDuplicate->assertStatus(400);
});

test('public product reviews API with name masking', function () {
    $user = NguoiDung::create([
        'ho_ten' => 'Nguyễn Quốc Việt',
        'email' => 'viet@gmail.com',
        'mat_khau' => bcrypt('password123'),
        'vai_tro' => 3,
        'dang_hoat_dong' => true,
    ]);

    $product = SanPham::create([
        'ten_san_pham' => 'Deadpool Resin Figure',
        'mo_ta' => 'Deadpool 1/6 Scale Resin Figure',
        'hinh_anh' => 'deadpool.jpg',
        'gia_co_ban' => 1500000,
        'tinh_trang' => 1,
    ]);

    $variant = BienTheSanPham::create([
        'id_san_pham' => $product->id,
        'ma_kho' => 'DP-RESIN-01',
        'thuoc_tinh' => json_encode(['mau_sac' => 'Resin Red']),
        'gia_goc' => 1600000,
        'gia_ban' => 1500000,
        'so_luong_ton_kho' => 10,
    ]);

    $order = DonHang::create([
        'id_nguoi_dung' => $user->id,
        'ma_don_hang' => 'ORD-PUBLIC-123',
        'tong_tien_hang' => 1500000,
        'tien_duoc_giam' => 0,
        'phi_giao_hang' => 30000,
        'tong_thanh_toan' => 1530000,
        'trang_thai' => 'da_giao',
        'dia_chi_giao_hang' => ['name' => 'Viet'],
    ]);

    $detail = ChiTietDonHang::create([
        'id_don_hang' => $order->id,
        'id_bien_the' => $variant->id,
        'ten_bien_the_luc_mua' => 'Deadpool Resin Figure - Resin Red',
        'so_luong' => 1,
        'don_gia' => 1500000,
        'thanh_tien' => 1500000,
    ]);

    // Create a review
    DanhGia::create([
        'id_nguoi_dung' => $user->id,
        'id_san_pham' => $product->id,
        'id_bien_the' => $variant->id,
        'id_chi_tiet_don_hang' => $detail->id,
        'so_sao' => 5,
        'binh_luan' => 'Cực kỳ đẹp!',
        'trang_thai' => 'hien_thi',
    ]);

    $response = $this->getJson("/api/san-pham/{$product->id}/danh-gia");
    $response->assertStatus(200);

    // Verify name masking Nguyễn Quốc Việt -> N***t
    $response->assertJsonFragment([
        'name' => 'N***t',
        'rating' => 5,
        'body' => 'Cực kỳ đẹp!',
        'variant' => 'Deadpool Resin Figure - Resin Red',
    ]);
});

test('admin review actions', function () {
    $admin = NguoiDung::create([
        'ho_ten' => 'Admin User',
        'email' => 'admin@gmail.com',
        'mat_khau' => bcrypt('password123'),
        'vai_tro' => 1, // Admin
        'dang_hoat_dong' => true,
    ]);

    $user = NguoiDung::create([
        'ho_ten' => 'Customer User',
        'email' => 'cust@gmail.com',
        'mat_khau' => bcrypt('password123'),
        'vai_tro' => 3,
        'dang_hoat_dong' => true,
    ]);

    $product = SanPham::create([
        'ten_san_pham' => 'Deadpool Resin Figure',
        'mo_ta' => 'Deadpool 1/6 Scale Resin Figure',
        'hinh_anh' => 'deadpool.jpg',
        'gia_co_ban' => 1500000,
        'tinh_trang' => 1,
    ]);

    $variant = BienTheSanPham::create([
        'id_san_pham' => $product->id,
        'ma_kho' => 'DP-RESIN-ADMIN',
        'thuoc_tinh' => json_encode(['mau_sac' => 'Resin Red']),
        'gia_goc' => 1600000,
        'gia_ban' => 1500000,
        'so_luong_ton_kho' => 10,
    ]);

    $order = DonHang::create([
        'id_nguoi_dung' => $user->id,
        'ma_don_hang' => 'ORD-ADMIN-123',
        'tong_tien_hang' => 1500000,
        'tien_duoc_giam' => 0,
        'phi_giao_hang' => 30000,
        'tong_thanh_toan' => 1530000,
        'trang_thai' => 'da_giao',
        'dia_chi_giao_hang' => ['name' => 'Viet'],
    ]);

    $detail = ChiTietDonHang::create([
        'id_don_hang' => $order->id,
        'id_bien_the' => $variant->id,
        'ten_bien_the_luc_mua' => 'Deadpool Resin Figure - Resin Red',
        'so_luong' => 1,
        'don_gia' => 1500000,
        'thanh_tien' => 1500000,
    ]);

    $review = DanhGia::create([
        'id_nguoi_dung' => $user->id,
        'id_san_pham' => $product->id,
        'id_bien_the' => $variant->id,
        'id_chi_tiet_don_hang' => $detail->id,
        'so_sao' => 4,
        'binh_luan' => 'Review text for admin',
        'trang_thai' => 'cho_duyet',
    ]);

    // Test: Get all reviews
    $response = $this->actingAs($admin)->getJson('/api/quan-ly/danh-gia');
    $response->assertStatus(200);
    $response->assertJsonFragment(['customer' => 'Customer User']);

    // Test: Toggle visibility to "an"
    $responseToggle = $this->actingAs($admin)->postJson("/api/quan-ly/danh-gia/{$review->id}/trang-thai", [
        'trang_thai' => 'an'
    ]);
    $responseToggle->assertStatus(200);
    $this->assertDatabaseHas('danh_gia', [
        'id' => $review->id,
        'trang_thai' => 'an'
    ]);

    // Test: Reply
    $responseReply = $this->actingAs($admin)->postJson("/api/quan-ly/danh-gia/{$review->id}/phan-hoi", [
        'phan_hoi_admin' => 'Cảm ơn quý khách!'
    ]);
    $responseReply->assertStatus(200);
    $this->assertDatabaseHas('danh_gia', [
        'id' => $review->id,
        'phan_hoi_admin' => 'Cảm ơn quý khách!'
    ]);
});
