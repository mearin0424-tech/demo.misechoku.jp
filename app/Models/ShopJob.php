<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShopJob extends Model
{
    use SoftDeletes;

    protected $table = 'shop_jobs';

    protected $fillable = [
        'shop_id',
        'status',
        'hourly_wage_regular',
        'normal_time',
        'noruma_reward',
        'noruma_reward2',
        'hours_day',
        'noruma_cond',
        'has_trial',
        'trial_hourly_wage',
        'has_help',
        'help_hourly_wage',
        'job_description',
        'salary',
        'atmosphere',
        'working_day',
        'working_hours',
        'regular_holiday',
        'qualification',
    ];

    protected function casts(): array
    {
        return [
            'has_trial' => 'boolean',
            'has_help' => 'boolean',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_id', 'id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(ShopJobApplication::class, 'shop_job_id', 'id');
    }
}
