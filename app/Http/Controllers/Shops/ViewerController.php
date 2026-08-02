<?php

namespace App\Http\Controllers\Shops;

use App\Http\Concerns\ResolvesActor;
use App\Http\Controllers\Controller;
use App\Services\PlanSubscriptionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * 求人（店舗プロフィール）を閲覧したキャスト一覧（Premium機能）。
 * 非加入店舗にはティーザー（件数のみ + プラン案内）を表示する。
 */
class ViewerController extends Controller
{
    use ResolvesActor;

    public function __construct(private readonly PlanSubscriptionService $planService)
    {
    }

    public function index()
    {
        $shopId = (string) auth()->guard('shop')->user()->shop_id;
        $isPremium = $this->planService->isPremium($shopId);

        $rows = $this->planService->recentViewersFor($shopId, 50);
        $totalViewers = count($rows);

        $viewers = [];
        if ($isPremium && $rows !== []) {
            $castIds = array_map(fn ($r) => (string) $r->cast_id, $rows);

            $profiles = DB::table('cast_profiles')
                ->select('cast_id', 'nickname', 'name', 'birthday', 'pref', 'city')
                ->whereIn('cast_id', $castIds)
                ->get()
                ->keyBy('cast_id');

            $imageRows = DB::table('cast_images')
                ->select('cast_id', 'image_path')
                ->whereIn('cast_id', $castIds)
                ->orderByDesc('is_main')
                ->orderBy('id')
                ->get()
                ->groupBy('cast_id');

            foreach ($rows as $r) {
                $castId = (string) $r->cast_id;
                $p = $profiles[$castId] ?? null;
                if ($p === null) {
                    continue;
                }

                $age = null;
                if (!empty($p->birthday)) {
                    try {
                        $age = Carbon::parse($p->birthday)->age;
                    } catch (\Throwable $e) {
                        $age = null;
                    }
                }

                $img = optional(($imageRows[$castId] ?? collect())->first())->image_path;

                $viewers[] = [
                    'cast_id' => $castId,
                    'name' => (string) ($p->nickname ?: $p->name ?: 'キャスト'),
                    'age' => $age,
                    'area' => trim(($p->pref ?? '') . ' ' . ($p->city ?? '')),
                    'avatar_url' => $this->assetPathForStored($img),
                    'view_count' => (int) $r->view_count,
                    'last_viewed_at' => $r->last_viewed_at
                        ? Carbon::parse($r->last_viewed_at)->locale('ja')->diffForHumans()
                        : '',
                ];
            }
        }

        return view('shops.mypage.viewers', [
            'isPremium' => $isPremium,
            'viewers' => $viewers,
            'totalViewers' => $totalViewers,
        ]);
    }

    // assetPathForStored() is now provided by ResolvesActor trait.
}
