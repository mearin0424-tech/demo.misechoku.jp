<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreNoticeRequest;
use App\Http\Requests\Admin\UpdateNoticeRequest;
use App\Models\Notice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NoticeController extends Controller
{
    public function index(): View
    {
        $notices = Notice::query()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.notices.index', compact('notices'));
    }

    public function create(): View
    {
        return view('admin.notices.create');
    }

    public function store(StoreNoticeRequest $request): RedirectResponse
    {
        $data = $this->normalizePayload($request);
        unset($data['slug']);

        $slugInput = $request->input('slug');
        $data['slug'] = $slugInput !== null && $slugInput !== ''
            ? Notice::ensureUniqueSlug($slugInput)
            : Notice::makeUniqueSlugFromTitle($data['title']);

        Notice::query()->create($data);

        return redirect()
            ->route('admin.notices.index')
            ->with('status', 'お知らせを登録しました。');
    }

    public function edit(Notice $notice): View
    {
        return view('admin.notices.edit', compact('notice'));
    }

    public function update(UpdateNoticeRequest $request, Notice $notice): RedirectResponse
    {
        $data = $this->normalizePayload($request);
        unset($data['slug']);

        $slugInput = $request->input('slug');
        if ($slugInput !== null && $slugInput !== '') {
            $data['slug'] = Notice::ensureUniqueSlug($slugInput, $notice->id);
        }

        $notice->update($data);

        return redirect()
            ->route('admin.notices.index')
            ->with('status', 'お知らせを更新しました。');
    }

    public function destroy(Notice $notice): RedirectResponse
    {
        $notice->delete();

        return redirect()
            ->route('admin.notices.index')
            ->with('status', 'お知らせを削除しました。');
    }

    private function normalizePayload(Request $request): array
    {
        $data = array_intersect_key(
            $request->validated(),
            array_flip((new Notice)->getFillable())
        );

        $data['is_published'] = $request->boolean('is_published');
        $data['visible_to_cast'] = $request->boolean('visible_to_cast');
        $data['visible_to_shop'] = $request->boolean('visible_to_shop');
        $data['visible_to_guest'] = $request->boolean('visible_to_guest');

        if (! $data['is_published']) {
            $data['published_at'] = null;
        } elseif (empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
