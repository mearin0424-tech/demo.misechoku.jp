<?php

namespace App\Http\Controllers\Shops;

use App\Http\Concerns\ResolvesActor;
use App\Http\Controllers\Controller;
use App\Services\PlanSubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Emergency help broadcast: send the same "kinkyu shoushuu" (urgent help wanted)
 * message to multiple casts at once. Complements the "今すぐ入れる" declaration
 * on the cast side and the "help" quick-reply template.
 *
 * Called from the shop DISCOVERY screen (top of tier A/B chip stack).
 * Limits:
 *   - Max 20 recipients per broadcast (avoid spam).
 *   - Same recipient blocked from repeat broadcasts within 6 hours.
 *   - Counts against the shop's scout limit (Free: 5/day, Premium: 30/day).
 */
class HelpBroadcastController extends Controller
{
    use ResolvesActor;

    private const MAX_RECIPIENTS_PER_CALL = 20;
    private const REBROADCAST_COOLDOWN_HOURS = 6;

    public function __construct(
        private readonly PlanSubscriptionService $planService,
    ) {}

    public function send(Request $request): JsonResponse
    {
        $shopId = $this->currentShopId();
        if (!$shopId) {
            return response()->json(['success' => false, 'message' => 'ログイン後にご利用ください。'], 401);
        }

        $data = $request->validate([
            'cast_ids'   => ['required', 'array', 'max:' . self::MAX_RECIPIENTS_PER_CALL],
            'cast_ids.*' => ['string', 'regex:/^c[0-9]+$/'],
            'body'       => ['required', 'string', 'min:5', 'max:500'],
        ]);

        // Deduplicate + verify targets actually exist
        $targetIds = array_values(array_unique($data['cast_ids']));
        $existing = DB::table('casts')
            ->whereIn('id', $targetIds)
            ->whereNull('deleted_at')
            ->pluck('id')
            ->all();
        if (empty($existing)) {
            return response()->json(['success' => false, 'message' => '有効な送信先がありません。'], 422);
        }

        // Cooldown filter: skip casts we've already broadcasted to recently
        $recent = DB::table('messages')
            ->where('shop_id', $shopId)
            ->whereIn('cast_id', $existing)
            ->where('sender_type', 2)    // 2 = shop
            ->where('created_at', '>=', now()->subHours(self::REBROADCAST_COOLDOWN_HOURS))
            ->pluck('cast_id')
            ->unique()
            ->all();
        $sendableIds = array_values(array_diff($existing, $recent));

        if (empty($sendableIds)) {
            return response()->json([
                'success' => false,
                'message' => '選択したキャスト全員が直近 ' . self::REBROADCAST_COOLDOWN_HOURS . ' 時間以内にメッセージを受信済みです。',
            ], 422);
        }

        // Scout-quota check: current used + requested count must be within limit.
        $quota = $this->planService->checkScoutQuota($shopId);
        $needed = count($sendableIds);
        if (($quota['used'] + $needed) > $quota['limit']) {
            $remaining = max(0, $quota['limit'] - $quota['used']);
            return response()->json([
                'success' => false,
                'message' => "本日のスカウト送信上限（{$quota['limit']}件）を超えます。あと {$remaining} 件送信可能です"
                    . ($quota['is_premium'] ? '' : '。Premium プランなら 1 日 30 件まで送信できます。'),
                'quota'   => $quota,
            ], 429);
        }

        // Insert messages in one batch
        $now = now();
        $rows = [];
        foreach ($sendableIds as $castId) {
            $rows[] = [
                'cast_id'    => $castId,
                'shop_id'    => $shopId,
                'sender_type' => 2,   // 2 = shop
                'type'       => 1,    // 1 = text
                'content'    => (string) $data['body'],
                'is_read'    => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('messages')->insert($rows);

        return response()->json([
            'success'      => true,
            'sent_count'   => count($sendableIds),
            'skipped_count' => count($existing) - count($sendableIds),
            'message'      => count($sendableIds) . ' 名に緊急ヘルプメッセージを送信しました。',
        ]);
    }

    /**
     * Return current tier A/B/C candidate cast ids for the UI to pre-select from.
     * Reuses DiscoveryController's ranking indirectly by hitting the same tables.
     */
    public function candidates(Request $request): JsonResponse
    {
        $shopId = $this->currentShopId();
        if (!$shopId) {
            return response()->json(['success' => false], 401);
        }

        // Tier A: available_until > NOW()  (top priority)
        // Tier B: last_login_at within 24h AND location known
        $now = now();
        $q = DB::table('casts as c')
            ->leftJoin('cast_profiles as cp', 'c.id', '=', 'cp.cast_id')
            ->whereNull('c.deleted_at')
            ->where('c.status', 1)
            ->select(
                'c.id',
                'c.last_login_at',
                'cp.nickname',
                'cp.name',
                'cp.available_until',
                'cp.latitude',
                'cp.longitude'
            );

        $rows = $q->limit(60)->get();

        $now = now();
        $items = [];
        foreach ($rows as $r) {
            $availActive = !empty($r->available_until) && \Carbon\Carbon::parse($r->available_until)->isFuture();
            $lastLogin = $r->last_login_at ? \Carbon\Carbon::parse($r->last_login_at) : null;
            $onlineNow = $lastLogin && $lastLogin->diffInMinutes($now, false) <= 30;
            $recent24h = $lastLogin && $lastLogin->diffInHours($now, false) <= 24;

            if ($availActive) {
                $tier = 'A';
            } elseif ($recent24h && $r->latitude !== null && $r->longitude !== null) {
                $tier = 'B';
            } else {
                continue; // Skip cold casts
            }

            $items[] = [
                'id'    => (string) $r->id,
                'name'  => (string) ($r->nickname ?: $r->name ?: $r->id),
                'tier'  => $tier,
                'online_now' => $onlineNow,
                'available_until' => $r->available_until,
            ];
        }

        // Tier A first, then B; within each, alpha order (no distance calc here for speed)
        usort($items, fn($a, $b) => [$a['tier'], $a['id']] <=> [$b['tier'], $b['id']]);
        return response()->json(['success' => true, 'items' => array_slice($items, 0, 30)]);
    }
}
