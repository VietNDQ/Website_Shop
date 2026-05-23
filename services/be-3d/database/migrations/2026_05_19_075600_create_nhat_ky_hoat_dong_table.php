<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nhat_ky_hoat_dong', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_nguoi_dung')->nullable()->constrained('nguoi_dung')->onDelete('cascade');
            $table->string('ten_nguoi_dung', 255)->nullable();
            $table->string('hanh_dong', 500);
            $table->string('mau_sac', 20)->default('#6366f1');
            $table->timestamp('tao_luc')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nhat_ky_hoat_dong');
    }
};
