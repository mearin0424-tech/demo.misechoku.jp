<?php

namespace App\Http\Controllers\Common;

use App\Consts\CommonConsts;
use App\Http\Controllers\Controller;
use App\Models\Cast;
use App\Models\ShopManager;
use App\Services\AdminMasterService;
use App\Services\ShopProfileLocationSyncService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function __construct(
        private readonly AdminMasterService $adminMasterService,
        private readonly ShopProfileLocationSyncService $shopProfileLocationSyncService,
    ) {
    }

    public function showCast(): View
    {
        return view('common.register', array_merge($this->buildViewData('cast'), [
            'masters' => $this->adminMasterService->getCastProfileMasters(),
        ]));
    }

    public function showShop(): View
    {
        return view('common.register', array_merge($this->buildViewData('shop'), [
            'masters' => $this->adminMasterService->getShopProfileMasters(),
        ]));
    }

    public function storeCast(Request $request): RedirectResponse
    {
        $rules = [
            'nickname' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:100'],
            'birth_date' => ['required', 'date'],
            'zip' => ['required', 'regex:/^\d{3}-?\d{4}$/'],
            'pref' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:100'],
            'addr1' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', 'unique:casts,email'],
            'experience' => ['required', 'in:beginner,experienced'],
            'shift_style' => ['required', 'in:once,twice,flex'],
            'profile_image' => ['required', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['accepted'],
            'intro' => ['nullable', 'string', 'max:5000'],
            'height' => ['nullable', 'integer', 'min:100', 'max:250'],
            'weight' => ['nullable', 'integer', 'min:30', 'max:150'],
            'bust' => ['nullable', 'integer', 'min:50', 'max:120'],
            'waist' => ['nullable', 'integer', 'min:40', 'max:120'],
            'hip' => ['nullable', 'integer', 'min:50', 'max:120'],
            'shift_hope' => ['nullable', 'string', 'in:週1回出勤,週2回出勤,週3回以上'],
            'work_time' => ['nullable', 'string', 'in:morning,day_night'],
            'current_job' => ['nullable', 'string', 'max:1000'],
            'night_work_exp' => ['nullable', 'string', 'in:none,yes'],
            'industry_ids' => ['nullable', 'array'],
            'industry_ids.*' => ['integer'],
            'look_tag_ids' => ['nullable', 'array'],
            'look_tag_ids.*' => ['integer'],
            'personality_tag_ids' => ['nullable', 'array'],
            'personality_tag_ids.*' => ['integer'],
        ];
        if (Schema::hasTable('industries')) {
            $rules['industry_ids.*'][] = 'exists:industries,id';
        }
        if (Schema::hasTable('cast_tags')) {
            $rules['look_tag_ids.*'][] = 'exists:cast_tags,id';
            $rules['personality_tag_ids.*'][] = 'exists:cast_tags,id';
        }

        $request->validate($rules, [
            'zip.required' => '郵便番号を入力してください。',
            'zip.regex' => '郵便番号は 7 桁、または 123-4567 形式で入力してください。',
        ]);

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

            $shiftHope = $request->filled('shift_hope') ? $request->input('shift_hope') : $this->shiftStyleToShiftHope((string) $request->input('shift_style'));
            $workTime = $request->filled('work_time') ? (string) $request->input('work_time') : $this->shiftStyleToWorkTime((string) $request->input('shift_style'));
            $nightWorkExp = $request->filled('night_work_exp') ? $request->input('night_work_exp') : ($request->input('experience') === 'experienced' ? 'yes' : 'none');
            $industryIds = array_values(array_unique(array_map('intval', (array) $request->input('industry_ids', []))));

            $profilePayload = [
                'cast_id' => $castId,
                'nickname' => $request->input('nickname'),
                'name' => $request->input('name'),
                'birthday' => $request->input('birth_date'),
                'zip' => $this->normalizeZip($request->input('zip')),
                'pref' => $request->input('pref'),
                'city' => $request->input('city'),
                'addr' => $request->input('addr1'),
                'building' => null,
                'tel' => $request->input('phone'),
                'work_time' => $this->workTimeToShiftCode($workTime),
                'work_where' => $shiftHope,
                'exp' => $nightWorkExp === 'yes' ? 1 : 0,
                'pr' => $request->input('intro'),
                'height' => $request->filled('height') ? (int) $request->input('height') : null,
                'weight' => $request->filled('weight') ? (int) $request->input('weight') : null,
                'bust' => $request->filled('bust') ? (int) $request->input('bust') : null,
                'waist' => $request->filled('waist') ? (int) $request->input('waist') : null,
                'hip' => $request->filled('hip') ? (int) $request->input('hip') : null,
                'profession' => $request->input('current_job'),
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if (Schema::hasColumn('cast_profiles', 'industry_id')) {
                $profilePayload['industry_id'] = $industryIds[0] ?? null;
            }
            DB::table('cast_profiles')->insert($profilePayload);
            $this->syncCastIndustries($castId, $industryIds);

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

            }

            $this->syncCastTags($castId, 'looks', $request->input('look_tag_ids', []));
            $this->syncCastTags($castId, 'personality', $request->input('personality_tag_ids', []));

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
        $shopRules = [
            'company_name' => ['required', 'string', 'max:100'],
            'shop_name' => ['required', 'string', 'max:100'],
            'contact_name' => ['required', 'string', 'max:100'],
            'zip' => ['required', 'regex:/^\d{3}-?\d{4}$/'],
            'pref' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'addr' => ['nullable', 'string', 'max:255', 'required_without:address'],
            'building' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', 'unique:shop_managers,email'],
            'business_type' => ['required', 'in:club,lounge,girls-bar,other'],
            'plan' => ['required', 'in:basic,premium'],
            'shop_profile_image' => ['required', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['accepted'],
            'word' => ['nullable', 'string', 'max:255'],
            'overview' => ['nullable', 'string', 'max:2000'],
            'industry_ids' => ['nullable', 'array'],
            'industry_ids.*' => ['integer'],
        ];
        if (Schema::hasTable('industries')) {
            $shopRules['industry_ids.*'][] = 'exists:industries,id';
        }
        $request->validate($shopRules, [
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

            $industryIds = array_values(array_unique(array_map('intval', (array) $request->input('industry_ids', []))));

            $resolvedAddress = trim(implode(' ', array_filter([
                (string) $request->input('addr', ''),
                (string) $request->input('building', ''),
            ])));
            if ($resolvedAddress === '') {
                $resolvedAddress = trim((string) $request->input('address', ''));
            }

            $shopProfilePayload = [
                'shop_id' => $shopId,
                'shop_name' => $request->input('shop_name'),
                'zip' => $this->normalizeZip($request->input('zip')),
                'pref' => $request->input('pref'),
                'city' => $request->input('city'),
                'addr2' => $resolvedAddress,
                'tel' => $request->input('phone'),
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if (Schema::hasColumn('shop_profiles', 'industry_id')) {
                $shopProfilePayload['industry_id'] = $industryIds[0] ?? null;
            }
            DB::table('shop_profiles')->insert($shopProfilePayload);
            $this->syncShopIndustries($shopId, $industryIds);

            if (Schema::hasColumn('shop_profiles', 'latitude')) {
                $fullAddr = $this->shopProfileLocationSyncService->buildFullAddressLineForGeocode($request);
                $this->shopProfileLocationSyncService->persistResolvedLocation($shopId, $fullAddr);
            }

            if (Schema::hasTable('shop_posts')) {
                $hitokoto = $request->filled('word')
                    ? (string) $request->input('word')
                    : $this->mapBusinessTypeLabel((string) $request->input('business_type'));
                $shopPostRow = [
                    'shop_id'    => $shopId,
                    'body'       => $hitokoto,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                if (Schema::hasColumn('shop_posts', 'type')) {
                    $shopPostRow['type'] = 2;
                }
                DB::table('shop_posts')->insert($shopPostRow);
            }

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
                $jobRow = [
                    'shop_id' => $shopId,
                    'status' => 0,                 // 初期状態は非公開
                    'hourly_wage_regular' => null, // 本入の時給（未設定）
                    'normal_time' => null,
                    'has_trial' => 0,              // 体入は未設定
                    'trial_hourly_wage' => null,
                    'has_help' => 0,               // ヘルプは未設定
                    'help_hourly_wage' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                if (Schema::hasColumn('shop_jobs', 'job_type')) {
                    $jobRow['job_type'] = 1;
                }
                if (Schema::hasColumn('shop_jobs', 'pr')) {
                    $jobRow['pr'] = null;
                }
                DB::table('shop_jobs')->insert($jobRow);
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
            ->route('shop.mypage.index')
            ->with('message', '店舗アカウントを登録しました。許可証の提出はマイページからお願いします。');
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

    private function shiftStyleToShiftHope(string $shiftStyle): string
    {
        return match ($shiftStyle) {
            'once' => '週1回出勤',
            'twice' => '週2回出勤',
            'flex' => '週3回以上',
            default => '週1回出勤',
        };
    }

    private function shiftStyleToWorkTime(string $shiftStyle): string
    {
        return match ($shiftStyle) {
            'once' => 'morning',
            'twice', 'flex' => 'day_night',
            default => 'day_night',
        };
    }

    private function workTimeToShiftCode(?string $workTime): int
    {
        return match ($workTime) {
            'morning' => 1,
            'day_night' => 2,
            default => 2,
        };
    }

    private function shiftHopeToCode(?string $shiftHope): ?int
    {
        return match ($shiftHope) {
            '週1回出勤' => 1,
            '週2回出勤' => 2,
            '週3回以上' => 3,
            default => null,
        };
    }

    private function syncCastTags(string $castId, string $tagType, array $tagIds): void
    {
        if (!DB::getSchemaBuilder()->hasTable('cast_tag_relations')) {
            return;
        }
        $normalizedType = rtrim($tagType, 's');
        $targetTypes = array_values(array_unique(array_filter([$tagType, $normalizedType])));
        $tagIds = array_values(array_unique(array_filter(array_map('intval', $tagIds))));
        DB::table('cast_tag_relations')
            ->where('cast_id', $castId)
            ->whereIn('tag_type', $targetTypes)
            ->delete();
        foreach ($tagIds as $tagId) {
            DB::table('cast_tag_relations')->insert([
                'cast_id' => $castId,
                'tag_id' => $tagId,
                'tag_type' => $normalizedType,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /** @param array<int, int> $industryIds */
    private function syncCastIndustries(string $castId, array $industryIds): void
    {
        $industryIds = array_values(array_unique(array_filter(array_map('intval', $industryIds))));
        if (!Schema::hasTable('cast_industry')) {
            return;
        }
        DB::table('cast_industry')->where('cast_id', $castId)->delete();
        if ($industryIds === []) {
            return;
        }
        $now = now();
        $rows = array_map(fn ($industryId) => [
            'cast_id' => $castId,
            'industry_id' => $industryId,
            'created_at' => $now,
            'updated_at' => $now,
        ], $industryIds);
        DB::table('cast_industry')->insert($rows);
    }

    /** @param array<int, int> $industryIds */
    private function syncShopIndustries(string $shopId, array $industryIds): void
    {
        $industryIds = array_values(array_unique(array_filter(array_map('intval', $industryIds))));
        $table = null;
        if (Schema::hasTable('shop_industry')) {
            $table = 'shop_industry';
        } elseif (Schema::hasTable('industry_shop')) {
            $table = 'industry_shop';
        } elseif (Schema::hasTable('shop_industries')) {
            $table = 'shop_industries';
        }
        if ($table === null) {
            return;
        }

        DB::table($table)->where('shop_id', $shopId)->delete();
        if ($industryIds === []) {
            return;
        }
        $now = now();
        $rows = array_map(fn ($industryId) => [
            'shop_id' => $shopId,
            'industry_id' => $industryId,
            'created_at' => $now,
            'updated_at' => $now,
        ], $industryIds);
        DB::table($table)->insert($rows);
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
