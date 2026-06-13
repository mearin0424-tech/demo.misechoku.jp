<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportInquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupportInquiryController extends Controller
{
    public function index(Request $request): View
    {
        $status = (string) $request->query('status', '');
        $category = (string) $request->query('category', '');

        $query = SupportInquiry::query();
        if ($status !== '' && array_key_exists($status, SupportInquiry::STATUS_LABELS)) {
            $query->where('status', $status);
        }
        if ($category !== '' && array_key_exists($category, SupportInquiry::CATEGORY_LABELS)) {
            $query->where('category', $category);
        }

        $inquiries = $query->orderByDesc('created_at')->paginate(30)->withQueryString();

        $counts = [
            'new' => SupportInquiry::where('status', SupportInquiry::STATUS_NEW)->count(),
            'in_progress' => SupportInquiry::where('status', SupportInquiry::STATUS_IN_PROGRESS)->count(),
            'all' => SupportInquiry::count(),
        ];

        return view('admin.support-inquiries.index', compact('inquiries', 'counts', 'status', 'category'));
    }

    public function show(SupportInquiry $inquiry): View
    {
        return view('admin.support-inquiries.show', compact('inquiry'));
    }

    public function updateStatus(Request $request, SupportInquiry $inquiry): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(SupportInquiry::STATUS_LABELS))],
        ]);

        $update = ['status' => $validated['status']];

        // 「対応中」「完了」へ最初に遷移した瞬間に responded_at を記録（一次応対した時刻）
        if (in_array($validated['status'], [SupportInquiry::STATUS_IN_PROGRESS, SupportInquiry::STATUS_RESOLVED], true)
            && $inquiry->responded_at === null) {
            $update['responded_at'] = now();
        }

        // 担当を未割当なら自分（admin）で埋める
        if (empty($inquiry->assigned_admin_id)) {
            $update['assigned_admin_id'] = (string) (Auth::guard('admin')->id() ?? '');
        }

        $inquiry->update($update);

        return redirect()
            ->route('admin.support-inquiries.show', $inquiry->id)
            ->with('status', '対応ステータスを更新しました。');
    }

    public function updateNote(Request $request, SupportInquiry $inquiry): RedirectResponse
    {
        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:4000'],
        ]);

        $inquiry->update(['admin_note' => $validated['admin_note']]);

        return redirect()
            ->route('admin.support-inquiries.show', $inquiry->id)
            ->with('status', 'メモを保存しました。');
    }
}
