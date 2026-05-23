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
        Schema::create('danh_gia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_nguoi_dung')->constrained('nguoi_dung')->onDelete('cascade');
            $table->foreignId('id_san_pham')->constrained('san_pham')->onDelete('cascade');
            $table->foreignId('id_bien_the')->nullable()->constrained('bien_the_san_pham')->onDelete('set null');
            $table->foreignId('id_chi_tiet_don_hang')->constrained('chi_tiet_don_hang')->onDelete('cascade');
            $table->tinyInteger('so_sao');
            $table->text('binh_luan')->nullable();
            $table->text('phan_hoi_admin')->nullable();
            $table->enum('trang_thai', ['cho_duyet', 'hien_thi', 'an'])->default('hien_thi');
            $table->timestamp('tao_luc')->nullable();
            $table->timestamp('cap_nhat_luc')->nullable();
        });

        Schema::create('anh_danh_gia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_danh_gia')->constrained('danh_gia')->onDelete('cascade');
            $table->string('duong_dan_anh', 500);
            $table->timestamp('tao_luc')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anh_danh_gia');
        Schema::dropIfExists('danh_gia');
    }
};
