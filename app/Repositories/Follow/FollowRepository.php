<?php
namespace App\Repositories\Follow;

use App\Models\Follow;
use App\Models\Matching;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Lib\StrUtil;
use App\Lib\FileUtil;
use Illuminate\Http\Request;
use App\Consts\ShopConsts;


class FollowRepository implements FollowRepositoryInterface
{
    /**
     * @var App\Models\Project
     */
    private $project;

    public function __construct(Follow $project) {
        $this->project = $project;
    }


     /**
     * 足あとを登録する
     *
     * @param array $data
     * @return Project
     */
    public function store(Request $request)
    {
/*
        $project = $this->project->create($data);
        $shop_id = $request['shop_id'];
        $member_id = $request['member_id'];

        Footprints::updateOrCreate(
            ['member_id' => $member_id,'shop_id' => $shop_id],
            $request->all()
        );
*/
        $project = $this->project->create($data);
        return $project;

    }

   public function findFollowShop($member_id)
    {

        $records = DB::table('follows')
                   ->join('shops', 'follows.shop_id', '=', 'shops.id')
                   ->where('follows.member_id',$member_id)
                   ->where('shops.approval',\ShopConsts::APPROVAL_ON)
                   ->groupby('shops.id')
                   ->select('shops.*')
                   ->get();

        $records2 = DB::table('follows')
                   ->join('w_shops', 'follows.shop_id', '=', 'w_shops.id')
                   ->where('follows.member_id',$member_id)
                   ->where('w_shops.approval',\ShopConsts::APPROVAL_OFF2)
                   ->groupby('w_shops.id')
                   ->select('w_shops.*')
                   ->get();

        return $records->merge($records2);
//      return $records;

    }

   public function findGoodByShop($member_id)
    {

        $records = DB::table('good2s')
                   ->join('shops', 'good2s.shop_id', '=', 'shops.id')
                   ->join('jobs', 'jobs.shop_id', '=', 'shops.id')
                   ->where('good2s.member_id',$member_id)
//                   ->where('shops.approval',\ShopConsts::APPROVAL_ON)
                   ->groupby('shops.id','jobs.helpjob')
                   ->select('shops.*','jobs.helpjob as helpjob')
                   ->get();
/*
        $records2 = DB::table('good2s')
                   ->join('w_shops', 'good2s.shop_id', '=', 'w_shops.id')
                   ->where('good2s.member_id',$member_id)
                   ->where('w_shops.approval',\ShopConsts::APPROVAL_OFF2)
                   ->groupby('w_shops.id')
                   ->select('w_shops.*')
                   ->get();

        return $records->merge($records2);
*/
      return $records;

    }

   public function findGoodByMember($member_id)
    {

        $records = DB::table('goods')
                   ->join('shops', 'goods.shop_id', '=', 'shops.id')
                   ->join('jobs', 'jobs.shop_id', '=', 'shops.id')
                   ->where('goods.member_id',$member_id)
//                   ->where('shops.approval',\ShopConsts::APPROVAL_ON)
                   ->groupby('shops.id','jobs.helpjob')
                   ->select('shops.*','jobs.helpjob as helpjob')
                   ->get();
/*
        $records2 = DB::table('goods')
                   ->join('w_shops', 'goods.shop_id', '=', 'w_shops.id')
                   ->where('goods.member_id',$member_id)
                   ->where('w_shops.approval',\ShopConsts::APPROVAL_OFF2)
                   ->groupby('w_shops.id')
                   ->select('w_shops.*')
                   ->get();
*/
//        return $records->merge($records);
       return $records;

    }

    public function findFollowMember($shop_id)
    {

        $records = DB::table('follows')
                   ->join('members', 'follows.member_id', '=', 'members.id')
                   ->where('follows.shop_id',$shop_id)
                   ->select('members.*')
                   ->get();

        return $records;

    }

