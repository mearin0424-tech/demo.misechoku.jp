<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cast_posts')) {
            return;
        }

        if (!Schema::hasColumn('cast_posts', 'body')) {
            Schema::table('cast_posts', function (Blueprint $table) {
                $table->text('body')->nullable()->comment('ひとこと');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('cast_posts') && Schema::hasColumn('cast_posts', 'body')) {
            Schema::table('cast_posts', function (Blueprint $table) {
                $table->dropColumn('body');
            });
        }
    }
};
