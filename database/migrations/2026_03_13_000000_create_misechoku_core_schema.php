<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ==============================================================================
        // 【1】 Core Entity (コア実体)
        // ==============================================================================

        Schema::create('casts', function (Blueprint $table) {
            $table->string('id', 20)->primary(); // c00000001~
            $table->string('email')->nullable()->unique();
            $table->string('password')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('identity_status')->default(1);
            $table->timestamp('last_login_at')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::create('cast_profiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('cast_id', 20);
            $table->string('nickname')->nullable();
            $table->string('name')->nullable();
            $table->string('name_kana', 100)->nullable();
            $table->date('birthday')->nullable();
            $table->tinyInteger('gender')->nullable();
            $table->string('zip', 10)->nullable();
            $table->string('pref', 10)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('addr1', 100)->nullable();
            $table->string('addr2', 100)->nullable();
            $table->string('addr3', 100)->nullable();
            $table->string('tel', 20)->nullable();
            $table->smallInteger('height')->nullable();
            $table->smallInteger('weight')->nullable();
            $table->smallInteger('bust')->nullable();
            $table->smallInteger('waist')->nullable();
            $table->smallInteger('hip')->nullable();
            $table->integer('shift')->nullable();
            $table->string('profession', 1000)->nullable();
            $table->tinyInteger('exp')->nullable();
            $table->string('years_exp', 100)->nullable();
            $table->string('where_work', 500)->nullable();
            $table->text('pr')->nullable();
            $table->text('charm_point')->nullable();
            $table->text('memo')->nullable();
            $table->text('ng_reason')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();

            $table->foreign('cast_id')
                ->references('id')
                ->on('casts')
                ->onDelete('cascade');
        });

        Schema::create('cast_providers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('cast_id', 20);
            $table->string('provider', 50)->default('line');
            $table->string('provider_id');
            $table->timestamps();

            $table->unique(['provider', 'provider_id'], 'cast_providers_provider_id_unique');
            $table->foreign('cast_id')
                ->references('id')
                ->on('casts')
                ->onDelete('cascade');
        });

        Schema::create('shops', function (Blueprint $table) {
            $table->string('id', 20)->primary(); // s00000001~
            $table->string('email')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('license_status')->default(1);
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::create('shop_profiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('shop_id', 20);
            $table->string('shop_name');
            $table->date('opened_on')->nullable();
            $table->string('zip', 20)->nullable();
            $table->string('pref');
            $table->string('city', 100)->nullable();
            $table->string('addr2');
            $table->string('addr3')->nullable();
            $table->string('tel', 20)->nullable();
            $table->string('station1')->nullable();
            $table->string('station2')->nullable();
            $table->string('catch')->nullable();
            $table->string('overview')->nullable();
            $table->text('message')->nullable();
            $table->text('memo')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();

            $table->foreign('shop_id')
                ->references('id')
                ->on('shops')
                ->onDelete('cascade');
        });

        Schema::create('shop_managers', function (Blueprint $table) {
            $table->string('id', 20)->primary(); // m00000001~
            $table->string('shop_id', 20);
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->tinyInteger('role')->default(0);
            $table->tinyInteger('status')->default(0);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();

            $table->foreign('shop_id')
                ->references('id')
                ->on('shops')
                ->onDelete('cascade');
        });

        Schema::create('shop_jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('shop_id', 20);
            $table->tinyInteger('status')->default(1);
            $table->string('hourly_wage_regular')->nullable();
            $table->integer('normal_time')->nullable();
            $table->integer('noruma_reward')->nullable();
            $table->string('noruma_reward2')->nullable();
            $table->integer('hours_day')->nullable();
            $table->string('noruma_cond', 2000)->nullable();
            $table->boolean('has_trial')->default(false);
            $table->string('trial_hourly_wage')->nullable();
            $table->boolean('has_help')->default(false);
            $table->string('help_hourly_wage')->nullable();
            $table->string('job_description')->nullable();
            $table->string('salary')->nullable();
            $table->string('atmosphere')->nullable();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();

            $table->foreign('shop_id')
                ->references('id')
                ->on('shops')
                ->onDelete('cascade');
        });

        // ==============================================================================
        // 【2】 Event (アクション履歴)
        // ==============================================================================

        Schema::create('shop_job_applications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('cast_id', 20);
            $table->unsignedBigInteger('shop_job_id');
            $table->tinyInteger('status')->default(1);
            $table->date('result_date')->nullable();
            $table->date('real_start_date')->nullable();
            $table->string('hourly_wage_regular')->nullable();
            $table->string('normal_time')->nullable();
            $table->timestamps();

            $table->foreign('cast_id')
                ->references('id')
                ->on('casts')
                ->onDelete('cascade');
            $table->foreign('shop_job_id')
                ->references('id')
                ->on('shop_jobs')
                ->onDelete('cascade');
        });

        Schema::create('application_deposits', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('shop_job_application_id');
            $table->tinyInteger('status');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->foreign('shop_job_application_id')
                ->references('id')
                ->on('shop_job_applications')
                ->onDelete('cascade');
        });

        Schema::create('application_deposit_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('application_deposit_id');
            $table->tinyInteger('status');
            $table->dateTime('status_date');
            $table->timestamp('created_at')->nullable();

            $table->foreign('application_deposit_id')
                ->references('id')
                ->on('application_deposits')
                ->onDelete('cascade');
        });

        Schema::create('favorites', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('cast_id', 20)->nullable();
            $table->string('shop_id', 20)->nullable();
            $table->tinyInteger('action_type');
            $table->timestamp('created_at')->nullable();

            $table->foreign('cast_id')
                ->references('id')
                ->on('casts')
                ->onDelete('cascade');
            $table->foreign('shop_id')
                ->references('id')
                ->on('shops')
                ->onDelete('cascade');
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('cast_id', 20);
            $table->string('shop_id', 20);
            $table->tinyInteger('sender_type');
            $table->tinyInteger('type')->default(1);
            $table->text('content');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->foreign('cast_id')
                ->references('id')
                ->on('casts')
                ->onDelete('cascade');
            $table->foreign('shop_id')
                ->references('id')
                ->on('shops')
                ->onDelete('cascade');
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('cast_id', 20);
            $table->string('shop_id', 20);
            $table->text('contents')->nullable();
            $table->decimal('eva', 3, 1)->default(0.0);
            $table->timestamp('created_at')->nullable();

            $table->foreign('cast_id')
                ->references('id')
                ->on('casts')
                ->onDelete('cascade');
            $table->foreign('shop_id')
                ->references('id')
                ->on('shops')
                ->onDelete('cascade');
        });

        Schema::create('review_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('review_id');
            $table->unsignedBigInteger('review_content_id');
            $table->decimal('score', 3, 1);

            $table->foreign('review_id')
                ->references('id')
                ->on('reviews')
                ->onDelete('cascade');
        });

        // ==============================================================================
        // 【3】 Master (システム設定)
        // ==============================================================================

        Schema::create('tags', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type', 50);
            $table->string('name');
            $table->timestamp('created_at')->nullable();
        });

        // ==============================================================================
        // 【4】 Pivot (中間テーブル)
        // ==============================================================================

        Schema::create('cast_tag', function (Blueprint $table) {
            $table->string('cast_id', 20);
            $table->unsignedBigInteger('tag_id');

            $table->primary(['cast_id', 'tag_id']);

            $table->foreign('cast_id')
                ->references('id')
                ->on('casts')
                ->onDelete('cascade');
            $table->foreign('tag_id')
                ->references('id')
                ->on('tags')
                ->onDelete('cascade');
        });

        Schema::create('shop_tag', function (Blueprint $table) {
            $table->string('shop_id', 20);
            $table->unsignedBigInteger('tag_id');

            $table->primary(['shop_id', 'tag_id']);

            $table->foreign('shop_id')
                ->references('id')
                ->on('shops')
                ->onDelete('cascade');
            $table->foreign('tag_id')
                ->references('id')
                ->on('tags')
                ->onDelete('cascade');
        });

        // ==============================================================================
        // 【5】 System (システム)
        // ==============================================================================

        Schema::create('system_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 100)->comment('管理者名');
            $table->string('email', 255)->unique()->comment('ログインメールアドレス');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 255)->comment('ハッシュ化パスワード');
            $table->string('role', 20)->default('staff')->comment('権限(admin:全機能, staff:一部機能)');
            $table->boolean('is_active')->default(true)->comment('有効フラグ(falseでログイン不可)');
            $table->rememberToken();
            $table->timestamps();
        });

        // ==============================================================================
        // 初期データ投入（ダミーデータ）
        // ==============================================================================

        DB::table('casts')->insert([
            [
                'id' => 'c00000001',
                'email' => 'cast01@example.com',
                'password' => '$2y$10$dummyhashedpassword01',
                'status' => 1,
                'identity_status' => 3,
                'last_login_at' => '2026-03-01 10:00:00',
                'created_at' => '2026-01-10 12:00:00',
                'updated_at' => '2026-03-01 10:00:00',
            ],
            [
                'id' => 'c00000002',
                'email' => 'cast02@example.com',
                'password' => '$2y$10$dummyhashedpassword02',
                'status' => 1,
                'identity_status' => 3,
                'last_login_at' => '2026-03-05 15:30:00',
                'created_at' => '2026-02-15 09:00:00',
                'updated_at' => '2026-03-05 15:30:00',
            ],
            [
                'id' => 'c00000003',
                'email' => 'cast03@example.com',
                'password' => '$2y$10$dummyhashedpassword03',
                'status' => 1,
                'identity_status' => 1,
                'last_login_at' => '2026-03-10 20:15:00',
                'created_at' => '2026-03-01 18:45:00',
                'updated_at' => '2026-03-10 20:15:00',
            ],
        ]);

        DB::table('cast_profiles')->insert([
            [
                'cast_id' => 'c00000001',
                'nickname' => 'みさき',
                'name' => '桜井美咲',
                'birthday' => '2001-05-15',
                'gender' => 1,
                'pref' => '東京都',
                'city' => '港区',
                'height' => 160,
                'weight' => 48,
                'bust' => 85,
                'waist' => 58,
                'hip' => 86,
                'profession' => 'アパレル店員',
                'exp' => 1,
                'pr' => '楽しくお話しするのが大好きです！よろしくお願いします。',
            ],
            [
                'cast_id' => 'c00000002',
                'nickname' => 'あい',
                'name' => '山田愛',
                'birthday' => '1999-10-22',
                'gender' => 1,
                'pref' => '神奈川県',
                'city' => '横浜市',
                'height' => 155,
                'weight' => 45,
                'bust' => 82,
                'waist' => 56,
                'hip' => 84,
                'profession' => '美容師',
                'exp' => 1,
                'pr' => '週末メインで働きたいです！',
            ],
            [
                'cast_id' => 'c00000003',
                'nickname' => 'ユナ',
                'name' => '佐藤結衣',
                'birthday' => '2003-02-14',
                'gender' => 1,
                'pref' => '埼玉県',
                'city' => 'さいたま市',
                'height' => 165,
                'weight' => 50,
                'bust' => 88,
                'waist' => 60,
                'hip' => 89,
                'profession' => '大学生',
                'exp' => 0,
                'pr' => '未経験ですが一生懸命頑張ります！',
            ],
        ]);

        DB::table('cast_providers')->insert([
            [
                'cast_id' => 'c00000001',
                'provider' => 'line',
                'provider_id' => 'U11112222333344445555666677778888',
            ],
            [
                'cast_id' => 'c00000002',
                'provider' => 'line',
                'provider_id' => 'Uaaaabbbbccccddddeeeeffffgggghhhh',
            ],
            [
                'cast_id' => 'c00000003',
                'provider' => 'line',
                'provider_id' => 'Uzzzzxxxxccccvvvvbbbbnnnnmmmmkkkk',
            ],
        ]);

        DB::table('shops')->insert([
            [
                'id' => 's00000001',
                'email' => 'info@club-luminous.example.com',
                'status' => 1,
                'license_status' => 3,
                'created_at' => '2025-12-01 10:00:00',
                'updated_at' => '2025-12-05 10:00:00',
            ],
            [
                'id' => 's00000002',
                'email' => 'contact@lounge-stella.example.com',
                'status' => 1,
                'license_status' => 3,
                'created_at' => '2026-01-15 14:00:00',
                'updated_at' => '2026-01-20 14:00:00',
            ],
        ]);

        DB::table('shop_profiles')->insert([
            [
                'shop_id' => 's00000001',
                'shop_name' => 'Club Luminous (ルミナス)',
                'opened_on' => '2015-04-01',
                'pref' => '東京都',
                'city' => '港区',
                'addr2' => '六本木3-1-1',
                'addr3' => 'ルミナスビル2F',
                'station1' => '六本木駅 徒歩3分',
                'catch' => '落ち着いた雰囲気の高級クラブ',
                'overview' => '未経験からでもしっかりサポートする安心の環境です。',
                'message' => '一緒に楽しく働ける方をお待ちしております！',
            ],
            [
                'shop_id' => 's00000002',
                'shop_name' => 'Lounge Stella (ステラ)',
                'opened_on' => '2020-09-15',
                'pref' => '東京都',
                'city' => '新宿区',
                'addr2' => '歌舞伎町1-2-3',
                'addr3' => 'ステラタワー5F',
                'station1' => '新宿駅 徒歩5分',
                'catch' => 'アットホームで働きやすいラウンジ',
                'overview' => 'ノルマなし！あなたのペースで働けます。',
                'message' => '学生さんやWワークの方も大歓迎です。',
            ],
        ]);

        DB::table('shop_managers')->insert([
            [
                'id' => 'm00000001',
                'shop_id' => 's00000001',
                'name' => '佐藤 店長',
                'email' => 'sato.mgr@club-luminous.example.com',
                'password' => '$2y$10$dummyhashedpasswordM1',
                'role' => 1,
                'status' => 1,
                'last_login_at' => '2026-03-12 18:00:00',
            ],
            [
                'id' => 'm00000002',
                'shop_id' => 's00000002',
                'name' => '鈴木 オーナー',
                'email' => 'suzuki.owner@lounge-stella.example.com',
                'password' => '$2y$10$dummyhashedpasswordM2',
                'role' => 1,
                'status' => 1,
                'last_login_at' => '2026-03-11 22:30:00',
            ],
        ]);

        DB::table('shop_jobs')->insert([
            [
                'id' => 1,
                'shop_id' => 's00000001',
                'hourly_wage_regular' => '5000',
                'normal_time' => 5,
                'has_trial' => true,
                'trial_hourly_wage' => '4000',
                'has_help' => true,
                'help_hourly_wage' => '3500',
                'job_description' => 'お客様と楽しくおしゃべりしてお酒を作るお仕事です。',
                'created_at' => '2025-12-05 12:00:00',
                'updated_at' => '2025-12-05 12:00:00',
            ],
            [
                'id' => 2,
                'shop_id' => 's00000002',
                'hourly_wage_regular' => '3500',
                'normal_time' => 4,
                'has_trial' => true,
                'trial_hourly_wage' => '3000',
                'has_help' => false,
                'help_hourly_wage' => null,
                'job_description' => '簡単なドリンク作成と接客をお任せします。ノルマなし！',
                'created_at' => '2026-01-20 15:00:00',
                'updated_at' => '2026-01-20 15:00:00',
            ],
        ]);

        DB::table('shop_job_applications')->insert([
            [
                'id' => 1,
                'cast_id' => 'c00000001',
                'shop_job_id' => 1,
                'status' => 4,
                'result_date' => '2026-01-15',
                'hourly_wage_regular' => '5000',
                'created_at' => '2026-01-10 15:30:00',
                'updated_at' => '2026-01-15 18:00:00',
            ],
            [
                'id' => 2,
                'cast_id' => 'c00000002',
                'shop_job_id' => 2,
                'status' => 3,
                'result_date' => null,
                'hourly_wage_regular' => '3500',
                'created_at' => '2026-03-05 18:00:00',
                'updated_at' => '2026-03-06 12:00:00',
            ],
            [
                'id' => 3,
                'cast_id' => 'c00000003',
                'shop_job_id' => 1,
                'status' => 1,
                'result_date' => null,
                'hourly_wage_regular' => '5000',
                'created_at' => '2026-03-10 21:00:00',
                'updated_at' => '2026-03-10 21:00:00',
            ],
        ]);

        DB::table('application_deposits')->insert([
            [
                'id' => 1,
                'shop_job_application_id' => 1,
                'status' => 6,
                'created_at' => '2026-02-15 10:00:00',
                'updated_at' => '2026-02-20 15:00:00',
            ],
        ]);

        DB::table('application_deposit_histories')->insert([
            [
                'id' => 1,
                'application_deposit_id' => 1,
                'status' => 1,
                'status_date' => '2026-02-15 10:00:00',
            ],
            [
                'id' => 2,
                'application_deposit_id' => 1,
                'status' => 2,
                'status_date' => '2026-02-16 11:30:00',
            ],
            [
                'id' => 3,
                'application_deposit_id' => 1,
                'status' => 6,
                'status_date' => '2026-02-20 15:00:00',
            ],
        ]);

        DB::table('favorites')->insert([
            [
                'cast_id' => 'c00000001',
                'shop_id' => 's00000002',
                'action_type' => 1,
                'created_at' => '2026-01-12 20:00:00',
            ],
            [
                'cast_id' => 'c00000002',
                'shop_id' => 's00000001',
                'action_type' => 3,
                'created_at' => '2026-02-10 21:15:00',
            ],
        ]);

        DB::table('messages')->insert([
            [
                'cast_id' => 'c00000002',
                'shop_id' => 's00000002',
                'sender_type' => 1,
                'content' => '面接をお願いしたいです！',
                'is_read' => true,
                'created_at' => '2026-03-05 18:05:00',
            ],
            [
                'cast_id' => 'c00000002',
                'shop_id' => 's00000002',
                'sender_type' => 2,
                'content' => 'ご応募ありがとうございます。今週の土曜日の19時はいかがでしょうか？',
                'is_read' => true,
                'created_at' => '2026-03-05 19:00:00',
            ],
            [
                'cast_id' => 'c00000003',
                'shop_id' => 's00000001',
                'sender_type' => 1,
                'content' => '未経験ですが応募可能でしょうか？',
                'is_read' => false,
                'created_at' => '2026-03-10 21:05:00',
            ],
        ]);

        DB::table('reviews')->insert([
            [
                'id' => 1,
                'cast_id' => 'c00000001',
                'shop_id' => 's00000001',
                'contents' => 'スタッフの皆さんが優しくて、とても働きやすいお店でした！',
                'created_at' => '2026-02-28 10:00:00',
            ],
        ]);

        DB::table('review_details')->insert([
            ['review_id' => 1, 'review_content_id' => 1, 'score' => 5.0],
            ['review_id' => 1, 'review_content_id' => 2, 'score' => 5.0],
            ['review_id' => 1, 'review_content_id' => 3, 'score' => 4.0],
            ['review_id' => 1, 'review_content_id' => 4, 'score' => 5.0],
        ]);

        DB::table('tags')->insert([
            ['id' => 1, 'type' => 'salary', 'name' => '1ヶ月払い', 'created_at' => '2025-01-14 05:33:11'],
            ['id' => 8, 'type' => 'salary', 'name' => '交通費支給', 'created_at' => '2025-01-14 05:33:12'],
            ['id' => 14, 'type' => 'howto', 'name' => '週1からOK', 'created_at' => '2025-01-14 05:33:12'],
            ['id' => 82, 'type' => 'casttag', 'name' => 'スレンダー', 'created_at' => '2025-01-14 05:33:13'],
            ['id' => 89, 'type' => 'casttag', 'name' => 'キレイ系', 'created_at' => '2025-01-14 05:33:13'],
        ]);

        DB::table('cast_tag')->insert([
            ['cast_id' => 'c00000001', 'tag_id' => 82],
            ['cast_id' => 'c00000001', 'tag_id' => 89],
            ['cast_id' => 'c00000002', 'tag_id' => 82],
        ]);

        DB::table('shop_tag')->insert([
            ['shop_id' => 's00000001', 'tag_id' => 8],
            ['shop_id' => 's00000002', 'tag_id' => 14],
        ]);

        DB::table('system_accounts')->insert([
            [
                'name' => '管理者アカウント１',
                'email' => 'admin@misechoku.jp',
                'password' => '$2y$10$dummyhashedpasswordAdmin01',
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => '2025-01-01 00:00:00',
                'created_at' => '2025-01-01 00:00:00',
                'updated_at' => '2025-01-01 00:00:00',
            ],
        ]);
    }

    public function down(): void
    {
        // 外部キー依存関係の逆順で削除
        Schema::dropIfExists('shop_tag');
        Schema::dropIfExists('cast_tag');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('review_details');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('application_deposit_histories');
        Schema::dropIfExists('application_deposits');
        Schema::dropIfExists('shop_job_applications');
        Schema::dropIfExists('shop_jobs');
        Schema::dropIfExists('shop_managers');
        Schema::dropIfExists('shop_profiles');
        Schema::dropIfExists('shops');
        Schema::dropIfExists('cast_providers');
        Schema::dropIfExists('cast_profiles');
        Schema::dropIfExists('casts');
        Schema::dropIfExists('system_accounts');
    }
};

