<?php

namespace App\Http\Controllers\Casts;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * キャスト向け：店舗の求人情報表示
 */
class RecruitmentController extends Controller
{
    /**
     * 求人情報詳細
     * ルートの {id} は以下どちらも許容する:
     * - 数値ID (1, 2, 3, ...)
     * - 店舗ID文字列 (s00000001 など)
     */
    public function show($id)
    {
        $numericId = $this->normalizeRouteIdToNumeric($id);
        $data = $this->getRecruitDataFromDatabase($numericId, true);
        abort_if(empty($data['recruit']), 404);

        return view('shops.recruit.show', $this->buildRecruitViewData($data, $numericId, true, false));
    }

    /**
     * SNS共有用の公開求人情報詳細
     */
    public function publicShow(int $id)
    {
        $data = $this->getRecruitDataFromDatabase($id, true);
        abort_if(empty($data['recruit']), 404);

        return view('shops.recruit.show', $this->buildRecruitViewData($data, $id, true, true));
    }

    /**
     * 求人情報＋店舗情報をDBから取得して画面用に整形
     * ルートの {id} は内部の shop_id を 1,2.. とした表示用として扱い、
     * 実テーブルの文字列ID形式へ変換する。
     */
    private function getRecruitDataFromDatabase(int $shopNumericId, bool $publishedOnly = false): array
    {
        $shopId = 's' . str_pad((string) $shopNumericId, 8, '0', STR_PAD_LEFT);

        $q = DB::table('shops')
            ->join('shop_profiles', 'shops.id', '=', 'shop_profiles.shop_id')
            ->leftJoin('shop_jobs', 'shops.id', '=', 'shop_jobs.shop_id')
            ->where('shops.id', $shopId)
            ->select(
                'shops.id',
                'shops.status as shop_status',
                'shop_profiles.shop_name',
                'shop_profiles.main_image_path',
                'shop_profiles.opened_on',
                'shop_profiles.pref',
                'shop_profiles.city',
                'shop_profiles.addr2',
                'shop_profiles.addr3',
                'shop_profiles.station1',
                'shop_jobs.hourly_wage_regular',
                'shop_jobs.has_trial',
                'shop_jobs.trial_hourly_wage',
                'shop_jobs.noruma_reward',
                'shop_jobs.atmosphere',
                'shop_jobs.working_day',
                'shop_jobs.working_hours',
                'shop_jobs.regular_holiday',
                'shop_jobs.qualification',
                'shop_jobs.status as recruit_status',
                'shop_jobs.salary',
                'shop_jobs.noruma_cond'
            );
        if (Schema::hasColumn('shop_jobs', 'pr')) {
            $q->addSelect('shop_jobs.pr');
        }
        $row = $q->first();

        if (!$row || ($publishedOnly && (int) ($row->recruit_status ?? 0) !== 1)) {
            return [
                'recruit' => [],
                'shop'    => null,
            ];
        }

        $address = trim(($row->pref ?? '') . ($row->city ?? '') . ($row->addr2 ?? '') . ' ' . ($row->addr3 ?? ''));
        $meta = $this->decodeMeta($row->noruma_cond ?? null);
        $tagMap = $this->resolveRecruitTagNames($meta['tag_ids'] ?? []);
        $jobContent = (string) ($meta['job_content'] ?? '');
        $managerMessage = Schema::hasColumn('shop_jobs', 'pr')
            ? ((string) ($row->pr ?? '') !== '' ? (string) ($row->pr ?? '') : (string) ($meta['message'] ?? ''))
            : (string) ($meta['message'] ?? '');

        $recruit = [
            'store_name'         => $row->shop_name,
            'open_date'          => $row->opened_on ? date('Y年n月j日', strtotime($row->opened_on)) : null,
            'address'            => $address,
            'map_embed_src'      => null,
            'nearest_station'    => $row->station1,
            'hourly_wage_regular'=> $row->hourly_wage_regular ? (int) $row->hourly_wage_regular : 0,
            'trial_hourly_wage'  => !empty($row->has_trial) ? (int) $row->trial_hourly_wage : null,
            'noruma_reward'      => $row->noruma_reward ? (int) $row->noruma_reward : 0,
            'bonus_condition'    => $meta['bonus_condition'] ?? '',
            'salary_text'        => $row->salary ?? '',
            'working_hours'      => Schema::hasColumn('shop_jobs', 'working_hours') ? ($row->working_hours ?? null) : ($meta['working_hours'] ?? null),
            'working_days'       => Schema::hasColumn('shop_jobs', 'working_day') ? ($row->working_day ?? null) : ($meta['working_days'] ?? null),
            'regular_holiday'    => Schema::hasColumn('shop_jobs', 'regular_holiday') ? ($row->regular_holiday ?? null) : ($meta['regular_holiday'] ?? null),
            'job_content'        => $jobContent,
            'store_atmosphere'   => $row->atmosphere ?? '',
            'qualification'      => Schema::hasColumn('shop_jobs', 'qualification') ? ($row->qualification ?? '18歳以上（高校生不可）') : ($meta['qualification'] ?? '18歳以上（高校生不可）'),
            'catch_copy'         => $meta['catch_copy'] ?? '',
            'message'            => $managerMessage,
            'benefits'           => [],
            'selected_benefits'  => $tagMap['merit'],
            'store_features'     => [
                '報酬' => $tagMap['salary'],
                '働き方' => $tagMap['howto'],
                'メリット' => $tagMap['merit'],
                '特徴' => $tagMap['feature'],
                '設備' => $tagMap['facility'],
                'お店の雰囲気' => $tagMap['atmosphere'],
            ],
        ];

        $mainImg = $this->imageUrl($row->main_image_path);
        $subImagesRows = DB::table('shop_images')
            ->where('shop_id', $shopId)
            ->orderByRaw('main_order IS NULL')
            ->orderBy('main_order')
            ->orderBy('id')
            ->get();
        $subImages = [];
        foreach ($subImagesRows as $img) {
            $subImages[] = $this->imageUrl($img->image_path);
        }
        if (empty($subImages)) {
            $subImages[] = $mainImg;
        }

        $galleryImages = [];
        if ($mainImg) {
            $galleryImages[] = $mainImg;
        }
        foreach ($subImages as $path) {
            if ($path && !in_array($path, $galleryImages, true)) {
                $galleryImages[] = $path;
            }
        }

        $shopPost = DB::table('shop_posts')->where('shop_id', $shopId)->first();
        $shopHitokoto = $shopPost && isset($shopPost->body) ? (string) $shopPost->body : '';

        $shop = [
            'name'       => $row->shop_name,
            'word'       => $shopHitokoto,
            'main_img'   => $mainImg,
            'area'       => trim(($row->pref ?? '') . ' ' . ($row->city ?? '')),
            'concept'    => '',
            'review_avg' => 0,
            'review_cnt' => 0,
            'sub_images' => $subImages,
            'gallery_images' => $galleryImages,
        ];

        return [
            'recruit' => $recruit,
            'shop'    => $shop,
        ];
    }

