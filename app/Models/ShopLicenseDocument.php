<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopLicenseDocument extends Model
{
    public const STATUS_DRAFT = 0;
    public const STATUS_PENDING = 1;
    public const STATUS_APPROVED = 2;
    public const STATUS_REJECTED = 3;

    protected $table = 'shop_license_documents';

    protected $fillable = [
        'shop_id',
        'type',
        'image_path',
        'status',
        'ng_reason',
        'expired_at',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'expired_at' => 'date',
            'approved_at' => 'datetime',
            // 機密ファイルパス＋差戻し理由を暗号化（復号失敗時は null）
            'image_path' => \App\Casts\SafeEncrypted::class,
            'ng_reason'  => \App\Casts\SafeEncrypted::class,
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_id', 'id');
    }
}
