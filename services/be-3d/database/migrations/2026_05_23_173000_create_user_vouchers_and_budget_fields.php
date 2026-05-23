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
        // 1. Thêm cột ngân sách vào bảng ma_giam_gia
        Schema::table('ma_giam_gia', function (Blueprint $table) {
            if (!Schema::hasColumn('ma_giam_gia', 'ngan_sach')) {
                $table->decimal('ngan_sach', 15, 2)->nullable()->after('so_lan_da_dung');
            }
            if (!Schema::hasColumn('ma_giam_gia', 'ngan_sach_da_dung')) {
                $table->decimal('ngan_sach_da_dung', 15, 2)->default(0.00)->after('ngan_sach');
            }
            if (!Schema::hasColumn('ma_giam_gia', 'hinh_thuc_phat_hanh')) {
                // public: Đại trà, claimable: Thu thập, targeted: Phân bổ kín
                $table->string('hinh_thuc_phat_hanh', 50)->default('public')->after('dang_hoat_dong');
            }
        });

        // 2. Tạo bảng trung gian nguoi_dung_voucher để lưu ví voucher và lịch sử sử dụng
        Schema::create('nguoi_dung_voucher', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_nguoi_dung')->constrained('nguoi_dung')->onDelete('cascade');
            $table->foreignId('id_ma_giam_gia')->constrained('ma_giam_gia')->onDelete('cascade');
            $table->enum('trang_thai', ['unused', 'used'])->default('unused');
            $table->timestamp('tao_luc')->nullable();
            $table->timestamp('cap_nhat_luc')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nguoi_dung_voucher');

        Schema::table('ma_giam_gia', function (Blueprint $table) {
            $table->dropColumn(['ngan_sach', 'ngan_sach_da_dung', 'hinh_thuc_phat_hanh']);
        });
    }
};
