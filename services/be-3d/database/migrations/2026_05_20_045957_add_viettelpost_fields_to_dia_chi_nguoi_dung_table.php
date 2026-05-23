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
        Schema::table('dia_chi_nguoi_dung', function (Blueprint $table) {
            $table->integer('thanh_pho_id')->nullable()->after('quan_huyen');
            $table->integer('quan_huyen_id')->nullable()->after('thanh_pho_id');
            $table->integer('phuong_xa_id')->nullable()->after('quan_huyen_id');
            $table->string('phuong_xa', 100)->nullable()->after('phuong_xa_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dia_chi_nguoi_dung', function (Blueprint $table) {
            $table->dropColumn(['thanh_pho_id', 'quan_huyen_id', 'phuong_xa_id', 'phuong_xa']);
        });
    }
};
