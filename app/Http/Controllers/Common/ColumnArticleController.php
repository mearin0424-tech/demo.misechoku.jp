<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\ColumnArticle;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ColumnArticleController extends Controller
{
    public function index(Request $request): View
    {
        $isGuest = $request->routeIs('pages.support.column');
        $isCast = $request->is('cast/*');
        $isShop = $request->is('shop/*');

        $query = ColumnArticle::query()
            ->with('columnCategory')
            ->published()
            ->orderByDesc('published_at')
            ->orderByDesc('id');

        if ($isGuest) {
            $query->forGuest();
        } elseif ($isCast) {
            $query->forCast();
        } elseif ($isShop) {
            $query->forShop();
        }

        // タグ絞り込みチップ用：現在の閲覧対象で公開中の記事に付いている全タグ
        $availableTags = (clone $query)
            ->pluck('tags')
            ->filter(fn ($tags) => is_array($tags))
            ->flatten()
            ->map(fn ($t) => trim((string) $t))
            ->filter(fn ($t) => $t !== '')
            ->unique()
            ->values()
            ->all();

        // ?tag= で絞り込み
        $activeTag = trim((string) $request->query('tag', ''));
        if ($activeTag !== '' && in_array($activeTag, $availableTags, true)) {
            $query->whereJsonContains('tags', $activeTag);
        } else {
            $activeTag = '';
        }

        $articles = $query->paginate(12)->withQueryString();

        return view('common.support.column-index', [
            'articles' => $articles,
            'isCast' => $isCast,
            'isShop' => $isShop,
            'isGuest' => $isGuest,
            'availableTags' => $availableTags,
            'activeTag' => $activeTag,
        ]);
    }

    public function show(Request $request, string $slug): View
    {
        $isGuest = $request->routeIs('pages.support.column.show');
        $isCast = $request->is('cast/*');
        $isShop = $request->is('shop/*');

        $query = ColumnArticle::query()
            ->with('columnCategory')
            ->published()
            ->where('slug', $slug);

        if ($isGuest) {
            $query->forGuest();
        } elseif ($isCast) {
            $query->forCast();
        } elseif ($isShop) {
            $query->forShop();
        }

        $article = $query->firstOrFail();

        return view('common.support.column-show', [
            'article' => $article,
            'isCast' => $isCast,
            'isShop' => $isShop,
            'isGuest' => $isGuest,
        ]);
    }
}
