<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->string('user_type', 32);
            $table->string('user_id', 32);
            $table->boolean('push_enabled')->default(true);
            $table->boolean('line_enabled')->default(true);
            $table->boolean('interview_reminder_enabled')->default(true);
            $table->boolean('deadline_reminder_enabled')->default(true);
            $table->timestamps();

            $table->unique(['user_type', 'user_id'], 'notification_preferences_user_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
