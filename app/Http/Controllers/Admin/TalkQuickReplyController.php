<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TalkQuickReplyMasterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TalkQuickReplyController extends Controller
{
    public function __construct(
        private readonly TalkQuickReplyMasterService $service,
    ) {
    }

    /**
     * ステータス x 役割ごとのクイック定型文マスタ 一覧・編集画面。
     */
    public function index()
    {
        return view('admin.talk_quick_reply.index', [
            'groups'     => $this->service->getGroupedForAdmin(),
            'categories' => TalkQuickReplyMasterService::CATEGORIES,
        ]);
    }

    /**
     * 一括保存。行の順序が sort_order になる。body 空 or delete=1 の既存行は削除。
     */
    public function update(Request $request): RedirectResponse
    {
        $rawGroups = (array) $request->input('groups', []);

        $inputs = [];
        foreach (TalkQuickReplyMasterService::GROUPS as $group) {
            $key = $group['owner_type'] . '|' . $group['status_code'];
            $rows = $rawGroups[$key] ?? [];
            if (!is_array($rows)) {
                $rows = [];
            }
            $inputs[$key] = array_values(array_map(function ($row) {
                if (!is_array($row)) {
                    return ['id' => null, 'category' => '', 'body' => '', 'delete' => false];
                }
                return [
                    'id'       => isset($row['id']) && $row['id'] !== '' ? (int) $row['id'] : null,
                    'category' => (string) ($row['category'] ?? ''),
                    'body'     => (string) ($row['body'] ?? ''),
                    'delete'   => !empty($row['delete']),
                ];
            }, $rows));
        }

        $this->service->saveAll($inputs);

        return redirect()
            ->route('admin.talk-quick-replies.index')
            ->with('status', 'クイック定型文マスタを保存しました。');
    }

    /**
     * 指定グループを既定値 (ハードコード配列) にリセット。
     */
    public function reset(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'owner_type'  => ['required', 'in:cast,shop'],
            'status_code' => ['required', 'in:chatting,interview_pending,interview_fixed,hired,rejected'],
        ]);

        $this->service->resetGroupToDefault($data['owner_type'], $data['status_code']);

        return redirect()
            ->route('admin.talk-quick-replies.index')
            ->with('status', '既定値に戻しました。');
    }
}
