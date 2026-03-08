# demo.misechoku.jp

## PWA Push 通知のセットアップ

デスクトップ通知・アイコンバッジ・テスト送信を使う場合:

1. **パッケージのインストール**
   ```bash
   composer require minishlink/web-push ^8.0
   ```

2. **VAPID キーの生成と .env 設定**
   ```bash
   php artisan push:vapid
   ```
   表示された `VAPID_PUBLIC_KEY` / `VAPID_PRIVATE_KEY` を .env に追加する。

3. **マイグレーション**
   ```bash
   php artisan migrate
   ```

4. **テスト手順**
   - ブラウザでアプリを開く（HTTPS または localhost）
   - ヘッダーのベルアイコン → お知らせポップアップを開く
   - 「通知を有効にする」をクリック → 許可
   - 「テスト通知を送る」をクリック → デスクトップに通知が表示される
   - PWA としてインストールしている場合はタブ/アイコンにバッジも表示される