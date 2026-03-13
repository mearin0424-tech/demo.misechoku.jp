<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewContent extends Model
{
    protected $table = 'review_contents';

    protected $fillable = [
        'content',
        'name',
        'del_flg',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'del_flg' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
