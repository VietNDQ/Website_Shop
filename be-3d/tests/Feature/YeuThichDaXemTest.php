<?php

use App\Models\NguoiDung;
use App\Models\SanPham;
use App\Models\YeuThich;
use App\Models\DaXem;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest or unauthenticated users cannot access wishlist/recently viewed APIs', function () {
    $this->getJson('/api/khach-hang/yeu-thich')->assertStatus(401);
    $this->postJson('/api/khach-hang/yeu-thich/toggle', ['id_san_pham' => 1])->assertStatus(401);
    $this->postJson('/api/khach-hang/yeu-thich/sync', ['id_san_phams' => [1]])->assertStatus(401);
    $this->getJson('/api/khach-hang/da-xem')->assertStatus(401);
    $this->postJson('/api/khach-hang/da-xem/add', ['id_san_pham' => 1])->assertStatus(401);
    $this->postJson('/api/khach-hang/da-xem/sync', ['id_san_phams' => [1]])->assertStatus(401);
});

test('authenticated user can toggle favorite and view wishlist', function () {
    $user = NguoiDung::create([
        'ho_ten' => 'Nguyen Quoc Viet',
        'email' => 'viet@gmail.com',
        'mat_khau' => bcrypt('password123'),
        'vai_tro' => 3,
        'dang_hoat_dong' => true,
    ]);

    $product = SanPham::create([
        'ten_san_pham' => 'Deadpool Model',
        'gia_co_ban' => 500000,
        'tinh_trang' => 1,
    ]);

    // 1. Add to wishlist
    $response = $this->actingAs($user)->postJson('/api/khach-hang/yeu-thich/toggle', [
        'id_san_pham' => $product->id
    ]);

    $response->assertStatus(200);
    $response->assertJsonFragment(['is_wished' => true]);
    $this->assertDatabaseHas('yeu_thich', [
        'id_nguoi_dung' => $user->id,
        'id_san_pham' => $product->id
    ]);

    // 2. Load wishlist
    $getRes = $this->actingAs($user)->getJson('/api/khach-hang/yeu-thich');
    $getRes->assertStatus(200);
    $getRes->assertJsonCount(1, 'wishlist');
    $getRes->assertJsonFragment(['ten_san_pham' => 'Deadpool Model']);

    // 3. Remove from wishlist (toggle again)
    $response2 = $this->actingAs($user)->postJson('/api/khach-hang/yeu-thich/toggle', [
        'id_san_pham' => $product->id
    ]);
    $response2->assertStatus(200);
    $response2->assertJsonFragment(['is_wished' => false]);
    $this->assertDatabaseMissing('yeu_thich', [
        'id_nguoi_dung' => $user->id,
        'id_san_pham' => $product->id
    ]);
});

test('authenticated user can sync wishlist from guest localStorage', function () {
    $user = NguoiDung::create([
        'ho_ten' => 'Nguyen Quoc Viet',
        'email' => 'viet@gmail.com',
        'mat_khau' => bcrypt('password123'),
        'vai_tro' => 3,
        'dang_hoat_dong' => true,
    ]);

    $p1 = SanPham::create(['ten_san_pham' => 'Ironman Figure', 'gia_co_ban' => 450000, 'tinh_trang' => 1]);
    $p2 = SanPham::create(['ten_san_pham' => 'Batman Figure', 'gia_co_ban' => 600000, 'tinh_trang' => 1]);

    $response = $this->actingAs($user)->postJson('/api/khach-hang/yeu-thich/sync', [
        'id_san_phams' => [$p1->id, $p2->id]
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('yeu_thich', ['id_nguoi_dung' => $user->id, 'id_san_pham' => $p1->id]);
    $this->assertDatabaseHas('yeu_thich', ['id_nguoi_dung' => $user->id, 'id_san_pham' => $p2->id]);
});

test('authenticated user can track recently viewed products up to 20 items', function () {
    $user = NguoiDung::create([
        'ho_ten' => 'Nguyen Quoc Viet',
        'email' => 'viet@gmail.com',
        'mat_khau' => bcrypt('password123'),
        'vai_tro' => 3,
        'dang_hoat_dong' => true,
    ]);

    // Create 22 products
    $products = [];
    for ($i = 1; $i <= 22; $i++) {
        $products[] = SanPham::create([
            'ten_san_pham' => "Product {$i}",
            'gia_co_ban' => 100000 * $i,
            'tinh_trang' => 1,
        ]);
    }

    // View them one by one
    foreach ($products as $p) {
        $res = $this->actingAs($user)->postJson('/api/khach-hang/da-xem/add', [
            'id_san_pham' => $p->id
        ]);
        $res->assertStatus(200);
    }

    // Load recently viewed
    $getRes = $this->actingAs($user)->getJson('/api/khach-hang/da-xem');
    $getRes->assertStatus(200);
    
    // Check count is capped at 20
    $getRes->assertJsonCount(20, 'recentlyViewed');

    // The oldest products (Product 1 and Product 2) should have been pruned
    $this->assertDatabaseMissing('da_xem', ['id_nguoi_dung' => $user->id, 'id_san_pham' => $products[0]->id]);
    $this->assertDatabaseMissing('da_xem', ['id_nguoi_dung' => $user->id, 'id_san_pham' => $products[1]->id]);
    
    // The newest products (Product 22) should be present
    $this->assertDatabaseHas('da_xem', ['id_nguoi_dung' => $user->id, 'id_san_pham' => $products[21]->id]);
});

test('authenticated user can sync recently viewed history', function () {
    $user = NguoiDung::create([
        'ho_ten' => 'Nguyen Quoc Viet',
        'email' => 'viet@gmail.com',
        'mat_khau' => bcrypt('password123'),
        'vai_tro' => 3,
        'dang_hoat_dong' => true,
    ]);

    $p1 = SanPham::create(['ten_san_pham' => 'Figure A', 'gia_co_ban' => 200000, 'tinh_trang' => 1]);
    $p2 = SanPham::create(['ten_san_pham' => 'Figure B', 'gia_co_ban' => 300000, 'tinh_trang' => 1]);

    $response = $this->actingAs($user)->postJson('/api/khach-hang/da-xem/sync', [
        'id_san_phams' => [$p1->id, $p2->id]
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('da_xem', ['id_nguoi_dung' => $user->id, 'id_san_pham' => $p1->id]);
    $this->assertDatabaseHas('da_xem', ['id_nguoi_dung' => $user->id, 'id_san_pham' => $p2->id]);
});
