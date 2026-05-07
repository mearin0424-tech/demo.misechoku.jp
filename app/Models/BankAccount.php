<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    public const HOLDER_CAST = 'casts';
    public const HOLDER_SHOP = 'shops';
    public const HOLDER_SYSTEM_ACCOUNT = 'system_accounts';
    public const HOLDER_USER = self::HOLDER_SYSTEM_ACCOUNT;

    protected $table = 'bank_accounts';

    protected $fillable = [
        'holder_type',
        'holder_id',
        'bank_code',
        'bank_name',
        'bank_name_kana',
        'branch_code',
        'branch_name',
        'branch_name_kana',
        'account_type',
        'account_number',
        'account_name',
    ];

    protected function casts(): array
    {
        return [
            // 口座番号と口座名義は機微情報のため暗号化（復号失敗時は null）
            'account_number' => \App\Casts\SafeEncrypted::class,
            'account_name'   => \App\Casts\SafeEncrypted::class,
        ];
    }

    public function scopeForHolder(Builder $query, string $holderType, string $holderId): Builder
    {
        return $query
            ->where('holder_type', $holderType)
            ->where('holder_id', $holderId);
    }
}
