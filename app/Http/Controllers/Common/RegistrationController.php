<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\Manager;
use App\Models\Member;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
            'email' => ['required', 'email', 'max:255', 'unique:casts,email'],
            'experience' => ['required', 'in:beginner,experienced'],
            'shift_style' => ['required', 'in:once,twice,flex'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['accepted'],
        ]);

        if (!checkdate(
            (int) $request->input('birth_month'),
            (int) $request->input('birth_day'),
            (int) $request->input('birth_year')
        )) {
            return back()
                ->withErrors(['birth_day' => '生年月日を正しく入力してください。'])
                ->withInput();
        }

        $member = DB::transaction(function () use ($request) {
            $castId = $this->nextSequentialId('casts', 'c');

            DB::table('casts')->insert([
                'id' => $castId,
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'status' => 1,
                'identity_status' => 1,
                'last_login_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('cast_profiles')->insert([
                'cast_id' => $castId,
                'nickname' => $request->input('nickname'),
                'name' => $request->input('name'),
                'birthday' => sprintf(
                    '%04d-%02d-%02d',
                    (int) $request->input('birth_year'),
                    (int) $request->input('birth_month'),
                    (int) $request->input('birth_day')
                ),
                'pref' => $request->input('pref'),
                'city' => $request->input('city'),
                'tel' => $request->input('phone'),
                'shift' => $this->mapCastShift((string) $request->input('shift_style')),
                'exp' => $request->input('experience') === 'experienced' ? 1 : 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return Member::query()->findOrFail($castId);
        });

        auth()->guard('shop')->logout();
        auth()->guard('member')->login($member);
        $request->session()->regenerate();

        return redirect()
            ->route('cast.home')
            ->with('message', 'キャストアカウントを登録しました。');
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
            'email' => ['required', 'email', 'max:255', 'unique:shop_managers,email'],
            'business_type' => ['required', 'in:club,lounge,girls-bar,other'],
            'plan' => ['required', 'in:basic,premium'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['accepted'],
        ]);

        $manager = DB::transaction(function () use ($request) {
            $shopId = $this->nextSequentialId('shops', 's');
            $managerId = $this->nextSequentialId('shop_managers', 'm');

            DB::table('shops')->insert([
                'id' => $shopId,
                'email' => $request->input('email'),
                'status' => 1,
                'license_status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('shop_profiles')->insert([
                'shop_id' => $shopId,
                'shop_name' => $request->input('shop_name'),
                'pref' => $request->input('pref'),
                'city' => $request->input('city'),
                'addr2' => $request->input('address'),
                'tel' => $request->input('phone'),
                'catch' => $this->mapBusinessTypeLabel((string) $request->input('business_type')),
                'overview' => $request->input('company_name'),
                'message' => 'ご利用プラン: ' . $this->mapPlanLabel((string) $request->input('plan')),
                'memo' => '運営会社名: ' . $request->input('company_name'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('shop_managers')->insert([
                'id' => $managerId,
                'shop_id' => $shopId,
                'name' => $request->input('contact_name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'role' => 1,
                'status' => 1,
                'last_login_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return Manager::query()->findOrFail($managerId);
        });

        auth()->guard('member')->logout();
        auth()->guard('shop')->login($manager);
        $request->session()->regenerate();

        return redirect()
            ->route('shop.home')
            ->with('message', '店舗アカウントを登録しました。');
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

    private function nextSequentialId(string $table, string $prefix): string
    {
        $lastId = DB::table($table)
            ->where('id', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('id');

        $nextNumber = $lastId
            ? ((int) substr($lastId, 1)) + 1
            : 1;

        return $prefix . str_pad((string) $nextNumber, 8, '0', STR_PAD_LEFT);
    }

    private function mapCastShift(string $shiftStyle): int
    {
        return match ($shiftStyle) {
            'once' => 1,
            'twice' => 2,
            default => 3,
        };
    }

    private function mapBusinessTypeLabel(string $businessType): string
    {
        return match ($businessType) {
            'club' => 'クラブ',
            'lounge' => 'ラウンジ',
            'girls-bar' => 'ガールズバー',
            default => 'その他',
        };
    }

    private function mapPlanLabel(string $plan): string
    {
        return $plan === 'premium' ? 'Premium' : 'Basic';
    }
}
