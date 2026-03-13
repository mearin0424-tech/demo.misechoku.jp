<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('member_id', 20)->unique();
            $table->string('bank_name', 100);
            $table->string('branch_name', 100)->nullable();
            $table->string('account_type', 20);
            $table->string('account_number', 30);
            $table->string('account_name', 100);
            $table->timestamps();

            $table->foreign('member_id')
                ->references('id')
                ->on('casts')
                ->onDelete('cascade');
        });

        Schema::create('bank_account_shops', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('shop_id', 20)->unique();
            $table->string('bank_name', 100);
            $table->string('branch_name', 100)->nullable();
            $table->string('account_type', 20);
            $table->string('account_number', 30);
            $table->string('account_name', 100);
            $table->timestamps();

            $table->foreign('shop_id')
                ->references('id')
                ->on('shops')
                ->onDelete('cascade');
        });

        Schema::create('admin_bank_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('bank_name', 100);
            $table->string('branch_name', 100)->nullable();
            $table->string('account_type', 20);
            $table->string('account_number', 30);
            $table->string('account_name', 100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('application_deposits', function (Blueprint $table) {
            $table->string('invoice_number', 50)->nullable()->after('is_read');
            $table->integer('bonus_amount')->nullable()->after('invoice_number');
            $table->integer('system_fee_amount')->nullable()->after('bonus_amount');
            $table->integer('invoice_amount')->nullable()->after('system_fee_amount');
            $table->integer('cast_transfer_amount')->nullable()->after('invoice_amount');
            $table->timestamp('invoice_issued_at')->nullable()->after('cast_transfer_amount');
            $table->date('invoice_due_date')->nullable()->after('invoice_issued_at');
            $table->timestamp('invoice_sent_at')->nullable()->after('invoice_due_date');
            $table->timestamp('shop_payment_reported_at')->nullable()->after('invoice_sent_at');
            $table->integer('shop_payment_reported_amount')->nullable()->after('shop_payment_reported_at');
            $table->string('shop_payment_reference', 255)->nullable()->after('shop_payment_reported_amount');
            $table->timestamp('shop_payment_confirmed_at')->nullable()->after('shop_payment_reference');
            $table->timestamp('cast_transferred_at')->nullable()->after('shop_payment_confirmed_at');
            $table->string('cast_transfer_reference', 255)->nullable()->after('cast_transferred_at');
            $table->text('cast_transfer_note')->nullable()->after('cast_transfer_reference');
            $table->timestamp('completed_at')->nullable()->after('cast_transfer_note');
        });

        DB::table('admin_bank_accounts')->insert([
            'bank_name' => 'みせちょく銀行',
            'branch_name' => '本店営業部',
            'account_type' => 'ordinary',
            'account_number' => '1234567',
            'account_name' => 'ミセチョク ウンエイ',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('bank_account_shops')->insert([
            [
                'shop_id' => 's00000001',
                'bank_name' => '六本木銀行',
                'branch_name' => '六本木支店',
                'account_type' => 'ordinary',
                'account_number' => '7654321',
                'account_name' => 'クラブルミナス',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'shop_id' => 's00000002',
                'bank_name' => '新宿銀行',
                'branch_name' => '歌舞伎町支店',
                'account_type' => 'ordinary',
                'account_number' => '1122334',
                'account_name' => 'ラウンジステラ',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('bank_accounts')->insert([
            [
                'member_id' => 'c00000001',
                'bank_name' => '渋谷銀行',
                'branch_name' => '青山支店',
                'account_type' => 'ordinary',
                'account_number' => '2200113',
                'account_name' => 'サクライ ミサキ',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'member_id' => 'c00000002',
                'bank_name' => '横浜銀行',
                'branch_name' => '横浜中央支店',
                'account_type' => 'ordinary',
                'account_number' => '3344556',
                'account_name' => 'ヤマダ アイ',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('application_deposits')
            ->where('id', 1)
            ->update([
                'invoice_number' => 'INV-202602-0001',
                'bonus_amount' => 30000,
                'system_fee_amount' => 3000,
                'invoice_amount' => 33000,
                'cast_transfer_amount' => 30000,
                'invoice_issued_at' => '2026-02-16 11:30:00',
                'invoice_due_date' => '2026-02-23',
                'invoice_sent_at' => '2026-02-16 11:35:00',
                'shop_payment_reported_at' => '2026-02-19 10:15:00',
                'shop_payment_reported_amount' => 33000,
                'shop_payment_reference' => 'RCP-20260219-01',
                'shop_payment_confirmed_at' => '2026-02-20 10:00:00',
                'cast_transferred_at' => '2026-02-20 14:30:00',
                'cast_transfer_reference' => 'TRF-20260220-01',
                'cast_transfer_note' => '窓口振込を実施済み',
                'completed_at' => null,
            ]);
    }

    public function down(): void
    {
        Schema::table('application_deposits', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_number',
                'bonus_amount',
                'system_fee_amount',
                'invoice_amount',
                'cast_transfer_amount',
                'invoice_issued_at',
                'invoice_due_date',
                'invoice_sent_at',
                'shop_payment_reported_at',
                'shop_payment_reported_amount',
                'shop_payment_reference',
                'shop_payment_confirmed_at',
                'cast_transferred_at',
                'cast_transfer_reference',
                'cast_transfer_note',
                'completed_at',
            ]);
        });

        Schema::dropIfExists('admin_bank_accounts');
        Schema::dropIfExists('bank_account_shops');
        Schema::dropIfExists('bank_accounts');
    }
};
