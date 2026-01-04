<?php

namespace App\Lib;

use Illuminate\Support\Facades\Facade;
use App\Consts\ShopConsts;
use App\Consts\TreatmentConsts;
use Illuminate\Support\Facades\DB;
use App\Models\Shop;
use App\Models\Member;
use App\Lib\FileUtil;
use App\Models\Matching;
use Carbon\Carbon;
use App\Models\Like2;
use App\Models\Good2;
use App\Models\Follow;
use App\Models\Apply;

class MemberInfoUtil extends Facade
{



    public static function imgCnt($member_id)
    {

        return DB::table('member_images')->where('member_id', $member_id)->count();
    }

    public static function imgCnt2($member_id)
    {

        return DB::table('member_images')->where('type', \FrontConsts::IDENTITY_99)->where('member_id', $member_id)->count();
    }


    public static function getImgFile($member_id)
    {

        $files =  DB::table('member_images')->where('member_id', $member_id)->where('type', \FrontConsts::IDENTITY_99)->
                  orderBy('main_order')->select('img')->get();

        $file_arr = [];
        foreach ($files as $file) {
            $file_arr[] = \FileUtil::getMemberMainImgFilePath($member_id, $file->img);
        }
        return $file_arr;
    }

    public static function avatarCnt($member_id)
    {

        return DB::table('member_images')->where('member_id', $member_id)->where('type', \FrontConsts::IDENTITY_5)->count();
    }

    public static function getAvatarImgFile($member_id)
    {

        $records =  DB::table('member_images')->where('member_id', $member_id)
            ->where('type', \FrontConsts::IDENTITY_5)->select('img')->get();


        return \FileUtil::getMemberMainImgFilePath($member_id, $records[0]->img);
    }

    public static function getPrefCityMemberStats($memberIds)
    {
        // 1 Get the total number of members for each prefecture (including empty cities)
        $prefTotals = DB::table('members')
            ->whereIn('id', $memberIds)
            ->whereIn('pref', \CommonConsts::PREFS)
            ->whereNotNull('pref')
            ->where('pref', '<>', '')
            ->select('pref', DB::raw('COUNT(*) as total'))
            ->groupBy('pref')
            ->pluck('total', 'pref'); // trả về dạng ['Tokyo' => 50, ...]

        // 2 Get valid city list by prefecture
        $cities = DB::table('members')
            ->whereIn('id', $memberIds)
            ->whereIn('pref', \CommonConsts::PREFS)
            ->whereNotNull('pref')
            ->where('pref', '<>', '')
            ->whereNotNull('city')
            ->where('city', '<>', '')
            ->select('pref', 'city', DB::raw('COUNT(*) as cnt'))
            ->groupBy('pref', 'city')
            ->orderBy('pref')
            ->orderBy('city')
            ->get()
            ->groupBy('pref');

        // 3 Attach total count to result (for view use)
        $result = collect();
        foreach ($cities as $pref => $cityList) {
            $result->put($pref, (object)[
                'total' => $prefTotals[$pref] ?? 0,
                'cities' => $cityList
            ]);
        }

        // 4 Ensure even prefixes with empty cities are visible
        foreach ($prefTotals as $pref => $total) {
            if (!$result->has($pref)) {
                $result->put($pref, (object)[
                    'total' => $total,
                    'cities' => collect()
                ]);
            }
        }

        return $result->sortKeys();
    }

    public static function isMemberDeleted($member_id)
    {
        $member = DB::table('members')
            ->where('id', $member_id)
            ->first();

        if (!$member) {
            return false;
        }

        return $member->del_flg == \CommonConsts::DEL_ON;
    }

    public static function matchingStatusName4($member_id, $shop_id)
    {


        $status = "";

        if(!Follow::where('member_id', $member_id)->where('shop_id', $shop_id)->exists()) {
            return $status;
        }

        $records = DB::table('follows')->where('member_id', $member_id)->where('shop_id', $shop_id)->first();
        $type = $records->type;

        if( \CommonConsts::FORROW_BY_MEMBER == $type || \CommonConsts::FORROW_BY_MEMBER == $type) {
            $status = "マッチング承認中";
        }else if( \CommonConsts::FORROW_BY_MEMBER_2 == $type || \CommonConsts::FORROW_BY_SHOP_2 == $type) {
            $status = "マッチング成立";
        }else if( \CommonConsts::FORROW_BY_SHOP_NG == $type || \CommonConsts::FORROW_BY_MEMBER_NG == $type) {
            $status = "マッチング不成立";
        }
        return $status;
    }


