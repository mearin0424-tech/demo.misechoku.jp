<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_posts', function (Blueprint $table) {
            $table->id();
            $table->string('shop_id', 32);
            $table->unsignedTinyInteger('type')->default(2)->comment('1: 求人投稿 / 2: 店舗のひとこと');
            $table->text('body')->nullable()->comment('店舗のひとこと等');
            $table->timestamps();

            $table->index(['shop_id', 'type']);
            $table->foreign('shop_id')
                ->references('id')
                ->on('shops')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_posts');
    }
};
