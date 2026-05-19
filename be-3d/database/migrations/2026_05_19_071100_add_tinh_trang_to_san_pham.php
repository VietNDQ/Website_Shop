<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('san_pham', function (Blueprint $table) {
            $table->tinyInteger('tinh_trang')->default(1)->comment('1: dang_ban, 0: het_hang, 2: an')->after('mo_ta');
        });
    }

    public function down(): void
    {
        Schema::table('san_pham', function (Blueprint $table) {
            $table->dropColumn('tinh_trang');
        });
    }
};
