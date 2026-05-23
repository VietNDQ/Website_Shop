<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ma_giam_gia', function (Blueprint $table) {
            $table->boolean('dang_hoat_dong')->default(true)->after('so_lan_da_dung');
        });
    }

    public function down(): void
    {
        Schema::table('ma_giam_gia', function (Blueprint $table) {
            $table->dropColumn('dang_hoat_dong');
        });
    }
};
