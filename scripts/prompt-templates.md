# Claude Code プロンプトテンプレート集
# ミセチョク開発用

---

## 【A】機能開発（バイブコーディング）

```
【開発依頼テンプレート】

対象画面: SCR-xxx / URL: /xxx/xxx
機能名: ○○機能

■ やること
- 〇〇を実装する
- 〇〇のバリデーションを追加する

■ 関連ファイル（わかる範囲で）
- Controller: app/Http/Controllers/Xxx/XxxController.php
- View: resources/views/xxx/xxx.blade.php
- Model: app/Models/Xxx.php

■ 完了条件
- php artisan test がパスすること
- /xxx/xxx にアクセスして〇〇が表示されること

実装が終わったら php artisan test を実行して、
パスしたら git commit & push してください。
コミットメッセージ: "feat(SCR-xxx): ○○機能の実装"
```

---

## 【B】バグ修正

```
【バグ修正テンプレート】

発生URL: /xxx/xxx
エラー内容: 〇〇のエラーが発生する / 〇〇が表示されない

■ 再現手順
1. 〇〇でログイン
2. /xxx/xxx にアクセス
3. 〇〇をクリック → エラー発生

■ ログ（あれば貼る）
storage/logs/laravel.log の最新エラー:
[エラー内容をここに]

修正後に php artisan test を実行して、
パスしたら git commit & push してください。
コミットメッセージ: "fix(SCR-xxx): ○○のバグ修正"
```

---

## 【C】リファクタリング

```
【リファクタリングテンプレート】

対象: app/Http/Controllers/Xxx/XxxController.php

■ 目的
- 〇〇のロジックをサービスクラスに切り出す
- 重複コードを共通化する

■ 注意点
- 既存のAPIレスポンス形式は変えないこと
- テストは全件パスさせること

完了後に php artisan test を実行して、
パスしたら git commit & push してください。
コミットメッセージ: "refactor: ○○のリファクタリング"
```

---

## 【D】テスト追加

```
【テスト追加テンプレート】

対象機能: 〇〇（BillingManagementService / TalkController など）

■ テストしたい内容
- 正常系: 〇〇したとき〇〇が返る
- 異常系: 〇〇が不正なとき〇〇エラーになる

■ 参考
- 既存テスト: tests/Feature/Xxx/XxxTest.php

tests/Feature/ 配下に新規テストを作成して、
php artisan test --filter=XxxTest で動作確認してください。
```

---

## 【E】デザイン実装（Blade + CSS）

```
【デザイン実装テンプレート】

対象画面: SCR-xxx / resources/views/xxx/xxx.blade.php

■ デザイン仕様（ミセチョクのデザインガイドライン）
- 背景: #0a0a0a（ブラックベース）
- アクセントカラー: #c5a059（シャンパンゴールド）
- スタイル: グラスモーフィズム（backdrop-blur + 半透明）
- フォント: タイトル=Noto Serif JP、本文=游ゴシック
- ボタン: 細い枠線のみ（Outline）、ホバーで塗りつぶし

■ 実装したい内容
- 〇〇コンポーネントを実装する
- 〇〇のレイアウトを整える

完了後に git commit & push してください。
コミットメッセージ: "style(SCR-xxx): ○○のUI実装"
```
