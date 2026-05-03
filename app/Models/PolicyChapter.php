<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PolicyChapter extends Model
{
    protected $fillable = [
        'policy_document_id',
        'sort_order',
        'title',
        'body',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(PolicyDocument::class, 'policy_document_id');
    }
}
