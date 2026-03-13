<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TalkController extends Controller
{
    /**
     * メッセージ一覧
     */
    public function index()
    {
        $isCastPortal = request()->is('cast/*');

        if ($isCastPortal) {
            $profileRoute = 'cast.shopprofileview.show';
        } else {
            $profileRoute = 'shop.castprofileview.show';
        }

        $conversations = $this->buildTalkList($isCastPortal);
        $requestTalks = $conversations
            ->filter(fn ($talk) => ($talk['unread_count'] ?? 0) > 0 && ($talk['reply_count'] ?? 0) === 0)
            ->values()
            ->all();
        $ongoingTalks = $conversations
            ->reject(fn ($talk) => ($talk['unread_count'] ?? 0) > 0 && ($talk['reply_count'] ?? 0) === 0)
            ->values()
            ->all();

        return view('common.talk.index', compact('ongoingTalks', 'requestTalks', 'profileRoute'));
    }
    /**
     * トークルーム
     */
    public function room($id)
    {
        $isCastPortal = request()->is('cast/*');
        $currentId = $isCastPortal ? $this->currentCastId() : $this->currentShopId();
        $partnerId = (string) $id;
        $partner = $this->resolvePartner($partnerId, $isCastPortal);
        abort_unless($partner, 404);

        $messages = DB::table('messages')
            ->where($isCastPortal ? 'cast_id' : 'shop_id', $currentId)
            ->where($isCastPortal ? 'shop_id' : 'cast_id', $partnerId)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->map(function ($message) use ($isCastPortal) {
                return (object) [
                    'content' => $message->content,
                    'is_mine' => (int) $message->sender_type === ($isCastPortal ? 1 : 2),
                    'created_at' => Carbon::parse($message->created_at),
                ];
            });

        DB::table('messages')
            ->where($isCastPortal ? 'cast_id' : 'shop_id', $currentId)
            ->where($isCastPortal ? 'shop_id' : 'cast_id', $partnerId)
            ->where('sender_type', $isCastPortal ? 2 : 1)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'updated_at' => now(),
            ]);

        return view('common.talk.room', [
            'partnerName' => $partner['name'],
            'partnerAvatar' => $partner['avatar'],
            'messages' => $messages,
            'partnerId' => $partnerId
        ]);
    }

    /**
     * メッセージ送信 (Shops/TalkController より統合)
     */
    public function store(Request $request)
    {
        $isCastPortal = request()->is('cast/*');
        $request->validate([
            'partner_id' => ['required', 'string'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $partnerId = (string) $request->input('partner_id');
        abort_unless($this->resolvePartner($partnerId, $isCastPortal), 404);

        $payload = [
            'cast_id' => $isCastPortal ? $this->currentCastId() : $partnerId,
            'shop_id' => $isCastPortal ? $partnerId : $this->currentShopId(),
            'sender_type' => $isCastPortal ? 1 : 2,
            'type' => 1,
            'content' => trim((string) $request->input('message')),
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('messages')->insert($payload);

        return response()->json([
            'success' => true,
            'message' => '送信しました',
            'data' => [
                'content' => $payload['content'],
                'time' => Carbon::now()->format('H:i')
            ]
        ]);
    }

    private function buildTalkList(bool $isCastPortal)
    {
        $currentId = $isCastPortal ? $this->currentCastId() : $this->currentShopId();
        $mySenderType = $isCastPortal ? 1 : 2;
        $partnerColumn = $isCastPortal ? 'shop_id' : 'cast_id';

        $rows = DB::table('messages')
            ->where($isCastPortal ? 'cast_id' : 'shop_id', $currentId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return $rows
            ->groupBy($partnerColumn)
            ->map(function ($messages, $partnerId) use ($isCastPortal, $mySenderType) {
                $latest = $messages->first();
                $partner = $this->resolvePartner((string) $partnerId, $isCastPortal);
                if (!$partner) {
                    return null;
                }

                $latestAt = Carbon::parse($latest->created_at);

                return [
                    'partner_id' => (string) $partnerId,
                    'name' => $partner['name'],
                    'age' => $partner['age'],
                    'location' => $partner['location'],
                    'avatar' => $partner['avatar'],
                    'last_message' => $latest->content,
                    'last_time' => $this->formatTalkTime($latestAt),
                    'sort_key' => $latestAt,
                    'unread_count' => $messages
                        ->where('sender_type', '!=', $mySenderType)
                        ->where('is_read', false)
                        ->count(),
                    'reply_count' => $messages
                        ->where('sender_type', $mySenderType)
                        ->count(),
                    'last_message_by_me' => (int) $latest->sender_type === $mySenderType,
                    'is_read' => (bool) $latest->is_read,
                    'pinned' => false,
                ];
            })
            ->filter()
            ->sortByDesc('sort_key')
            ->values();
    }

    private function resolvePartner(string $partnerId, bool $isCastPortal): ?array
    {
        if ($isCastPortal) {
            $row = DB::table('shops')
                ->leftJoin('shop_profiles', 'shops.id', '=', 'shop_profiles.shop_id')
                ->where('shops.id', $partnerId)
                ->select(
                    'shops.id',
                    'shop_profiles.shop_name',
                    'shop_profiles.pref',
                    'shop_profiles.city',
                    'shop_profiles.main_image_path'
                )
                ->first();

            if (!$row) {
                return null;
            }

            return [
                'name' => $row->shop_name ?: 'お店',
                'age' => null,
                'location' => trim(implode('', array_filter([$row->pref ?? null, $row->city ?? null]))),
                'avatar' => $this->assetPathForStored($row->main_image_path ?? null),
            ];
        }

        $row = DB::table('casts')
            ->leftJoin('cast_profiles', 'casts.id', '=', 'cast_profiles.cast_id')
            ->where('casts.id', $partnerId)
            ->select(
                'casts.id',
                'cast_profiles.nickname',
                'cast_profiles.name',
                'cast_profiles.pref',
                'cast_profiles.city',
                'cast_profiles.birthday'
            )
            ->first();

        if (!$row) {
            return null;
        }

        $mainImagePath = DB::table('cast_images')
            ->where('cast_id', $partnerId)
            ->where('type', 1)
            ->orderByRaw('is_main DESC')
            ->orderByRaw('main_order IS NULL')
            ->orderBy('main_order')
            ->orderBy('id')
            ->value('image_path');

        return [
            'name' => $row->nickname ?: ($row->name ?: 'キャスト'),
            'age' => !empty($row->birthday) ? Carbon::parse($row->birthday)->age : null,
            'location' => trim(implode('', array_filter([$row->pref ?? null, $row->city ?? null]))),
            'avatar' => $this->assetPathForStored($mainImagePath),
        ];
    }

    private function currentCastId(): string
    {
        return (string) auth()->guard('member')->id();
    }

    private function currentShopId(): string
    {
        return (string) auth()->guard('shop')->user()->shop_id;
    }

    private function formatTalkTime(Carbon $dateTime): string
    {
        if ($dateTime->isToday()) {
            return $dateTime->format('H:i');
        }

        if ($dateTime->isYesterday()) {
            return '昨日';
        }

        return $dateTime->format('Y/m/d');
    }

    private function assetPathForStored(?string $path): string
    {
        if (empty($path)) {
            return asset('assets/images/common/no-image.png');
        }

        if (str_starts_with($path, 'uploads/')) {
            return asset($path);
        }

        if (str_starts_with($path, 'public/')) {
            return asset('storage/' . substr($path, 7));
        }

        return asset(ltrim($path, '/'));
    }
}