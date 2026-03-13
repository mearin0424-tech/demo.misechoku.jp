<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminMasterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MasterController extends Controller
{
    public function __construct(
        private readonly AdminMasterService $adminMasterService
    ) {
    }

    /**
     * マスタ設定 管理トップ
     *
     * レビュー項目・検索タグなどへのリンク集をまとめる。
     */
    public function index()
    {
        return view('admin.master.index', $this->adminMasterService->getMasterIndexData());
    }

    public function storeReviewContent(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:review_contents,name'],
            'sort_order' => ['required', 'integer', 'min:1', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $this->adminMasterService->createReviewContent([
            'name' => $data['name'],
            'sort_order' => (int) $data['sort_order'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.masters.index')
            ->with('status', 'レビュー項目を登録しました。');
    }

    public function storeTag(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'string', Rule::in(['salary', 'howto', 'casttag'])],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tags', 'name')->where(fn ($query) => $query->where('type', $request->input('type'))),
            ],
        ]);

        $this->adminMasterService->createTag($data);

        return redirect()
            ->route('admin.masters.index')
            ->with('status', '検索タグを登録しました。');
    }

    public function storeNgWord(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'word' => ['required', 'string', 'max:255', 'unique:ng_words,word'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $this->adminMasterService->createNgWord([
            'word' => $data['word'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.masters.index')
            ->with('status', 'NGワードを登録しました。');
    }
}

