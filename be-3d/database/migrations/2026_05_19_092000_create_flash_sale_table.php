<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flash_sale', function (Blueprint $table) {
            $table->id();
            $table->string('ten_san_pham', 255);
            $table->string('emoji', 20)->default('⚡');
            $table->decimal('gia_goc', 15, 2);
            $table->decimal('gia_flash', 15, 2);
            $table->unsignedTinyInteger('phan_tram_giam'); // 1-99
            $table->timestamp('thoi_gian_bat_dau')->nullable();
            $table->timestamp('thoi_gian_ket_thuc')->nullable();
            $table->boolean('dang_hoat_dong')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flash_sale');
    }
};
