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
      Common/         # 共通機能（トーク、登録など）
      Api/            # Push通知、銀行検索API
    Services/
      BillingManagementService.php  # 請求・振込ロジック
      DocumentReviewService.php     # 書類審査ロジック
  Models/
    Cast.php / Shop.php / Admin.php
    CastProfile.php / ShopProfile.php
    ShopJob.php / ShopJobApplication.php
    Message.php / TalkBlock.php
    ApplicationDeposit.php

resources/views/
  layouts/           # 共通レイアウト
    parts/           # header / footer / sidebar
  common/            # ロール共通ビュー（トーク、検索）
  casts/             # キャスト向けビュー
  shops/             # 店舗向けビュー
  admin/             # 管理者ビュー
  lp/                # LPページ

public/assets/js/    # フロントエンドJS
```

---

## ロール・ルートの対応

| ロール | URLプレフィックス | ミドルウェア |
|--------|-----------------|-------------|
| キャスト | `/cast/*` | `member.auth` |
| 店舗 | `/shop/*` | `shop.auth` |
| 管理者 | `/admin/*` | `admin.auth` |
| 共通 | `/setting/*`, `/api/*` | ログイン共通 |

---

## 開発ルール

### テスト
- テスト方針・実装ルールは `AUTO-TEST.md` を参照（未整備の場合はスキップ）

### Git
- Gitコマンドは開発者が個別に実行する。Claudeはgit操作を一切行わない。
- コミットメッセージの形式やブランチ命名などの規約は以下のとおり：
  - ブランチ: `feature/SCR-xxx-機能名`
  - コミット prefix: `feat` / `fix` / `refactor` / `style` / `test` / `docs`
  - 例: `feat(SCR-xxx): 機能名の実装`

### DB操作
- DB操作はClaudeが直接実行しない。
- マイグレーションやデータ変更が必要な場合、**MySQL 8.0 対応のSQL文を生成して開発者に渡し、実行を促す**。
- スキーマ変更（CREATE TABLE / ALTER TABLE / DROP 等）を行った場合、`schema.sql` を必ず最新化する（本番適用時の参照用）。
- 生クエリは避け、Eloquent ORM を使用する。DDL変更は新規マイグレーションで対応。
- **最新のテーブル構造は `database/mock_demo.sql` を参照**。コード・ロジック・テストを書く際は必ずこのファイルのスキーマと照合し、カラム名・型・制約の不整合があれば指摘または修正すること。

### デザイン
- デザインは `DESIGN.md` の定義に従う。コード・ビュー生成時は必ず参照すること。

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

---

## 画面ID リファレンス（主要）

| 画面ID | URL | 説明 |
|--------|-----|------|
| SCR-100 | /shop/home | 店舗ホーム |
| SCR-101 | /shop/search/{tab} | キャスト検索 |
| SCR-200 | /cast/home | キャストホーム |
| SCR-201 | /cast/search/{tab} | 店舗検索+AI |
| SCR-111 | /shop/recruits/status | 求人ステータス |
| SCR-112 | /shop/recruits/edit | 求人票編集 |
| SCR-204 | /cast/mypage | キャストマイページ |
| SCR-300 | /admin | 管理ダッシュボード |
| SCR-303 | /admin/deposits | 入金・振込管理 |

---

## 注意事項

- `storage/` 配下のファイルは Git 管理外（画像・ログ）
- `.env` は絶対にコミットしない
- マイグレーション済みのカラム変更は新しいマイグレーションで対応
- Pleskデプロイ後は `php artisan cache:clear` が必要