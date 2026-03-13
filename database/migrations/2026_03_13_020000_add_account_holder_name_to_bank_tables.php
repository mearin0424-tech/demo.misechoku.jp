<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->string('account_holder_name', 100)->nullable()->after('account_number');
        });

        Schema::table('bank_account_shops', function (Blueprint $table) {
            $table->string('account_holder_name', 100)->nullable()->after('account_number');
        });

        Schema::table('admin_bank_accounts', function (Blueprint $table) {
            $table->string('account_holder_name', 100)->nullable()->after('account_number');
        });
    }

    public function down(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropColumn('account_holder_name');
        });

        Schema::table('bank_account_shops', function (Blueprint $table) {
            $table->dropColumn('account_holder_name');
        });

        Schema::table('admin_bank_accounts', function (Blueprint $table) {
            $table->dropColumn('account_holder_name');
        });
    }
};
