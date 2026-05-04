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
        'job_type',
        'regular_status',
        'trial_status',
        'help_status',
        'hourly_wage_regular',
        'regular_hourly_wage',
        'regular_hourly_wage_max',
        'trial_hourly_wage_max',
        'help_hourly_wage_max',
        'shift_time_start',
        'shift_time_end',
        'shift_end_is_last',
        'normal_time',
        'norma_day',
        'noruma_reward',
        'bonus_reward',
        'noruma_reward2',
        'bonus_remarks',
        'hours_day',
        'norma_hours',
        'noruma_cond',
        'catch_copy',
        'job_content',
        'bonus_condition',
        'has_trial',
        'trial_hourly_wage',
        'has_help',
        'help_hourly_wage',
        'pr',
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
