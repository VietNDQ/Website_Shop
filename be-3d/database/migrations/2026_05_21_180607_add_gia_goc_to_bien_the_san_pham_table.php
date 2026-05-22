<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bien_the_san_pham', function (Blueprint $table) {
            $table->decimal('gia_goc', 15, 2)->nullable()->after('gia_ban');
        });
    }

    public function down(): void
    {
        Schema::table('bien_the_san_pham', function (Blueprint $table) {
            $table->dropColumn('gia_goc');
        });
    }
};
