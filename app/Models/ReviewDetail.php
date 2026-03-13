<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewDetail extends Model
{
    protected $table = 'review_details';

    protected $fillable = [
        'review_id',
        'review_content_id',
        'val',
        'score',
    ];

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class, 'review_id', 'id');
    }

    public function content(): BelongsTo
    {
        return $this->belongsTo(ReviewContent::class, 'val', 'id');
    }
}
