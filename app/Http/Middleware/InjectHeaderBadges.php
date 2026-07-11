<?php

namespace App\Http\Middleware;

use App\Models\Notice;
use App\Services\NotificationService;
use App\Services\UserTaskService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

/**
 * 全ての認証済みページのヘッダーで使う以下のViewデータを注入する：
 *  - $todoList            … やることリスト
 *  - $notifications       … 個人宛おしらせ（最新N件）
 *  - $notificationGroups  … おしらせを 今日/昨日/今週/それ以前 でグルーピング済
 *  - $operationalNotices  … 運営からの公式お知らせ（公開中・対象ロール）
 *  - $unreadNewsCount     … ベルバッジ件数（個人宛未読 + 未確認の公式お知らせ）
 *  - $taskGroups          … やることを urgency (high / normal) でグルーピング済
 *  - $taskHighCount       … 緊急タスクの数（ヘッダーバッジ色を変える用）
 */
class InjectHeaderBadges
{
    /**
     * Notification type → { icon, color, label } のマッピング。
     * NotificationService でセットする type 文字列と揃える。
     */
    private const NOTIFICATION_CATEGORIES = [
        'talk.message_received'      => ['icon' => 'fa-comment-dots',     'color' => 'accent',  'label' => 'トーク'],
        'talk.interview_offer'       => ['icon' => 'fa-calendar-check',   'color' => 'accent',  'label' => '面談'],
        'talk.hired'                 => ['icon' => 'fa-check-double',     'color' => 'success', 'label' => '採用'],
        'talk.rejected'              => ['icon' => 'fa-circle-xmark',     'color' => 'muted',   'label' => '選考結果'],
        'favorite.like_received'     => ['icon' => 'fa-heart',            'color' => 'pink',    'label' => 'いいね'],
        'favorite.keep_received'     => ['icon' => 'fa-bookmark',         'color' => 'gold',    'label' => 'キープ'],
        'review.posted'              => ['icon' => 'fa-star',             'color' => 'gold',    'label' => 'レビュー'],
        'document.approved'          => ['icon' => 'fa-id-card',          'color' => 'success', 'label' => '書類審査'],
        'document.rejected'          => ['icon' => 'fa-id-card',          'color' => 'muted',   'label' => '書類審査'],
        'billing.request'            => ['icon' => 'fa-file-invoice-yen', 'color' => 'accent',  'label' => '請求'],
        'billing.paid'               => ['icon' => 'fa-yen-sign',         'color' => 'success', 'label' => '入金'],
        'billing.transferred'        => ['icon' => 'fa-money-bill-transfer', 'color' => 'success', 'label' => '振込'],
    ];

    /**
     * UserTaskService の task key に対応するアイコン/色マッピング。
     */
    private const TASK_CATEGORIES = [
        'cast.identity_unsubmitted'      => ['icon' => 'fa-id-card',      'label' => '本人確認'],
        'cast.identity_rejected'         => ['icon' => 'fa-id-card',      'label' => '本人確認'],
        'cast.bank_account_unset'        => ['icon' => 'fa-building-columns', 'label' => '口座情報'],
        'cast.talk_unread'               => ['icon' => 'fa-comment-dots', 'label' => 'トーク'],
        'cast.deposit_unconfirmed'       => ['icon' => 'fa-yen-sign',     'label' => '入金確認'],
        'shop.license_unsubmitted'       => ['icon' => 'fa-file-shield',  'label' => '許可書類'],
        'shop.license_rejected'          => ['icon' => 'fa-file-shield',  'label' => '許可書類'],
        'shop.bank_account_unset'        => ['icon' => 'fa-building-columns', 'label' => '口座情報'],
        'shop.talk_unread'               => ['icon' => 'fa-comment-dots', 'label' => 'トーク'],
        'shop.deposit_pending_approval'  => ['icon' => 'fa-file-circle-check', 'label' => '入金依頼'],
        'shop.invoice_pending_payment'   => ['icon' => 'fa-file-invoice-yen', 'label' => '請求書'],
    ];

    public function __construct(
        private readonly NotificationService $notifications,
        private readonly UserTaskService $tasks,
    ) {
    }

