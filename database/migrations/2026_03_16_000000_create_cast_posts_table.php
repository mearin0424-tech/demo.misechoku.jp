<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cast_posts', function (Blueprint $table) {
            $table->id();
            $table->string('cast_id', 20)->unique();
            $table->text('body')->nullable()->comment('ひとこと');
            $table->timestamps();

            $table->foreign('cast_id')
                ->references('id')
                ->on('casts')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cast_posts');
    }
};
