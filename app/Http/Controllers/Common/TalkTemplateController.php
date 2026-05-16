<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\UserTalkTemplate;
use App\Services\MessageTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TalkTemplateController extends Controller
{
    public function __construct(private readonly MessageTemplateService $messageTemplateService)
    {
    }

    /**
     * 定型文の設定画面。
     */
    public function index()
    {
        [$ownerType, $ownerId] = $this->resolveOwner();
        $isCast = $ownerType === 'cast';

        $templates = ($ownerType && $ownerId)
            ? UserTalkTemplate::query()
                ->where('owner_type', $ownerType)
                ->where('owner_id', $ownerId)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
            : collect();

        $defaults = $this->messageTemplateService->getDefaultQuickTemplates($ownerType ?? 'cast');

        return view('common.setting.talk-templates', [
            'isCast' => $isCast,
            'isLoggedIn' => (bool) $ownerType,
            'templates' => $templates,
            'defaults' => $defaults,
        ]);
    }

    /**
     * 新規追加。
     */
    public function store(Request $request): RedirectResponse
    {
        [$ownerType, $ownerId] = $this->resolveOwner();
        if (!$ownerType || !$ownerId) {
            return redirect()->route('setting.talk-templates.index')
                ->withErrors(['ログイン後に設定してください。']);
        }

        $data = $request->validate([
            'category' => ['nullable', 'string', 'max:64'],
            'title' => ['required', 'string', 'max:80'],
            'body' => ['required', 'string', 'max:2000'],
        ], [
            'title.required' => 'タイトルを入力してください。',
            'body.required' => '本文を入力してください。',
        ]);

        $nextOrder = (int) UserTalkTemplate::query()
            ->where('owner_type', $ownerType)
            ->where('owner_id', $ownerId)
            ->max('sort_order');

        UserTalkTemplate::create([
            'owner_type' => $ownerType,
            'owner_id' => $ownerId,
            'category' => $data['category'] ?: 'その他',
            'title' => $data['title'],
            'body' => $data['body'],
            'sort_order' => $nextOrder + 1,
            'is_active' => true,
        ]);

        return redirect()->route('setting.talk-templates.index')
            ->with('message', '定型文を追加しました。');
    }

    /**
     * 編集（保存）。
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        [$ownerType, $ownerId] = $this->resolveOwner();
        if (!$ownerType || !$ownerId) {
            return redirect()->route('setting.talk-templates.index')
                ->withErrors(['ログイン後に設定してください。']);
        }

        $template = $this->findOwned($id, $ownerType, $ownerId);
        if (!$template) {
            return redirect()->route('setting.talk-templates.index')
                ->withErrors(['対象の定型文が見つかりません。']);
        }

        $data = $request->validate([
            'category' => ['nullable', 'string', 'max:64'],
            'title' => ['required', 'string', 'max:80'],
            'body' => ['required', 'string', 'max:2000'],
            'is_active' => ['nullable'],
        ], [
            'title.required' => 'タイトルを入力してください。',
            'body.required' => '本文を入力してください。',
        ]);

        $template->fill([
            'category' => $data['category'] ?: 'その他',
            'title' => $data['title'],
            'body' => $data['body'],
            'is_active' => $request->boolean('is_active', true),
        ])->save();

        return redirect()->route('setting.talk-templates.index')
            ->with('message', '定型文を更新しました。');
    }

    /**
     * 削除。
     */
    public function destroy(int $id): RedirectResponse
    {
        [$ownerType, $ownerId] = $this->resolveOwner();
        if (!$ownerType || !$ownerId) {
            return redirect()->route('setting.talk-templates.index')
                ->withErrors(['ログイン後に設定してください。']);
        }

        $template = $this->findOwned($id, $ownerType, $ownerId);
        if ($template) {
            $template->delete();
        }

        return redirect()->route('setting.talk-templates.index')
            ->with('message', '定型文を削除しました。');
    }

    /**
     * プリセットの定型文を自分のリストへ取り込む。
     * 既に取り込み済（自分のテンプレートが1件でもある場合）は何もしない。
     */
    public function importDefaults(): RedirectResponse
    {
        [$ownerType, $ownerId] = $this->resolveOwner();
        if (!$ownerType || !$ownerId) {
            return redirect()->route('setting.talk-templates.index')
                ->withErrors(['ログイン後に設定してください。']);
        }

        $exists = UserTalkTemplate::query()
            ->where('owner_type', $ownerType)
            ->where('owner_id', $ownerId)
            ->exists();
        if ($exists) {
            return redirect()->route('setting.talk-templates.index')
                ->withErrors(['既に自分の定型文が登録されています。プリセットを上書き取り込みする場合は、いったん全て削除してから再度お試しください。']);
        }

        $defaults = $this->messageTemplateService->getDefaultQuickTemplates($ownerType);
        $order = 0;
        foreach ($defaults as $tpl) {
            $order++;
            UserTalkTemplate::create([
                'owner_type' => $ownerType,
                'owner_id' => $ownerId,
                'category' => (string) ($tpl['category'] ?? 'その他'),
                'title' => (string) ($tpl['title'] ?? ''),
                'body' => (string) ($tpl['body'] ?? ''),
                'sort_order' => $order,
                'is_active' => true,
            ]);
        }

        return redirect()->route('setting.talk-templates.index')
            ->with('message', 'プリセットの定型文を取り込みました。自由に編集できます。');
    }

    /**
     * ログイン中のアクター（cast / shop）を解決する。
     * shop は shop_id（管理者ではなく店舗）に紐付ける。
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

    private function findOwned(int $id, string $ownerType, string $ownerId): ?UserTalkTemplate
    {
        return UserTalkTemplate::query()
            ->where('id', $id)
            ->where('owner_type', $ownerType)
            ->where('owner_id', $ownerId)
            ->first();
    }
}
