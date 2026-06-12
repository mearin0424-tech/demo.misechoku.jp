<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShopManager extends Authenticatable
{
    /** 権限: オーナー（店舗運営の全権限） */
    public const ROLE_OWNER = 1;

    /** 権限: スタッフ（日常業務のみ。スタッフ管理・店舗削除は不可） */
    public const ROLE_STAFF = 2;

    /** 稼働ステータス: 有効 */
    public const STATUS_ACTIVE = 1;

    /** 稼働ステータス: 無効 */
    public const STATUS_DISABLED = 0;

    protected $table = 'shop_managers';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'shop_id',
        'name',
        'email',
        'password',
        'role',
        'status',
        'last_login_at',
        'line_user_id',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'last_login_at' => 'datetime',
            // PII（本名・LINE ID）を暗号化。
            // email は認証で WHERE 検索するため平文のまま（暗号化すると同一値でもIVが異なるため WHERE が機能しない）。
            'name'         => \App\Casts\SafeEncrypted::class,
            'line_user_id' => \App\Casts\SafeEncrypted::class,
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_id', 'id');
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(ShopJob::class, 'shop_id', 'shop_id');
    }
}
