<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Table: nguoi_dung (Users)
        Schema::create('nguoi_dung', function (Blueprint $table) {
            $table->id();
            $table->string('ho_ten', 255);
            $table->string('email', 255)->unique();
            $table->string('mat_khau', 255);
            $table->tinyInteger('vai_tro')->default(3)->comment('1: quan_tri, 2: quan_ly, 3: khach_hang');
            $table->boolean('dang_hoat_dong')->default(true);
            $table->timestamp('dang_nhap_lan_cuoi_luc')->nullable();
            $table->timestamp('tao_luc')->nullable();
            $table->timestamp('cap_nhat_luc')->nullable();
        });

        // Table: dia_chi_nguoi_dung (User Addresses)
        Schema::create('dia_chi_nguoi_dung', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_nguoi_dung')->constrained('nguoi_dung')->onDelete('cascade');
            $table->string('so_dien_thoai', 20);
            $table->string('dia_chi_chi_tiet', 255);
            $table->string('thanh_pho', 100);
            $table->string('quan_huyen', 100);
            $table->boolean('la_mac_dinh')->default(false);
            $table->timestamp('tao_luc')->nullable();
            $table->timestamp('cap_nhat_luc')->nullable();
        });

        // Table: danh_muc (Categories)
        Schema::create('danh_muc', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_danh_muc_cha')->nullable()->constrained('danh_muc')->onDelete('set null');
            $table->string('ten_danh_muc', 255);
            $table->string('duong_dan_mau', 255)->unique();
            $table->timestamp('tao_luc')->nullable();
            $table->timestamp('cap_nhat_luc')->nullable();
        });

        // Table: san_pham (Products)
        Schema::create('san_pham', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_danh_muc')->nullable()->constrained('danh_muc')->onDelete('set null');
            $table->string('ten_san_pham', 255);
            $table->decimal('gia_co_ban', 15, 2);
            $table->longText('mo_ta')->nullable();
            $table->softDeletes('da_xoa_luc'); // Use da_xoa_luc for soft deletes
            $table->timestamp('tao_luc')->nullable();
            $table->timestamp('cap_nhat_luc')->nullable();
        });

        // Table: hinh_anh_san_pham (Product Images)
        Schema::create('hinh_anh_san_pham', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_san_pham')->constrained('san_pham')->onDelete('cascade');
            $table->string('duong_dan_anh', 500);
            $table->boolean('la_anh_dai_dien')->default(false);
            $table->integer('thu_tu_hien_thi')->default(0);
            $table->timestamp('tao_luc')->nullable();
        });

        // Table: bien_the_san_pham (Product Variants)
        Schema::create('bien_the_san_pham', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_san_pham')->constrained('san_pham')->onDelete('cascade');
            $table->string('ma_kho', 100)->unique();
            $table->json('thuoc_tinh')->nullable();
            $table->decimal('gia_ban', 15, 2);
            $table->integer('so_luong_ton_kho')->default(0);
            $table->timestamp('tao_luc')->nullable();
            $table->timestamp('cap_nhat_luc')->nullable();
        });

        // Table: nhat_ky_ton_kho (Inventory Logs)
        Schema::create('nhat_ky_ton_kho', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_bien_the')->constrained('bien_the_san_pham')->onDelete('cascade');
            $table->integer('so_luong_thay_doi');
            $table->enum('loai_giao_dich', ['nhap_hang', 'khach_dat', 'khach_huy', 'dieu_chinh']);
            $table->string('ma_tham_chieu', 100)->nullable();
            $table->string('ghi_chu', 255)->nullable();
            $table->timestamp('tao_luc')->nullable();
        });

        // Table: ma_giam_gia (Vouchers)
        Schema::create('ma_giam_gia', function (Blueprint $table) {
            $table->id();
            $table->string('ma_code', 50)->unique();
            $table->enum('loai_giam_gia', ['phan_tram', 'tien_mat']);
            $table->decimal('gia_tri_giam', 15, 2);
            $table->decimal('don_hang_toi_thieu', 15, 2)->default(0);
            $table->decimal('muc_giam_toi_da', 15, 2)->nullable();
            $table->timestamp('ngay_bat_dau')->nullable();
            $table->timestamp('ngay_ket_thuc')->nullable();
            $table->integer('gioi_han_su_dung')->nullable();
            $table->integer('so_lan_da_dung')->default(0);
            $table->timestamp('tao_luc')->nullable();
        });

        // Table: don_hang (Orders)
        Schema::create('don_hang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_nguoi_dung')->constrained('nguoi_dung')->onDelete('cascade');
            $table->string('ma_don_hang', 50)->unique();
            $table->foreignId('id_ma_giam_gia')->nullable()->constrained('ma_giam_gia')->onDelete('set null');
            $table->decimal('tong_tien_hang', 15, 2);
            $table->decimal('tien_duoc_giam', 15, 2)->default(0);
            $table->decimal('phi_giao_hang', 15, 2)->default(0);
            $table->decimal('tong_thanh_toan', 15, 2);
            $table->enum('trang_thai', ['cho_xu_ly', 'dang_chuan_bi', 'dang_giao', 'da_giao', 'da_huy', 'hoan_tien'])->default('cho_xu_ly');
            $table->json('dia_chi_giao_hang');
            $table->longText('ghi_chu_khach_hang')->nullable();
            $table->timestamp('tao_luc')->nullable();
            $table->timestamp('cap_nhat_luc')->nullable();
        });

        // Table: chi_tiet_don_hang (Order Details)
        Schema::create('chi_tiet_don_hang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_don_hang')->constrained('don_hang')->onDelete('cascade');
            $table->foreignId('id_bien_the')->nullable()->constrained('bien_the_san_pham')->onDelete('set null');
            $table->string('ten_bien_the_luc_mua', 255);
            $table->integer('so_luong');
            $table->decimal('don_gia', 15, 2);
            $table->decimal('thanh_tien', 15, 2);
            $table->timestamp('tao_luc')->nullable();
        });

        // Table: lich_su_trang_thai_don_hang (Order Status History)
        Schema::create('lich_su_trang_thai_don_hang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_don_hang')->constrained('don_hang')->onDelete('cascade');
            $table->enum('trang_thai', ['cho_xu_ly', 'dang_chuan_bi', 'dang_giao', 'da_giao', 'da_huy', 'hoan_tien']);
            $table->longText('ghi_chu')->nullable();
            $table->timestamp('tao_luc')->nullable();
        });

        // Table: thanh_toan (Payments)
        Schema::create('thanh_toan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_don_hang')->constrained('don_hang')->onDelete('cascade');
            $table->enum('phuong_thuc', ['tien_mat', 'vnpay', 'momo', 'the_tin_dung']);
            $table->string('ma_giao_dich', 255)->nullable();
            $table->decimal('so_tien', 15, 2);
            $table->enum('trang_thai', ['chua_thanh_toan', 'da_thanh_toan', 'that_bai', 'da_hoan_tien'])->default('chua_thanh_toan');
            $table->timestamp('ngay_thanh_toan')->nullable();
            $table->timestamp('tao_luc')->nullable();
            $table->timestamp('cap_nhat_luc')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thanh_toan');
        Schema::dropIfExists('lich_su_trang_thai_don_hang');
        Schema::dropIfExists('chi_tiet_don_hang');
        Schema::dropIfExists('don_hang');
        Schema::dropIfExists('ma_giam_gia');
        Schema::dropIfExists('nhat_ky_ton_kho');
        Schema::dropIfExists('bien_the_san_pham');
        Schema::dropIfExists('hinh_anh_san_pham');
        Schema::dropIfExists('san_pham');
        Schema::dropIfExists('danh_muc');
        Schema::dropIfExists('dia_chi_nguoi_dung');
        Schema::dropIfExists('nguoi_dung');
    }
};