    public static function matchingStatusName($member_status, $shop_status)
    {

        $matching = \MatchingConsts::MATCHING_MID;

        if (\MatchingConsts::MATCHING_OK == $member_status && \MatchingConsts::MATCHING_OK == $shop_status) {
           $matching = \MatchingConsts::MATCHING_OK;
        } else if (\MatchingConsts::MATCHING_NG == $member_status || \MatchingConsts::MATCHING_NG == $shop_status) {
           $matching = \MatchingConsts::MATCHING_NG;
        }

        foreach (\MatchingConsts::MATCHING_STATUS as $key => $val) {
            if ($matching == $val) {
                return $key;
            }
        }

        return $matching;
    }


    public static function nickname($member_id)
    {
        $records = Member::find($member_id);
        return $records->nickname;
    }

    public static function approvalStatusName($approval)
    {

        foreach (ShopConsts::APPROVAL as $key => $val) {
            if ($approval == $val) {
                return $key;
            }
        }
        return "";
    }

    public static function cert($cert)
    {

        foreach (\FrontConsts::IDENTITY as $key => $val) {
            if ($cert == $val) {
                return $key;
            }
        }
        return "";
    }

    public static function isCert($member_id)
    {

        $records = DB::select(
            "SELECT count(*) as cnt FROM member_images where member_id =? and type in (" . \FrontConsts::IDENTITY_1 . "," . \FrontConsts::IDENTITY_2 . " ," . \FrontConsts::IDENTITY_3 . ") ",
            [$member_id]
        );
        if ($records[0]->cnt > 0) return true;
        return false;
    }

    public static function getCert($member_id)
    {

        $records = DB::select(
            "SELECT type  FROM member_images where member_id =? and type in (" . \FrontConsts::IDENTITY_1 . "," . \FrontConsts::IDENTITY_2 . " ," . \FrontConsts::IDENTITY_3 . ") ",
            [$member_id]
        );
        return self::cert($records[0]->type);
    }

    public static function isSubImg($member_id)
    {

        $records = DB::select(
            "SELECT count(*) as cnt FROM member_images where member_id =? and type =?",
            [$member_id, \FrontConsts::IDENTITY_99]
        );
        if ($records[0]->cnt > 0) return true;
        return false;
    }

    public static function isBank($member_id)
    {

        $records = DB::select(
            "SELECT count(*) as cnt FROM bank_accounts where member_id =?",
            [$member_id]
        );
        if ($records[0]->cnt > 0) return true;
        return false;
    }

    public static function isBankShop($shop_id)
    {
        return DB::table('bank_account_shops')->where('shop_id', $shop_id)->exists();
    }

    public static function isJobShop($shop_id)
    {
        return DB::table('jobs')->where('shop_id', $shop_id)->exists();
    }

    public static function depsitStatusNameById($member_id, $shop_id)
    {

        $status = "";
        $records = DB::table('deposits')->where('member_id', $member_id)->where('shop_id', $shop_id)->get();
        foreach ($records as $val) {
            $status = $val->status;
            break;
        }
        //$status  = $records[0]->status;
        foreach (\DepositConsts::DEPOSITS as $key => $val) {

            if ($status == $val) {
                return $key;
            }
        }
        return "ノルマ消化中";
    }

    public static function depositStatusNameById($member_id, $shop_id, $apply_id)
    {
        if (!$member_id || !$shop_id || !$apply_id) {
            return "";
        }

        $record = DB::table('deposits')
            ->where(compact('apply_id', 'member_id', 'shop_id'))
            ->first();

        if (!$record || !isset($record->status)) {
            return "";
        }

        $status = (int) $record->status;

        $key = array_search($status, \DepositConsts::DEPOSITS, true);

        return $key ?: "ノルマ消化中";
    }


    public static function depsitStatusNameById3($member_id, $shop_id)
    {

        $status = "";
        $records = DB::table('deposits')->where('member_id', $member_id)->where('shop_id', $shop_id)->get();
        foreach ($records as $val) {
            $status = $val->status;
            break;
        }
        //$status  = $records[0]->status;
        foreach (\DepositConsts::DEPOSITS3 as $key => $val) {
            if ($status == $val) {
                return $key;
            }
        }
        return "";
    }

    public static function depsitStatusNameById3Cnt($member_id, $shop_id)
    {

        return DB::table('deposits')->where('member_id', $member_id)
            ->where('shop_id', $shop_id)
            ->whereIn('status', [\DepositConsts::DEPOSITS_3, \DepositConsts::DEPOSITS_6])
            ->count();
    }

    public static function depsitStatusNameById1Cnt($member_id, $shop_id)
    {

        return DB::table('deposits')->where('member_id', $member_id)
            ->where('shop_id', $shop_id)
            ->whereIn('status', [\DepositConsts::DEPOSITS_3, \DepositConsts::DEPOSITS_1])
            ->count();
    }

