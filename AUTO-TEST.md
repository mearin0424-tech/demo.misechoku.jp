# AUTO-TEST.md — ミセチョク テスト方針

## 基本方針

- テストは `tests/Feature/` 配下に機能テストとして作成する
- テストDBは `.env.testing` を参照、`RefreshDatabase` トレイトを使用
- テストクラス名は対象コントローラ・サービス名に合わせる（例: `BillingManagementServiceTest`）
- **スキーマは `database/mock_demo.sql` と照合**し、カラム名・型・制約を一致させること
- 新規追加テーブル（`profile_views` / `shop_plan_subscriptions` / `character_guide_settings`）も同様に照合

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
| 請求書番号採番 | `INV-YYYYMM-{id:04d}` の形式で発番されること |

### 優先度 2 — Premiumプラン（`PlanSubscriptionService`）

銀行振込 + 運営目視確認で有効化する運用フロー。金額・機能ゲート・請求書/領収書の各テスト。

| テストケース | 確認内容 |
|------------|---------|
| **契約フロー** | |
| `contract()` 正常系（月払い） | `status=1`（入金待ち）で作成、`amount=20000`、期限 = 今日+7日 |
| `contract()` 正常系（年払い） | `amount=200000` で作成 |
| `contract()` 二重申込 | 既に入金待ち／有効な契約がある場合、既存レコードを返す |
| 請求書番号 | `PLN-YYYYMM-xxxx` で採番される |
| **入金確認** | |
| `confirmPayment()` 正常系 | `status=2`（有効）に遷移、`starts_at=now`、月払いなら `ends_at=now+1ヶ月` |
| 年払いの `ends_at` | 年払いは `ends_at=now+1年` |
| 領収書番号 | `RCT-YYYYMM-xxxx` で採番される |
| 入金待ち以外の contract を confirm | 何もしない（冪等） |
| **有効判定** | |
| `activeFor()` 期限切れ | 期限切れなら自動的に `STATUS_EXPIRED` に更新して null を返す |
| `isPremium()` | 有効な契約があるときのみ true |
| **スカウト上限** | |
| `isScout()` 正常系 | 既存メッセージが無い相手＝スカウト（true） |
| `isScout()` 既存やりとりあり | 1通でも履歴があれば false |
| `scoutCountToday()` | 「本日開始した会話（初回メッセージが店舗発）」の数を返す |
| `checkScoutQuota()` 無料 | limit=5、5件目送信後は `allowed=false` |
| `checkScoutQuota()` Premium | limit=30、30件目送信後は `allowed=false` |
| `TalkController::store` スカウト超過 | 店舗→新規キャストへの6件目送信で 429 が返る |
| `TalkController::store` 既存やりとり | Premium/無料に関わらず送信できる |
| **閲覧キャスト一覧** | |
| `recentViewersFor()` 正常系 | `profile_views` から target_type=shop の閲覧者を集計、`view_count` 降順（実装は `last_viewed_at` 降順） |

### 優先度 3 — トークアクション遷移（`TalkController@action`）

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

### 優先度 4 — 閲覧数（`ProfileViewService`）

| テストケース | 確認内容 |
|------------|---------|
| `record()` 正常系 | `profile_views` に viewer_type/id, target_type/id で INSERT される |
| `record()` 同一閲覧者の再訪 | 毎回加算される（重複除去しない） |
| `countFor()` | target_type/id に一致する件数を返す |
| `countForMany()` | 複数 target を一括集計、キー=target_id / 値=閲覧数 |
| プロフィール表示時の記録 | `Casts\ProfileController::show()` などから呼ばれること |

### 優先度 5 — オコジョガイド（`CharacterGuideService`）

| テストケース | 確認内容 |
|------------|---------|
| `getForRoute()` 有効なルート | `enabled=true` / `message` が返る |
| `getForRoute()` 無効なルート | `enabled=false` / `message=''` |
| `getForRoute()` カタログにないルート | `has_catalog=false` |
| DB 未準備 | Schema チェックで例外を出さず既定値を返す |
| 管理画面での更新 | `character_guide_settings` の `route_name` UNIQUE 制約が働く |

### 優先度 6 — バリデーション（FormRequest 系）

| 対象 | 主な確認項目 |
|-----|------------|
| キャスト登録 | `email` ユニーク、`password` confirmed、`checkdate` 実在日付 |
| 店舗登録 | `email` ユニーク、`business_type` in値、`plan` in値 |
| キャストプロフィール更新 | 画像1枚以上必須、`personality_type` regex |
| 求人更新 | `hourly_wage_regular` min:0、各タグIDの exists チェック |
| 口座登録（キャスト・店舗共通） | `bank_code` 4桁、`branch_code` 3桁、`account_number` 7〜8桁 |
| 本人確認書類アップロード | `mimes` / `max:8192`、`type` in値 |
| プラン契約 | `billing_cycle` in `monthly`/`yearly` |

### 優先度 7 — APIエンドポイント

| エンドポイント | 確認内容 |
|-------------|---------|
| `GET /api/bank-lookup/banks` | 銀行コードで名称が返ること |
| `GET /api/bank-lookup/branches` | `bank_code` 必須、`q` で絞り込めること |
| `POST /api/push/subscribe` | `endpoint` / `keys` が保存されること |

### 優先度 8 — Premium プラン画面フロー（Feature テスト）

