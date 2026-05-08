<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CharacterGuideSetting extends Model
{
    protected $table = 'character_guide_settings';

    protected $fillable = [
        'route_name',
        'screen_label',
        'is_enabled',
        'message',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }
}
