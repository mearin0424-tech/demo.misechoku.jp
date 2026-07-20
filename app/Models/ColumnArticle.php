<?php

namespace App\Models;

use App\Models\Master\ColumnCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ColumnArticle extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'column_category_id',
        'body',
        'tags',
        'is_published',
        'published_at',
        'visible_to_cast',
        'visible_to_shop',
        'visible_to_guest',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'visible_to_cast' => 'boolean',
        'visible_to_shop' => 'boolean',
        'visible_to_guest' => 'boolean',
        'published_at' => 'datetime',
        'tags' => 'array',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeForCast(Builder $query): Builder
    {
        return $query->where('visible_to_cast', true);
    }

    public function scopeForShop(Builder $query): Builder
    {
        return $query->where('visible_to_shop', true);
    }

    public function scopeForGuest(Builder $query): Builder
    {
        return $query->where('visible_to_guest', true);
    }

    public function columnCategory(): BelongsTo
    {
        return $this->belongsTo(ColumnCategory::class, 'column_category_id');
    }

    public function getStatusLabelAttribute(): string
    {
        if (! $this->is_published || $this->published_at === null || $this->published_at->isFuture()) {
            return '下書き／非公開';
        }

        return '公開';
    }

    /**
     * タイトルからスラッグを生成し、重複があれば連番を付与する。
     */
    public static function makeUniqueSlugFromTitle(string $title, ?int $exceptId = null): string
    {
        $base = Str::slug($title);
        if ($base === '') {
            $base = 'col-' . Str::lower(Str::random(10));
        }

        return static::ensureUniqueSlug($base, $exceptId);
    }

    /**
     * 英数字ハイフンのベース文字列を正規化し、重複があれば連番を付与する。
     */
    public static function ensureUniqueSlug(string $base, ?int $exceptId = null): string
    {
        $slug = Str::slug($base);
        if ($slug === '') {
            $slug = 'col-' . Str::lower(Str::random(10));
        }

        $candidate = $slug;
        $n = 0;
        while (static::query()
            ->where('slug', $candidate)
            ->when($exceptId !== null, fn (Builder $q) => $q->where('id', '!=', $exceptId))
            ->exists()) {
            $n++;
            $candidate = $slug . '-' . $n;
        }

        return $candidate;
    }
}
