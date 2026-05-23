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
        Schema::table('bien_the_san_pham', function (Blueprint $table) {
            $table->string('hinh_anh', 500)->nullable()->after('thuoc_tinh');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bien_the_san_pham', function (Blueprint $table) {
            $table->dropColumn('hinh_anh');
        });
    }
};