    public function handle(Request $request, Closure $next)
    {
        // どのガードでログインしているかを判定
        $resolved = $this->resolveCurrentUser();

        if ($resolved === null) {
            // 未ログインでもキーは入れておく（テンプレ側の isset で問題ないが念のため）
            View::share([
                'todoList'           => [],
                'taskGroups'         => ['high' => [], 'normal' => []],
                'taskHighCount'      => 0,
                'notifications'      => [],
                'notificationGroups' => [],
                'operationalNotices' => [],
                'unreadNewsCount'    => 0,
            ]);
            return $next($request);
        }

        [$userType, $userId] = $resolved;

        // 1. やることリスト（失敗時は空配列で握り潰してヘッダー崩壊を防止）
        try {
            $todoList = match ($userType) {
                'cast'         => $this->tasks->forCast($userId),
                'shop_manager' => $this->tasks->forShop($this->shopIdForManager($userId) ?? ''),
                default        => [],
            };
        } catch (\Throwable) {
            $todoList = [];
        }
        $todoList = $this->decorateTasks($todoList);
        $taskGroups = $this->groupTasks($todoList);
        $taskHighCount = count($taskGroups['high']);

        // 2. 個人宛おしらせ（最新20件）— テーブル未作成・カラム不一致でも壊さない
        $notifications = [];
        $unreadPersonal = 0;
        try {
            $notificationsCollection = $this->notifications->latestForUser($userType, $userId, 30);
            $notifications = $notificationsCollection
                ->map(fn ($n) => $this->decorateNotification($n))
                ->all();
            $unreadPersonal = $notificationsCollection->whereNull('read_at')->count();
        } catch (\Throwable) {
            // notifications テーブルが未作成 / 旧スキーマ / 権限エラー等。ヘッダーは空で表示。
            $notifications = [];
            $unreadPersonal = 0;
        }
        $notificationGroups = $this->groupNotificationsByDate($notifications);

        // 3. 公式お知らせ（ロール別・公開中）
        try {
            $operationalNotices = $this->loadOperationalNotices($userType);
        } catch (\Throwable) {
            $operationalNotices = [];
        }

        View::share([
            'todoList'           => $todoList,
            'taskGroups'         => $taskGroups,
            'taskHighCount'      => $taskHighCount,
            'notifications'      => $notifications,
            'notificationGroups' => $notificationGroups,
            'operationalNotices' => $operationalNotices,
            'unreadNewsCount'    => $unreadPersonal,
        ]);

        return $next($request);
    }

