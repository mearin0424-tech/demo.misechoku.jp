<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Services\MessageTemplateService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TalkController extends Controller
{
    private const MESSAGE_TYPE_TEXT = 1;
    private const MESSAGE_TYPE_INTERVIEW_OFFER = 2;
    private const MESSAGE_TYPE_INTERVIEW_CONFIRMED = 3;
    private const MESSAGE_TYPE_HIRED = 4;
    private const MESSAGE_TYPE_REJECTED = 5;
    private const APPLICATION_STATUS_CHATTING = 1;
    private const APPLICATION_STATUS_INTERVIEW_PENDING = 2;
    private const APPLICATION_STATUS_INTERVIEW_FIXED = 3;
    private const APPLICATION_STATUS_HIRED = 4;
    private const APPLICATION_STATUS_REJECTED = 5;

    public function __construct(private readonly MessageTemplateService $messageTemplateService)
    {
    }

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
        $castId = $isCastPortal ? $currentId : $partnerId;
        $shopId = $isCastPortal ? $partnerId : $currentId;
        $blockState = $this->getBlockState($castId, $shopId, $isCastPortal);
        $currentApplicationStatus = $this->getCurrentApplicationStatus($castId, $shopId);

        $rawMessages = DB::table('messages')
            ->where($isCastPortal ? 'cast_id' : 'shop_id', $currentId)
            ->where($isCastPortal ? 'shop_id' : 'cast_id', $partnerId)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();
        $messages = $this->mapRoomMessages($rawMessages, $isCastPortal);

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
            'partnerId' => $partnerId,
            'actionUrl' => $isCastPortal ? route('cast.talk.action') : route('shop.talk.action'),
            'blockUrl' => $isCastPortal ? route('cast.talk.block') : route('shop.talk.block'),
            'blockState' => $blockState,
            'canSend' => !$blockState['is_blocked'],
            'currentStatusCode' => $this->applicationStatusCode($currentApplicationStatus),
            'currentStatusLabel' => $this->statusLabel($this->applicationStatusCode($currentApplicationStatus)),
            'canOfferInterview' => !$isCastPortal
                && !$blockState['is_blocked']
                && $currentApplicationStatus === self::APPLICATION_STATUS_CHATTING,
            'canConfirmInterview' => $isCastPortal
                && !$blockState['is_blocked']
                && $currentApplicationStatus === self::APPLICATION_STATUS_INTERVIEW_PENDING,
            'canSelectResult' => !$isCastPortal
                && !$blockState['is_blocked']
                && $currentApplicationStatus === self::APPLICATION_STATUS_INTERVIEW_FIXED,
            'canCancelStatus' => !$isCastPortal
                && !$blockState['is_blocked']
                && in_array($currentApplicationStatus, [
                    self::APPLICATION_STATUS_INTERVIEW_PENDING,
                    self::APPLICATION_STATUS_INTERVIEW_FIXED,
                ], true),
            'resultMessageTemplates' => !$isCastPortal
                ? [
                    'hired' => $this->messageTemplateService->getTemplates('talk_hired'),
                    'rejected' => $this->messageTemplateService->getTemplates('talk_rejected'),
                ]
                : [],
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
        $this->abortIfBlocked($partnerId, $isCastPortal);

        $payload = [
            'cast_id' => $isCastPortal ? $this->currentCastId() : $partnerId,
            'shop_id' => $isCastPortal ? $partnerId : $this->currentShopId(),
            'sender_type' => $this->mySenderType($isCastPortal),
            'type' => self::MESSAGE_TYPE_TEXT,
            'content' => trim((string) $request->input('message')),
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $messageId = DB::table('messages')->insertGetId($payload);

        return response()->json([
            'success' => true,
            'message' => '送信しました',
            'data' => [
                'message_id' => $messageId,
                'content' => $payload['content'],
                'time' => Carbon::now()->format('H:i')
            ]
        ]);
    }

    /**
     * メッセージ削除（送信から10分以内の自分のテキストメッセージのみ）
     */
    public function destroy(Request $request)
    {
        $isCastPortal = request()->is('cast/*');
        $request->validate([
            'partner_id' => ['required', 'string'],
            'message_id' => ['required', 'integer', 'min:1'],
        ]);

        $partnerId = (string) $request->input('partner_id');
        $messageId = (int) $request->input('message_id');
        abort_unless($this->resolvePartner($partnerId, $isCastPortal), 404);

        $currentId = $isCastPortal ? $this->currentCastId() : $this->currentShopId();
        $mySenderType = $this->mySenderType($isCastPortal);
        $castId = $isCastPortal ? $currentId : $partnerId;
        $shopId = $isCastPortal ? $partnerId : $currentId;

        $message = DB::table('messages')
            ->where('id', $messageId)
            ->where('cast_id', $castId)
            ->where('shop_id', $shopId)
            ->first();

        abort_unless($message, 404);
        abort_if((int) $message->sender_type !== $mySenderType, 403, '自分のメッセージのみ削除できます。');
        abort_if((int) $message->type !== self::MESSAGE_TYPE_TEXT, 403, 'テキストメッセージのみ削除できます。');

        $createdAt = Carbon::parse($message->created_at);
        $limit = Carbon::now()->subMinutes(10);
        abort_if($createdAt->lt($limit), 422, '送信から10分を過ぎたメッセージは削除できません。');

        DB::table('messages')->where('id', $messageId)->delete();

        return response()->json(['success' => true, 'message' => '削除しました']);
    }

    public function action(Request $request)
    {
        $isCastPortal = request()->is('cast/*');
        $request->validate([
            'partner_id' => ['required', 'string'],
            'action_type' => ['required', 'string', 'in:interview_offer,interview_confirm,hired,rejected,cancel_status'],
            'options' => ['nullable', 'array'],
            'options.*' => ['nullable', 'string'],
            'offer_token' => ['nullable', 'string'],
            'selected_option' => ['nullable', 'string'],
            'message' => ['nullable', 'string', 'max:5000'],
        ]);

        $partnerId = (string) $request->input('partner_id');
        abort_unless($this->resolvePartner($partnerId, $isCastPortal), 404);
        $this->abortIfBlocked($partnerId, $isCastPortal);

        $actionType = $request->input('action_type');
        abort_if($isCastPortal && $actionType !== 'interview_confirm', 403);
        abort_if(!$isCastPortal && $actionType === 'interview_confirm', 403);
        $castId = $isCastPortal ? $this->currentCastId() : $partnerId;
        $shopId = $isCastPortal ? $partnerId : $this->currentShopId();
        $currentApplicationStatus = $this->getCurrentApplicationStatus($castId, $shopId);

        if ($actionType === 'interview_offer') {
            abort_if(
                $currentApplicationStatus !== self::APPLICATION_STATUS_CHATTING,
                422,
                '面談候補日は「やり取り中」のときのみ送信できます。再設定する場合は先にキャンセルしてください。'
            );
        }

        if ($actionType === 'interview_confirm') {
            abort_if(
                $currentApplicationStatus !== self::APPLICATION_STATUS_INTERVIEW_PENDING,
                422,
                '面談日は「面談日調整中」の候補に対してのみ確定できます。'
            );
        }

        if (in_array($actionType, ['hired', 'rejected'], true)) {
            abort_if(
                $currentApplicationStatus !== self::APPLICATION_STATUS_INTERVIEW_FIXED,
                422,
                '採用／不採用は面談日決定後にのみ選択できます。'
            );
        }

        if ($actionType === 'cancel_status') {
            abort_if(
                !in_array($currentApplicationStatus, [
                    self::APPLICATION_STATUS_INTERVIEW_PENDING,
                    self::APPLICATION_STATUS_INTERVIEW_FIXED,
                ], true),
                422,
                'キャンセルできるのは面談日調整中または面談日決定のステータスのみです。'
            );
        }

        [$messageType, $content] = match ($actionType) {
            'interview_offer' => [
                self::MESSAGE_TYPE_INTERVIEW_OFFER,
                json_encode([
                    'offer_token' => (string) Str::uuid(),
                    'options' => collect($request->input('options', []))
                        ->map(fn ($option) => trim((string) $option))
                        ->filter()
                        ->values()
                        ->all(),
                ], JSON_UNESCAPED_UNICODE),
            ],
            'interview_confirm' => [
                self::MESSAGE_TYPE_INTERVIEW_CONFIRMED,
                json_encode([
                    'offer_token' => (string) $request->input('offer_token'),
                    'selected_option' => trim((string) $request->input('selected_option')),
                ], JSON_UNESCAPED_UNICODE),
            ],
            'hired' => [
                self::MESSAGE_TYPE_HIRED,
                $this->resolveResultMessage((string) $actionType, (string) $request->input('message')),
            ],
            'rejected' => [
                self::MESSAGE_TYPE_REJECTED,
                $this->resolveResultMessage((string) $actionType, (string) $request->input('message')),
            ],
            'cancel_status' => [
                self::MESSAGE_TYPE_TEXT,
                '面談ステータスをキャンセルし、やり取り中に戻しました。必要に応じて面談候補日を再設定してください。',
            ],
        };

        if ($actionType === 'interview_offer') {
            abort_if(empty(json_decode($content, true)['options'] ?? []), 422);
        }

        if ($actionType === 'interview_confirm') {
            $payload = json_decode($content, true) ?: [];
            abort_if(empty($payload['offer_token']) || empty($payload['selected_option']), 422);
        }

        DB::table('messages')->insert([
            'cast_id' => $isCastPortal ? $this->currentCastId() : $partnerId,
            'shop_id' => $isCastPortal ? $partnerId : $this->currentShopId(),
            'sender_type' => $this->mySenderType($isCastPortal),
            'type' => $messageType,
            'content' => $content,
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->syncApplicationStatusFromTalkAction($partnerId, $isCastPortal, $actionType, $content);

        return response()->json([
            'success' => true,
        ]);
    }

    public function toggleBlock(Request $request)
    {
        $isCastPortal = request()->is('cast/*');
        $request->validate([
            'partner_id' => ['required', 'string'],
        ]);

        $partnerId = (string) $request->input('partner_id');
        abort_unless($this->resolvePartner($partnerId, $isCastPortal), 404);

        $castId = $isCastPortal ? $this->currentCastId() : $partnerId;
        $shopId = $isCastPortal ? $partnerId : $this->currentShopId();
        $actor = $isCastPortal ? 'cast' : 'shop';
        $block = DB::table('talk_blocks')
            ->where('cast_id', $castId)
            ->where('shop_id', $shopId)
            ->first();

        if ($block) {
            abort_if($block->blocked_by !== $actor, 403);

            DB::table('talk_blocks')
                ->where('id', $block->id)
                ->delete();

            return redirect()
                ->back()
                ->with('message', 'ブロックを解除しました。');
        }

        DB::table('talk_blocks')->insert([
            'cast_id' => $castId,
            'shop_id' => $shopId,
            'blocked_by' => $actor,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->back()
            ->with('message', 'この相手をブロックしました。');
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
            ->map(function ($messages, $partnerId) use ($isCastPortal, $mySenderType, $currentId) {
                $latest = $messages->first();
                $partner = $this->resolvePartner((string) $partnerId, $isCastPortal);
                if (!$partner) {
                    return null;
                }

                $latestAt = Carbon::parse($latest->created_at);
                $statusCode = $this->resolveConversationStatusCode($messages);
                $applicationStatus = $this->getCurrentApplicationStatus(
                    $isCastPortal ? $currentId : (string) $partnerId,
                    $isCastPortal ? (string) $partnerId : $currentId
                );
                $statusCode = $this->applicationStatusCode($applicationStatus);
                $blockState = $this->getBlockState(
                    $isCastPortal ? $currentId : (string) $partnerId,
                    $isCastPortal ? (string) $partnerId : $currentId,
                    $isCastPortal
                );

                return [
                    'partner_id' => (string) $partnerId,
                    'profile_id' => $isCastPortal ? $this->toNumericShopId((string) $partnerId) : (string) $partnerId,
                    'name' => $partner['name'],
                    'age' => $partner['age'],
                    'location' => $partner['location'],
                    'avatar' => $partner['avatar'],
                    'last_message' => $this->formatTalkPreview($latest, $mySenderType),
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
                    'status_code' => $statusCode,
                    'status_label' => $blockState['is_blocked']
                        ? ($blockState['blocked_by_me'] ? 'ブロック中' : '相手がブロック中')
                        : $this->statusLabel($statusCode),
                    'pinned' => false,
                ];
            })
            ->filter()
            ->sortByDesc('sort_key')
            ->values();
    }

    private function mapRoomMessages($messages, bool $isCastPortal)
    {
        $confirmedByToken = $messages
            ->filter(fn ($message) => (int) $message->type === self::MESSAGE_TYPE_INTERVIEW_CONFIRMED)
            ->mapWithKeys(function ($message) {
                $meta = $this->decodeMessageMeta($message);
                return empty($meta['offer_token']) ? [] : [$meta['offer_token'] => ($meta['selected_option'] ?? null)];
            });

        $now = Carbon::now();
        $deleteLimit = $now->copy()->subMinutes(10);

        return $messages->map(function ($message) use ($isCastPortal, $confirmedByToken, $deleteLimit) {
            $meta = $this->decodeMessageMeta($message);
            $type = (int) $message->type;
            $offerToken = $meta['offer_token'] ?? null;
            $isMine = (int) $message->sender_type === $this->mySenderType($isCastPortal);
            $createdAt = Carbon::parse($message->created_at);
            $canDelete = $isMine && $type === self::MESSAGE_TYPE_TEXT && $createdAt->gte($deleteLimit);

            return (object) [
                'id' => (int) $message->id,
                'type' => $type,
                'content' => $type === self::MESSAGE_TYPE_TEXT || $type === self::MESSAGE_TYPE_HIRED || $type === self::MESSAGE_TYPE_REJECTED
                    ? $message->content
                    : ($meta['selected_option'] ?? ''),
                'is_mine' => $isMine,
                'created_at' => $createdAt,
                'can_delete' => $canDelete,
                'interview_options' => $type === self::MESSAGE_TYPE_INTERVIEW_OFFER ? ($meta['options'] ?? []) : [],
                'offer_token' => $offerToken,
                'selected_option' => $offerToken ? ($confirmedByToken[$offerToken] ?? null) : ($meta['selected_option'] ?? null),
            ];
        });
    }

    private function conversationMessages(string $partnerId, bool $isCastPortal)
    {
        return DB::table('messages')
            ->where($isCastPortal ? 'cast_id' : 'shop_id', $isCastPortal ? $this->currentCastId() : $this->currentShopId())
            ->where($isCastPortal ? 'shop_id' : 'cast_id', $partnerId)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();
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
                'avatar' => $this->resolveShopAvatar((string) $row->id, $row->main_image_path ?? null),
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

    private function mySenderType(bool $isCastPortal): int
    {
        return $isCastPortal ? 1 : 2;
    }

    private function decodeMessageMeta($message): array
    {
        $decoded = json_decode((string) $message->content, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function formatTalkPreview($message, int $mySenderType): string
    {
        $type = (int) $message->type;
        $isMine = (int) $message->sender_type === $mySenderType;
        $meta = $this->decodeMessageMeta($message);

        return match ($type) {
            self::MESSAGE_TYPE_INTERVIEW_OFFER => $isMine
                ? '面談候補日を送りました'
                : '面談候補日が届いています',
            self::MESSAGE_TYPE_INTERVIEW_CONFIRMED => empty($meta['selected_option'])
                ? '面談日が確定しました'
                : '面談日が確定しました: ' . Carbon::parse($meta['selected_option'])->format('Y年n月j日 H:i'),
            self::MESSAGE_TYPE_HIRED => $isMine
                ? '採用メッセージを送りました'
                : '採用メッセージが届いています',
            self::MESSAGE_TYPE_REJECTED => $isMine
                ? '不採用メッセージを送りました'
                : '不採用メッセージが届いています',
            default => Str::limit(preg_replace('/\s+/u', ' ', trim((string) $message->content)), 60, '...'),
        };
    }

    private function resolveConversationStatusCode($messages): string
    {
        $latestAction = $this->latestConversationAction($messages);

        if (!$latestAction) {
            return 'chatting';
        }

        return match ((int) $latestAction->type) {
            self::MESSAGE_TYPE_INTERVIEW_OFFER => 'interview_pending',
            self::MESSAGE_TYPE_INTERVIEW_CONFIRMED => 'interview_fixed',
            self::MESSAGE_TYPE_HIRED => 'hired',
            self::MESSAGE_TYPE_REJECTED => 'rejected',
            default => 'chatting',
        };
    }

    private function latestConversationAction($messages)
    {
        return $messages
            ->filter(fn ($message) => in_array((int) $message->type, [
                self::MESSAGE_TYPE_INTERVIEW_OFFER,
                self::MESSAGE_TYPE_INTERVIEW_CONFIRMED,
                self::MESSAGE_TYPE_HIRED,
                self::MESSAGE_TYPE_REJECTED,
            ], true))
            ->sortByDesc(fn ($message) => sprintf(
                '%s-%010d',
                Carbon::parse($message->created_at)->format('YmdHis'),
                (int) ($message->id ?? 0)
            ))
            ->first();
    }

    private function statusLabel(string $code): string
    {
        return match ($code) {
            'interview_pending' => '面談調整中',
            'interview_fixed' => '面談日決定',
            'hired' => '採用',
            'rejected' => '不採用',
            default => 'やり取り中',
        };
    }

    private function getBlockState(string $castId, string $shopId, bool $isCastPortal): array
    {
        $block = DB::table('talk_blocks')
            ->where('cast_id', $castId)
            ->where('shop_id', $shopId)
            ->first();
        $actor = $isCastPortal ? 'cast' : 'shop';

        return [
            'is_blocked' => (bool) $block,
            'blocked_by' => $block->blocked_by ?? null,
            'blocked_by_me' => $block ? $block->blocked_by === $actor : false,
            'blocked_by_other' => $block ? $block->blocked_by !== $actor : false,
        ];
    }

    private function abortIfBlocked(string $partnerId, bool $isCastPortal): void
    {
        $castId = $isCastPortal ? $this->currentCastId() : $partnerId;
        $shopId = $isCastPortal ? $partnerId : $this->currentShopId();
        $blockState = $this->getBlockState($castId, $shopId, $isCastPortal);

        abort_if($blockState['is_blocked'], 403, 'このトークはブロックされています。');
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

    private function resolveShopAvatar(string $shopId, ?string $fallbackPath = null): string
    {
        $imagePath = DB::table('shop_images')
            ->where('shop_id', $shopId)
            ->orderByRaw('is_main DESC')
            ->orderByRaw('main_order IS NULL')
            ->orderBy('main_order')
            ->orderBy('id')
            ->value('image_path');

        return $this->assetPathForStored($imagePath ?: $fallbackPath);
    }

    private function toNumericShopId(string $shopId): int
    {
        return (int) ltrim(str_starts_with($shopId, 's') ? substr($shopId, 1) : $shopId, '0');
    }

    private function getCurrentApplicationStatus(string $castId, string $shopId): int
    {
        $application = $this->findApplicationForTalk($castId, $shopId);

        return (int) ($application->status ?? self::APPLICATION_STATUS_CHATTING);
    }

    private function applicationStatusCode(int $status): string
    {
        return match ($status) {
            self::APPLICATION_STATUS_INTERVIEW_PENDING => 'interview_pending',
            self::APPLICATION_STATUS_INTERVIEW_FIXED => 'interview_fixed',
            self::APPLICATION_STATUS_HIRED => 'hired',
            self::APPLICATION_STATUS_REJECTED => 'rejected',
            default => 'chatting',
        };
    }

    private function syncApplicationStatusFromTalkAction(string $partnerId, bool $isCastPortal, string $actionType, string $content): void
    {
        $castId = $isCastPortal ? $this->currentCastId() : $partnerId;
        $shopId = $isCastPortal ? $partnerId : $this->currentShopId();
        $application = $this->resolveOrCreateApplicationForTalk($castId, $shopId);

        if (!$application) {
            return;
        }

        $updates = ['updated_at' => now()];
        $meta = $content !== '' ? (json_decode($content, true) ?: []) : [];

        if ($actionType === 'interview_offer') {
            $updates['status'] = self::APPLICATION_STATUS_INTERVIEW_PENDING;
        } elseif ($actionType === 'interview_confirm') {
            $updates['status'] = self::APPLICATION_STATUS_INTERVIEW_FIXED;
            $updates['result_date'] = !empty($meta['selected_option'])
                ? Carbon::parse($meta['selected_option'])->toDateString()
                : $application->result_date;
        } elseif ($actionType === 'hired') {
            $updates['status'] = self::APPLICATION_STATUS_HIRED;
            $updates['reason_rejection'] = null;
        } elseif ($actionType === 'rejected') {
            $updates['status'] = self::APPLICATION_STATUS_REJECTED;
            $updates['reason_rejection'] = trim($content);
        } elseif ($actionType === 'cancel_status') {
            $updates['status'] = self::APPLICATION_STATUS_CHATTING;
            $updates['result_date'] = null;
            $updates['reason_rejection'] = null;
        }

        DB::table('shop_job_applications')
            ->where('id', $application->id)
            ->update($updates);
    }

    private function findApplicationForTalk(string $castId, string $shopId): ?object
    {
        return DB::table('shop_job_applications')
            ->join('shop_jobs', 'shop_job_applications.shop_job_id', '=', 'shop_jobs.id')
            ->where('shop_job_applications.cast_id', $castId)
            ->where('shop_jobs.shop_id', $shopId)
            ->orderByDesc('shop_job_applications.id')
            ->select('shop_job_applications.*')
            ->first();
    }

    private function resolveOrCreateApplicationForTalk(string $castId, string $shopId): ?object
    {
        $application = $this->findApplicationForTalk($castId, $shopId);

        if ($application) {
            return $application;
        }

        $shopJob = DB::table('shop_jobs')
            ->where('shop_id', $shopId)
            ->orderByDesc('id')
            ->first();

        if (!$shopJob) {
            return null;
        }

        $applicationId = DB::table('shop_job_applications')->insertGetId([
            'cast_id' => $castId,
            'shop_job_id' => $shopJob->id,
            'status' => 1,
            'result_date' => null,
            'hourly_wage_regular' => $shopJob->hourly_wage_regular,
            'normal_time' => $shopJob->normal_time,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('shop_job_applications')->where('id', $applicationId)->first();
    }

    private function resolveResultMessage(string $actionType, string $customMessage): string
    {
        $message = trim($customMessage);
        if ($message !== '') {
            return $message;
        }

        return match ($actionType) {
            'hired' => $this->messageTemplateService->getDefaultBody('talk_hired'),
            'rejected' => $this->messageTemplateService->getDefaultBody('talk_rejected'),
            default => '',
        };
    }
}