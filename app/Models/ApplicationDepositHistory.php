<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationDepositHistory extends Model
{
    protected $table = 'application_deposit_histories';

    public $timestamps = false;

    protected $fillable = [
        'application_deposit_id',
        'status',
        'status_date',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'status_date' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function deposit(): BelongsTo
    {
        return $this->belongsTo(ApplicationDeposit::class, 'application_deposit_id', 'id');
    }
}
