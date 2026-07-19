# Agent.md — Claude Code エージェント運用ガイド（ミセチョク）

このファイルは、本プロジェクトで Claude Code のサブエージェントをどう使い分けるか、
どんな作業パターンで進めるかをまとめたガイドです。CLAUDE.md / DESIGN.md / AUTO-TEST.md と
併せて参照してください。

---

## 1. サブエージェントの使い分け

Claude Code は複数の専門エージェントを持ちます。本プロジェクトでは次の判断基準で選びます。

| エージェント | 使いどころ | 使わない場面 |
|---|---|---|
| **Explore**（read-only 探索） | 「どのファイルにあるか」「既存の実装パターンは」を 3〜10 ファイル横断で確認したいとき。開いたことのない領域の下調べ | 特定ファイルの詳細レビュー / 全ファイル読み込みが必要な設計監査 |
| **Plan**（実装計画） | 3ステップ以上・複数ファイル横断する非自明な変更の設計を先に固めたいとき | 1〜2ファイルで完結する明快な修正 |
| **general-purpose** | 多段の調査 + 判断 + まとめが必要で、Explore では窓が浅すぎるとき（例: 一括で複数コントローラ・複数 CSS を俯瞰） | 単一ファイルの読み書き |
| **claude-code-guide** | Claude Code / SDK / API の使い方に関する質問（開発ではなくメタ質問） | プロジェクト実装作業 |

### 明確なルール
- **既にファイルパスが判っているならエージェントを起動しない**。Read / Grep / Glob 直接使う
- **1〜3 ファイル程度の読み込みで済むなら Grep で十分**。エージェントは context を消費するので、5+ 呼ぶ規模のときだけ
- **調査結果を要約させたいとき**は必ず「◯語以内で報告」と語数キャップを付ける（無指定だと冗長になる）
- 「実装させる」ときは Explore ではなく general-purpose を使う（Explore は書き込み権限が無い）

---

## 2. タスク管理（TaskCreate）

以下の条件に当てはまるときは必ず TaskCreate で計画をタスク化する:

- 3 ステップ以上に分かれる変更
- 1 メッセージで複数の独立した依頼が来た
- 影響範囲が広く、途中でユーザーに進捗を見せたい

タスクは:
- **開始直前に in_progress、終わったら即 completed**（batch で更新しない）
- 途中で新しい作業が見つかったら **新規タスクを追加**（既存を膨らませない）
- 単純な 1 ステップ作業ではタスクを作らない（過剰）

---

## 3. 本プロジェクト固有の作業パターン

### パターン A: ライトモードの色調整
1. `light-theme.css` を Read（章構成 §1-14 を把握）
2. 対象クラスを Grep で探す（Blade 内の `@push('styles')` にハードコードが多い）
3. **個別 blade を編集しない**。§14（またはより適切な章）に `body.theme-light .selector { color: ... !important; }` を追加
4. `light-theme.css` のキャッシュバージョン `?v=xxxx` を `layouts/app-v2.blade.php` で更新
5. `php artisan view:clear` 実行

### パターン B: 新規深階層ページを追加
1. ページ内には h1 も説明文も置かない（DESIGN.md §12 §13）
2. `resources/views/layouts/parts/header.blade.php` の `$jaByRoute` / `$jaByLast` にタイトルを追加
3. `CharacterGuideService::CATALOG` に route_name とグループ（cast/shop/common）を追加
4. 開発者に SQL を渡す（`INSERT ... ON DUPLICATE KEY UPDATE` で `character_guide_settings` に説明文を投入）
5. ライト画面にしたい場合は `app-v2.blade.php` の `$isDarkPage` の除外条件を確認・追加

### パターン C: 管理画面の新規テーブル
1. `<table class="admin-table admin-table--stack">` を使う
2. 各 `<td>` に `data-label="列名"` を付与
3. 操作列は `class="stack-actions"` にする
4. 空状態行の colspan は data-label 不要
5. Blade 内に `<style>` でモバイル用 `@media` を書かない（`admin-mobile.css` が担当）

### パターン D: DB スキーマ変更
1. Claude は DB を直接触らない。SQL 生成のみ
2. `database/schema.sql` と `database/mock_demo.sql` の **両方** を更新
3. 開発者向け SQL は `database/*.sql` として独立ファイルに置く（例: `database/character_guide_deep_pages.sql`）
4. UNIQUE キーがあれば `ON DUPLICATE KEY UPDATE` で再実行安全にする
5. `PlanSubscriptionService` などのサービスは `Schema::hasTable()` でガードして DB 未準備を安全に扱う

### パターン E: ライト画面用モーダルの追加
1. モーダルの基本枠は `.payment-bank-modal` / `.shop-action-modal` のパターンを踏襲
2. ライトテーマ用の白パネル化は `light-theme.css §7 §12` で既に対応済み。新規クラスは §14 に追記
3. モーダル内の入力欄・チェックボックスも同ルールで補正
4. トーストは `window.appToast()`（`alert()` を使わない）

---

## 4. アセット・キャッシュ運用

- 全 CSS/JS は `?v=YYYYMMDD-識別子` 付きで読み込む
- グローバル用は `layouts/app-v2.blade.php` の `$assetVersion` を進める
- 個別ファイルは個別 blade で `?v=` を付ける
- **Blade を変更したら必ず** `php artisan view:clear`
- **Tailwind クラスを追加/削除したら** `npm run tw:build`（`@source` の再走査が必要）

---

## 5. コミュニケーション規則

### ユーザーへの応答
- 深階層の作業は「何を・なぜ・どう変えたか」を1〜3項目で要約する
- 実機確認が必要な視覚変更は必ず「確認ポイント」を提示する（Claude はブラウザ確認できない）
- SQL 生成物は開発者が実行するため、応答本文にコードブロックで貼り付ける

### AskUserQuestion の使いどころ
- ユーザーの意図が本当に分岐しうるとき（配色の候補、実装範囲の広狭）
- 単純な作業は聞かずに実行する
- **プランモードで「これで進めていいか」を聞くのに使わない**（プラン自体は ExitPlanMode で確認）

### 禁止事項
- Git 操作（コミット・push・ブランチ作成など一切）
- DB への直接クエリ実行
- `.env` の変更
- 個別 blade を編集して回るライトモード補正（`light-theme.css` に集約）
- ページ内リード文の追加（オコジョガイドに集約）
- 独自のトースト実装（`.app-toast` を使う）
- Phosphor アイコン生クラスの直書き（`<x-ui.icon>` を使う）

---

## 6. デバッグ / 検証コマンド

```bash
# ルート確認
php artisan route:list --name=subscription
php artisan route:list --name=admin.plans

# ビュー キャッシュクリア（Blade 変更のたび）
php artisan view:clear

# シンタックスチェック
php -l app/Http/Controllers/XxxController.php

# Tailwind 再ビルド
npm run tw:build

# Tailwind 差分監視
npm run tw:watch
```

---

## 7. 参照ドキュメント

| ファイル | 役割 |
|---|---|
| `CLAUDE.md` | プロジェクト全体設定・命名規則・業務ロジック |
| `DESIGN.md` | デザインシステム（トークン / コンポーネント / ライトモード / タイトル方針） |
| `AUTO-TEST.md` | テスト方針・優先度・対象サービス |
| `Agent.md`（本ファイル） | サブエージェント運用 / 作業パターン / コミュニケーション規則 |
| `database/mock_demo.sql` | 最新のテーブル構造の正 |
| `database/schema.sql` | 本番適用用のスキーマ |

不整合や更新漏れを見つけたら、実装前にドキュメント側を修正する（コードと乖離させない）。
