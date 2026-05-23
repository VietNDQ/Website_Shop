<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nguoi_dung', function (Blueprint $table) {
            $table->decimal('tong_chi_tieu', 15, 2)->default(0)->after('anh_dai_dien');
            $table->integer('diem_thanh_vien')->default(0)->after('tong_chi_tieu');
        });

        Schema::table('dia_chi_nguoi_dung', function (Blueprint $table) {
            $table->softDeletes('da_xoa_luc')->after('la_mac_dinh');
        });
    }

    public function down(): void
    {
        Schema::table('nguoi_dung', function (Blueprint $table) {
            $table->dropColumn(['tong_chi_tieu', 'diem_thanh_vien']);
        });

        Schema::table('dia_chi_nguoi_dung', function (Blueprint $table) {
            $table->dropColumn('da_xoa_luc');
        });
    }
};
