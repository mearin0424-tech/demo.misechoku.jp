<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_search_locations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('owner_type', 16)->comment('cast または shop');
            $table->string('owner_id', 20);
            $table->string('mode', 16)->nullable()->comment('profile / passport / current');
            $table->text('passport_address')->nullable();
            $table->decimal('passport_latitude', 10, 7)->nullable();
            $table->decimal('passport_longitude', 10, 7)->nullable();
            $table->string('passport_label', 80)->nullable();
            $table->smallInteger('max_distance_km')->nullable()->comment('0=制限なし、>0 で半径 km');
            $table->timestamps();

            $table->unique(['owner_type', 'owner_id'], 'uq_user_search_locations_owner');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_search_locations');
    }
};
