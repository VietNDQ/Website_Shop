<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('thong_tin_cua_hang', function (Blueprint $table) {
            $table->id();
            $table->string('ten_thuong_hieu');
            $table->string('hotline')->nullable();
            $table->string('email_ho_tro')->nullable();
            $table->string('website')->nullable();
            $table->string('dia_chi_kho')->nullable();
            $table->text('mo_ta')->nullable();
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->timestamps();
        });

        // Seed default store info
        DB::table('thong_tin_cua_hang')->insert([
            'ten_thuong_hieu' => 'Mô Hình BALAB',
            'hotline' => '1800 2097',
            'email_ho_tro' => 'support@BALAB.vn',
            'website' => 'https://BALAB.vn',
            'dia_chi_kho' => '123 Nguyễn Văn Linh, Q.7, TP.HCM',
            'mo_ta' => 'Chuyên phân phối mô hình chính hãng, chất lượng cao.',
            'facebook' => 'https://facebook.com/mohinhBALAB',
            'instagram' => 'https://instagram.com/mohinhBALAB',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thong_tin_cua_hang');
    }
};
