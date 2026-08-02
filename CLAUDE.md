# ミセチョク — Claude Code プロジェクト設定

## プロジェクト概要

水商売・夜職特化のマッチングプラットフォーム。
キャスト（求職者）と店舗（求人）をマッチングし、採用ボーナスの請求・振込まで一貫して管理するサービス。

- **フレームワーク**: Laravel（PHP）
- **テンプレートエンジン**: Blade
- **フロントエンド**: Vanilla JS（一部 jQuery）、Tailwind CSS v4（CSS-first config / @tailwindcss/cli）
- **ビルド**: Tailwind は CLI で `public/assets/css/tailwind.css` に直接出力（Vite はバイパス運用中）
- **DB**: MySQL 8.0
- **デプロイ先**: Plesk サーバ
- **認証**: キャスト / 店舗 / 管理者 の3ロール

---

## ディレクトリ構成（主要部分）

```
app/
  Http/
    Controllers/
      Admin/          # 管理者ポータル
      Auth/Cast/      # キャスト認証
      Auth/Shop/      # 店舗認証
      Casts/          # キャスト向け機能
      Shops/          # 店舗向け機能
      Common/         # 共通機能（トーク、登録、プラン契約）
      Api/            # Push通知、銀行検索API
    Services/
      BillingManagementService.php    # 請求・振込ロジック
      DocumentReviewService.php       # 書類審査ロジック
      PlanSubscriptionService.php     # Premiumプラン契約・機能ゲート
      ProfileViewService.php          # プロフィール閲覧記録
      CharacterGuideService.php       # オコジョガイド（画面別説明）
      UserTaskService.php             # やることリスト（都度計算）
  Models/
    Cast.php / Shop.php / Admin.php
    CastProfile.php / ShopProfile.php
    ShopJob.php / ShopJobApplication.php
    Message.php / TalkBlock.php
    ApplicationDeposit.php
    ProfileView.php                   # プロフィール閲覧ログ
    ShopPlanSubscription.php          # 店舗Premiumプラン契約

resources/views/
  layouts/           # 共通レイアウト（app-v2 が現行の唯一のフロント用レイアウト）
    parts/           # header / footer / sidebar / character-guide
  common/            # ロール共通ビュー（トーク、検索、設定、サポート）
  casts/             # キャスト向けビュー
  shops/             # 店舗向けビュー
  admin/             # 管理者ビュー（layouts.admin を使用）
  billing/           # 請求書・領収書テンプレート（PDF/印刷HTML）
  lp/                # LPページ

resources/css/
  app.css            # Tailwind v4 の @theme トークン正本

public/assets/
  css/
    tailwind.css              # Tailwind CLI の出力先（Blade を @source で走査）
    light-theme.css           # ライトモード上書き（§1-14）
    admin-mobile.css          # 管理画面モバイル最適化
    ui-consistency.css        # ボタン・バッジ・共通部品の統一
    home.css / search.css / talk.css / mypage.css / recruitment.css …
  js/
    home.js / search.js / talk-room.js / talk-list.js / character-guide.js …
```

---

## ロール・ルートの対応

| ロール | URLプレフィックス | ミドルウェア |
|--------|-----------------|-------------|
| キャスト | `/cast/*` | `member.auth` |
| 店舗 | `/shop/*` | `shop.auth` |
| 管理者 | `/admin/*` | `admin.auth` + `admin.permission:*` |
| 共通 | `/setting/*`, `/subscription`, `/support/*`, `/api/*` | ログイン共通 |

---

## 開発ルール

### テスト
- テスト方針・実装ルールは `AUTO-TEST.md` を参照（未整備の場合はスキップ）

### テスト用機能（本番デプロイ時に除外すべきもの）
本番運用では意図しない動作/情報漏洩を招く可能性があるため、以下は「**テスト用**」と明記して開発・
デモ用途に限定する。新規追加時も同じルールで**用途と本番での扱い**をコメント/ドキュメントに残すこと。

- **デモ用ログイン画面** — `/login/demo`（`App\Http\Controllers\Common\DemoLoginController`、view `resources/views/common/demo-login.blade.php`）。
  1画面から複数ロール（cast / shop / admin）へワンクリックログインできる利便性優先の画面で、
  正式なログイン画面（`cast.login` / `shop.login` / `admin.login`）とは別扱い。**本番デプロイ時は
  ルート除外 or `.env` フラグでガード**すること。
- **テスト用データセット** — `database/test_reset.sql`。全ユーザースケールのテーブルを TRUNCATE
  してから、`cast01@test.jp` … `shop01@test.jp` … などのテスト用パーソナを投入。
  **本番 DB には絶対に流さない**。
- **`@test.jp` ドメインのメールアドレス** — テスト用アカウントで使用中。本番の実ユーザー登録時に
  意図せず同ドメインが混入しないよう、必要に応じて登録バリデーションで拒否できるよう検討。