    public static function depsitStatusKeyById($member_id, $shop_id, $apply_id = null)
    {

        $status = "";
        $records = DB::table('deposits')
            ->when(!is_null($apply_id), function ($query) use ($apply_id) {
                $query->where('apply_id', $apply_id);
            })
            ->where('member_id', $member_id)
            ->where('shop_id', $shop_id)
            ->get();
        foreach ($records as $val) {
            $status = $val->status;
            break;
        }
        //$status  = $records[0]->status;
        foreach (\DepositConsts::DEPOSITS as $key => $val) {
            if ($status == $val) {
                return $val;
            }
        }

        return "";
    }

    public static function historyDepositStatusName($status) {

        foreach (\DepositConsts::DEPOSITS as $key => $val ) {
            if($status==$val) {
                return $key;
            }
        }
        return "";
    }

    public static function getNorumaAmount(?object $apply): int
    {
        if (!$apply) return 0;

        switch ($apply->about_recruit) {
            case \RicruitConsts::ABOUT_RECRUIT_FULLTIME:
                return (int) $apply->noruma_reward ?? 0;
            case \RicruitConsts::ABOUT_RECRUIT_TRIAL:
                return (int) $apply->noruma_reward_trial ?? 0;
            case \RicruitConsts::ABOUT_RECRUIT_HELP:
                return (int) $apply->noruma_reward_help ?? 0;
            default:
                return 0;
        }
    }


    public static function depsit($enc_member_id, $enc_shop_id)
    {

        $member_id = \StrUtil::dec($enc_member_id);
        $shop_id   = \StrUtil::dec($enc_shop_id);
        $records = DB::table('deposits')->where('member_id', $member_id)->where('shop_id', $shop_id)->get();
        return $records;
    }


    public static function applyStatusNameByIdArr($member_id, $shop_id)
    {

        $result = [];
        $records = DB::table('applies')->where('member_id', $member_id)->where('shop_id', $shop_id)->get();

        foreach ($records as $val) {
            $result[] = $val->result;
        }
        //$status  = $records[0]->status;
        $result = array_unique($result);
        $res = [];
        foreach (\RicruitConsts::ADOPTION as $key => $val) {
            if ( in_array($val, $result) ) {
                $res[] = $key;
            }
        }
        return $res;
    }

    public static function applyStatusNameById($member_id, $shop_id)
    {

        $result = "";
        $records = DB::table('applies')->where('member_id', $member_id)->where('shop_id', $shop_id)->get();


        foreach ($records as $val) {
            $result = $val->status;
            break;
        }
        //$status  = $records[0]->status;
        foreach (\RicruitConsts::ADOPTION as $key => $val) {
            if ($result == $val) {
                return $key;
            }
        }
        return "";
    }

    public static function applyStatusNameById4($id)
    {

        $result = "";
        $records = Apply::find($id);

        foreach (\RicruitConsts::ADOPTION as $key => $val) {
            if ($records->result == $val) {
                return $key;
            }
        }
        return "";
    }

    public static function applyStatusNameMultiById($member_id, $shop_id)
    {
        $results = [];
        $records = DB::table('applies')
            ->where('member_id', $member_id)
            ->where('shop_id', $shop_id)
            ->get();

        foreach ($records as $record) {
            $result = $record->status;

            // RicruitConsts::ADOPTIONの中で一致するキーを探す
            foreach (\RicruitConsts::ADOPTION as $key => $val) {
                if ($result == $val) {
                    // 結果が重複していない場合のみ追加
                    if (!in_array($key, $results)) {
                        $results[] = $key;
                    }
                }
            }
        }

        return $results;
    }

    public static function recruitText(?int $about_recruit): string
    {
        return array_flip(\RicruitConsts::ABOUT_RECRUIT)[$about_recruit] ?? '';
    }

    public static function depositText(?int $deposits_status): string
    {
        return array_flip(\DepositConsts::DEPOSITS)[$deposits_status] ?? '';
    }

    public static function adoptionText(?int $adoption): string
    {
        return array_flip(\RicruitConsts::ADOPTION_STATUS)[$adoption] ?? '';
    }

    public static function getRecruitInfo($record)
    {
        $info = [
            \RicruitConsts::ABOUT_RECRUIT_FULLTIME => [
                'label'        => '本入店',
                'day'          => (int) $record->normal_time,
                'reward_min'   => $record->noruma_reward,
                'reward_max'   => $record->noruma_reward2,
                'hours_day'    => $record->hours_day,
                'bonus_rewards'    => $record->bonus_rewards,
            ],
            \RicruitConsts::ABOUT_RECRUIT_TRIAL => [
                'label'        => '体験入店',
                'day'          => (int) $record->normal_time_trial,
                'reward_min'   => $record->noruma_reward_trial,
                'reward_max'   => $record->noruma_reward2_trial,
                'hours_day'    => $record->hours_day_trial,
                'bonus_rewards'    => $record->bonus_rewards,
            ],
            \RicruitConsts::ABOUT_RECRUIT_HELP => [
                'label'        => 'ヘルプ',
                'day'          => (int) $record->normal_time_help,
                'reward_min'   => $record->noruma_reward_help,
                'reward_max'   => $record->noruma_reward2_help,
                'hours_day'    => $record->hours_day_help,
                'bonus_rewards'    => $record->bonus_rewards,
            ],
        ];

        $current = $info[$record->about_recruit] ?? null;
        return $current;
    }

