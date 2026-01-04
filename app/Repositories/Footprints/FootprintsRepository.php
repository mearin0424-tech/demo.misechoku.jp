<?php
namespace App\Repositories\Footprints;

use App\Models\Footprints;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Lib\StrUtil;
use App\Lib\FileUtil;
use Illuminate\Http\Request;
use App\Consts\ShopConsts;


class FootprintsRepository implements FootprintsRepositoryInterface
{
    /**
     * @var App\Models\Project
     */
    private $project;

    public function __construct(Footprints $project) {
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

    public function save(Request $request,$member_id,$shop_id){
/*
        Footprints::updateOrCreate(
            ['member_id' => $member_id,'shop_id' => $shop_id], 
            $request->all()
        );
*/
        Footprints::firstOrCreate(
            ['member_id' => $member_id, 'shop_id' => $shop_id]
        );

   }
   public function findFootprintsShop($member_id)
    {

        $records = DB::table('footprints')
                   ->join('shops', 'footprints.shop_id', '=', 'shops.id')
                   ->join('jobs', 'jobs.shop_id', '=', 'shops.id')
                   ->where('footprints.member_id',$member_id)
                   ->groupby('shops.id','jobs.helpjob')
                   ->select('shops.*','jobs.helpjob as helpjob')
                   ->get();

        return $records;

    }

   public function findFootprintsByMember($member_id)
    {

        $records = DB::table('footprints')
                   ->join('shops', 'footprints.shop_id', '=', 'shops.id')
                   ->join('jobs', 'jobs.shop_id', '=', 'footprints.shop_id')
                   ->where('footprints.member_id',$member_id)
                   ->select('shops.*','jobs.*','shops.id as shop_id')
                   ->get();

        return $records;

    }


    public function findFootprintsMember($shop_id)
    {

/*
        $records = DB::table('footprints')
                   ->join('members', 'footprints.member_id', '=', 'members.id')
                   ->where('footprints.shop_id',$shop_id)
                   ->select('members.*')
*/


/*
        $latest_messages = DB::table('footprints')
            ->select(DB::raw('MAX(id) as id'))
            ->groupBy('member_id');

        $records = DB::table('footprints')
            ->join('shops', 'footprints.shop_id', '=', 'shops.id')
            ->joinSub($latest_messages, 'latest_messages', function($join) {
                $join->on('footprints.id', '=', 'latest_messages.id');
            })
            ->leftJoin('w_members', 'footprints.member_id', '=', 'w_members.id')
            ->leftJoin('members', function($join) {
                $join->on('footprints.member_id', '=', 'members.id')
                     ->where(function($query) {
                         $query->where('members.approval', \FrontConsts::APPROVAL_ON)
                               ->orWhereNull('w_members.id');
                     });
            })
            ->where('shops.id', $shop_id)
            ->where(function ($query) {
                $query->where('members.del_flg', \CommonConsts::DEL_OFF)
                      ->orWhere('w_members.del_flg', \CommonConsts::DEL_OFF)
                      ->orWhere('members.del_flg', \CommonConsts::DEL_OFF);
            })
            ->select(
                'footprints.*',
                DB::raw('COALESCE(w_members.name, members.name) as name'),
                DB::raw('COALESCE(w_members.email, members.email) as email'),
                DB::raw('COALESCE(w_members.nickname, members.nickname) as nickname'),
                DB::raw('COALESCE(w_members.birthday_y, members.birthday_y) as birthday_y'),
                DB::raw('COALESCE(w_members.birthday_m, members.birthday_m) as birthday_m'),
                DB::raw('COALESCE(w_members.birthday_d, members.birthday_d) as birthday_d'),
                DB::raw('COALESCE(w_members.pref, members.pref) as pref'),
                DB::raw('COALESCE(w_members.addr1, members.addr1) as addr1'),
                DB::raw('COALESCE(w_members.b, members.b) as b'),
                DB::raw('COALESCE(w_members.w, members.w) as w'),
                DB::raw('COALESCE(w_members.h, members.h) as h'),
                DB::raw('COALESCE(w_members.exp, members.exp) as exp'),

                'footprints.id as messages_id',
                'members.id as members_id',

            )

*/
        $records = DB::table('footprints')
                   ->join('members', 'footprints.member_id', '=', 'members.id')
                   ->where('footprints.shop_id',$shop_id)
                   ->select('members.*','members.id as members_id')
                   ->paginate(\ShopConsts::PAGENATION_COUNT);

        return $records;

    }

    public function findFootprintsByMemberId($member_id)
    {

        $records = DB::table('footprints')
                   ->join('shops', 'footprints.shop_id', '=', 'shops.id')
                   ->where('footprints.member_id',$member_id)
                   ->select('shops.*')
                   ->get();

        return $records;

    }

    public function findFootprintsByShopId($shop_id)
    {

        $records = DB::table('footprints')
                   ->join('members', 'footprints.memeber_id', '=', 'members.id')
                   ->where('footprints.shop_id',$shop_id)
                   ->select('members.*')
                   ->get();

        return $records;

    }



}
