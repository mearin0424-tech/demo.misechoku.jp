<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * 管理者：ユーザー通報の一覧・対応。
 * ステータス変更（対応中／完了／却下）+ メモ書き。
 */
class UserReportController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'all');

        $q = UserReport::query()->orderByDesc('created_at');
        if ($status === 'pending') {
            $q->where('status', UserReport::STATUS_PENDING);
        } elseif ($status === 'in_review') {
            $q->where('status', UserReport::STATUS_IN_REVIEW);
        } elseif ($status === 'resolved') {
            $q->where('status', UserReport::STATUS_RESOLVED);
        } elseif ($status === 'dismissed') {
            $q->where('status', UserReport::STATUS_DISMISSED);
        }

        $reports = $q->limit(200)->get();

        // 通報者・対象の表示名を lookup（重複解消）
        $names = $this->fetchNamesFor($reports);

        $counts = [
            'pending'   => UserReport::where('status', UserReport::STATUS_PENDING)->count(),
            'in_review' => UserReport::where('status', UserReport::STATUS_IN_REVIEW)->count(),
            'resolved'  => UserReport::where('status', UserReport::STATUS_RESOLVED)->count(),
            'dismissed' => UserReport::where('status', UserReport::STATUS_DISMISSED)->count(),
        ];

        return view('admin.user_reports.index', [
            'reports'      => $reports,
            'names'        => $names,
            'counts'       => $counts,
            'currentTab'   => $status,
            'reasonLabels' => UserReport::REASONS,
        ]);
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'status'     => ['required', Rule::in([
                UserReport::STATUS_PENDING,
                UserReport::STATUS_IN_REVIEW,
                UserReport::STATUS_RESOLVED,
                UserReport::STATUS_DISMISSED,
            ])],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $report = UserReport::findOrFail($id);

        $report->update([
            'status'     => (int) $data['status'],
            'admin_note' => $data['admin_note'] ?? $report->admin_note,
            'handled_by' => (int) auth()->guard('admin')->id(),
            'handled_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.user_reports.index', ['status' => $request->query('return_to', 'all')])
            ->with('message', '通報のステータスを更新しました。');
    }

    /**
     * @return array<string, string>  key = "{type}:{id}", value = 表示名
     */
    private function fetchNamesFor($reports): array
    {
        $castIds = [];
        $shopIds = [];
        foreach ($reports as $r) {
            if ($r->reporter_type === 'cast') $castIds[] = $r->reporter_id;
            if ($r->target_type === 'cast') $castIds[] = $r->target_id;
            if ($r->reporter_type === 'shop') $shopIds[] = $r->reporter_id;
            if ($r->target_type === 'shop') $shopIds[] = $r->target_id;
        }
        $castIds = array_unique($castIds);
        $shopIds = array_unique($shopIds);

        $names = [];
        if (!empty($castIds)) {
            DB::table('cast_profiles')
                ->whereIn('cast_id', $castIds)
                ->select('cast_id', 'nickname', 'name')
                ->get()
                ->each(function ($row) use (&$names) {
                    $names['cast:' . $row->cast_id] = $row->nickname ?: ($row->name ?: $row->cast_id);
                });
        }
        if (!empty($shopIds)) {
            DB::table('shop_profiles')
                ->whereIn('shop_id', $shopIds)
                ->select('shop_id', 'shop_name')
                ->get()
                ->each(function ($row) use (&$names) {
                    $names['shop:' . $row->shop_id] = $row->shop_name ?: $row->shop_id;
                });
        }
        return $names;
    }
}
