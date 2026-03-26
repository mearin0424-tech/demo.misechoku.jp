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

        $articles = $query->paginate(12)->withQueryString();

        return view('common.support.column-index', [
            'articles' => $articles,
            'isCast' => $isCast,
            'isShop' => $isShop,
            'isGuest' => $isGuest,
        ]);
    }

    public function show(Request $request, string $slug): View
    {
        $isGuest = $request->routeIs('pages.support.column.show');
        $isCast = $request->is('cast/*');
        $isShop = $request->is('shop/*');

        $query = ColumnArticle::query()
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
