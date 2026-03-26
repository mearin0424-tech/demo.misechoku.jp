<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportNoticeController extends Controller
{
    public function index(Request $request): View
    {
        $isGuest = $request->routeIs('pages.support.notices');
        $isCast = $request->is('cast/*');
        $isShop = $request->is('shop/*');

        $query = Notice::query()
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

        $notices = $query->paginate(12)->withQueryString();

        return view('common.support.notice-index', [
            'notices' => $notices,
            'isCast' => $isCast,
            'isShop' => $isShop,
            'isGuest' => $isGuest,
        ]);
    }

    public function show(Request $request, string $slug): View
    {
        $isGuest = $request->routeIs('pages.support.notices.show');
        $isCast = $request->is('cast/*');
        $isShop = $request->is('shop/*');

        $query = Notice::query()
            ->published()
            ->where('slug', $slug);

        if ($isGuest) {
            $query->forGuest();
        } elseif ($isCast) {
            $query->forCast();
        } elseif ($isShop) {
            $query->forShop();
        }

        $notice = $query->firstOrFail();

        return view('common.support.notice-show', [
            'notice' => $notice,
            'isCast' => $isCast,
            'isShop' => $isShop,
            'isGuest' => $isGuest,
        ]);
    }
}
