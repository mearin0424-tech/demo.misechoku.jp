<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cast_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('cast_profiles', 'search_location_mode')) {
                $table->string('search_location_mode', 16)->nullable()->after('longitude');
            }
            if (!Schema::hasColumn('cast_profiles', 'search_passport_address')) {
                $table->text('search_passport_address')->nullable()->after('search_location_mode');
            }
            if (!Schema::hasColumn('cast_profiles', 'search_passport_latitude')) {
                $table->decimal('search_passport_latitude', 10, 7)->nullable()->after('search_passport_address');
            }
            if (!Schema::hasColumn('cast_profiles', 'search_passport_longitude')) {
                $table->decimal('search_passport_longitude', 10, 7)->nullable()->after('search_passport_latitude');
            }
            if (!Schema::hasColumn('cast_profiles', 'search_passport_label')) {
                $table->string('search_passport_label', 80)->nullable()->after('search_passport_longitude');
            }
            if (!Schema::hasColumn('cast_profiles', 'search_max_distance_km')) {
                $table->smallInteger('search_max_distance_km')->nullable()->after('search_passport_label');
            }
        });

        Schema::table('shop_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('shop_profiles', 'search_location_mode')) {
                $table->string('search_location_mode', 16)->nullable()->after('longitude');
            }
            if (!Schema::hasColumn('shop_profiles', 'search_passport_address')) {
                $table->text('search_passport_address')->nullable()->after('search_location_mode');
            }
            if (!Schema::hasColumn('shop_profiles', 'search_passport_latitude')) {
                $table->decimal('search_passport_latitude', 10, 7)->nullable()->after('search_passport_address');
            }
            if (!Schema::hasColumn('shop_profiles', 'search_passport_longitude')) {
                $table->decimal('search_passport_longitude', 10, 7)->nullable()->after('search_passport_latitude');
            }
            if (!Schema::hasColumn('shop_profiles', 'search_passport_label')) {
                $table->string('search_passport_label', 80)->nullable()->after('search_passport_longitude');
            }
            if (!Schema::hasColumn('shop_profiles', 'search_max_distance_km')) {
                $table->smallInteger('search_max_distance_km')->nullable()->after('search_passport_label');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cast_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'search_location_mode',
                'search_passport_address',
                'search_passport_latitude',
                'search_passport_longitude',
                'search_passport_label',
                'search_max_distance_km',
            ]);
        });
        Schema::table('shop_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'search_location_mode',
                'search_passport_address',
                'search_passport_latitude',
                'search_passport_longitude',
                'search_passport_label',
                'search_max_distance_km',
            ]);
        });
    }
};
