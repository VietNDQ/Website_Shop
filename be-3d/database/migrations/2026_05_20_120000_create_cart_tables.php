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
        Schema::create('gio_hang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_nguoi_dung')->unique()->constrained('nguoi_dung')->onDelete('cascade');
            $table->timestamp('tao_luc')->nullable();
            $table->timestamp('cap_nhat_luc')->nullable();
        });

        Schema::create('chi_tiet_gio_hang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_gio_hang')->constrained('gio_hang')->onDelete('cascade');
            $table->foreignId('id_bien_the')->constrained('bien_the_san_pham')->onDelete('cascade');
            $table->integer('so_luong')->default(1);
            $table->timestamp('tao_luc')->nullable();
            $table->timestamp('cap_nhat_luc')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chi_tiet_gio_hang');
        Schema::dropIfExists('gio_hang');
    }
};
