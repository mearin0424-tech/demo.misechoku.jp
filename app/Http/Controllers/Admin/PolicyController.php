<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePolicyRequest;
use App\Models\PolicyChapter;
use App\Models\PolicyDocument;
use App\Models\PolicyRevision;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PolicyController extends Controller
{
    /**
     * 既定で表示する規約ページ一覧
     *
     * @var array<int, array{key: string, title: string, lead_title: ?string, with_meta: bool}>
     */
    private const DOCUMENTS = [
        ['key' => PolicyDocument::KEY_ABOUT,   'title' => '運営協会',           'lead_title' => 'GREETING / 理事長 挨拶', 'with_meta' => true],
        ['key' => PolicyDocument::KEY_TERMS,   'title' => '利用規約',           'lead_title' => null,                       'with_meta' => false],
        ['key' => PolicyDocument::KEY_PRIVACY, 'title' => 'プライバシーポリシー', 'lead_title' => null,                       'with_meta' => false],
    ];

    public function index(): View
    {
        // 既定の3ドキュメントを未生成ならば作成
        foreach (self::DOCUMENTS as $def) {
            $this->ensureDocument($def);
        }

        $orderMap = collect(self::DOCUMENTS)->pluck('key')->flip();
        $documents = PolicyDocument::query()
            ->withCount('chapters')
            ->whereIn('key', array_column(self::DOCUMENTS, 'key'))
            ->get()
            ->sortBy(fn (PolicyDocument $doc) => $orderMap[$doc->key] ?? PHP_INT_MAX)
            ->values();

        return view('admin.policies.index', [
            'documents' => $documents,
        ]);
    }

    public function show(string $key): View
    {
        $document = $this->resolveDocument($key);
        $document->load(['chapters', 'revisions']);

        return view('admin.policies.show', [
            'document' => $document,
            'metaSchema' => PolicyDocument::defaultMetaSchema(),
        ]);
    }

    public function edit(string $key): View
    {
        $document = $this->resolveDocument($key);
        $document->load('chapters');

        return view('admin.policies.edit', [
            'document' => $document,
            'metaSchema' => PolicyDocument::defaultMetaSchema(),
        ]);
    }

    public function update(UpdatePolicyRequest $request, string $key): RedirectResponse
    {
        $document = $this->resolveDocument($key);

        if ($document->is_locked && ! $request->boolean('confirm_unlock')) {
            return back()
                ->withInput()
                ->withErrors(['confirm_unlock' => '編集を行うには「ロックを解除して更新する」のチェックが必要です。']);
        }

        $validated = $request->validated();
        $updaterName = trim((string) $validated['updater_name']);
        $updaterId = optional(auth()->guard('admin')->user())->id;
        $summary = $validated['change_summary'] ?? null;

        $metaPayload = $document->isAbout()
            ? $this->normalizeMeta($validated['meta'] ?? [])
            : null;

        $chapterPayload = $this->normalizeChapters($validated['chapters'] ?? []);

        DB::transaction(function () use ($document, $validated, $metaPayload, $chapterPayload, $updaterId, $updaterName, $summary) {
            $document->fill([
                'title' => $validated['title'],
                'lead_title' => $validated['lead_title'] ?? null,
                'lead_body' => $validated['lead_body'] ?? null,
                'meta' => $metaPayload,
                'updated_by_id' => $updaterId,
                'updated_by_name' => $updaterName,
                'content_updated_at' => now(),
                // 更新後は再ロック（次回の編集にも明示の解除を要する）
                'is_locked' => true,
            ])->save();

            $document->chapters()->delete();
            foreach ($chapterPayload as $sortOrder => $chapter) {
                $document->chapters()->create([
                    'sort_order' => $sortOrder,
                    'title' => $chapter['title'],
                    'body' => $chapter['body'],
                ]);
            }

            PolicyRevision::create([
                'policy_document_id' => $document->id,
                'action' => PolicyRevision::ACTION_UPDATED,
                'summary' => $summary,
                'snapshot' => [
                    'title' => $document->title,
                    'lead_title' => $document->lead_title,
                    'lead_body' => $document->lead_body,
                    'meta' => $metaPayload,
                    'chapters' => array_values($chapterPayload),
                ],
                'updated_by_id' => $updaterId,
                'updated_by_name' => $updaterName,
                'created_at' => now(),
            ]);
        });

        return redirect()
            ->route('admin.policies.show', ['key' => $document->key])
            ->with('status', '規約を更新しました（更新者: ' . $updaterName . '）。');
    }

    /**
     * ロックの個別切替（明示的な編集モード切替用）
     */
    public function toggleLock(Request $request, string $key): RedirectResponse
    {
        $document = $this->resolveDocument($key);
        $updaterName = trim((string) $request->input('updater_name', ''));

        if ($updaterName === '') {
            return back()->withErrors(['updater_name' => '操作者名を入力してください。']);
        }

        $document->is_locked = ! $document->is_locked;
        $document->updated_by_id = optional(auth()->guard('admin')->user())->id;
        $document->updated_by_name = $updaterName;
        $document->save();

        PolicyRevision::create([
            'policy_document_id' => $document->id,
            'action' => $document->is_locked ? PolicyRevision::ACTION_LOCKED : PolicyRevision::ACTION_UNLOCKED,
            'summary' => $document->is_locked ? 'ロックしました' : 'ロックを解除しました',
            'snapshot' => null,
            'updated_by_id' => $document->updated_by_id,
            'updated_by_name' => $updaterName,
            'created_at' => now(),
        ]);

        return back()->with('status', $document->is_locked ? 'ロックしました。' : 'ロックを解除しました。編集が可能です。');
    }

    private function resolveDocument(string $key): PolicyDocument
    {
        $def = collect(self::DOCUMENTS)->firstWhere('key', $key);
        abort_unless($def !== null, 404);

        return $this->ensureDocument($def);
    }

    /**
     * 該当ドキュメントが存在しなければ初期レコードを作成する.
     *
     * @param array{key: string, title: string, lead_title: ?string, with_meta: bool} $def
     */
    private function ensureDocument(array $def): PolicyDocument
    {
        return PolicyDocument::firstOrCreate(
            ['key' => $def['key']],
            [
                'title' => $def['title'],
                'lead_title' => $def['lead_title'],
                'lead_body' => null,
                'meta' => $def['with_meta'] ? $this->buildEmptyMeta() : null,
                'is_locked' => true,
            ]
        );
    }

    private function buildEmptyMeta(): array
    {
        return collect(PolicyDocument::defaultMetaSchema())
            ->mapWithKeys(fn (array $row) => [
                $row['key'] => ['label' => $row['label'], 'value' => ''],
            ])
            ->all();
    }

    /**
     * @param array<string, array{label?: string, value?: string}> $rows
     * @return array<string, array{label: string, value: string}>
     */
    private function normalizeMeta(array $rows): array
    {
        $schema = collect(PolicyDocument::defaultMetaSchema())->keyBy('key');
        $normalized = [];

        foreach ($schema as $key => $def) {
            $row = $rows[$key] ?? [];
            $normalized[$key] = [
                'label' => trim((string) ($row['label'] ?? $def['label'])),
                'value' => trim((string) ($row['value'] ?? '')),
            ];
        }

        return $normalized;
    }

    /**
     * @param array<int, array{title?: ?string, body?: ?string}> $rows
     * @return array<int, array{title: string, body: string}>
     */
    private function normalizeChapters(array $rows): array
    {
        $normalized = [];
        foreach ($rows as $row) {
            $title = trim((string) ($row['title'] ?? ''));
            $body = trim((string) ($row['body'] ?? ''));
            if ($title === '' && $body === '') {
                continue;
            }
            $normalized[] = ['title' => $title, 'body' => $body];
        }

        return $normalized;
    }
}
