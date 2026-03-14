<?php

use Illuminate\Database\Migrations\Migration;
return new class extends Migration
{
    public function up(): void
    {
        // `bank_accounts` 統合後は account_holder_name カラムを持たない。
    }

    public function down(): void
    {
        // no-op
    }
};
