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
        Schema::table('thanh_toan', function (Blueprint $table) {
            $table->string('phuong_thuc', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('thanh_toan', function (Blueprint $table) {
            $table->enum('phuong_thuc', ['tien_mat', 'vnpay', 'momo', 'the_tin_dung'])->change();
        });
    }
};
