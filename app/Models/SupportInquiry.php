<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportInquiry extends Model
{
    public const SENDER_CAST = 'cast';
    public const SENDER_SHOP = 'shop';
    public const SENDER_GUEST = 'guest';

    public const STATUS_NEW = 'new';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_DISMISSED = 'dismissed';

    public const CATEGORY_ACCOUNT = 'account';
    public const CATEGORY_FEATURE = 'feature';
    public const CATEGORY_BUG = 'bug';
    public const CATEGORY_FEEDBACK = 'feedback';
    public const CATEGORY_OTHER = 'other';

    public const CATEGORY_LABELS = [
        self::CATEGORY_ACCOUNT => 'アカウント・ログインについて',
        self::CATEGORY_FEATURE => '機能や使い方について',
        self::CATEGORY_BUG => '不具合の報告',
        self::CATEGORY_FEEDBACK => 'ご意見・ご要望',
        self::CATEGORY_OTHER => 'その他',
    ];

    public const STATUS_LABELS = [
        self::STATUS_NEW => '新着',
        self::STATUS_IN_PROGRESS => '対応中',
        self::STATUS_RESOLVED => '完了',
        self::STATUS_DISMISSED => '対応不要',
    ];

    protected $table = 'support_inquiries';

    protected $fillable = [
        'sender_type',
        'sender_id',
        'category',
        'email',
        'body',
        'status',
        'assigned_admin_id',
        'admin_note',
        'user_agent',
        'ip_address',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function categoryLabel(): string
    {
        return self::CATEGORY_LABELS[$this->category] ?? $this->category;
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }
}
