<?php

namespace App\Http\Controllers\Common;

use App\Consts\CommonConsts;
use App\Http\Controllers\Controller;
use App\Models\Cast;
use App\Models\ShopManager;
use Illuminate\Support\Facades\File;
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
            'zip' => ['required', 'regex:/^\d{3}-?\d{4}$/'],
            'pref' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:100'],
            'addr1' => ['nullable', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', 'unique:casts,email'],
            'experience' => ['required', 'in:beginner,experienced'],
            'shift_style' => ['required', 'in:once,twice,flex'],
            'profile_image' => ['required', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['accepted'],
        ], [
            'zip.required' => '郵便番号を入力してください。',
            'zip.regex' => '郵便番号は 7 桁、または 123-4567 形式で入力してください。',
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
                'zip' => $this->normalizeZip($request->input('zip')),
                'pref' => $request->input('pref'),
                'city' => $request->input('city'),
                'addr1' => $request->input('addr1'),
                'tel' => $request->input('phone'),
                'shift' => $this->mapCastShift((string) $request->input('shift_style')),
                'exp' => $request->input('experience') === 'experienced' ? 1 : 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // プロフィール画像（必須1枚）を保存
            if ($request->hasFile('profile_image')) {
                $file = $request->file('profile_image');
                $dir = public_path('uploads/casts/gallery');
                File::ensureDirectoryExists($dir);
                $name = $file->hashName();
                $file->move($dir, $name);
                $path = 'uploads/casts/gallery/' . $name;

                DB::table('cast_images')->insert([
                    'cast_id'       => $castId,
                    'image_path'    => $path,
                    'type'          => 1,
                    'front_and_back'=> 0,
                    'status'        => 0,
                    'is_main'       => 1,
                    'main_order'    => 0,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);

                // プロフィールのメイン画像パスを同期（カラムが存在する場合）
                if (DB::getSchemaBuilder()->hasColumn('cast_profiles', 'main_image_path')) {
                    DB::table('cast_profiles')
                        ->where('cast_id', $castId)
                        ->update(['main_image_path' => $path]);
                }
            }

            return Cast::query()->findOrFail($castId);
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
            'zip' => ['required', 'regex:/^\d{3}-?\d{4}$/'],
            'pref' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', 'unique:shop_managers,email'],
            'business_type' => ['required', 'in:club,lounge,girls-bar,other'],
            'plan' => ['required', 'in:basic,premium'],
            'shop_profile_image' => ['required', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['accepted'],
        ], [
            'zip.required' => '郵便番号を入力してください。',
            'zip.regex' => '郵便番号は 7 桁、または 123-4567 形式で入力してください。',
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
                'zip' => $this->normalizeZip($request->input('zip')),
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

            // 店舗登録と同時に、求人票（本入・体入・ヘルプを想定したベースレコード）を1件作成しておく
            // 各勤務形態は時給やフラグの設定有無で任意に利用可能
            if (DB::getSchemaBuilder()->hasTable('shop_jobs')) {
                DB::table('shop_jobs')->insert([
                    'shop_id' => $shopId,
                    'status' => 0,                 // 初期状態は非公開
                    'hourly_wage_regular' => null, // 本入の時給（未設定）
                    'normal_time' => null,
                    'has_trial' => 0,              // 体入は未設定
                    'trial_hourly_wage' => null,
                    'has_help' => 0,               // ヘルプは未設定
                    'help_hourly_wage' => null,
                    'job_description' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 店舗プロフィール画像（必須1枚）を保存
            if ($request->hasFile('shop_profile_image')) {
                $file = $request->file('shop_profile_image');
                $dir = public_path('uploads/shops/gallery');
                File::ensureDirectoryExists($dir);
                $name = $file->hashName();
                $file->move($dir, $name);
                $path = 'uploads/shops/gallery/' . $name;

                DB::table('shop_images')->insert([
                    'shop_id'    => $shopId,
                    'image_path' => $path,
                    'type'       => null,
                    'is_main'    => 1,
                    'main_order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if (DB::getSchemaBuilder()->hasColumn('shop_profiles', 'main_image_path')) {
                    DB::table('shop_profiles')
                        ->where('shop_id', $shopId)
                        ->update(['main_image_path' => $path]);
                }
            }

            return ShopManager::query()->findOrFail($managerId);
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
                ? "キャスト登録です。\n必要項目を入力してください。"
                : "店舗登録です。\n必要項目を入力してください。",
            'eyebrow' => '',
            'heroTitle' => $isCast ? 'キャスト登録' : '店舗登録',
            'heroDescription' => '',
            'benefits' => [],
            'formAction' => $isCast ? route('cast.register.store') : route('shop.register.store'),
            'submitLabel' => '登録する',
            'alternateUrl' => $isCast ? route('shop.register') : route('cast.register'),
            'alternateLabel' => $isCast ? '店舗' : 'キャスト',
            'loginUrl' => route('login.demo'),
            'prefOptions' => CommonConsts::PREFS,
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

    private function normalizeZip(?string $zip): ?string
    {
        if ($zip === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $zip);

        if ($digits === null || $digits === '') {
            return null;
        }

        if (strlen($digits) !== 7) {
            return trim($zip);
        }

        return substr($digits, 0, 3) . '-' . substr($digits, 3);
    }
}
