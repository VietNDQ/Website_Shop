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
        Schema::table('thong_tin_cua_hang', function (Blueprint $table) {
            $table->string('bank_id')->nullable()->after('instagram');
            $table->string('bank_account_no')->nullable()->after('bank_id');
            $table->string('bank_account_name')->nullable()->after('bank_account_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('thong_tin_cua_hang', function (Blueprint $table) {
            $table->dropColumn(['bank_id', 'bank_account_no', 'bank_account_name']);
        });
    }
};
