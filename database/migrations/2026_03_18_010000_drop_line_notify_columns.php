<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * LINE Notify 廃止に伴い不要カラムを削除
     */
    public function up(): void
    {
        if (Schema::hasColumn('casts', 'line_notify_token')) {
            Schema::table('casts', function (Blueprint $table) {
                $table->dropColumn('line_notify_token');
            });
        }

        if (Schema::hasColumn('shop_managers', 'line_notify_token')) {
            Schema::table('shop_managers', function (Blueprint $table) {
                $table->dropColumn('line_notify_token');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('casts', 'line_notify_token')) {
            Schema::table('casts', function (Blueprint $table) {
                $table->string('line_notify_token', 255)->nullable()->after('remember_token');
            });
        }

        if (!Schema::hasColumn('shop_managers', 'line_notify_token')) {
            Schema::table('shop_managers', function (Blueprint $table) {
                $table->string('line_notify_token', 255)->nullable()->after('line_user_id');
            });
        }
    }
};