    public static function applyStatusNameSingleById($member_id, $shop_id)
    {
        $results = [];
        $records = DB::table('applies')
            ->where('member_id', $member_id)
            ->where('shop_id', $shop_id)
            ->get();
        foreach ($records as $record) {
            $result[] = $record->status;

            // RicruitConsts::ADOPTIONの中で一致するキーを探す
            foreach (\RicruitConsts::ADOPTION as $key => $val) {
                if ( in_array($val,$result)) {
                    // 結果が重複していない場合のみ追加
                    //if (!in_array($key, $results)) {
                        $results[] = $key;
                    //}
                }
            }
        }

        return array_unique($results);
    }
    public static function applyStatusNameById3($id)
    {

        $result = "";
        $records = DB::table('applies')->where('id', $id)->get();

        foreach ($records as $val) {
            $result = $val->about_recruit;
            break;
        }
        //$status  = $records[0]->status;
        foreach (\RicruitConsts::ABOUT_RECRUIT as $key => $val) {
            if ($result == $val) {
                return $key;
            }
        }
        return "";
    }

    public static function applyTypeNameById($member_id, $shop_id)
    {
        $type = "";
        $records = DB::table('applies')->orderBy('updated_at','desc')->where('member_id', $member_id)->where('shop_id', $shop_id)->get();

        foreach ($records as $val) {
            $type = $val->type;
            break;
        }

        foreach (\RicruitConsts::RECRUITMENT as $key => $val) {
            if ($type == $val) {
                return $key;
            }
        }
        return "";
    }

    public static function applyTypeNameById2($member_id, $shop_id)
    {

        $type = "";
        $records = DB::table('applies')->where('member_id', $member_id)->where('shop_id', $shop_id)->get();
        foreach ($records as $val) {
            $type = $val->type;
            break;
        }

        foreach (\RicruitConsts::ADOPTION as $key => $val) {
            if ($type == $val) {
                return $key;
            }
        }
        return "";
    }

    public static function applyTypeNameById3($id)
    {

        $type = "";
        $records = DB::table('applies')->where('id', $id)->get();
        foreach ($records as $val) {
            $type = $val->adoption;
            break;
        }

        foreach (\RicruitConsts::ADOPTION_STATUS as $key => $val) {
            if ($type == $val) {
                return $key;
            }
        }
        return "";
    }

    public static function applyHelpCnt($enc_member_id, $enc_shop_id)
    {

        $member_id = \StrUtil::dec($enc_member_id);
        $shop_id   = \StrUtil::dec($enc_shop_id);

        return DB::table('applies')->where('member_id', $member_id)->where('shop_id', $shop_id)
            //->where('type', \RicruitConsts::RECRUITMENT_HELP)
            ->count();
    }

    public static function applyHelp($enc_member_id, $enc_shop_id)
    {

        $member_id = \StrUtil::dec($enc_member_id);
        $shop_id   = \StrUtil::dec($enc_shop_id);
        return DB::table('applies')->where('member_id', $member_id)->where('shop_id', $shop_id)
            //->where('type', \RicruitConsts::RECRUITMENT_HELP)
            ->get();
    }

    public static function applyFullCnt($enc_member_id, $enc_shop_id)
    {

        $member_id = \StrUtil::dec($enc_member_id);
        $shop_id   = \StrUtil::dec($enc_shop_id);

        return DB::table('applies')->where('member_id', $member_id)->where('shop_id', $shop_id)
            //->where('type', \RicruitConsts::RECRUITMENT_FULL)
            ->count();
    }

    public static function applyFull($enc_member_id, $enc_shop_id)
    {

        $member_id = \StrUtil::dec($enc_member_id);
        $shop_id   = \StrUtil::dec($enc_shop_id);

        return DB::table('applies')->where('member_id', $member_id)->where('shop_id', $shop_id)
            //->where('type', \RicruitConsts::RECRUITMENT_FULL)
            ->get();
    }


    public static function applyCnt($member_id, $shop_id)
    {

        return DB::table('applies')->where('member_id', $member_id)->where('shop_id', $shop_id)
            //->whereIn('result',[\RicruitConsts::INTERVIEW_YET.\RicruitConsts::INTERVIEW_DONE])
//            ->whereIn('result', [\RicruitConsts::ADOPTION_HELP, \RicruitConsts::ADOPTION_FULL])
            ->count();
    }

