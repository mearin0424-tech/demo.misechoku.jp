<?php
namespace App\Repositories\Matching;

use App\Models\Matching;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Lib\StrUtil;
use App\Lib\FileUtil;
use Illuminate\Http\Request;

class MatchingRepository implements MatchingRepositoryInterface
{
    /**
     * @var App\Models\Project
     */
    private $project;

    public function __construct(Matching $project) {
        $this->project = $project;
    }


     /**
     * 登録
     *
     * @param array $data
     * @return Project
     */
    public function store($request)
    {

        $shop_id = $request['shop_id'];
        $member_id = $request['member_id'];

        Matchnig::updateOrCreate(
            ['member_id' => $member_id,'shop_id' => $shop_id], 
            $request->all()
        );

    }


    public function findMatching($member_id, $shop_id)
    {

        $records = Matching::where("member_id",$member_id)->where('shop_id',$shop_id)->get();
        return $records;

    }

    public function findMatchingShop($member_id)
    {

        $records = DB::table('matchings')
                   ->join('shops', 'matching.shop_id', '=', 'shops.id')
                   ->where('matching.member_id',$member_id)
                   ->select('shops.*')
                   ->get();

        return $records;

    }

    public function findMessagedMember($shop_id)
    {

        $records = DB::table('messages')
                   ->join('mmembers', 'messages.shop_id', '=', 'members.id')
                   ->where('messages.shop_id',$shop_id)
                   ->select('members.*')
                   ->get();

        return $records;

    }

    public function findMessageByMemberId($member_id)
    {

        $records = DB::table('messages')
                   ->join('shops', 'messages.shop_id', '=', 'shops.id')
                   ->where('messages.member_id',$member_id)
                   ->select('shops.*')
                   ->get();

        return $records;

    }

    public function findMessageByShopId($shop_id)
    {

        $records = DB::table('messages')
                   ->join('mmembers', 'messages.shop_id', '=', 'members.id')
                   ->where('messages.shop_id',$shop_id)
                   ->select('members.*')
                   ->get();

        return $records;

    }

   /**
    *    メンバーから店舗にマッチング依頼
    */
    public function findMatchigByMemberIdReadYet($shop_id)
    {

        $records = DB::table('matchings')
                   ->join('members', 'matchings.member_id', '=', 'members.id')
                   ->where('matchings.shop_id',$shop_id)
                   ->where('readed',\CommonConsts::READ_YET)
                   ->select('members.*','members.id as member_id')
                   ->get();

        return $records;

    }

    public function findMatchigEst($member_id)
    {

        $records = DB::table('matchings')
                   ->join('members', 'matchings.member_id', '=', 'members.id')
                   ->join('shops', 'matchings.shop_id', '=', 'shops.id')
                   ->where('matchings.member_id',$member_id)
                   ->where('matchings.matching_status',\MatchingConsts::MATCHING_OK)
                   ->where('readed',\CommonConsts::READ_YET)
                   ->orderBy('matchings.updated_at','desc')
                   ->select('shops.*','shops.id as shop_id','matchings.updated_at as updated_at')
                   ->get();

        return $records;

    }

    public function findMatchigByMemberId($member_id)
    {

        $records = DB::table('matchings')
                   ->join('members', 'matchings.member_id', '=', 'members.id')
                   ->join('shops', 'matchings.shop_id', '=', 'shops.id')
                   ->where('matchings.member_id',$member_id)
                   ->where('matchings.matching_status',\MatchingConsts::MATCHING_OK)
                   //->where('readed',\CommonConsts::READ_YET)
                   ->orderBy('matchings.updated_at','desc')
                   ->select('shops.*','shops.id as shop_id','matchings.updated_at as updated_at')
                   ->get();

        return $records;

    }



    public function matchingToReadedByShop($member_id,$shop_id)
    {

        $records = Matching::where('member_id',$member_id)->where('shop_id',$shop_id)->first();

        $records->readed = \CommonConsts::READ_DONE;
        $records->save();
    }


    public function matchingToReadedByMember($member_id,$shop_id)
    {

        $records = Matching::where('member_id',$member_id)->where('shop_id',$shop_id)->first();
        $records->readed_by_member = \CommonConsts::READ_DONE;
        $records->save();
    }
















} 