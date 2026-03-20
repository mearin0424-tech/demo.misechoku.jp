<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('push_subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('push_subscriptions', 'user_type')) {
                $table->string('user_type', 32)->nullable()->after('id');
            }
            if (!Schema::hasColumn('push_subscriptions', 'user_id')) {
                $table->string('user_id', 32)->nullable()->after('user_type');
            }
            $table->index(['user_type', 'user_id'], 'push_subscriptions_user_idx');
        });
    }

    public function down(): void
    {
        Schema::table('push_subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('push_subscriptions', 'user_type')) {
                $table->dropIndex('push_subscriptions_user_idx');
                $table->dropColumn(['user_type', 'user_id']);
            }
        });
    }
};
