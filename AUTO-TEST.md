# AUTO-TEST.md — ミセチョク テスト方針

## 基本方針

- テストは `tests/Feature/` 配下に機能テストとして作成する
- テストDBは `.env.testing` を参照、`RefreshDatabase` トレイトを使用
- テストクラス名は対象コントローラ・サービス名に合わせる（例: `BillingManagementServiceTest`）
- **スキーマは `database/mock_demo.sql` と照合**し、カラム名・型・制約を一致させること

---

## テスト対象の優先度

### 優先度 1 — 請求・振込ロジック（`BillingManagementService`）

金銭に関わる最重要ロジック。以下のケースを網羅する。

| テストケース | 確認内容 |
|------------|---------|
| 採用ボーナス申請（正常系） | `application_deposits` にレコードが作成される |
| 採用ボーナス申請（重複） | 同一 `application_id` で二重申請できないこと |
| 店舗入金確認 | `confirmed_amount` が正しく記録される |
| キャスト振込実行 | `cast_transferred_at` / `cast_transfer_amount` が更新される |
| 口座名義バリデーション | `KouzaMeig` ルールで全角カナ以外が弾かれること |

### 優先度 2 — トークアクション遷移（`TalkController@action`）

ステータス遷移のルールが複雑なため、境界値を重点的にテストする。

| テストケース | 確認内容 |
|------------|---------|
| `interview_offer`（正常系） | ステータスが「やり取り中」のときのみ実行可 |
| `interview_offer`（options 空） | バリデーションエラーになること |
| `interview_confirm`（正常系） | キャスト側のみ実行可、`offer_token` / `selected_option` 必須 |
| `interview_confirm`（店舗側） | 403 または拒否されること |
| `hired` / `rejected` | 店舗側のみ実行可 |
| `cancel_status` | 双方から実行可、ステータスがリセットされること |
| メッセージ削除（10分以内） | 削除できること |
| メッセージ削除（10分超過） | 削除できないこと |
| 他人のメッセージ削除 | 拒否されること |

### 優先度 3 — バリデーション（FormRequest 系）

| 対象 | 主な確認項目 |
|-----|------------|
| キャスト登録 | `email` ユニーク、`password` confirmed、`checkdate` 実在日付 |
| 店舗登録 | `email` ユニーク、`business_type` in値、`plan` in値 |
| キャストプロフィール更新 | 画像1枚以上必須、`personality_type` regex |
| 求人更新 | `hourly_wage_regular` min:0、各タグIDの exists チェック |
| 口座登録（キャスト・店舗共通） | `bank_code` 4桁、`branch_code` 3桁、`account_number` 7〜8桁 |
| 本人確認書類アップロード | `mimes` / `max:8192`、`type` in値 |

### 優先度 4 — APIエンドポイント

| エンドポイント | 確認内容 |
|-------------|---------|
| `GET /api/bank-lookup/banks` | 銀行コードで名称が返ること |
| `GET /api/bank-lookup/branches` | `bank_code` 必須、`q` で絞り込めること |
| `POST /api/push/subscribe` | `endpoint` / `keys` が保存されること |

---

## ブロック・つながりのテスト

| テストケース | 確認内容 |
|------------|---------|
| ブロック登録 | `talk_blocks`（cast_id, shop_id）に UNIQUE 制約で重複不可 |
| ブロック解除 | 自分がブロックしたもののみ解除可 |
| LIKE登録 | `favorites` テーブルにレコード作成 |
| LIKE重複 | 同一組み合わせで重複しないこと |

---

## テストコマンド

```bash
# 全テスト実行
php artisan test

# クラス指定
php artisan test --filter=BillingManagementServiceTest

# グループ指定（将来的に @group アノテーションを付与した場合）
php artisan test --group=billing
```

---

## 注意事項

- `RefreshDatabase` を使用するため、テスト実行のたびにDBがリセットされる
- シーダーが必要なテストは `$this->seed()` を明示的に呼ぶ
- 外部API（銀行検索など）は本番APIを叩かないよう Mock / Http::fake() を使用する
- 金額・日付の境界値テストは必ず含める