    private function buildRecruitViewData(array $data, int $id, bool $forCast, bool $isPublicShare): array
    {
        $shopName = $data['shop']['name'] ?? $data['recruit']['store_name'] ?? '店舗';
        $shareUrl = route('share.recruit.show', ['id' => $id]);
        $shareText = trim((string) ($data['recruit']['catch_copy'] ?? $data['recruit']['message'] ?? ''));

        if ($shareText === '') {
            $shareText = 'ミセチョクの求人情報です。';
        }

        return [
            'pageId' => 'job_info',
            'recruit' => $data['recruit'],
            'recruit_trial' => $data['recruit'],
            'recruit_help' => $data['recruit'],
            'usesJobTypes' => false,
            'shop' => $data['shop'],
            'forCast' => $forCast,
            'isPublicShare' => $isPublicShare,
            'shareUrl' => $shareUrl,
            'shareTitle' => $shopName . 'の求人情報',
            'shareText' => mb_strimwidth($shareText, 0, 80, '…'),
        ];
    }

    private function imageUrl(?string $path): string
    {
        if (empty($path)) {
            return asset('assets/images/common/no-image.png');
        }
        if (str_starts_with($path, 'uploads/')) {
            return asset($path);
        }
        if (str_starts_with($path, 'public/')) {
            return asset('storage/' . substr($path, 7));
        }

        return asset(ltrim($path, '/'));
    }

    private function decodeMeta(?string $raw): array
    {
        if (empty($raw)) {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function resolveRecruitTagNames(array $tagIds): array
    {
        $tables = [
            'salary'     => 'tags_salary',
            'howto'      => 'tags_shop_working_styles',
            'merit'      => 'tags_shop_benefits',
            'feature'    => 'tags_shop_conditions',
            'facility'   => 'tags_shop_facilities',
            'atmosphere' => 'tags_shop_atmospheres',
        ];

        $resolved = [];
        foreach ($tables as $key => $table) {
            $ids = collect($tagIds[$key] ?? [])
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (empty($ids) || !Schema::hasTable($table)) {
                $resolved[$key] = [];
                continue;
            }

            $resolved[$key] = DB::table($table)
                ->whereIn('id', $ids)
                ->orderBy('id')
                ->pluck('name')
                ->all();
        }

        return $resolved;
    }

    /**
     * ルートの {id} を内部で扱う数値IDに正規化する
     * - "s00000001" → 1
     * - "1" / 1     → 1
     */
    private function normalizeRouteIdToNumeric($id): int
    {
        if (is_int($id) || ctype_digit((string) $id)) {
            return (int) $id;
        }

        $id = (string) $id;
        if (str_starts_with($id, 's')) {
            return (int) ltrim(substr($id, 1), '0') ?: 0;
        }

        return 0;
    }
}
