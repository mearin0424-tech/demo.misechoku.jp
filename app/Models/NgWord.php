<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NgWord extends Model
{
    protected $table = 'ng_words';

    protected $fillable = [
        'word',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
