<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreColumnArticleRequest;
use App\Http\Requests\Admin\UpdateColumnArticleRequest;
use App\Models\ColumnArticle;
use App\Models\Master\ColumnCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ColumnController extends Controller
{
    public function index(): View
    {
        $columns = ColumnArticle::query()
            ->with('columnCategory')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.column.index', compact('columns'));
    }

    public function create(): View
    {
        return view('admin.column.create', [
            'categories' => $this->columnCategories(),
        ]);
    }

    public function store(StoreColumnArticleRequest $request): RedirectResponse
    {
        $data = $this->normalizePayload($request);
        unset($data['slug']);

        $slugInput = $request->input('slug');
        $data['slug'] = $slugInput !== null && $slugInput !== ''
            ? ColumnArticle::ensureUniqueSlug($slugInput)
            : ColumnArticle::makeUniqueSlugFromTitle($data['title']);

        ColumnArticle::query()->create($data);

        return redirect()
            ->route('admin.columns.index')
            ->with('status', 'コラムを登録しました。');
    }

    public function edit(ColumnArticle $column): View
    {
        return view('admin.column.edit', [
            'column' => $column,
            'categories' => $this->columnCategories(),
        ]);
    }

    public function update(UpdateColumnArticleRequest $request, ColumnArticle $column): RedirectResponse
    {
        $data = $this->normalizePayload($request);
        unset($data['slug']);

        $slugInput = $request->input('slug');
        if ($slugInput !== null && $slugInput !== '') {
            $data['slug'] = ColumnArticle::ensureUniqueSlug($slugInput, $column->id);
        }

        $column->update($data);

        return redirect()
            ->route('admin.columns.index')
            ->with('status', 'コラムを更新しました。');
    }

    public function destroy(ColumnArticle $column): RedirectResponse
    {
        $column->delete();

        return redirect()
            ->route('admin.columns.index')
            ->with('status', 'コラムを削除しました。');
    }

    private function normalizePayload(Request $request): array
    {
        // DB に存在するカラムのみ（fillable）に限定し、旧 category / summary などを除外する
        $data = array_intersect_key(
            $request->validated(),
            array_flip((new ColumnArticle)->getFillable())
        );
        unset($data['category'], $data['summary']);

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

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, ColumnCategory>
     */
    private function columnCategories()
    {
        return ColumnCategory::query()->active()->orderBy('name')->get();
    }
}
