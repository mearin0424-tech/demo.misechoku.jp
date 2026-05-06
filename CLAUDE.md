# ミセチョク — Claude Code プロジェクト設定

## プロジェクト概要

水商売・夜職特化のマッチングプラットフォーム。
キャスト（求職者）と店舗（求人）をマッチングし、採用ボーナスの請求・振込まで一貫して管理するサービス。

- **フレームワーク**: Laravel（PHP）
- **テンプレートエンジン**: Blade
- **フロントエンド**: Vanilla JS（一部 jQuery）、Tailwind CSS
- **DB**: MySQL
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

## よく使うコマンド

```bash
# テスト実行
php artisan test
php artisan test --filter=ClassName

# キャッシュクリア
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# マイグレーション
php artisan migrate
php artisan migrate:fresh --seed

# ルート確認
php artisan route:list
php artisan route:list --path=cast

# ログ確認
tail -f storage/logs/laravel.log
```

---

## 開発ルール

### コーディング規約
- PSR-12 準拠（PHP）
- コントローラは薄く、ロジックはサービスクラスに集約
- バリデーションは FormRequest または `$request->validate()` を使用
- DB操作は Eloquent ORM を使用（生クエリは避ける）
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

### デザインシステム（大型リニューアル中）
- **カラー**: 黒ベース(`#0a0a0a`) + シャンパンゴールド(`#c5a059`)
- **スタイル**: グラスモーフィズム（`backdrop-blur` + 半透明）
- **フォント**: タイトル系=Noto Serif JP、本文=游ゴシック
- **UI パターン**: TikTok風スワイプ（ホーム）、ボトムナビ5タブ

---

## Git ワークフロー

```bash
# 機能ブランチで開発
git checkout -b feature/SCR-xxx-機能名

# コミットメッセージ形式
git commit -m "feat(SCR-xxx): 機能名の実装"
git commit -m "fix(SCR-xxx): バグ修正の内容"
git commit -m "refactor: リファクタリング内容"

# プッシュ
git push origin feature/SCR-xxx-機能名
```

### プレフィックス一覧
- `feat`: 新機能
- `fix`: バグ修正
- `refactor`: リファクタリング
- `style`: CSS/デザイン変更
- `test`: テスト追加・修正
- `docs`: ドキュメント更新

---

## テストの方針

- **単体テスト**: `tests/Feature/` 配下に機能テストを作成
- **テスト対象の優先度**:
  1. 請求・振込ロジック（`BillingManagementService`）
  2. トークアクション遷移（`TalkController@action`）
  3. バリデーション（FormRequest 系）
  4. API エンドポイント（Push、銀行検索）
- **テストDB**: `.env.testing` を参照、`RefreshDatabase` を使用

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