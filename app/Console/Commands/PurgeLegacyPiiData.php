<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * 機密データのレガシー（暗号化前／public ディスク保管）を削除する。
 *
 * - 機密ファイル：storage/app/public 配下の casts/identity, shops/documents, payment_evidence を削除
 * - DB の image_path 系：'public/...' または接頭辞なしの旧形式を NULL に設定
 * - 平文 PII 列：暗号化キャストを適用するため、現存する平文値を NULL でクリア
 *
 * 実行：
 *   php artisan pii:purge-legacy --force
 */
class PurgeLegacyPiiData extends Command
{
    protected $signature = 'pii:purge-legacy {--force : 確認なしで実行する}';
    protected $description = 'モックデータのレガシー機密データ（旧 public ファイル＋平文 PII）を削除する。';

    public function handle(): int
    {
        if (!$this->option('force')) {
            $this->warn('このコマンドは機密ファイルと平文 PII を削除します。');
            if (!$this->confirm('続行しますか？', false)) {
                return 1;
            }
        }

        $this->info('========== 1. 旧公開ディスクの機密ファイル削除 ==========');
        $this->purgePublicFiles();

        $this->info('========== 2. DB 上の旧 image_path を NULL 化 ==========');
        $this->nullifyLegacyImagePaths();

        $this->info('========== 3. 平文 PII 列を NULL 化（暗号化キャスト導入のため） ==========');
        $this->nullifyPlaintextPii();

        $this->info('完了。');
        return 0;
    }

    private function purgePublicFiles(): void
    {
        $targets = [
            'casts/identity',
            'shops/documents',
            'payment_evidence',
        ];

        foreach ($targets as $rel) {
            try {
                if (Storage::disk('public')->exists($rel)) {
                    $files = Storage::disk('public')->allFiles($rel);
                    Storage::disk('public')->deleteDirectory($rel);
                    $this->line(sprintf('  - public/%s : %d ファイル削除', $rel, count($files)));
                } else {
                    $this->line(sprintf('  - public/%s : 既に存在しません', $rel));
                }
            } catch (\Throwable $e) {
                $this->error(sprintf('  - public/%s : %s', $rel, $e->getMessage()));
            }
        }
    }

    private function nullifyLegacyImagePaths(): void
    {
        $tables = [
            'cast_identity_documents' => ['image_path_front', 'image_path_back'],
            'shop_license_documents' => ['image_path'],
        ];

        foreach ($tables as $table => $cols) {
            if (!Schema::hasTable($table)) {
                $this->line(sprintf('  - %s : テーブル無し', $table));
                continue;
            }
            foreach ($cols as $col) {
                if (!Schema::hasColumn($table, $col)) {
                    continue;
                }
                $count = DB::table($table)
                    ->whereNotNull($col)
                    ->where(function ($q) use ($col) {
                        $q->where($col, 'LIKE', 'public/%')
                          ->orWhere(function ($q2) use ($col) {
                              // 接頭辞なしの旧形式（'casts/identity/...' 等）も対象
                              $q2->where($col, 'NOT LIKE', 'private/%')
                                 ->where($col, 'NOT LIKE', 'public/%');
                          });
                    })
                    ->update([$col => null, 'updated_at' => now()]);
                $this->line(sprintf('  - %s.%s : %d 行を NULL 化', $table, $col, $count));
            }
        }
    }

    private function nullifyPlaintextPii(): void
    {
        // bank_accounts は account_number/account_name が NOT NULL のため、
        // 平文を持つ既存行は丸ごと削除（再登録は app から実施）
        if (Schema::hasTable('bank_accounts')) {
            $count = DB::table('bank_accounts')->delete();
            $this->line(sprintf('  - bank_accounts : %d 行を物理削除（NOT NULL ＋暗号化のため）', $count));
        }

        $piiColumns = [
            'cast_profiles' => ['name', 'name_kana', 'tel', 'zip', 'addr1', 'addr2', 'addr3', 'addr', 'building', 'memo', 'ng_reason'],
            'shop_profiles' => ['tel', 'zip', 'addr2', 'addr3', 'addr', 'building', 'memo'],
            'shop_managers' => ['name', 'line_user_id'],
            'cast_identity_documents' => ['ng_reason'],
            'shop_license_documents' => ['ng_reason'],
        ];

        foreach ($piiColumns as $table => $cols) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            $existingCols = array_filter($cols, fn ($c) => Schema::hasColumn($table, $c));
            if (empty($existingCols)) {
                continue;
            }
            $update = [];
            foreach ($existingCols as $c) {
                $update[$c] = null;
            }
            if (Schema::hasColumn($table, 'updated_at')) {
                $update['updated_at'] = now();
            }
            $count = DB::table($table)->update($update);
            $this->line(sprintf('  - %s : %d 行をクリア（%s）', $table, $count, implode(', ', $existingCols)));
        }
    }
}