- **外部プレースホルダー画像 URL**（`randomuser.me` / `loremflickr.com` / `picsum.photos`）—
  テストデータの `cast_images.image_path` / `shop_images.image_path` に格納されている URL は
  検証用のフリー素材。本番運用ではキャスト・店舗が自身でアップロードした画像パスに置き換わる。
- 新しくテスト用機能・エンドポイント・シード等を追加する場合は、**ファイル冒頭のコメントに「テスト用」と明記**し、
  本セクションにも追記すること。

### エージェント運用
- Claude Code のサブエージェント（Explore/Plan/general-purpose）の使い分けは `Agent.md` を参照

### Git
- Gitコマンドは開発者が個別に実行する。Claudeはgit操作を一切行わない。
- コミットメッセージの形式やブランチ命名などの規約は以下のとおり：
  - ブランチ: `feature/SCR-xxx-機能名`
  - コミット prefix: `feat` / `fix` / `refactor` / `style` / `test` / `docs`
  - 例: `feat(SCR-xxx): 機能名の実装`

### DB操作
- DB操作はClaudeが直接実行しない。
- マイグレーションやデータ変更が必要な場合、**MySQL 8.0 対応のSQL文を生成して開発者に渡し、実行を促す**。
- スキーマ変更（CREATE TABLE / ALTER TABLE / DROP 等）を行った場合、`database/schema.sql` と `database/mock_demo.sql` を **両方** 最新化する。
- 生クエリは避け、Eloquent ORM を使用する。DDL変更は新規マイグレーションで対応。
- **最新のテーブル構造は `database/mock_demo.sql` を参照**。コード・ロジック・テストを書く際は必ずこのファイルのスキーマと照合し、カラム名・型・制約の不整合があれば指摘または修正すること。
- **`database/` に置く SQL は 3 種類のみ**：
  1. `schema.sql` — スキーマ定義（初期構築時）
  2. `mock_demo.sql` — スキーマ + 参照テーブル + 最小データ（初期構築時。schema.sql の代わりにこちらを流せば全部入る）
  3. `test_reset.sql` — テストデータ投入（`mock_demo.sql` を流した後で使用）
- 追加のマイグレーション用 SQL（ALTER TABLE 等）は**上記 3 ファイルに直接反映**する。単発 SQL ファイルを新設して残さない（`schema.sql` / `mock_demo.sql` の CREATE 定義を書き換え、既に本番デプロイ済みなら開発者に手動 ALTER コマンドを口頭/PR 説明で伝える運用）。

### デザイン
- デザインは `DESIGN.md` の定義に従う。コード・ビュー生成時は必ず参照すること。
- 深階層ページのタイトルは **ヘッダー中央（日本語）** に統一。ページ内の h1・説明文は置かない。
- 説明文は **オコジョガイド**（`character_guide_settings` テーブル + `resources/views/layouts/parts/character-guide.blade.php`）に集約する。ページ内に `p.page-lead` 等を新設しない。
- 画面テーマ（ライト/ダーク）は `layouts/app-v2.blade.php` の `$isLightTheme` で自動判定される：
  - **ダーク画面**: `*/home*` / `*/mypage*`（一部除外あり）/ `*/castprofileview/*` / `*/shopprofiles/*` / `share/*` / 認証系
  - **ライト画面**: 上記以外（検索・トーク・採用/入金管理・書類・設定・サポート・コラム・Premium画面）
  - ライト画面向けの上書きは `public/assets/css/light-theme.css` に集約（§1トークン反転 / §2クローム維持 / §3-14 個別コンポーネント）

### Tailwind CSS（v4）
- Tailwind v4 を使用。`tailwind.config.js` は **使わない**（v4 から CSS-first config に変更）。
- 設定の正本は `resources/css/app.css`:
  - `@import "tailwindcss";` で本体を読み込む
  - `@source "../views";` で Blade を走査対象に追加（`resources/js` 等も必要に応じて `@source` で追記）
  - 色・影などのデザイントークンは `@theme { ... }` ブロックに集約（例: `--color-gold: #c5a059;`）
- ビルドは **`@tailwindcss/cli` 直叩き** で `public/assets/css/tailwind.css` に出力（Vite は使わない）。
  - 本番: `npm run tw:build`（minify あり）
  - 開発: `npm run tw:watch`（差分監視）
- ビュー側からは `<link rel="stylesheet" href="{{ asset('assets/css/tailwind.css') }}">` で読み込む。
  - すでに `layouts/app.blade.php` / `layouts/admin.blade.php` / `layouts/lp.blade.php` に組み込み済み。
  - 既存のページ固有 CSS（`asset('assets/css/*.css')`）**より前** に置くこと（preflight を既存スタイルで上書き可能にして回帰を防ぐ）。
- PostCSS 設定や `@tailwind base/components/utilities` 旧ディレクティブは **書かない**（v4 では不要）。
- Blade を新規追加・移動したら `npm run tw:build` を必ず再実行（CLI は起動時の `@source` 走査結果でクラスを抽出するため）。

