<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Services\MessageTemplateService;
use App\Services\ShopJobApplicationJobSnapshotService;
use App\Support\ShopJobApplicationView;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

    public function __construct(
        private readonly MessageTemplateService $messageTemplateService,
        private readonly ShopJobApplicationJobSnapshotService $shopJobApplicationJobSnapshotService,
    ) {
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
        $selectedTalkJobKind = $this->getSelectedTalkJobKind($castId, $shopId);
        $applicationForReview = $isCastPortal && $currentApplicationStatus === self::APPLICATION_STATUS_HIRED
            ? $this->findApplicationForTalk($castId, $shopId)
            : null;

        $rawMessages = DB::table('messages')
            ->where($isCastPortal ? 'cast_id' : 'shop_id', $currentId)
            ->where($isCastPortal ? 'shop_id' : 'cast_id', $partnerId)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();
        $messages = $this->mapRoomMessages($rawMessages, $isCastPortal);
        $initialTalkTopic = $this->normalizeTalkTopic((string) request()->query('talk_topic', ''));
        $initialTalkJobKind = $this->normalizeTalkJobKind((string) request()->query('job_kind', ''));

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
                && $currentApplicationStatus === self::APPLICATION_STATUS_CHATTING
                && $selectedTalkJobKind !== null,
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
            'reviewApplicationId' => $applicationForReview ? (int) $applicationForReview->id : null,
            'initialTalkTopic' => $initialTalkTopic,
            'initialTalkJobKind' => $initialTalkJobKind,
            'hasMessages' => $messages->isNotEmpty(),
            'selectedTalkJobKind' => $selectedTalkJobKind,
            'canSelectTalkJobKind' => !$blockState['is_blocked']
                && in_array($currentApplicationStatus, [
                    self::APPLICATION_STATUS_CHATTING,
                    self::APPLICATION_STATUS_INTERVIEW_PENDING,
                ], true),
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
            'talk_topic' => ['nullable', 'string', 'in:new_hire,help,other'],
            'talk_job_kind' => ['nullable', 'string', 'in:fulltime,trial,help'],
        ]);

        $partnerId = (string) $request->input('partner_id');
        abort_unless($this->resolvePartner($partnerId, $isCastPortal), 404);
        $this->abortIfBlocked($partnerId, $isCastPortal);

        $content = trim((string) $request->input('message'));
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        $content = preg_replace('/\n{2,}/', "\n", $content);

        $payload = [
            'cast_id' => $isCastPortal ? $this->currentCastId() : $partnerId,
            'shop_id' => $isCastPortal ? $partnerId : $this->currentShopId(),
            'sender_type' => $this->mySenderType($isCastPortal),
            'type' => self::MESSAGE_TYPE_TEXT,
            'content' => $content,
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $messageId = DB::table('messages')->insertGetId($payload);
        if ($isCastPortal) {
            $this->ensureApplicationForTalkStart(
                (string) $this->currentCastId(),
                (string) $partnerId,
                $this->normalizeTalkTopic((string) $request->input('talk_topic', '')),
                $this->normalizeTalkJobKind((string) $request->input('talk_job_kind', ''))
            );
        }

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
            'action_type' => ['required', 'string', 'in:interview_offer,interview_confirm,hired,rejected,cancel_status,set_job_kind'],
            'options' => ['nullable', 'array'],
            'options.*' => ['nullable', 'string'],
            'offer_token' => ['nullable', 'string'],
            'selected_option' => ['nullable', 'string'],
            'message' => ['nullable', 'string', 'max:5000'],
            'hired_regular_hourly_wage' => ['nullable', 'string', 'max:32'],
            'employment_kind' => ['nullable', 'string', 'in:fulltime,trial,help'],
            'job_kind' => ['nullable', 'string', 'in:fulltime,trial,help'],
        ]);

        $partnerId = (string) $request->input('partner_id');
        abort_unless($this->resolvePartner($partnerId, $isCastPortal), 404);
        $this->abortIfBlocked($partnerId, $isCastPortal);

        $actionType = $request->input('action_type');
        abort_if($isCastPortal && $actionType !== 'interview_confirm', 403);
        abort_if(!$isCastPortal && $actionType === 'interview_confirm', 403);
        $castId = $isCastPortal ? $this->currentCastId() : $partnerId;
        $shopId = $isCastPortal ? $partnerId : $this->currentShopId();
        $bonusMeta = in_array($actionType, ['interview_offer', 'interview_confirm'], true)
            ? $this->buildJobBonusMetaForConversation($castId, $shopId)
            : null;
        $currentApplicationStatus = $this->getCurrentApplicationStatus($castId, $shopId);

        if ($actionType === 'interview_offer') {
            abort_if(
                $this->getSelectedTalkJobKind($castId, $shopId) === null,
                422,
                '面談候補日を送る前に求人種別（体験入店／本入店／ヘルプ）を選択してください。'
            );
            abort_if(
                $currentApplicationStatus !== self::APPLICATION_STATUS_CHATTING,
                422,
                '面談候補日は「やり取り中」のときのみ送信できます。再設定する場合は先にキャンセルしてください。'
            );
        }

        if ($actionType === 'set_job_kind') {
            abort_if(
                !in_array($currentApplicationStatus, [
                    self::APPLICATION_STATUS_CHATTING,
                    self::APPLICATION_STATUS_INTERVIEW_PENDING,
                ], true),
                422,
                '面談日確定後は求人種別を変更できません。'
            );
            $jobKind = $this->normalizeTalkJobKind((string) $request->input('job_kind', ''));
            if ($jobKind === null) {
                abort(422, '求人種別を選択してください。');
            }
            $this->setConversationJobKind($castId, $shopId, $jobKind);

            return response()->json(['success' => true]);
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
                    'bonus_meta' => $bonusMeta,
                ], JSON_UNESCAPED_UNICODE),
            ],
            'interview_confirm' => [
                self::MESSAGE_TYPE_INTERVIEW_CONFIRMED,
                json_encode([
                    'offer_token' => (string) $request->input('offer_token'),
                    'selected_option' => trim((string) $request->input('selected_option')),
                    'bonus_meta' => $bonusMeta,
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

        $hiredWageNormalized = null;
        if ($actionType === 'hired') {
            $hiredWageNormalized = ShopJobApplicationView::normalizeWageDigits(
                $request->input('hired_regular_hourly_wage') !== null
                    ? (string) $request->input('hired_regular_hourly_wage')
                    : null
            );
        }

        $selectedEmploymentKind = null;
        if (in_array($actionType, ['hired', 'rejected'], true)) {
            $selectedEmploymentKind = $this->normalizeEmploymentKind((string) $request->input('employment_kind', ''));
            if ($selectedEmploymentKind === null) {
                abort(422, '採用区分を選択してください。');
            }
        }

        $this->syncApplicationStatusFromTalkAction($partnerId, $isCastPortal, $actionType, $content, $hiredWageNormalized);
        if ($selectedEmploymentKind !== null) {
            $this->syncApplicationEmploymentKindFromTalkAction($partnerId, $isCastPortal, $selectedEmploymentKind, $actionType);
        }

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
                    DB::raw("(SELECT si.image_path FROM shop_images si WHERE si.shop_id = shops.id ORDER BY si.is_main DESC, si.main_order IS NULL, si.main_order, si.id LIMIT 1) as main_image_path")
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

    private function syncApplicationStatusFromTalkAction(
        string $partnerId,
        bool $isCastPortal,
        string $actionType,
        string $content,
        ?string $hiredRegularHourlyWage = null,
    ): void {
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
            $hiredBonus = $this->resolveHiredBonusForApplicationUpdate($application);
            if ($hiredBonus !== null) {
                $updates['hired_bonus_amount'] = $hiredBonus['bonus_amount'];
                if (Schema::hasColumn('shop_job_applications', 'hired_bonus_condition')) {
                    $updates['hired_bonus_condition'] = $hiredBonus['bonus_condition'];
                }
            }
            if (Schema::hasColumn('shop_job_applications', 'hired_regular_hourly_wage') && $hiredRegularHourlyWage !== null) {
                $updates['hired_regular_hourly_wage'] = $hiredRegularHourlyWage;
            }
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

    private function syncApplicationEmploymentKindFromTalkAction(
        string $partnerId,
        bool $isCastPortal,
        string $employmentKind,
        string $actionType,
    ): void {
        $castId = $isCastPortal ? $this->currentCastId() : $partnerId;
        $shopId = $isCastPortal ? $partnerId : $this->currentShopId();
        $application = $this->resolveOrCreateApplicationForTalk($castId, $shopId);
        if (!$application) {
            return;
        }

        $targetJobType = match ($employmentKind) {
            'trial' => 2,
            'help' => 3,
            default => 1,
        };
        $targetShopJobId = $this->resolveShopJobIdByType($shopId, $targetJobType);
        if ($targetShopJobId === null) {
            return;
        }

        $status = (int) $application->status;
        if ($actionType === 'hired') {
            $status = $employmentKind === 'fulltime'
                ? 6
                : self::APPLICATION_STATUS_HIRED;
        } elseif ($actionType === 'rejected') {
            $status = $employmentKind === 'trial'
                ? 7
                : self::APPLICATION_STATUS_REJECTED;
        }

        DB::table('shop_job_applications')
            ->where('id', $application->id)
            ->update([
                'shop_job_id' => $targetShopJobId,
                'status' => $status,
                'updated_at' => now(),
            ]);
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

        $row = [
            'cast_id' => $castId,
            'shop_job_id' => $shopJob->id,
            'status' => 1,
            'result_date' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        $row = array_merge($row, $this->shopJobApplicationJobSnapshotService->snapshotColumnsForApplication($shopJob));

        $applicationId = DB::table('shop_job_applications')->insertGetId($row);

        return DB::table('shop_job_applications')->where('id', $applicationId)->first();
    }

    private function ensureApplicationForTalkStart(
        string $castId,
        string $shopId,
        ?string $talkTopic,
        ?string $talkJobKind,
    ): void {
        if ($talkTopic === 'other') {
            return;
        }
        if ($this->findApplicationForTalk($castId, $shopId)) {
            return;
        }
        $targetJobType = match ($talkJobKind) {
            'trial' => 2,
            'help' => 3,
            default => 1,
        };
        $this->createApplicationForTalk($castId, $shopId, $targetJobType);
    }

    private function createApplicationForTalk(string $castId, string $shopId, int $preferredJobType): ?object
    {
        $shopJobId = $this->resolveShopJobIdByType($shopId, $preferredJobType);
        if ($shopJobId === null) {
            $shopJobId = $this->resolveShopJobIdByType($shopId, 1);
        }
        if ($shopJobId === null) {
            return null;
        }
        $shopJob = DB::table('shop_jobs')->where('id', $shopJobId)->first();
        if (!$shopJob) {
            return null;
        }

        $row = [
            'cast_id' => $castId,
            'shop_job_id' => $shopJob->id,
            'status' => 1,
            'result_date' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        $row = array_merge($row, $this->shopJobApplicationJobSnapshotService->snapshotColumnsForApplication($shopJob));
        $applicationId = DB::table('shop_job_applications')->insertGetId($row);

        return DB::table('shop_job_applications')->where('id', $applicationId)->first();
    }

    private function resolveShopJobIdByType(string $shopId, int $jobType): ?int
    {
        $query = DB::table('shop_jobs')->where('shop_id', $shopId);
        if (Schema::hasColumn('shop_jobs', 'job_type')) {
            $query->where('job_type', $jobType);
        } elseif ($jobType !== 1) {
            return null;
        }
        $id = $query->orderByDesc('id')->value('id');

        return $id ? (int) $id : null;
    }

    private function normalizeTalkTopic(string $value): ?string
    {
        $v = trim($value);
        return in_array($v, ['new_hire', 'help', 'other'], true) ? $v : null;
    }

    private function normalizeTalkJobKind(string $value): ?string
    {
        $v = trim($value);
        return in_array($v, ['fulltime', 'trial', 'help'], true) ? $v : null;
    }

    private function normalizeEmploymentKind(string $value): ?string
    {
        $v = trim($value);
        return in_array($v, ['fulltime', 'trial', 'help'], true) ? $v : null;
    }

    private function getSelectedTalkJobKind(string $castId, string $shopId): ?string
    {
        $application = $this->findApplicationForTalk($castId, $shopId);
        if (!$application) {
            return null;
        }
        $jobType = DB::table('shop_jobs')->where('id', $application->shop_job_id)->value('job_type');
        $jt = $jobType !== null ? (int) $jobType : 1;

        return match ($jt) {
            2 => 'trial',
            3 => 'help',
            default => 'fulltime',
        };
    }

    private function setConversationJobKind(string $castId, string $shopId, string $jobKind): void
    {
        $targetJobType = match ($jobKind) {
            'trial' => 2,
            'help' => 3,
            default => 1,
        };
        $targetShopJobId = $this->resolveShopJobIdByType($shopId, $targetJobType);
        if ($targetShopJobId === null) {
            abort(422, '選択した求人種別の求人票が見つかりません。');
        }

        $application = $this->findApplicationForTalk($castId, $shopId);
        if (!$application) {
            $this->createApplicationForTalk($castId, $shopId, $targetJobType);
            return;
        }

        DB::table('shop_job_applications')
            ->where('id', $application->id)
            ->update([
                'shop_job_id' => $targetShopJobId,
                'updated_at' => now(),
            ]);
    }

    /**
     * トーク相手との会話コンテキストから、求人票ベースのボーナス条件メタ情報を取得する
     * （ボーナス金額・勤務日数・勤務時間・フリーテキスト条件）。
     *
     * 面談候補日送信／面談日確定メッセージに埋め込み、後続のチェックリスト機能でも再利用する想定。
     */
    private function buildJobBonusMetaForConversation(string $castId, string $shopId): ?array
    {
        $application = $this->resolveOrCreateApplicationForTalk($castId, $shopId);
        if (!$application) {
            return null;
        }

        $job = DB::table('shop_jobs')->where('id', $application->shop_job_id)->first();
        if (!$job) {
            return null;
        }

        $meta = [];
        if (!empty($job->noruma_cond)) {
            $decoded = json_decode($job->noruma_cond, true);
            if (is_array($decoded)) {
                $meta = $decoded;
            }
        }

        if (property_exists($application, 'applied_norma_day') && $application->applied_norma_day !== null && $application->applied_norma_day !== '') {
            $meta['working_days'] = (string) (int) $application->applied_norma_day;
        }
        if (property_exists($application, 'applied_norma_hours') && $application->applied_norma_hours !== null && $application->applied_norma_hours !== '') {
            $meta['working_hours'] = (string) (int) $application->applied_norma_hours;
        }

        $extraCondition = ShopJobApplicationView::bonusConditionAtApplication($application);
        if ($extraCondition === '' && property_exists($job, 'bonus_condition') && $job->bonus_condition !== null && $job->bonus_condition !== '') {
            $extraCondition = trim((string) $job->bonus_condition);
        }
        if ($extraCondition === '' && isset($meta['bonus_condition'])) {
            $extraCondition = trim((string) $meta['bonus_condition']);
        }

        $bonusAmount = 0;
        if (property_exists($application, 'applied_bonus_reward') && $application->applied_bonus_reward !== null) {
            $bonusAmount = (int) $application->applied_bonus_reward;
        } else {
            $bonusAmount = (int) ($job->bonus_reward ?? $job->noruma_reward ?? $job->hourly_wage_regular ?? 0);
        }

        return [
            'bonus_amount' => $bonusAmount,
            'working_days' => isset($meta['working_days']) ? (string) $meta['working_days'] : '',
            'working_hours' => isset($meta['working_hours']) ? (string) $meta['working_hours'] : '',
            'extra_condition' => $extraCondition,
        ];
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

    /**
     * 採用時点のボーナス金・達成条件を求人から取得して返す（レガシー行・列向け）
     */
    private function snapshotHiredBonusForApplication(int $shopJobId): ?array
    {
        $job = DB::table('shop_jobs')->where('id', $shopJobId)->first();
        if (!$job) {
            return null;
        }

        $bonusAmount = 0;
        if (property_exists($job, 'bonus_reward') && $job->bonus_reward !== null) {
            $bonusAmount = (int) $job->bonus_reward;
        } elseif (property_exists($job, 'noruma_reward') && $job->noruma_reward !== null) {
            $bonusAmount = (int) $job->noruma_reward;
        } elseif (!property_exists($job, 'bonus_reward')
            && !property_exists($job, 'noruma_reward')
            && property_exists($job, 'hourly_wage_regular')
            && $job->hourly_wage_regular !== null) {
            $bonusAmount = (int) $job->hourly_wage_regular;
        }

        $bonusCondition = '';
        if (property_exists($job, 'bonus_condition') && $job->bonus_condition !== null && $job->bonus_condition !== '') {
            $bonusCondition = trim((string) $job->bonus_condition);
        } elseif (!empty($job->noruma_cond)) {
            $meta = json_decode((string) $job->noruma_cond, true);
            $bonusCondition = trim((string) ($meta['bonus_condition'] ?? ''));
        }

        return [
            'bonus_amount' => $bonusAmount,
            'bonus_condition' => $bonusCondition,
        ];
    }

    /**
     * @return array{bonus_amount: int, bonus_condition: string}|null
     */
    private function resolveHiredBonusForApplicationUpdate(object $application): ?array
    {
        if (!Schema::hasColumn('shop_job_applications', 'hired_bonus_amount')) {
            return null;
        }

        $fromJob = $this->snapshotHiredBonusForApplication((int) $application->shop_job_id);

        $amount = 0;
        if (Schema::hasColumn('shop_job_applications', 'applied_bonus_reward')) {
            if ($application->applied_bonus_reward !== null) {
                $amount = (int) $application->applied_bonus_reward;
            } elseif ($fromJob !== null) {
                $amount = (int) $fromJob['bonus_amount'];
            }
        } elseif ($fromJob !== null) {
            $amount = (int) $fromJob['bonus_amount'];
        }

        $condition = '';
        if (Schema::hasColumn('shop_job_applications', 'applied_bonus_condition')) {
            if ($application->applied_bonus_condition !== null) {
                $condition = (string) $application->applied_bonus_condition;
            } elseif ($fromJob !== null) {
                $condition = (string) $fromJob['bonus_condition'];
            }
        } elseif ($fromJob !== null) {
            $condition = (string) $fromJob['bonus_condition'];
        }

        return [
            'bonus_amount' => $amount,
            'bonus_condition' => $condition,
        ];
    }
}