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
        // Table: yeu_thich (Wishlist / Favorites)
        Schema::create('yeu_thich', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_nguoi_dung')->constrained('nguoi_dung')->onDelete('cascade');
            $table->foreignId('id_san_pham')->constrained('san_pham')->onDelete('cascade');
            $table->timestamp('tao_luc')->nullable();
            $table->timestamp('cap_nhat_luc')->nullable();
            
            $table->unique(['id_nguoi_dung', 'id_san_pham']);
        });

        // Table: da_xem (Recently Viewed)
        Schema::create('da_xem', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_nguoi_dung')->constrained('nguoi_dung')->onDelete('cascade');
            $table->foreignId('id_san_pham')->constrained('san_pham')->onDelete('cascade');
            $table->timestamp('tao_luc')->nullable();
            $table->timestamp('cap_nhat_luc')->nullable();
            
            $table->unique(['id_nguoi_dung', 'id_san_pham']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('da_xem');
        Schema::dropIfExists('yeu_thich');
    }
};
