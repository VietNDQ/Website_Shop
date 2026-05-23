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
        Schema::create('bai_viet', function (Blueprint $table) {
            $table->id();
            $table->string('tieu_de', 255);
            $table->string('slug', 255)->unique();
            $table->string('anh_dai_dien', 255)->nullable();
            $table->text('tom_tat')->nullable();
            $table->longText('noi_dung');
            $table->integer('luot_xem')->default(0);
            $table->string('loai', 50)->default('tin_tuc'); // 'tin_tuc', 'huong_dan', 'danh_gia'
            $table->boolean('trang_thai')->default(true); // 1: cong_khai, 0: nhap
            $table->foreignId('id_nguoi_dang')->nullable()->constrained('nguoi_dung')->onDelete('set null');
            $table->timestamp('tao_luc')->nullable();
            $table->timestamp('cap_nhat_luc')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bai_viet');
    }
};