| ルート | 確認内容 |
|-------|---------|
| `GET /subscription` | 店舗ログイン時に活性/入金待ち/未加入で表示が変わる |
| `POST /subscription/contract` | `billing_cycle` を保存、リダイレクト + `session('message')` |
| `POST /subscription/cancel` | 入金待ちのみキャンセル可 |
| `GET /subscription/invoice` | 契約がある場合のみ PDF/HTML 応答（dompdf 有無で分岐） |
| `GET /subscription/receipt` | 入金確認後のみ発行、`paid_confirmed_at` が null なら 302 でエラー |
| `GET /admin/plans` | 契約一覧、入金待ちを先頭に、`admin.permission:operations.deposits` 必須 |
| `POST /admin/plans/{sub}/confirm` | 入金待ちのみ confirmPayment、それ以外は `session('error')` |
| `GET /shop/mypage/viewers` | Premium 店舗はキャスト一覧、それ以外はティーザー |

### 優先度 9 — 検索の Premium 優先表示

| テストケース | 確認内容 |
|------------|---------|
| `Casts\SearchController@index` sort=relevance | Premium 店舗が非 Premium より上位に並ぶ |
| Premium 店舗が期限切れの場合 | `activeFor()` が null を返すため優先されない |
| 通常のマッチ度順 | 同一 Premium 状態内では従来のスコア順 |

---

## ブロック・つながりのテスト

| テストケース | 確認内容 |
|------------|---------|
| ブロック登録 | `talk_blocks`（cast_id, shop_id）に UNIQUE 制約で重複不可 |
| ブロック解除 | 自分がブロックしたもののみ解除可 |
| キープ登録（旧 LIKE） | `favorites` テーブルにレコード作成（LIKE 機能は廃止済、キープは SEARCH タブに統合） |
| キープ重複 | 同一組み合わせで重複しないこと |

---

## UI テスト（手動）

自動化しないが PR チェックで確認する項目。ライトモードの回帰防止用。

| 画面 | 確認内容 |
|-----|---------|
| ライト画面全般 | 白背景に白/薄グレー文字が出ていないか（`light-theme.css §14` 参照） |
| ヘッダー中央タイトル | 深階層ページで日本語タイトルが中央に出るか、左右アイコンと重ならないか |
| ページ内リード文 | ページ内に説明文が出ていないか（すべてオコジョガイドに集約） |
| 管理画面（スマホ） | 5列以上のテーブルが `admin-table--stack` でカード化されているか |
| グローバルトースト | `window.appToast()` が濃色パネルで表示されるか |

---

## テストコマンド

```bash
# 全テスト実行
php artisan test

# クラス指定
php artisan test --filter=BillingManagementServiceTest
php artisan test --filter=PlanSubscriptionServiceTest

# グループ指定（将来的に @group アノテーションを付与した場合）
php artisan test --group=billing
php artisan test --group=premium
```

---

## 注意事項

- `RefreshDatabase` を使用するため、テスト実行のたびにDBがリセットされる
- シーダーが必要なテストは `$this->seed()` を明示的に呼ぶ
- 外部API（銀行検索など）は本番APIを叩かないよう Mock / Http::fake() を使用する
- 金額・日付の境界値テストは必ず含める
- Premium プランの `ends_at` 計算は `Carbon::addMonth()` / `addYear()` を使用するため、閏月・閏年の境界も確認する
- dompdf 未導入環境では PDF ではなく HTML ビュー（`$printMode = true`）が返るため、テストは両パターン想定

---

## 実装済み Feature テスト（2026-08-02 追加）

| ファイル | 対象機能 |
|---|---|
| `tests/Feature/Auth/PasswordResetTest.php` | パスワードリセット全フロー（enumeration 対策・トークン失効・成功パス） |
| `tests/Feature/Auth/EmailVerificationTest.php` | メール認証（署名 URL・未署名 URL 拒否・未ログイン再送信拒否） |
| `tests/Feature/UserReportTest.php` | ユーザー通報の送信・重複排除・自己通報禁止 |
| `tests/Feature/Support/TalkActionRegistryTest.php` | トークアクションの権限マトリクス（cast_only / shop_only / both_side） |
| `tests/Feature/Cast/AvailabilityDeclarationTest.php` | 「今すぐ入れる」宣言（2h/4h/8h・不正値拒否・取り消し） |
| `tests/Feature/Shop/HelpBroadcastTest.php` | 緊急ヘルプ一斉送信・6h クールダウン・バリデーション |
| `tests/Feature/Setting/WithdrawFlowTest.php` | 退会時の PII 匿名化・最後のオーナー保護・パスワード誤り拒否 |
| `tests/Feature/Shop/ReviewReplyTest.php` | 店舗返信投稿・削除・他店舗のレビューへの返信拒否 |
| `tests/Feature/Shop/StaffManagementTest.php` | 1店舗1オーナー制約、staff からの owner-only 操作 403、enum bridge |
| `tests/Feature/Shop/LicenseSubmit2StepTest.php` | 書類 2 段階提出フロー（upload → request-review → withdraw）+ staff 403 |
| `tests/Feature/Discovery/TierRankingTest.php` | DISCOVERY の Tier A/B/C 並び替え（DiscoveryController::getHomeCasts） |
| `tests/Feature/Support/NgWordDetectorTest.php` | NG 語検出（電話番号・LINE ID・URL・NG語テーブル・非アクティブ除外）|

**実行方法**:
```bash
php artisan test --testsuite=Feature
# 個別:
php artisan test --filter=PasswordResetTest
php artisan test --filter=HelpBroadcastTest
```
