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
        Schema::create('thu_lien_he', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_nguoi_dung')->nullable()->constrained('nguoi_dung')->onDelete('set null');
            $table->string('ho_ten', 255);
            $table->string('email', 255);
            $table->string('so_dien_thoai', 20)->nullable();
            $table->string('tieu_de', 255);
            $table->text('noi_dung');
            $table->tinyInteger('trang_thai')->default(0)->comment('0: chua_doc, 1: da_doc, 2: da_tra_loi');
            $table->text('phan_hoi')->nullable();
            $table->timestamp('tao_luc')->nullable();
            $table->timestamp('cap_nhat_luc')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thu_lien_he');
    }
};
