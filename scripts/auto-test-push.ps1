# ============================================================
# auto-test-push.ps1
# テスト実行 → 成功したら Git commit & push まで自動で行うスクリプト
# 使い方: .\scripts\auto-test-push.ps1 "feat(SCR-xxx): 実装内容"
# ============================================================

param(
    [string]$CommitMsg = "chore: auto commit"
)

$Branch = git rev-parse --abbrev-ref HEAD

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  ミセチョク 自動テスト & プッシュ" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  ブランチ : $Branch"
Write-Host "  コミット : $CommitMsg"
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# ─────────────────────────────────
# Step 1: キャッシュクリア
# ─────────────────────────────────
Write-Host "▶ [1/4] キャッシュクリア..." -ForegroundColor Yellow
php artisan config:clear --quiet
php artisan cache:clear --quiet
php artisan view:clear --quiet
Write-Host "  ✓ クリア完了" -ForegroundColor Green

# ─────────────────────────────────
# Step 2: 単体テスト実行
# ─────────────────────────────────
Write-Host ""
Write-Host "▶ [2/4] 単体テスト実行中..." -ForegroundColor Yellow
Write-Host "----------------------------------------"

php artisan test --stop-on-failure
if ($LASTEXITCODE -ne 0) {
    Write-Host "----------------------------------------"
    Write-Host "  ✗ テスト失敗 — コミット中止" -ForegroundColor Red
    Write-Host ""
    Write-Host "  エラーを修正してから再実行してください。"
    exit 1
}

Write-Host "----------------------------------------"
Write-Host "  ✓ テスト全件パス" -ForegroundColor Green

# ─────────────────────────────────
# Step 3: 変更確認
# ─────────────────────────────────
Write-Host ""
Write-Host "▶ [3/4] 変更ファイルを確認..." -ForegroundColor Yellow
Write-Host "----------------------------------------"
git status --short
Write-Host "----------------------------------------"

$Changed = (git status --porcelain | Measure-Object -Line).Lines
if ($Changed -eq 0) {
    Write-Host "  変更なし — コミット不要"
    exit 0
}

# ─────────────────────────────────
# Step 4: Git commit & push
# ─────────────────────────────────
Write-Host ""
Write-Host "▶ [4/4] Git commit & push..." -ForegroundColor Yellow
git add -A
git commit -m $CommitMsg
git push origin $Branch

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "  ✓ 完了！ブランチ '$Branch' にプッシュしました" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
