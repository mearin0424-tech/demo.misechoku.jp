# ============================================================
# auto-deploy.ps1
# git add → commit → push → Pleskデプロイ を全自動実行
#
# 使い方:
#   .\scripts\auto-deploy.ps1 "feat(SCR-112): タグ選択の実装"
#
# 初回セットアップ:
#   下の「設定」セクションを環境に合わせて変更してください
# ============================================================

param(
    [Parameter(Mandatory=$true)]
    [string]$CommitMsg
)

# ════════════════════════════════════════════════
# ★ 設定（ここだけ環境に合わせて変更）
# ════════════════════════════════════════════════
$SSH_USER    = "misechoku"       # PleskのSSHユーザー名
$SSH_HOST    = "203.183.138.47"      # サーバーIPまたはドメイン
$REMOTE_DIR  = "/var/www/vhosts/demo.misechoku.jp/httpdocs"
$BRANCH      = "main"               # デプロイするブランチ
$RUN_TESTS   = $true                # テストを実行する場合は $true
$RUN_MIGRATE = $true                # マイグレーションを実行する場合は $true
# ════════════════════════════════════════════════

function Write-Step($num, $total, $msg) {
    Write-Host ""
    Write-Host "▶ [$num/$total] $msg" -ForegroundColor Yellow
}

function Write-OK($msg) {
    Write-Host "  ✓ $msg" -ForegroundColor Green
}

function Write-Fail($msg) {
    Write-Host ""
    Write-Host "  ✗ $msg" -ForegroundColor Red
    Write-Host ""
    exit 1
}

$TOTAL = if ($RUN_TESTS) { 5 } else { 4 }

Write-Host ""
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Cyan
Write-Host "  ミセチョク 自動デプロイ" -ForegroundColor Cyan
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Cyan
Write-Host "  コミット : $CommitMsg"
Write-Host "  ブランチ : $BRANCH"
Write-Host "  サーバー : $SSH_USER@$SSH_HOST"
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Cyan

# ─────────────────────────────────
# Step 1: キャッシュクリア（ローカル）
# ─────────────────────────────────
Write-Step 1 $TOTAL "ローカルキャッシュクリア"
php artisan config:clear --quiet
php artisan cache:clear --quiet
php artisan view:clear --quiet
Write-OK "クリア完了"

# ─────────────────────────────────
# Step 2: 単体テスト（任意）
# ─────────────────────────────────
if ($RUN_TESTS) {
    Write-Step 2 $TOTAL "単体テスト実行"
    Write-Host "  ----------------------------------------"
    php artisan test --stop-on-failure
    if ($LASTEXITCODE -ne 0) { Write-Fail "テスト失敗 — デプロイを中止します" }
    Write-Host "  ----------------------------------------"
    Write-OK "テスト全件パス"
}

# ─────────────────────────────────
# Step 3: git add & commit
# ─────────────────────────────────
$STEP = if ($RUN_TESTS) { 3 } else { 2 }
Write-Step $STEP $TOTAL "git add & commit"

$Changed = (git status --porcelain | Measure-Object -Line).Lines
if ($Changed -eq 0) {
    Write-Host "  変更なし — コミットをスキップ" -ForegroundColor Gray
} else {
    git status --short
    git add -A
    git commit -m $CommitMsg
    if ($LASTEXITCODE -ne 0) { Write-Fail "git commit 失敗" }
    Write-OK "コミット完了"
}

# ─────────────────────────────────
# Step 4: git push
# ─────────────────────────────────
$STEP = if ($RUN_TESTS) { 4 } else { 3 }
Write-Step $STEP $TOTAL "git push → GitHub"

git push origin $BRANCH
if ($LASTEXITCODE -ne 0) { Write-Fail "git push 失敗" }
Write-OK "push 完了"

# ─────────────────────────────────
# Step 5: Pleskサーバーにデプロイ
# ─────────────────────────────────
$STEP = if ($RUN_TESTS) { 5 } else { 4 }
Write-Step $STEP $TOTAL "Pleskサーバーにデプロイ"
Write-Host "  接続中: $SSH_USER@$SSH_HOST ..."

$migrate = if ($RUN_MIGRATE) { "php artisan migrate --force &&" } else { "" }

$remote = @"
set -e
cd $REMOTE_DIR
echo '[1/5] git pull...'
git pull origin $BRANCH
echo '[2/5] キャッシュクリア...'
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
echo '[3/5] マイグレーション...'
$migrate true
echo '[4/5] 本番最適化...'
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo '[5/5] 完了'
echo '__DEPLOY_SUCCESS__'
"@

$result = ssh "$SSH_USER@$SSH_HOST" $remote 2>&1

Write-Host $result

if ($result -match "__DEPLOY_SUCCESS__") {
    Write-OK "デプロイ完了"
} else {
    Write-Fail "デプロイ中にエラーが発生しました（上のログを確認）"
}

# ─────────────────────────────────
# 完了
# ─────────────────────────────────
Write-Host ""
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Green
Write-Host "  ✓ デプロイ完了！" -ForegroundColor Green
Write-Host "  https://demo.misechoku.jp" -ForegroundColor Green
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Green
Write-Host ""