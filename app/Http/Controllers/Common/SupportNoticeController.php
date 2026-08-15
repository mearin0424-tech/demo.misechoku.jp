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
        [$isCast, $isShop, $isGuest] = $this->resolveAudience();

        $query = Notice::query()
            ->published()
            ->orderByDesc('published_at')
            ->orderByDesc('id');

        if ($isCast) {
            $query->forCast();
        } elseif ($isShop) {
            $query->forShop();
        } else {
            $query->forGuest();
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
        [$isCast, $isShop, $isGuest] = $this->resolveAudience();

        $query = Notice::query()
            ->published()
            ->where('slug', $slug);

        if ($isCast) {
            $query->forCast();
        } elseif ($isShop) {
            $query->forShop();
        } else {
            $query->forGuest();
        }

        $notice = $query->firstOrFail();

        return view('common.support.notice-show', [
            'notice' => $notice,
            'isCast' => $isCast,
            'isShop' => $isShop,
            'isGuest' => $isGuest,
        ]);
    }

    /**
     * @return array{0:bool,1:bool,2:bool} [$isCast, $isShop, $isGuest]
     */
    private function resolveAudience(): array
    {
        $isCast = auth()->guard('member')->check();
        $isShop = auth()->guard('shop')->check();
        $isGuest = !$isCast && !$isShop;

        return [$isCast, $isShop, $isGuest];
    }
}
