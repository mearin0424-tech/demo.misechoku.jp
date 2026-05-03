<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PolicyDocument extends Model
{
    public const KEY_ABOUT = 'about';
    public const KEY_TERMS = 'terms';
    public const KEY_PRIVACY = 'privacy';

    protected $fillable = [
        'key',
        'title',
        'lead_title',
        'lead_body',
        'meta',
        'is_locked',
        'updated_by_id',
        'updated_by_name',
        'content_updated_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'is_locked' => 'boolean',
        'content_updated_at' => 'datetime',
    ];

    public function chapters(): HasMany
    {
        return $this->hasMany(PolicyChapter::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(PolicyRevision::class)->orderByDesc('created_at');
    }

    public function isAbout(): bool
    {
        return $this->key === self::KEY_ABOUT;
    }

    public static function defaultMetaSchema(): array
    {
        return [
            ['key' => 'org_name', 'label' => '協会名'],
            ['key' => 'representative', 'label' => '理事長'],
            ['key' => 'capital', 'label' => '資本金'],
            ['key' => 'established_at', 'label' => '設立年月日'],
            ['key' => 'address', 'label' => '所在地'],
            ['key' => 'business', 'label' => '事業内容'],
        ];
    }
}
