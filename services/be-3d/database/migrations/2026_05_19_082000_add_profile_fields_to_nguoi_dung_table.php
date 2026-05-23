<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nguoi_dung', function (Blueprint $table) {
            $table->date('ngay_sinh')->nullable()->after('dang_nhap_lan_cuoi_luc');
            $table->text('gioi_thieu')->nullable()->after('ngay_sinh');
            $table->string('anh_dai_dien', 500)->nullable()->after('gioi_thieu');
        });
    }

    public function down(): void
    {
        Schema::table('nguoi_dung', function (Blueprint $table) {
            $table->dropColumn(['ngay_sinh', 'gioi_thieu', 'anh_dai_dien']);
        });
    }
};
