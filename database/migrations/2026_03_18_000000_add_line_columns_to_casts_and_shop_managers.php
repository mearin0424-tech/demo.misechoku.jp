<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Messaging API 連携用カラム追加
     */
    public function up(): void
    {
        Schema::table('shop_managers', function (Blueprint $table) {
            $table->string('line_user_id', 255)->nullable()->after('password')->comment('LINE Login ユーザーID');
        });
    }

    public function down(): void
    {
        Schema::table('shop_managers', function (Blueprint $table) {
            $table->dropColumn(['line_user_id']);
        });
    }
};