    public function findFollowByMemberId($member_id)
    {
/*
        $records = DB::table('follows')
                   ->join('shops', 'follows.shop_id', '=', 'shops.id')
                   ->join('jobs', 'jobs.shop_id', '=', 'follows.shop_id')
                   ->where('follows.member_id',$member_id)
                   ->select('shops.*','jobs.*','shops.id as shop_id')
                   ->get();
*/
        $records = DB::table('follows')
                   ->join('shops', 'follows.shop_id', '=', 'shops.id')
                   ->where('follows.member_id',$member_id)
                   //->where('readed',\CommonConsts::READ_YET)
                   ->whereIn('type',[\CommonConsts::FORROW_BY_SHOP,\CommonConsts::FORROW_BY_MEMBER_2,\CommonConsts::FORROW_BY_SHOP_2])
                   //->select('members.*','members.id as member_id','follows.id as follow_id')
                   ->select('shops.*','shops.id as shop_id')

                   ->get();


        return $records;

    }

    public function findFollowByShopId($shop_id)
    {


        $records = DB::table('follows')
                   ->join('members', 'members.id', '=', 'follows.member_id')
                   ->where('follows.shop_id',$shop_id)
                   ->select('members.*','members.id as member_id','follows.id as follow_id')
                   ->get();

        return $records;

    }

    public function follow($member_id,$shop_id)
   {

        $cnt = \MemberInfoUtil::followByMemberCnt($member_id,$shop_id);
        $type="";
        if($cnt>0){
            $type=\CommonConsts::FORROW_BY_SHOP_2;
        }else{
            $type=\CommonConsts::FORROW_BY_SHOP;
        }

        Follow::updateOrCreate(
            ['member_id' => $member_id,'shop_id' => $shop_id],
            ['type'=>$type]

        );
        if($cnt>0){

            Matching::updateOrCreate(
            ['member_id' => $member_id,'shop_id' => $shop_id],
            ['matching_status'=>\MatchingConsts::MATCHING_OK,"matching_date"=>now()]
            );

         }
    }


    public function doFollowByMember($member_id,$shop_id)
    {
        /*
        $request['type'] = \CommonConsts::FORROW_BY_MEMBER;
        $res = $request->fill($request->except('_token', '_method'))-> save();*/

        $records = DB::table('follows')->where('member_id',$member_id)->where('shop_id',$shop_id)->get();
        $type="";
        foreach($records as $record){
            $type=$record->type;
        }

         // メンバーから
        if(\CommonConsts::FORROW_BY_MEMBER==$type){

            // ショップからメンバーに→マッチング成立
            $type=\CommonConsts::FORROW_BY_MEMBER_2;

        // ショップから
        }else if(\CommonConsts::FORROW_BY_SHOP==$type){

            // メンバーからショップに→マッチング成立
            $type=\CommonConsts::FORROW_BY_SHOP_2;

        }else{
           $type=\CommonConsts::FORROW_BY_MEMBER;
        }

        $res = Follow::updateOrCreate(
            ['member_id' => $member_id,'shop_id' => $shop_id],
            ['type'=>$type]
        );

        //$cnt  = \MemberInfoUtil::followByShopCnt($member_id,$shop_id);
        if($type==\CommonConsts::FORROW_BY_MEMBER_2||$type==\CommonConsts::FORROW_BY_SHOP_2){

            Matching::updateOrCreate(
            ['member_id' => $member_id,'shop_id' => $shop_id],
            ['matching_status'=>\MatchingConsts::MATCHING_OK,"matching_date"=>now()]
            );

         }

    }



    public function isMatching($member_id,$shop_id)
    {

        $cnt = DB::table('follows')->whereIn('status',[\CommongConsts::FORROW_BY_MEMBER_2,\CommongConsts::FORROW_BY_SHOP_2])->count();
        if($cnt>0) return true;
        return false;
    }


