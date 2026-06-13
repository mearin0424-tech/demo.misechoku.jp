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
 *  - $operationalNotices  … 運営からの公式お知らせ（公開中・対象ロール）
 *  - $unreadNewsCount     … ベルバッジ件数（個人宛未読 + 未確認の公式お知らせ）
 */
class InjectHeaderBadges
{
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
                'notifications'      => [],
                'operationalNotices' => [],
                'unreadNewsCount'    => 0,
            ]);
            return $next($request);
        }

        [$userType, $userId] = $resolved;

        // 1. やることリスト
        $todoList = match ($userType) {
            'cast'         => $this->tasks->forCast($userId),
            'shop_manager' => $this->tasks->forShop($this->shopIdForManager($userId) ?? ''),
            default        => [],
        };

        // 2. 個人宛おしらせ（最新20件）
        $notificationsCollection = $this->notifications->latestForUser($userType, $userId, 20);
        $notifications = $notificationsCollection->map(function ($n) {
            return [
                'id'           => $n->id,
                'title'        => $n->title,
                'body'         => $n->body,
                'url'          => $n->url,
                'is_unread'    => $n->isUnread(),
                'created_at'   => $n->created_at,
                'created_at_label' => optional($n->created_at)->diffForHumans(),
            ];
        })->all();

        // 3. 公式お知らせ（ロール別・公開中）
        $operationalNotices = $this->loadOperationalNotices($userType);

        // 4. 未読バッジ件数 = 個人宛未読 + 未確認の公式お知らせ
        $unreadPersonal = $notificationsCollection->whereNull('read_at')->count();
        $unreadNewsCount = $unreadPersonal; // 公式お知らせの既読管理は後段で実装するため一旦個人宛のみ

        View::share([
            'todoList'           => $todoList,
            'notifications'      => $notifications,
            'operationalNotices' => $operationalNotices,
            'unreadNewsCount'    => $unreadNewsCount,
        ]);

        return $next($request);
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
