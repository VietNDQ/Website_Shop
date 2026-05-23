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
        Schema::table('nguoi_dung', function (Blueprint $table) {
            if (!Schema::hasColumn('nguoi_dung', 'diem_cho_duyet')) {
                $table->integer('diem_cho_duyet')->default(0)->after('diem_thanh_vien');
            }
            if (!Schema::hasColumn('nguoi_dung', 'hang_thanh_vien')) {
                $table->string('hang_thanh_vien', 50)->default('dong')->after('diem_cho_duyet');
            }
        });

        Schema::table('don_hang', function (Blueprint $table) {
            if (!Schema::hasColumn('don_hang', 'so_xu_dung')) {
                $table->integer('so_xu_dung')->default(0)->after('tong_thanh_toan');
            }
            if (!Schema::hasColumn('don_hang', 'diem_tich_luy')) {
                $table->integer('diem_tich_luy')->default(0)->after('so_xu_dung');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nguoi_dung', function (Blueprint $table) {
            $table->dropColumn(['diem_cho_duyet', 'hang_thanh_vien']);
        });

        Schema::table('don_hang', function (Blueprint $table) {
            $table->dropColumn(['so_xu_dung', 'diem_tich_luy']);
        });
    }
};