    public static function hasApplied($member_id, $shop_id)
    {
        return DB::table('applies')->where('member_id', $member_id)->where('shop_id', $shop_id)->exists();
    }

    public static function hasAppliedWithStatus($member_id, $shop_id, $active = null, $status = null, $adoption = null)
    {
        return DB::table('applies')
            ->where('member_id', $member_id)
            ->where('shop_id', $shop_id)
            ->when(!is_null($active), function ($query) use ($active) {
                $query->where('active', $active);
            })
            ->when(!is_null($status), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when(!is_null($adoption), function ($query) use ($adoption) {
                $query->where('adoption', $adoption);
            })
            ->exists();
    }


    public static function applyByFullCnt($member_id, $shop_id)
    {

        return DB::table('applies')->where('member_id', $member_id)->where('shop_id', $shop_id)
            //->whereIn('result',[\RicruitConsts::INTERVIEW_YET.\RicruitConsts::INTERVIEW_DONE])
            ->where('result', [ \RicruitConsts::ADOPTION_FULL])
            ->count();
    }



    public static function shop_name($shop_id)
    {
        $records = Shop::find($shop_id);
        return $records->shop_name;
    }

    public static function matchingCnt($member_id)
    {

        return DB::table('matchings')->where('member_id', $member_id)->where('matching_status', \MatchingConsts::MATCHING_OK)->count();
    }

    public static function matchingEeachCnt($enc_member_id, $enc_shop_id)
    {
        $member_id = \StrUtil::dec($enc_member_id);
        $shop_id   = \StrUtil::dec($enc_shop_id);

        return DB::table('matchings')->where('member_id', $member_id)->where('shop_id', $shop_id)->where('matching_status', '<>', \MatchingConsts::MATCHING_NG)->count();
    }

    public static function postedCnt($shop_id)
    {

        return DB::table('posteds')->where('shop_id', $shop_id)->count();
    }

    public static function reviewdCnt($shop_id)
    {

        return DB::table('reviews')->where('shop_id', $shop_id)->count();
    }

    public static function reviewByMemberToShopCnt($member_id, $shop_id)
    {

        return DB::table('reviews')->where('member_id', $member_id)->where('shop_id', $shop_id)->count();
    }

    public static function getReviewdByMemberToShop($member_id, $shop_id, $apply_id)
    {

        $records = DB::table('reviews')
            ->where('apply_id', $apply_id)
            ->where('member_id', $member_id)->where('shop_id', $shop_id)->get();
        foreach ($records as $record) {
            return $record->contents;
        }
    }

    public static function getReviewId($member_id, $shop_id)
    {

        $records = DB::table('reviews')->where('member_id', $member_id)->where('shop_id', $shop_id)->select('reviews.id as review_id')->get();
        foreach ($records as $record) {
            return $record->review_id;
        }
        return "";
    }


    public static function getReviewdStatusByMemberToShop($member_id, $shop_id, $apply_id)
    {

        $records = DB::table('reviews')
            ->where('apply_id', $apply_id)
            ->where('member_id', $member_id)->where('shop_id', $shop_id)->get();
        foreach ($records as $record) {
            return $record->release;
        }
        return "";
    }

    public static function followCnt($member_id)
    {

        return DB::table('follows')->where('member_id', $member_id)->count();
    }


    public static function followByShopCnt($member_id, $shop_id)
    {

        return DB::table('follows')->where('type', \CommonConsts::FORROW_BY_SHOP)->where('member_id', $member_id)->where('shop_id', $shop_id)->count();
    }

    public static function followByMemberCnt2($member_id, $shop_id)
    {

        return DB::table('follows')->where('type', \CommonConsts::FORROW_BY_MEMBER_2)->where('member_id', $member_id)->where('shop_id', $shop_id)->count();
    }

    public static function followByShopCnt2($member_id, $shop_id)
    {

        return DB::table('follows')->where('type', \CommonConsts::FORROW_BY_SHOP_2)->where('member_id', $member_id)->where('shop_id', $shop_id)->count();
    }

    public static function followByMemberCnt($member_id, $shop_id)
    {

        return DB::table('follows')->where('type', \CommonConsts::FORROW_BY_MEMBER)->where('member_id', $member_id)->where('shop_id', $shop_id)->count();
    }

    public static function blockByMemberCnt($member_id, $shop_id)
    {
        return DB::table('follows')
            ->where('member_id', $member_id)
            ->where('shop_id', $shop_id)
            ->where('blocked_by', 'member')
            ->where('type', \CommonConsts::FORROW_BY_MEMBER_NG)
            ->count();
    }

    public static function blockByShopCnt($member_id, $shop_id)
    {
        return DB::table('follows')
            ->where('member_id', $member_id)
            ->where('shop_id', $shop_id)
            ->where('blocked_by', 'shop')
            ->where('type', \CommonConsts::FORROW_BY_SHOP_NG)
            ->count();
    }

    public static function good2Cnt($member_id)
    {
        $count =  DB::table('good2s')->where('member_id',$member_id)->count();
        return $count;
    }

    public static function like2MemberCnt($member_id)
    {
        $count =  DB::table('like2s')->where('member_id',$member_id)->count();
        return $count;
    }

    public static function likeMemberCnt($member_id)
    {
        $count =  DB::table('likes')->where('member_id',$member_id)->count();
        return $count;
    }

    public function good2ByMyself($member_id)
    {
        try {
            Good2::Create(
                ['member_id' => $member_id,]
            );
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    public function unGood2ByMyself($member_id)
    {
        try {

            $like = Good2::where("member_id", $member_id)
                ->first();
            if ($like) {
                $like->delete();
            }
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    public static function isGoodByMyselfDaily($member_id)
    {
        $today = Carbon::today();

        $existingLike  =  Good2::where('member_id', $member_id)->where('created_at', '>=', $today)->first();

        if ($existingLike) {
            return true;
        }
        return false;
    }

    public static function isLikeDaily($shop_id, $member_id)
    {
        $today = Carbon::today();

        $existingLike  =  Like2::where('shop_id', $shop_id)->where('member_id', $member_id)->where('created_at', '>=', $today)->first();

        if ($existingLike) {
            return true;
        }
        return false;
    }

    public static function canfollowByMember($member_id, $shop_id)
    {

        if (
            DB::table('follows')->where('member_id', $member_id)->where('shop_id', $shop_id)->count() == 0 ||
            Self::followByShopCnt($member_id, $shop_id) > 0
        ) {
            return true;
        }

        return false;
    }

    public static function canDepostByMember($member_id, $shop_id)
    {

        if (
            DB::table('applies')->where('member_id', $member_id)
            ->where('shop_id', $shop_id)
            ->whereIn('status', ['\RicruitConsts::ADOPTION_FULL', '\RicruitConsts::ADOPTION_HELP'])
            ->count() > 0 &&
            DB::table('deposits')->where('member_id', $member_id)
            ->where('shop_id', $shop_id)
            //->whereIn('status',['\Deposits::ADOPTION_FULL','\Deposits::ADOPTION_HELP'])
            ->count() == 0
        ) {

            return true;
        }

        return false;
    }



    public static function canfollowByShop($member_id, $shop_id)
    {

        if (
            DB::table('follows')->where('member_id', $member_id)->where('shop_id', $shop_id)->count() == 0 ||
            Self::followByMemberCnt($member_id, $shop_id) > 0
        ) {
            return true;
        }

        return false;
    }



    public static function followByEachCnt($member_id, $shop_id)
    {

        $cnt  = Self::followByShopCnt($member_id, $shop_id);
        $cnt2 = Self::followByMemberCnt($member_id, $shop_id);
        return ($cnt + $cnt2);
    }


    public static function isCanTalk($member_id, $shop_id)
    {

        $cnt = Matching::where('matching_status', \MatchingConsts::MATCHING_OK)->where('member_id', $member_id)->where('shop_id', $shop_id)->count();

        if ($cnt > 0) return true;

        return false;
    }

    public static function shopUpdateCnt($shop_id)
    {

        return DB::table('shop_updates')->where('shop_id', $shop_id)->count();
    }

    public static function adoptionsnFullCnt($member_id)
    {
        return DB::table('applies')
            ->where('member_id', $member_id)
            ->where('status', \RicruitConsts::INTERVIEW_DONE)
            ->where('adoption', \RicruitConsts::ADOPTION_OK)
            ->where('about_recruit', \RicruitConsts::ABOUT_RECRUIT_FULLTIME)
            ->count();
    }

    public static function adoptionsnTrialCnt ($member_id)
    {
        return DB::table('applies')
            ->where('member_id', $member_id)
            ->where('status', \RicruitConsts::INTERVIEW_DONE)
            ->where('adoption', \RicruitConsts::ADOPTION_OK)
            ->where('about_recruit', \RicruitConsts::ABOUT_RECRUIT_TRIAL)
            ->count();
    }

    public static function adoptionsnHelpCnt($member_id)
    {
        return DB::table('applies')
            ->where('member_id', $member_id)
            ->where('status', \RicruitConsts::INTERVIEW_DONE)
            ->where('adoption', \RicruitConsts::ADOPTION_OK)
            ->where('about_recruit', \RicruitConsts::ABOUT_RECRUIT_HELP)
            ->count();
    }

    public static function adoptionsnNgCnt($member_id)
    {
        return DB::table('applies')
            ->where('member_id', $member_id)
            ->where('status', \RicruitConsts::INTERVIEW_DONE)
            ->where('adoption', \RicruitConsts::ADOPTION_REJECTED)
            ->count();
    }


    public static function adoptionsnFull($member_id)
    {

        $records = DB::table('applies')
            ->join('shops', 'applies.shop_id', '=', 'shops.id')
            ->where('applies.member_id', $member_id)
            ->where('applies.status', \RicruitConsts::INTERVIEW_DONE)
            ->where('applies.adoption', \RicruitConsts::ADOPTION_OK)
            ->where('applies.about_recruit', \RicruitConsts::ABOUT_RECRUIT_FULLTIME)
            ->select('shops.*')
            ->get();

        return  $records;
    }

    public static function adoptionsnTrial($member_id)
    {
        $records = DB::table('applies')
            ->join('shops', 'applies.shop_id', '=', 'shops.id')
            ->where('applies.member_id', $member_id)
            ->where('applies.status', \RicruitConsts::INTERVIEW_DONE)
            ->where('applies.adoption', \RicruitConsts::ADOPTION_OK)
            ->where('applies.about_recruit', \RicruitConsts::ABOUT_RECRUIT_TRIAL)
            ->select('shops.*')
            ->get();

        return  $records;
    }

    public static function adoptionsnHelp($member_id)
    {

        $records = DB::table('applies')
            ->join('shops', 'applies.shop_id', '=', 'shops.id')
            ->where('applies.member_id', $member_id)
            ->where('applies.status', \RicruitConsts::INTERVIEW_DONE)
            ->where('applies.adoption', \RicruitConsts::ADOPTION_OK)
            ->where('applies.about_recruit', \RicruitConsts::ABOUT_RECRUIT_HELP)
            ->select('shops.*')
            ->get();
        return  $records;
    }

    public static function adoptionsnNg($member_id)
    {

        $records = DB::table('applies')
            ->join('shops', 'applies.shop_id', '=', 'shops.id')
            ->where('applies.member_id', $member_id)
            ->where('applies.status', \RicruitConsts::INTERVIEW_DONE)
            ->where('applies.adoption', \RicruitConsts::ADOPTION_REJECTED)
            ->select('shops.*')
            ->get();
        return  $records;
    }



    public static function blockedShop($member_id)
    {

        $records = DB::table('blocks')
            ->join('shops', 'blocks.shop_id', '=', 'shops.id')
            ->where('blocks.member_id', $member_id)
            ->select('shops.*')
            ->get();
        return  $records;
    }

    public static function last_shop_update_at($shop_id)
    {

        $records = DB::table('shop_updates')->where('shop_id', $shop_id)->orderBy('updated_at', 'desc')->get();
        $updated_at = "";
        foreach ($records as $val) {
            $updated_at = $val->updated_at;
            break;
        }
        //return $last_login_at;
        if ($updated_at == "") return "未更新";
        else return \StrUtil::y_m_d_h_i($updated_at);
    }

    public static function last_login_at($shop_id)
    {

        $records = DB::table('shops')
            ->join('managers', 'shops.id', '=', 'managers.shop_id')
            ->where('shops.id', $shop_id)
            ->orderBy('managers.last_login_at', 'desc')
            ->limit(1)
            ->select('managers.last_login_at')
            ->get();
        $last_login_at = "";
        foreach ($records as $val) {
            $last_login_at = $val->last_login_at;
            break;
        }
        //return $last_login_at;
        if ($last_login_at == "") return "未ログイン";
        else return \StrUtil::y_m_d_h_i($last_login_at);
    }


    public static function bankAccounts($member_id)
    {

        return DB::table('bank_accounts')->where('member_id', $member_id)->get();
    }

    public static function matchingStatusName2($matching)
    {

        foreach (\MatchingConsts::MATCHING_STATUS as $key => $val) {
            if ($matching == $val) {
                return $key;
            }
        }
        return $matching;
    }

    public static function depositStatusName($status)
    {

        foreach (\DepositConsts::DEPOSITS2 as $key => $val) {
            if ($status == $val) {
                return $key;
            }
        }
        return "";
    }

    public static function getAllForMemberCnt($member_id)
    {
        /*
        return  DB::table('news')->where('type','<>',\NewsConsts::Type_3)->where('del_flg',\CommonConsts::DEL_OFF)->
                where('updated_at','>=','DATEADD(day,-7,GETDATE())')->count();
*/
        return DB::table('readed_member_news')->where('member_id', $member_id)->where('is_readed', '0')->count();
    }

    public static function isReadedNews($news_id, $member_id)
    {

        $cnt = DB::table('readed_member_news')->where('news_id', $news_id)->where('member_id', $member_id)->where('is_readed', '1')->count();

        if ($cnt > 0) return true;
        return false;
    }

    public static function findById($member_id)
    {
        $records = Member::find($member_id);
        return $records;
    }

    public static function isFullRigist($member_id)
    {
        $res = self::findById($member_id);

        //
        $img_cnt = MemberImage::where('member_id', $member_id)->where('type', \FrontConsts::IDENTITY_99)->count();
        $ind_cnt = MemberIndustry::where('member_id', $member_id)->count();
        $ind_exp_cnt = MemberIndustryExp::where('member_id', $member_id)->count();

        if (
            !emtpy($res->nickname) && !emtpy($res->name) && !emtpy($res->kana) && !emtpy($res->tel) &&
            !emtpy($res->zip) && !emtpy($res->pref) && !emtpy($res->addr1) && !emtpy($res->addr2) &&
            !emtpy($res->heigth) && !emtpy($res->weight) && !emtpy($res->b) && !emtpy($res->w) &&
            !emtpy($res->h) && !emtpy($res->shift) && !emtpy($res->profession) && !emtpy($res->pr) &&
            !emtpy($res->comment) && !emtpy($res->type) && self::isCert($member_id) && $img_cnt > 0 &&
            $ind_cnt > 0 && $ind_exp_cnt > 0
        ) {

            return true;
        }
        return false;
    }



    /**
     *    メンバーの未読数
     */
    public static function countReadYetByMember($member_id)
    {
        if (empty($member_id)) {
            return 0;
        }

        $count = DB::table('messages')
            ->join('shops', 'messages.shop_id', '=', 'shops.id')
            ->join('members', 'messages.member_id', '=', 'members.id')
//            ->where('members.approval', \FrontConsts::APPROVAL_ON)
//            ->where('members.del_flg', \CommonConsts::DEL_OFF)
            ->where('messages.member_id', $member_id)
            ->where('messages.readed', \CommonConsts::READ_YET)
            ->where('messages.type', 1)
            ->count();

        return $count;
    }

    public static function matchingStatusNameById($member_id, $shop_id)
    {

        $status = "";
        $records = DB::table('matchings')->where('member_id', $member_id)->where('shop_id', $shop_id)->get();
        foreach ($records as $val) {
            $status = $val->matching_status;
            break;
        }

        foreach (\MatchingConsts::MATCHING_STATUS as $key => $val) {
            if ($status == $val) {
                if ($val == \MatchingConsts::MATCHING_OK) {
                    return "<p class='status-txt active'>$key</p>";
                } else if ($val == \MatchingConsts::MATCHING_MID) {
                    return "<p class='status-txt'>$key</p>";
                } else if ($val == \MatchingConsts::MATCHING_NG) {
                    return "<p class='status-txt ng'>$key</p>";
                }
            }
        }

        $records = DB::table('follows')->where('member_id', $member_id)->where('shop_id', $shop_id)->get();
        foreach ($records as $val) {
            $status = $val->type;
            break;
        }

        foreach (\MatchingConsts::MATCHING_STATUS as $key => $val) {
            if ($status == $val) {
                if ($val == \CommonConsts::FORROW_BY_MEMBER) {
                    return "<p class='status-txt'>マッチング承認待ち</</p>";
                } else if ($val == \MatchingConsts::MATCHING_NG) {
                    return "<p class='status-txt ng'>マッチング不成立</p>";
                }
            }
        }

        return "";
    }

    public static function applyStatusNameById2($member_id, $shop_id)
    {

        $result = "";
        $records = DB::table('applies')->where('member_id', $member_id)->where('shop_id', $shop_id)->get();

        foreach ($records as $val) {
            $result = $val->result;
            break;
        }
        //$status  = $records[0]->status;
        foreach (\RicruitConsts::ADOPTION as $key => $val) {
            if ($result == $val) {
                if ($val == \MatchingConsts::MATCHING_OK) {
                    return "<p class='status-txt active'>採用</p>";
                }
            }
        }
        return "";
    }

    public static function distance($shop_lat,$shop_log)
    {


        if(!isset($_COOKIE['lat'])) return;
        if(!isset($_COOKIE['log'])) return;
        //Log::info('distance lat->'.$_COOKIE['lat']);
        //Log::info('distance log->'.$_COOKIE['log']);
        //Log::info('distance');

        $start_latutude = (float)$_COOKIE['lat'];
        $start_longitude = (float)$_COOKIE['log'];
        $earth_r = 6378.137;
        $end_latitude  = (float)$shop_lat;
        $end_longitude = (float)$shop_log;
        $latitude_margin = deg2rad($end_latitude - $start_latutude);
        $longitide_margin = deg2rad($end_longitude - $start_longitude );
        $south_north = $earth_r * $latitude_margin;
        $west_east = cos(deg2rad($start_latutude)) * $earth_r * $longitide_margin;
        $distance = sqrt(pow($west_east,2) + pow($south_north,2));
        //if($distance<1000) $distance= $distance/1000;

        if ($distance >= 1000) {
             $distance = $distance / 1000;
        }


        //Log::info('distance'.$distance);

        return $distance;


    }
}
