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
            $table->string('content')->comment('設問内容');
            $table->boolean('del_flg')->default(false)->comment('削除フラグ');
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
                'content' => '接客',
                'del_flg' => false,
                'created_at' => '2025-01-14 05:33:10',
                'updated_at' => '2025-01-14 05:33:10',
            ],
            [
                'id' => 2,
                'content' => '雰囲気',
                'del_flg' => false,
                'created_at' => '2025-01-14 05:33:10',
                'updated_at' => '2025-01-14 05:33:10',
            ],
            [
                'id' => 3,
                'content' => '給与条件',
                'del_flg' => false,
                'created_at' => '2025-01-14 05:33:10',
                'updated_at' => '2025-01-14 05:33:10',
            ],
            [
                'id' => 4,
                'content' => '働きやすさ',
                'del_flg' => false,
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

        $reviewContentColumn = Schema::hasColumn('review_details', 'val') ? 'val' : 'review_content_id';

        Schema::table('review_details', function (Blueprint $table) use ($reviewContentColumn) {
            $table->foreign($reviewContentColumn)
                ->references('id')
                ->on('review_contents')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        $reviewContentColumn = Schema::hasColumn('review_details', 'val') ? 'val' : 'review_content_id';

        Schema::table('review_details', function (Blueprint $table) use ($reviewContentColumn) {
            $table->dropForeign([$reviewContentColumn]);
        });

        Schema::dropIfExists('ng_words');
        Schema::dropIfExists('review_contents');
    }
};
