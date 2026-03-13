<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShopJobApplication extends Model
{
    protected $table = 'shop_job_applications';

    protected $fillable = [
        'cast_id',
        'shop_job_id',
        'status',
        'result_date',
        'real_start_date',
        'hourly_wage_regular',
        'normal_time',
        'reason_rejection',
    ];

    protected function casts(): array
    {
        return [
            'result_date' => 'date',
            'real_start_date' => 'date',
        ];
    }

    public function cast(): BelongsTo
    {
        return $this->belongsTo(Cast::class, 'cast_id', 'id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(ShopJob::class, 'shop_job_id', 'id');
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(ApplicationDeposit::class, 'shop_job_application_id', 'id');
    }
}
