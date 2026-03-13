<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function showCast(): View
    {
        return view('common.register', $this->buildViewData('cast'));
    }

    public function showShop(): View
    {
        return view('common.register', $this->buildViewData('shop'));
    }

    public function storeCast(Request $request): RedirectResponse
    {
        $request->validate([
            'nickname' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:100'],
            'birth_year' => ['required', 'integer', 'between:1950,2100'],
            'birth_month' => ['required', 'integer', 'between:1,12'],
            'birth_day' => ['required', 'integer', 'between:1,31'],
            'pref' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'experience' => ['required', 'in:beginner,experienced'],
            'shift_style' => ['required', 'in:once,twice,flex'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['accepted'],
        ]);

        return redirect()
            ->route('cast.register')
            ->with('success', 'キャスト登録フォームを受け付けました。デモ環境のため、実際のアカウント作成は行っていません。');
    }

    public function storeShop(Request $request): RedirectResponse
    {
        $request->validate([
            'company_name' => ['required', 'string', 'max:100'],
            'shop_name' => ['required', 'string', 'max:100'],
            'contact_name' => ['required', 'string', 'max:100'],
            'pref' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'business_type' => ['required', 'in:club,lounge,girls-bar,other'],
            'plan' => ['required', 'in:basic,premium'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['accepted'],
        ]);

        return redirect()
            ->route('shop.register')
            ->with('success', '店舗登録フォームを受け付けました。デモ環境のため、実際のアカウント作成は行っていません。');
    }

    private function buildViewData(string $role): array
    {
        $isCast = $role === 'cast';

        return [
            'role' => $role,
            'pageId' => 'register',
            'title' => $isCast ? 'キャスト新規登録' : '店舗新規登録',
            'bodyClass' => $isCast ? 'page-auth-register page-auth-register-cast' : 'page-auth-register page-auth-register-shop',
            'guideMessage' => $isCast
                ? "気になるお店と、もっと自然につながろう。\nまずは基本情報を登録してスタート。"
                : "お店の魅力が伝わる入口を整えよう。\nまずは店舗情報の登録から始めよう。",
            'eyebrow' => $isCast ? 'CAST REGISTRATION' : 'SHOP REGISTRATION',
            'heroTitle' => $isCast ? '理想のお店と出会うための、最初の一歩。' : '魅力あるお店づくりを、登録からスムーズに。',
            'heroDescription' => $isCast
                ? 'プロフィール作成や応募導線につながる、新規登録画面です。デモとして雰囲気と入力体験を確認できます。'
                : '店舗情報や担当者情報を整理して、求人掲載やキャストとのマッチングにつながる登録画面を用意しました。',
            'benefits' => $isCast
                ? [
                    '気になる店舗へスムーズに応募',
                    'プロフィール入力の土台をまとめて登録',
                    '希望条件に合う求人を探しやすく',
                ]
                : [
                    '店舗プロフィールの公開準備を開始',
                    '求人掲載に必要な基本情報を整理',
                    'キャストとの接点づくりをスムーズに',
                ],
            'formAction' => $isCast ? route('cast.register.store') : route('shop.register.store'),
            'submitLabel' => $isCast ? 'キャストとして無料登録する' : '店舗として無料登録する',
            'alternateUrl' => $isCast ? route('shop.register') : route('cast.register'),
            'alternateLabel' => $isCast ? '店舗登録はこちら' : 'キャスト登録はこちら',
            'loginUrl' => route('login.demo'),
            'prefOptions' => ['東京都', '大阪府', '愛知県', '福岡県', '北海道'],
        ];
    }
}
