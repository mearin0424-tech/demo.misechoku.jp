<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTalkTemplate extends Model
{
    protected $table = 'user_talk_templates';

    protected $fillable = [
        'owner_type',
        'owner_id',
        'category',
        'title',
        'body',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
