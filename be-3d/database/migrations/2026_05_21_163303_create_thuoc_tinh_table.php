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
        Schema::create('thuoc_tinh', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_san_pham')->constrained('san_pham')->onDelete('cascade');
            $table->string('ten_thuoc_tinh', 100);
            $table->json('gia_tri');
            $table->timestamp('tao_luc')->nullable();
            $table->timestamp('cap_nhat_luc')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thuoc_tinh');
    }
};