### 静的アセットのキャッシュバスト
- 全アセットは `?v=YYYYMMDD-識別子` 付きで読み込む。
- グローバルは `layouts/app-v2.blade.php` の `$assetVersion` を進める（例: `20260719-deep-pages`）。個別 CSS/JS は個別に `?v=` を付ける。
- Blade 変更後は毎回 `php artisan view:clear` を実行する。

### コーディング規約
- PSR-12 準拠（PHP）
- コントローラは薄く、ロジックはサービスクラスに集約
- バリデーションは FormRequest または `$request->validate()` を使用
- Blade テンプレートは `@component` / `@include` を活用

### 命名規則
- コントローラ: `PascalCase` + `Controller` suffix
- モデル: `PascalCase` 単数形
- テーブル: `snake_case` 複数形
- ルート名: `role.resource.action`（例: `shop.recruits.edit`）
- JS ファイル: `kebab-case.js`

### 重要な業務ロジック
- **採用ボーナス**: `application_deposits` テーブルで管理、ステータス履歴は `application_deposit_histories`
- **トークアクション**: `TalkController@action` の `action_type` は `interview_offer / interview_confirm / hired / rejected / cancel_status` のみ
- **メッセージ削除**: 送信から10分以内、自分のメッセージ、type=TEXT のみ削除可
- **ブロック**: `talk_blocks`（cast_id, shop_id）に複合UNIQUE制約
- **NGワード**: `ng_words` テーブル、レビュー・メッセージに適用
- **プロフィール閲覧**: `profile_views`（viewer_type/id, target_type/id）で全閲覧を記録。閲覧数は `ProfileViewService::countFor()` で集計、閲覧者一覧は Premium 店舗向けに提供
- **Premiumプラン**（店舗向け・月¥20,000／年¥200,000）: `shop_plan_subscriptions` テーブル。銀行振込 + 運営の目視確認で有効化（`STATUS_PENDING_PAYMENT` → `STATUS_ACTIVE`）
  - 提供機能: ①AIレコメンド優先表示（キャストの店舗検索 relevance ソートで上位グループ）、②求人閲覧キャスト一覧（`/shop/mypage/viewers`）、③スカウト送信上限緩和（無料5件/日 → Premium30件/日、既存やりとりは無制限）
  - 実装: `PlanSubscriptionService`（契約・入金確認・機能ゲート）
  - 請求書番号 `PLN-YYYYMM-xxxx` / 領収書番号 `RCT-YYYYMM-xxxx`
- **オコジョガイド**: `character_guide_settings`（route_name UNIQUE）に画面別 ON/OFF・メッセージを保存。ページ内説明文の正本

---

## 画面ID リファレンス（主要）

| 画面ID | URL | 説明 |
|--------|-----|------|
| SCR-100 | /shop/home | 店舗ホーム |
| SCR-101 | /shop/search/{tab} | キャスト検索 |
| SCR-200 | /cast/home | キャストホーム |
| SCR-201 | /cast/search/{tab} | 店舗検索+AI（Premium優先表示あり） |
| SCR-111 | /shop/recruits/status | 求人ステータス |
| SCR-112 | /shop/recruits/edit | 求人票編集 |
| SCR-204 | /cast/mypage | キャストマイページ |
| SCR-300 | /admin | 管理ダッシュボード |
| SCR-303 | /admin/deposits | 入金・振込管理 |
| —      | /admin/plans | Premiumプラン入金管理 |
| —      | /subscription | Premiumプラン申込・請求書/領収書DL（店舗） |
| —      | /shop/mypage/viewers | 閲覧キャスト一覧（Premium限定） |
| —      | /shop/mypage/management | 店舗：採用・入金管理 |
| —      | /cast/mypage/management | キャスト：採用・入金管理 |
| —      | /cast/mypage/identity | 本人確認 |
| —      | /shop/mypage/documents | 許可証の提出・管理 |

---

## 主要ページのテーマ振り分け（app-v2）

`$isLightTheme` の判定ロジック:

```php
$isDarkPage = request()->is('*/home*')
    || (request()->is('*/mypage*')
        && !request()->is('*/mypage/management*')
        && !request()->is('*/mypage/identity*')
        && !request()->is('*/mypage/documents*')
        && !request()->is('*/mypage/viewers*'))
    || request()->is('*/castprofileview/*')
    || request()->is('*/shopprofiles/*')
    || request()->is('share/*');
$isLightTheme = !$isDarkPage && !認証系;
```

---

## 注意事項

- `storage/` 配下のファイルは Git 管理外（画像・ログ）
- `.env` は絶対にコミットしない
- マイグレーション済みのカラム変更は新しいマイグレーションで対応
- Pleskデプロイ後は `php artisan cache:clear` が必要
- dompdf 未導入環境では帳票（請求書・領収書）は印刷用HTMLにフォールバック（`$printMode = true`）
- グローバルトースト `window.appToast(msg, variant)` はライト/ダーク共通の濃色パネル（`light-theme.css §14`）
