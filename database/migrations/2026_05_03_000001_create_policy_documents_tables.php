<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policy_documents', function (Blueprint $table) {
            $table->id();
            $table->string('key', 32)->unique()->comment('about / terms / privacy');
            $table->string('title', 200)->comment('ページタイトル（運営協会、利用規約、プライバシーポリシー）');
            $table->string('lead_title', 200)->nullable()->comment('リード見出し（例: GREETING / 理事長 挨拶）');
            $table->text('lead_body')->nullable()->comment('リード本文（運営協会の挨拶文等）');
            $table->json('meta')->nullable()->comment('協会概要（協会名／資本金 等）');
            $table->boolean('is_locked')->default(true)->comment('既定はロック状態（編集不可）');
            $table->unsignedBigInteger('updated_by_id')->nullable()->comment('最終更新者の system_account.id');
            $table->string('updated_by_name', 120)->nullable()->comment('最終更新者の表示名');
            $table->timestamp('content_updated_at')->nullable()->comment('最終更新日時（コンテンツの実質更新時刻）');
            $table->timestamps();
        });

        Schema::create('policy_chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('policy_document_id')
                ->constrained('policy_documents')
                ->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('title', 200);
            $table->longText('body');
            $table->timestamps();

            $table->index(['policy_document_id', 'sort_order']);
        });

        Schema::create('policy_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('policy_document_id')
                ->constrained('policy_documents')
                ->cascadeOnDelete();
            $table->string('action', 32)->comment('created / updated / locked / unlocked');
            $table->string('summary', 500)->nullable()->comment('変更内容の要約');
            $table->json('snapshot')->nullable()->comment('更新後スナップショット');
            $table->unsignedBigInteger('updated_by_id')->nullable();
            $table->string('updated_by_name', 120)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['policy_document_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policy_revisions');
        Schema::dropIfExists('policy_chapters');
        Schema::dropIfExists('policy_documents');
    }
};
