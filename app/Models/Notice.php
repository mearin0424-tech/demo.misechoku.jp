<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Notice extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'body',
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

    public function getStatusLabelAttribute(): string
    {
        if (! $this->is_published || $this->published_at === null || $this->published_at->isFuture()) {
            return '下書き／非公開';
        }

        return '公開';
    }

    public static function makeUniqueSlugFromTitle(string $title, ?int $exceptId = null): string
    {
        $base = Str::slug($title);
        if ($base === '') {
            $base = 'notice-' . Str::lower(Str::random(10));
        }

        return static::ensureUniqueSlug($base, $exceptId);
    }

    public static function ensureUniqueSlug(string $base, ?int $exceptId = null): string
    {
        $slug = Str::slug($base);
        if ($slug === '') {
            $slug = 'notice-' . Str::lower(Str::random(10));
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
