<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 振込作業のフェイルセーフ用。1 application_deposit = 1 PaymentTask（UNIQUE で二重支払い防止）
     */
    public function up(): void
    {
        Schema::create('payment_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_deposit_id');
            $table->tinyInteger('status')->default(0)->comment('0=待機 1=支払準備中 2=振込中 3=支払済 4=無効');
            $table->unsignedInteger('shop_received_amount')->comment('店舗入金額スナップショット');
            $table->unsignedInteger('platform_fee_amount')->default(0)->comment('プラットフォーム手数料');
            $table->unsignedInteger('bank_fee_amount')->default(0)->comment('銀行振込手数料');
            $table->unsignedInteger('payout_amount')->comment('キャスト振込額（自動計算）');
            $table->timestamp('transferred_at')->nullable()->comment('振込作業完了日時');
            $table->timestamp('completed_at')->nullable()->comment('支払済確定日時');
            $table->string('evidence_file_path', 500)->nullable()->comment('振込完了証跡画像');
            $table->boolean('checklist_confirmed_account')->default(false);
            $table->boolean('checklist_confirmed_amount')->default(false);
            $table->string('operator_id', 20)->nullable()->comment('振込作業担当者ID');
            $table->boolean('refund_required')->default(false)->comment('要返金フラグ');
            $table->timestamps();

            $table->unique('application_deposit_id');
            $table->foreign('application_deposit_id')->references('id')->on('application_deposits')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_tasks');
    }
};
