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
                'shops.license_status',
                'shop_profiles.shop_name',
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
                'shop_jobs.atmosphere'
            )
            ->first();

        if (!$row) {
            // データ不在時は空配列を返しておき、Blade側で「—」表示されるようにする
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
            'trial_hourly_wage'  => $row->has_trial ? (int) $row->trial_hourly_wage : null,
            'salary_text'        => $row->atmosphere ?? '',
            'working_hours'      => null,
            'working_days'       => null,
            'regular_holiday'    => null,
            'job_content'        => $row->job_description ?? '',
            'store_atmosphere'   => $row->atmosphere ?? '',
            'qualification'      => "18歳以上（高校生不可）",
            'catch_copy'         => $row->catch ?? '',
            'message'            => $row->message ?? '',
            'benefits'           => [],
            'selected_benefits'  => [],
            'store_features'     => [],
        ];

        // 店舗プロフィール（ギャラリー・コンセプトなど）
        $shop = [
            'name'       => $row->shop_name,
            'word'       => $row->catch ?? '',
            'main_img'   => asset('storage/mock/shops/out-1.png'),
            'area'       => trim(($row->pref ?? '') . ' ' . ($row->city ?? '')),
            'concept'    => $row->overview ?? '',
            'review_avg' => 0,
            'review_cnt' => 0,
            'sub_images' => [
                asset('storage/mock/shops/inside-1.png'),
                asset('storage/mock/shops/inside-2.png'),
                asset('storage/mock/shops/inside-3.png'),
            ],
        ];

        return [
            'recruit' => $recruit,
            'shop'    => $shop,
        ];
    }
}
