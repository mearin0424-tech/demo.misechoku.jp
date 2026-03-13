<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('talk_blocks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('cast_id', 20);
            $table->string('shop_id', 20);
            $table->string('blocked_by', 10);
            $table->timestamps();

            $table->unique(['cast_id', 'shop_id']);
            $table->foreign('cast_id')
                ->references('id')
                ->on('casts')
                ->onDelete('cascade');
            $table->foreign('shop_id')
                ->references('id')
                ->on('shops')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('talk_blocks');
    }
};
