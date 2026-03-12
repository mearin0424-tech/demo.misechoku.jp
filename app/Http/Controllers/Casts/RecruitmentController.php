<?php

namespace App\Http\Controllers\Casts;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

/**
 * キャスト向け：店舗の求人情報表示
 */
class RecruitmentController extends Controller
{
    /**
     * 求人情報詳細（店舗IDで表示）
     */
    public function show(int $id)
    {
        $data = $this->getRecruitDataFromDatabase($id);
        return view('shops.recruit.show', [
            'pageId' => 'job_info',
            'recruit' => $data['recruit'],
            'shop'    => $data['shop'],
            'forCast' => true,
        ]);
    }

    /**
     * 求人情報＋店舗情報をDBから取得して画面用に整形
     * ルートの {id} は内部の shop_id を 1,2.. とした表示用として扱い、
     * 実テーブルID (s00000001 形式) に変換する。
     */
    private function getRecruitDataFromDatabase(int $shopNumericId): array
    {
        $shopId = 's' . str_pad((string) $shopNumericId, 8, '0', STR_PAD_LEFT);

        $row = DB::table('shops')
            ->join('shop_profiles', 'shops.id', '=', 'shop_profiles.shop_id')
            ->leftJoin('shop_jobs', 'shops.id', '=', 'shop_jobs.shop_id')
            ->where('shops.id', $shopId)
            ->select(
                'shops.id',
                'shops.status',
                'shop_profiles.shop_name',
                'shop_profiles.main_image_path',
                'shop_profiles.opened_on',
                'shop_profiles.pref',
                'shop_profiles.city',
                'shop_profiles.addr2',
                'shop_profiles.addr3',
                'shop_profiles.station1',
                'shop_profiles.catch',
                'shop_profiles.overview',
                'shop_profiles.message',
                'shop_jobs.hourly_wage_regular',
                'shop_jobs.has_trial',
                'shop_jobs.trial_hourly_wage',
                'shop_jobs.job_description',
                'shop_jobs.atmosphere',
                'shop_jobs.working_day',
                'shop_jobs.working_hours',
                'shop_jobs.regular_holiday',
                'shop_jobs.qualification'
            )
            ->first();

        if (!$row) {
            return [
                'recruit' => [],
                'shop'    => null,
            ];
        }

        $address = trim(($row->pref ?? '') . ($row->city ?? '') . ($row->addr2 ?? '') . ' ' . ($row->addr3 ?? ''));

        $recruit = [
            'store_name'         => $row->shop_name,
            'open_date'          => $row->opened_on ? date('Y年n月j日', strtotime($row->opened_on)) : null,
            'address'            => $address,
            'map_embed_src'      => null,
            'nearest_station'    => $row->station1,
            'hourly_wage_regular'=> $row->hourly_wage_regular ? (int) $row->hourly_wage_regular : 0,
            'trial_hourly_wage'  => !empty($row->has_trial) ? (int) $row->trial_hourly_wage : null,
            'salary_text'        => $row->atmosphere ?? '',
            'working_hours'      => $row->working_hours ?? null,
            'working_days'       => $row->working_day ?? null,
            'regular_holiday'    => $row->regular_holiday ?? null,
            'job_content'        => $row->job_description ?? '',
            'store_atmosphere'   => $row->atmosphere ?? '',
            'qualification'      => $row->qualification ?? '18歳以上（高校生不可）',
            'catch_copy'         => $row->catch ?? '',
            'message'            => $row->message ?? '',
            'benefits'           => [],
            'selected_benefits'  => [],
            'store_features'     => [],
        ];

        $mainImg = $row->main_image_path
            ? asset(ltrim($row->main_image_path, '/'))
            : asset('storage/mock/shops/out-1.png');
        $subImagesRows = DB::table('shop_images')
            ->where('shop_id', $shopId)
            ->orderByRaw('main_order IS NULL')
            ->orderBy('main_order')
            ->orderBy('id')
            ->get();
        $subImages = [];
        foreach ($subImagesRows as $img) {
            $subImages[] = asset(ltrim($img->image_path, '/'));
        }
        if (empty($subImages)) {
            $subImages = [
                asset('storage/mock/shops/inside-1.png'),
                asset('storage/mock/shops/inside-2.png'),
                asset('storage/mock/shops/inside-3.png'),
            ];
        }

        $shop = [
            'name'       => $row->shop_name,
            'word'       => $row->catch ?? '',
            'main_img'   => $mainImg,
            'area'       => trim(($row->pref ?? '') . ' ' . ($row->city ?? '')),
            'concept'    => $row->overview ?? '',
            'review_avg' => 0,
            'review_cnt' => 0,
            'sub_images' => $subImages,
        ];

        return [
            'recruit' => $recruit,
            'shop'    => $shop,
        ];
    }
}
