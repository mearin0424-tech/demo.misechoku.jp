<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_contents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('ng_words', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('word')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('review_contents')->insert([
            [
                'id' => 1,
                'name' => '接客',
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => '2025-01-14 05:33:10',
                'updated_at' => '2025-01-14 05:33:10',
            ],
            [
                'id' => 2,
                'name' => '雰囲気',
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => '2025-01-14 05:33:10',
                'updated_at' => '2025-01-14 05:33:10',
            ],
            [
                'id' => 3,
                'name' => '給与条件',
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => '2025-01-14 05:33:10',
                'updated_at' => '2025-01-14 05:33:10',
            ],
            [
                'id' => 4,
                'name' => '働きやすさ',
                'sort_order' => 4,
                'is_active' => true,
                'created_at' => '2025-01-14 05:33:10',
                'updated_at' => '2025-01-14 05:33:10',
            ],
        ]);

        DB::table('ng_words')->insert([
            [
                'word' => '個人連絡先',
                'is_active' => true,
                'created_at' => '2025-01-14 05:33:10',
                'updated_at' => '2025-01-14 05:33:10',
            ],
            [
                'word' => '直引き',
                'is_active' => true,
                'created_at' => '2025-01-14 05:33:10',
                'updated_at' => '2025-01-14 05:33:10',
            ],
            [
                'word' => '裏オプ',
                'is_active' => true,
                'created_at' => '2025-01-14 05:33:10',
                'updated_at' => '2025-01-14 05:33:10',
            ],
        ]);

        Schema::table('review_details', function (Blueprint $table) {
            $table->foreign('review_content_id')
                ->references('id')
                ->on('review_contents')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('review_details', function (Blueprint $table) {
            $table->dropForeign(['review_content_id']);
        });

        Schema::dropIfExists('ng_words');
        Schema::dropIfExists('review_contents');
    }
};
