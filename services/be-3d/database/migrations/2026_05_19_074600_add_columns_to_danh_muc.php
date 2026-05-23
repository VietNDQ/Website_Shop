<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('danh_muc', function (Blueprint $table) {
            $table->string('emoji', 50)->nullable()->after('id_danh_muc_cha');
            $table->text('mo_ta')->nullable()->after('ten_danh_muc');
            $table->integer('thu_tu_hien_thi')->default(1)->after('duong_dan_mau');
            $table->tinyInteger('trang_thai')->default(1)->comment('1: active, 0: hidden')->after('thu_tu_hien_thi');
        });
    }

    public function down(): void
    {
        Schema::table('danh_muc', function (Blueprint $table) {
            $table->dropColumn(['emoji', 'mo_ta', 'thu_tu_hien_thi', 'trang_thai']);
        });
    }
};