    /**
     * 通知1件をUI用のフラット配列に整形（カテゴリ情報とラベルを付ける）。
     *
     * @return array<string, mixed>
     */
    private function decorateNotification($n): array
    {
        $type = (string) ($n->type ?? '');
        $meta = self::NOTIFICATION_CATEGORIES[$type] ?? [
            'icon'  => 'fa-bell',
            'color' => 'muted',
            'label' => 'お知らせ',
        ];
        $created = $n->created_at ?? null;
        return [
            'id'               => $n->id ?? null,
            'type'             => $type,
            'title'            => (string) ($n->title ?? ''),
            'body'             => $n->body ?? null,
            'url'              => $n->url ?? null,
            'is_unread'        => method_exists($n, 'isUnread') ? $n->isUnread() : empty($n->read_at),
            'created_at'       => $created,
            'created_at_label' => $this->humanizeDate($created),
            'created_at_full'  => $created ? optional($created)->format('Y/m/d H:i') : null,
            'icon'             => $meta['icon'],
            'color'            => $meta['color'],
            'category_label'   => $meta['label'],
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $notifications
     * @return array<int, array{key: string, label: string, items: array<int, array<string, mixed>>}>
     */
    private function groupNotificationsByDate(array $notifications): array
    {
        $today     = ['key' => 'today',     'label' => '今日',     'items' => []];
        $yesterday = ['key' => 'yesterday', 'label' => '昨日',     'items' => []];
        $thisWeek  = ['key' => 'this_week', 'label' => '今週',     'items' => []];
        $older     = ['key' => 'older',     'label' => 'それ以前', 'items' => []];

        $now = Carbon::now();
        foreach ($notifications as $n) {
            $created = $n['created_at'] ?? null;
            if (!$created instanceof Carbon) {
                $older['items'][] = $n;
                continue;
            }
            if ($created->isSameDay($now)) {
                $today['items'][] = $n;
            } elseif ($created->isYesterday()) {
                $yesterday['items'][] = $n;
            } elseif ($created->greaterThanOrEqualTo($now->copy()->subDays(7))) {
                $thisWeek['items'][] = $n;
            } else {
                $older['items'][] = $n;
            }
        }

        return array_values(array_filter([$today, $yesterday, $thisWeek, $older], fn ($g) => count($g['items']) > 0));
    }

    /**
     * @param  \Illuminate\Support\Carbon|\DateTimeInterface|null $dt
     */
    private function humanizeDate($dt): ?string
    {
        if (!$dt) return null;
        try {
            $c = $dt instanceof Carbon ? $dt : Carbon::instance($dt);
            $now = Carbon::now();
            $mins = $c->diffInMinutes($now, false);
            if ($mins < 0) return $c->format('n/j H:i');
            if ($mins < 1)  return 'たった今';
            if ($mins < 60) return $mins . '分前';
            if ($c->isSameDay($now)) return $c->format('H:i');
            if ($c->isYesterday())   return '昨日 ' . $c->format('H:i');
            if ($c->greaterThanOrEqualTo($now->copy()->subDays(7))) return $c->diffForHumans();
            if ($c->isSameYear($now)) return $c->format('n/j');
            return $c->format('Y/n/j');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param  array<int, array{key?: string, text?: string, url?: ?string, urgency?: string}> $tasks
     * @return array<int, array<string, mixed>>
     */
    private function decorateTasks(array $tasks): array
    {
        $out = [];
        foreach ($tasks as $t) {
            $key = (string) ($t['key'] ?? '');
            $meta = self::TASK_CATEGORIES[$key] ?? ['icon' => 'fa-circle-info', 'label' => 'やること'];
            $out[] = [
                'key'            => $key,
                'text'           => (string) ($t['text'] ?? ''),
                'url'            => $t['url'] ?? null,
                'urgency'        => (string) ($t['urgency'] ?? 'normal'),
                'icon'           => $meta['icon'],
                'category_label' => $meta['label'],
            ];
        }
        return $out;
    }

    /**
     * @param  array<int, array<string, mixed>> $tasks
     * @return array{high: array<int, array<string, mixed>>, normal: array<int, array<string, mixed>>}
     */
    private function groupTasks(array $tasks): array
    {
        $high = [];
        $normal = [];
        foreach ($tasks as $t) {
            if (($t['urgency'] ?? 'normal') === 'high') {
                $high[] = $t;
            } else {
                $normal[] = $t;
            }
        }
        return ['high' => $high, 'normal' => $normal];
    }

    /**
     * @return array{0:string,1:string}|null  [user_type, user_id]
     */
    private function resolveCurrentUser(): ?array
    {
        if ($u = auth()->guard('member')->user()) {
            return ['cast', (string) $u->getKey()];
        }
        if ($u = auth()->guard('shop')->user()) {
            return ['shop_manager', (string) $u->getKey()];
        }
        if ($u = auth()->guard('admin')->user()) {
            return ['admin', (string) $u->getKey()];
        }
        return null;
    }

    private function shopIdForManager(string $managerId): ?string
    {
        if (!Schema::hasTable('shop_managers')) return null;
        $shopId = \DB::table('shop_managers')->where('id', $managerId)->value('shop_id');
        return $shopId ? (string) $shopId : null;
    }

    /**
     * 公式お知らせ：visible_to_* に応じて出し分け。
     *
     * @return array<int, array{title: string, url: ?string, published_at: ?string}>
     */
    private function loadOperationalNotices(string $userType): array
    {
        if (!class_exists(Notice::class)) return [];

        $col = match ($userType) {
            'cast'         => 'visible_to_cast',
            'shop_manager' => 'visible_to_shop',
            'admin'        => null,
            default        => 'visible_to_guest',
        };

        try {
            $q = Notice::query()
                ->where('is_published', true)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->orderByDesc('published_at')
                ->limit(10);
            if ($col !== null) {
                $q->where($col, true);
            }
            return $q->get()->map(function ($n) {
                return [
                    'title'        => (string) $n->title,
                    'url'          => $n->slug ? url('/support/notices/' . $n->slug) : null,
                    'published_at' => optional($n->published_at)->format('Y/m/d'),
                ];
            })->all();
        } catch (\Throwable) {
            return [];
        }
    }
}
