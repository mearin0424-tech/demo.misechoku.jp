<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\UserTalkTemplate;
use App\Services\MessageTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TalkTemplateController extends Controller
{
    public function __construct(private readonly MessageTemplateService $messageTemplateService)
    {
    }

    /**
     * 自分の 4 スロット分の定型文を JSON で返す。
     */
    public function slots(): JsonResponse
    {
        [$ownerType, $ownerId] = $this->resolveOwner();
        if (!$ownerType || !$ownerId) {
            return response()->json(['success' => false, 'message' => 'ログイン後にご利用ください。'], 401);
        }

        return response()->json([
            'success' => true,
            'slots' => $this->messageTemplateService->getQuickTemplateSlots($ownerType, $ownerId),
        ]);
    }

    /**
     * 指定スロットを保存する（無ければ作成、あれば更新）。
     */
    public function saveSlot(Request $request, int $slot): JsonResponse
    {
        if ($slot < 1 || $slot > MessageTemplateService::QUICK_TEMPLATE_SLOTS) {
            return response()->json(['success' => false, 'message' => 'スロット番号が不正です。'], 422);
        }

        [$ownerType, $ownerId] = $this->resolveOwner();
        if (!$ownerType || !$ownerId) {
            return response()->json(['success' => false, 'message' => 'ログイン後にご利用ください。'], 401);
        }

        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:80'],
            'body' => ['required', 'string', 'max:2000'],
        ], [
            'body.required' => '本文を入力してください。',
            'body.max' => '本文は2000文字以内で入力してください。',
        ]);

        $title = trim((string) ($data['title'] ?? ''));
        if ($title === '') {
            $title = '定型文' . $slot;
        }

        UserTalkTemplate::updateOrCreate(
            [
                'owner_type' => $ownerType,
                'owner_id' => $ownerId,
                'sort_order' => $slot,
            ],
            [
                'category' => '',
                'title' => $title,
                'body' => $data['body'],
                'is_active' => true,
            ]
        );

        return response()->json([
            'success' => true,
            'slots' => $this->messageTemplateService->getQuickTemplateSlots($ownerType, $ownerId),
        ]);
    }

    /**
     * 指定スロットをデフォルトに戻す（カスタム行を削除）。
     */
    public function resetSlot(int $slot): JsonResponse
    {
        if ($slot < 1 || $slot > MessageTemplateService::QUICK_TEMPLATE_SLOTS) {
            return response()->json(['success' => false, 'message' => 'スロット番号が不正です。'], 422);
        }

        [$ownerType, $ownerId] = $this->resolveOwner();
        if (!$ownerType || !$ownerId) {
            return response()->json(['success' => false, 'message' => 'ログイン後にご利用ください。'], 401);
        }

        UserTalkTemplate::query()
            ->where('owner_type', $ownerType)
            ->where('owner_id', $ownerId)
            ->where('sort_order', $slot)
            ->delete();

        return response()->json([
            'success' => true,
            'slots' => $this->messageTemplateService->getQuickTemplateSlots($ownerType, $ownerId),
        ]);
    }

    /**
     * ログイン中のアクター（cast / shop）を解決する。
     *
     * @return array{0: ?string, 1: ?string}
     */
    private function resolveOwner(): array
    {
        if (auth()->guard('member')->check()) {
            return ['cast', (string) auth()->guard('member')->id()];
        }
        if (auth()->guard('shop')->check()) {
            $user = auth()->guard('shop')->user();
            $shopId = (string) ($user->shop_id ?? '');
            if ($shopId !== '') {
                return ['shop', $shopId];
            }
        }
        return [null, null];
    }
}
