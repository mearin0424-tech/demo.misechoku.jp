<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * 復号失敗時に例外を投げず null を返す暗号化キャスト。
 *
 * Laravel 標準の `'encrypted'` キャストは復号失敗時に `DecryptException` を投げる。
 * そのため平文のレガシーデータが残っているテーブルから Eloquent で読み込むと
 * 画面が落ちる。`SafeEncrypted` を使うと復号できない値は null として扱えるため、
 * 段階的なマイグレーション（古い行は順次再保存される運用）に向く。
 *
 * 使い方（モデルの casts()）:
 *
 *   protected function casts(): array
 *   {
 *       return [
 *           'tel' => \App\Casts\SafeEncrypted::class,
 *       ];
 *   }
 */
class SafeEncrypted implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }
        try {
            return Crypt::decryptString((string) $value);
        } catch (\Throwable $e) {
            // 復号できない＝平文 or 旧鍵 or 破損。null として表示し、後続の再保存で暗号化される。
            return null;
        }
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }
        return Crypt::encryptString((string) $value);
    }
}