    public function follow2($member_id,$shop_id)
    {
        Follow::updateOrCreate(
            ['member_id' => $member_id,'shop_id' => $shop_id],
            ['type'=>\CommonConsts::FORROW_BY_SHOP]
        );

    }

    public function followNg($member_id,$shop_id)
    {
        Follow::updateOrCreate(
            ['member_id' => $member_id,'shop_id' => $shop_id],
            ['type'=>\CommonConsts::FORROW_BY_SHOP_NG]
        );

    }


    /**
    *    メンバーから店舗にフォロー
    */
    public function findFollowByMemberIdReadYet($shop_id)
    {
/*
        $records = DB::table('follows')
                   ->join('members', 'follows.member_id', '=', 'members.id')
                   ->where('follows.shop_id',$shop_id)
                   ->where('readed',\CommonConsts::READ_YET)
                   ->where('type',\CommonConsts::FORROW_BY_MEMBER)
                   ->select('members.*','members.id as member_id','follows.id as follow_id')
                   ->get();
*/

        $latest_applies = DB::table('follows')
            ->select(DB::raw('MAX(id) as id'))
            ->groupBy('member_id');

        $records = DB::table('follows')
            ->join('shops', 'follows.shop_id', '=', 'shops.id')
            ->joinSub($latest_applies, 'latest_applies', function($join) {
                $join->on('follows.id', '=', 'latest_applies.id');
            })
            ->leftJoin('w_members', 'follows.member_id', '=', 'w_members.id')
            ->leftJoin('members', function($join) {
                $join->on('follows.member_id', '=', 'members.id')
                     ->where(function($query) {

                       $query->where(function($subQuery) {
                           $subQuery->where('members.approval', \FrontConsts::APPROVAL_ON)
                                    ->where('members.del_flg', \CommonConsts::DEL_OFF);
                       })
                       ->orWhere(function($subQuery) {
                           $subQuery->where('w_members.del_flg', \CommonConsts::DEL_OFF);
                       })
                       ->orWhere(function($subQuery) {
                           $subQuery->where('members.del_flg', \CommonConsts::DEL_OFF);
                       });



                     });
            })
            ->where('shops.id', $shop_id)
            ->orderBy('updated_at','desc')
            ->select(
                'follows.*',
                DB::raw('COALESCE(w_members.name, members.name) as name'),
                DB::raw('COALESCE(w_members.email, members.email) as email'),
                DB::raw('COALESCE(w_members.nickname, members.nickname) as nickname'),
                DB::raw('COALESCE(w_members.birthday_y, members.birthday_y) as birthday_y'),
                DB::raw('COALESCE(w_members.birthday_m, members.birthday_m) as birthday_m'),
                DB::raw('COALESCE(w_members.birthday_d, members.birthday_d) as birthday_d'),
                DB::raw('COALESCE(w_members.pref, members.pref) as pref'),
                DB::raw('COALESCE(w_members.addr1, members.addr1) as addr1'),
                'members.id as members_id',
                'follows.updated_at as follows_updated_at'
            )
            ->get();

        return $records;

    }

    /**
    *    店舗からメンバーにフォロー
    */
    public function findFollowByShopIdReadYet($member_id)
    {

        $records = DB::table('follows')
                   ->join('shops', 'follows.shop_id', '=', 'shops.id')
                   ->where('follows.member_id',$member_id)
                   ->where('readed',\CommonConsts::READ_YET)
                   ->where('type',\CommonConsts::FORROW_BY_SHOP)
                   //->select('members.*','members.id as member_id','follows.id as follow_id')
                   ->select('shops.*','shops.id as shop_id')

                   ->get();

        return $records;

    }

    public function followToReadedByShop($member_id,$shop_id)
    {
        $records = Follow::where('member_id',$member_id)->where('shop_id',$shop_id)->first();
        if ($records) {
            $records->readed = \CommonConsts::READ_DONE;
            $records->save();
        }
    }

}
