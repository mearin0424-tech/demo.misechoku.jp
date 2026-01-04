<?php
namespace App\Repositories\Keep;

use App\Models\Good2;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Lib\StrUtil;
use App\Lib\FileUtil;
use Illuminate\Http\Request;
use App\Consts\ShopConsts;
use Carbon\Carbon;

class KeepRepository implements KeepRepositoryInterface
{
    /**
     * @var App\Models\Project
     */
    private $project;

    public function __construct(Good2 $project) {
        $this->project = $project;
    }


    public function getAll($shop_id)
    {

        $records = DB::table('likes')->where('shop_id',$shop_id)->select('member_id')->get();

        $scouted_id = [];
        foreach($records as $record){
             $scouted_id[] = $record->member_id;
        }
        $records = DB::table('blocks')->where('shop_id',$shop_id)->select('member_id')->get();
        $block_id = [];
        foreach($records as $record){
             $block_id[] = $record->member_id;
        }

        return DB::table('members')
                 //->where('members.approval',\FrontConsts::APPROVAL_ON)
                 ->where('members.del_flg',\CommonConsts::DEL_OFF)
                 ->whereIn('id',$scouted_id)
                 ->whereNotIn('id',$block_id)
                 ->orderBy('members.updated_at','desc')
                 ->select('members.*')
                 ->paginate(\ShopConsts::PAGENATION_COUNT);

    }

    public function getGoods2($shop_id) {

        $query = DB::table('members')
            ->leftJoin('w_members', 'members.id', '=', 'w_members.id')
            ->leftJoin('members as priority_members', function($join) {
                $join->on('members.id', '=', 'priority_members.id')
                     ->where(function($query) {
                         $query->where('priority_members.approval', \FrontConsts::APPROVAL_ON)
                               ->where('priority_members.del_flg', \CommonConsts::DEL_OFF);
                     });
            });
        
        $records = DB::table('good2s')->where('shop_id', $shop_id)->select('member_id')->get();
        $scouted_id = $records->pluck('member_id')->toArray();
        
        $records = DB::table('blocks')->where('shop_id', $shop_id)->select('member_id')->get();
        $block_id = $records->pluck('member_id')->toArray();

       $query->whereIn('members.id', $scouted_id);
       $query->whereNotIn('members.id', $block_id);
       $query->orderBy('members.updated_at', 'desc');
       $query->select(
           'members.*',
           'members.id as id',
           DB::raw('COALESCE(priority_members.name, w_members.name, members.name) as name'),
           DB::raw('COALESCE(priority_members.email, w_members.email, members.email) as email'),
           DB::raw('COALESCE(priority_members.nickname, w_members.nickname, members.nickname) as nickname'),
           DB::raw('COALESCE(priority_members.birthday_y, w_members.birthday_y, members.birthday_y) as birthday_y'),
           DB::raw('COALESCE(priority_members.birthday_m, w_members.birthday_m, members.birthday_m) as birthday_m'),
           DB::raw('COALESCE(priority_members.birthday_d, w_members.birthday_d, members.birthday_d) as birthday_d'),
           DB::raw('COALESCE(priority_members.pref, w_members.pref, members.pref) as pref'),
           DB::raw('COALESCE(priority_members.addr1, w_members.addr1, members.addr1) as addr1')
       );
       
       return $query->paginate(\ShopConsts::PAGENATION_COUNT);       

    }

    public function search(Request $request, $shop_id) {

/*
        $query = DB::table('members');

        $records = DB::table('good2s')->where('shop_id',$shop_id)->select('member_id')->get();
        $scouted_id = [];
        foreach($records as $record){
             $scouted_id[] = $record->member_id;
        }

        $records = DB::table('blocks')->where('shop_id',$shop_id)->select('member_id')->get();
        $block_id = [];
        foreach($records as $record){
             $block_id[] = $record->member_id;
        }

        if ($request->industry) {

            $query->join('member_industries','members.id', '=', 'member_industries.member_id');

            $cond=[];
            foreach($request-> industry as $industry) {
                $cond[] = $industry;
            }
            $query->whereIn('member_industries.industry_id',$cond);
        }
        if ($request->pref) {
             $query->where('members.pref', $request->pref);
        }

        if ($request->addr1) {
             $query->where('members.addr1', $request->addr1);
        }

        if ($request->area) {
             $start_latutude = (float)$request->lat;
             $start_longitude = (float)$request->log;
             $earth_r = 6378.137;
             $cond=[];

             $shops = DB::table('shops')->find($shop_id);
             if($start_latutude=="") $start_latutude  = $shops->latutude;
             if($start_longitude=="") $start_longitude = $shops->longitude;

             // 札幌
             //$start_latutude=43.068661;
             //$start_longitude=141.350755;
             // 東京
             //$start_latutude=35.681298;
             //$start_longitude=139.766247;
             //鹿児島駅
             //$start_latutude=31.583785;
             //$start_longitude=130.541245;
             // 浦安

             $members = DB::table('members')
                        ->where('members.approval',\FrontConsts::APPROVAL_ON)
                        ->where('members.del_flg',\CommonConsts::DEL_OFF)
                        ->get();
             foreach($members as $data){

                if(\StrUtil::is_emtpy($data->latitude) || \StrUtil::is_emtpy($data->longitude)) continue;

                $end_latitude  = (float)$data->latitude;
                $end_longitude = (float)$data->longitude;

                $latitude_margin = deg2rad($end_latitude - $start_latutude);     
                $longitide_margin = deg2rad($end_longitude - $start_longitude );
                $south_north = $earth_r * $latitude_margin;
                $west_east = cos(deg2rad($start_latutude)) * $earth_r * $longitide_margin;
                $distance = sqrt(pow($west_east,2) + pow($south_north,2));
                $this->distance_arr[] = $distance;
                if($request->location == 1 && $distance <= 1000){
                     $cond[] = $data->id;
                }else if($request->location == 2  && $distance <= 5000){
                     $cond[] = $data->id;

                }else if($request->location == 3  && $distance <= 10000){
                     $cond[] = $data->id;

                }else if($request->location == 4  && $distance > 10000){
                     $cond[] = $data->id;
                }
                if(($request->area<=10 && $distance <=$request->area*1000) ){
                     $cond[] = $data->id;
                }else if($request->area==11 && $distance > $request->area*1000){
                     $cond[] = $data->id;
                }

             }
             $query->whereIn('members.id',$cond);
        }


        if ($request->gender) {
             $query->where('members.gender', $request->gender);
        }

        if (!\StrUtil::is_empty($request->age_min) && !\StrUtil::is_empty($request->age_max) ) {
             $query->whereBetween('members.age', [$request->age_min,$request->age_max]);
        }

        if (!\StrUtil::is_empty($request->age_min)) {
             $query->where('members.age', '>=', $request->age_min);
        }
        if (!\StrUtil::is_empty($request->age_max)) {
             $query->where('members.age', '<=', $request->age_max);
        }

        if (!\StrUtil::is_empty($request->height_min) && !\StrUtil::is_empty($request->height_max) ) {
             $query->whereBetween('members.height', [$request->height_min,$request->height_max]);
        }

        if (!\StrUtil::is_empty($request->height_min)) {
             $query->where('members.height', '>=', $request->height_min);
        }
        if (!\StrUtil::is_empty($request->height_max)) {
             $query->where('members.height', '<=', $request->height_max);
        }

        if (!\StrUtil::is_empty($request->weight_min) && !\StrUtil::is_empty($request->weight_max) ) {
             $query->whereBetween('members.weight', [$request->weight_min,$request->weight_max]);
        }

        if (!\StrUtil::is_empty($request->weight_min)) {
             $query->where('members.weight', '>=', $request->weight_min);
        }
        if (!\StrUtil::is_empty($request->weight_max)) {
             $query->where('members.weight', '<=', $request->weight_max);
        }

        if (!\StrUtil::is_empty($request->b_min) && !\StrUtil::is_empty($request->b_max) ) {
             $query->whereBetween('members.b', [$request->b_min,$request->b_max]);
        }

        if (!\StrUtil::is_empty($request->b_min)) {
             $query->where('members.b', '>=', $request->b_min);
        }
        if (!\StrUtil::is_empty($request->b_max)) {
             $query->where('members.b', '<=', $request->b_max);
        }

        if (!\StrUtil::is_empty($request->w_min) && !\StrUtil::is_empty($request->w_max) ) {
             $query->whereBetween('members.w', [$request->w_min,$request->w_max]);
        }

        if (!\StrUtil::is_empty($request->w_min)) {
             $query->where('members.w', '>=', $request->w_min);
        }
        if (!\StrUtil::is_empty($request->w_max)) {
             $query->where('members.w', '<=', $request->w_max);
        }

        if (!\StrUtil::is_empty($request->h_min) && !\StrUtil::is_empty($request->h_max) ) {
             $query->whereBetween('members.h', [$request->h_min,$request->h_max]);
        }

        if (!\StrUtil::is_empty($request->h_min)) {
             $query->where('members.h', '>=', $request->h_min);
        }
        if (!\StrUtil::is_empty($request->h_max)) {
             $query->where('members.h', '<=', $request->h_max);
        }

        if ($request->shift) {
             $query->where('members.shifut', $request->shift);
        }

        if ($request->help) {
             $query->where('members.help', $request->help);
        }

        if ($request->gender) {
             $query->where('members.gender', $request->gender);
        }

        //$query->where('members.approval',\FrontConsts::APPROVAL_ON);
        $query->where('members.del_flg',\CommonConsts::DEL_OFF);
        $query->whereIn('members.id',$scouted_id);
        $query->whereNotIn('members.id',$block_id);
        $query->orderBy('members.updated_at','desc');
        $query->select('members.*','members.id as id');

        return $query->paginate(\ShopConsts::PAGENATION_COUNT);

*/

$query = DB::table('members')
    ->leftJoin('w_members', 'members.id', '=', 'w_members.id');

$records = DB::table('good2s')->where('shop_id', $shop_id)->select('member_id')->get();
$scouted_id = $records->pluck('member_id')->toArray();

$records = DB::table('blocks')->where('shop_id', $shop_id)->select('member_id')->get();
$block_id = $records->pluck('member_id')->toArray();

if ($request->industry) {
    $query->join('member_industries', 'members.id', '=', 'member_industries.member_id');
    $query->whereIn('member_industries.industry_id', $request->industry);
}

if ($request->pref) {
    $query->where('members.pref', $request->pref);
}

if ($request->addr1) {
    $query->where('members.addr1', $request->addr1);
}

if ($request->area) {
    $start_latutude = (float)$request->lat;
    $start_longitude = (float)$request->log;
    $earth_r = 6378.137;
    $cond = [];

    $shops = DB::table('shops')->find($shop_id);
    if (!$start_latutude) $start_latutude  = $shops->latitude;
    if (!$start_longitude) $start_longitude = $shops->longitude;

    $members = DB::table('members')
                ->where('members.approval', \FrontConsts::APPROVAL_ON)
                ->where('members.del_flg', \CommonConsts::DEL_OFF)
                ->get();
    
    foreach ($members as $data) {
        if (\StrUtil::is_empty($data->latitude) || \StrUtil::is_empty($data->longitude)) continue;

        $end_latitude  = (float)$data->latitude;
        $end_longitude = (float)$data->longitude;

        $latitude_margin = deg2rad($end_latitude - $start_latutude);     
        $longitide_margin = deg2rad($end_longitude - $start_longitude);
        $south_north = $earth_r * $latitude_margin;
        $west_east = cos(deg2rad($start_latutude)) * $earth_r * $longitide_margin;
        $distance = sqrt(pow($west_east, 2) + pow($south_north, 2));
        
        if ($request->location == 1 && $distance <= 1000) {
            $cond[] = $data->id;
        } else if ($request->location == 2 && $distance <= 5000) {
            $cond[] = $data->id;
        } else if ($request->location == 3 && $distance <= 10000) {
            $cond[] = $data->id;
        } else if ($request->location == 4 && $distance > 10000) {
            $cond[] = $data->id;
        }

        if ($request->area <= 10 && $distance <= $request->area * 1000) {
            $cond[] = $data->id;
        } else if ($request->area == 11 && $distance > $request->area * 1000) {
            $cond[] = $data->id;
        }
    }
    $query->whereIn('members.id', $cond);
}

if ($request->gender) {
    $query->where('members.gender', $request->gender);
}

if (!\StrUtil::is_empty($request->age_min) && !\StrUtil::is_empty($request->age_max)) {
    $query->whereRaw('TIMESTAMPDIFF(YEAR, DATE(CONCAT(members.birthday_y, "-", LPAD(members.birthday_m, 2, "0"), "-", LPAD(members.birthday_d, 2, "0"))), CURDATE()) BETWEEN ? AND ?', [$request->age_min, $request->age_max]);
}

if (!\StrUtil::is_empty($request->age_min)) {
    $query->whereRaw('TIMESTAMPDIFF(YEAR, DATE(CONCAT(members.birthday_y, "-", LPAD(members.birthday_m, 2, "0"), "-", LPAD(members.birthday_d, 2, "0"))), CURDATE()) >= ?', [$request->age_min]);
}

if (!\StrUtil::is_empty($request->age_max)) {
    $query->whereRaw('TIMESTAMPDIFF(YEAR, DATE(CONCAT(members.birthday_y, "-", LPAD(members.birthday_m, 2, "0"), "-", LPAD(members.birthday_d, 2, "0"))), CURDATE()) <= ?', [$request->age_max]);
}


if (!\StrUtil::is_empty($request->height_min) && !\StrUtil::is_empty($request->height_max)) {
    $query->whereBetween('members.height', [$request->height_min, $request->height_max]);
}

if (!\StrUtil::is_empty($request->height_min)) {
    $query->where('members.height', '>=', $request->height_min);
}
if (!\StrUtil::is_empty($request->height_max)) {
    $query->where('members.height', '<=', $request->height_max);
}

if (!\StrUtil::is_empty($request->weight_min) && !\StrUtil::is_empty($request->weight_max)) {
    $query->whereBetween('members.weight', [$request->weight_min, $request->weight_max]);
}

if (!\StrUtil::is_empty($request->weight_min)) {
    $query->where('members.weight', '>=', $request->weight_min);
}
if (!\StrUtil::is_empty($request->weight_max)) {
    $query->where('members.weight', '<=', $request->weight_max);
}

if (!\StrUtil::is_empty($request->b_min) && !\StrUtil::is_empty($request->b_max)) {
    $query->whereBetween('members.b', [$request->b_min, $request->b_max]);
}

if (!\StrUtil::is_empty($request->b_min)) {
    $query->where('members.b', '>=', $request->b_min);
}
if (!\StrUtil::is_empty($request->b_max)) {
    $query->where('members.b', '<=', $request->b_max);
}

if (!\StrUtil::is_empty($request->w_min) && !\StrUtil::is_empty($request->w_max)) {
    $query->whereBetween('members.w', [$request->w_min, $request->w_max]);
}

if (!\StrUtil::is_empty($request->w_min)) {
    $query->where('members.w', '>=', $request->w_min);
}
if (!\StrUtil::is_empty($request->w_max)) {
    $query->where('members.w', '<=', $request->w_max);
}

if (!\StrUtil::is_empty($request->h_min) && !\StrUtil::is_empty($request->h_max)) {
    $query->whereBetween('members.h', [$request->h_min, $request->h_max]);
}

if (!\StrUtil::is_empty($request->h_min)) {
    $query->where('members.h', '>=', $request->h_min);
}
if (!\StrUtil::is_empty($request->h_max)) {
    $query->where('members.h', '<=', $request->h_max);
}

if ($request->shift) {
    $query->where('members.shift', $request->shift);
}

if ($request->help) {
    $query->where('members.help', $request->help);
}

// 承認済みの members テーブルを優先し、次に w_members テーブル、最後に members テーブルを考慮
$query->where(function ($query) {
    $query->where(function ($subQuery) {
        $subQuery->where('members.approval', \FrontConsts::APPROVAL_ON)
                 ->where('members.del_flg', \CommonConsts::DEL_OFF);
    })
    ->orWhere(function ($subQuery) {
        $subQuery->where('w_members.del_flg', \CommonConsts::DEL_OFF);
    })
    ->orWhere(function ($subQuery) {
        $subQuery->where('members.del_flg', \CommonConsts::DEL_OFF);
    });
});

$query->whereIn('members.id', $scouted_id);
$query->whereNotIn('members.id', $block_id);
$query->orderBy('members.updated_at', 'desc');
$query->select(
    'members.*',
    'members.id as id',
    DB::raw('COALESCE(members.name, w_members.name) as name'),
    DB::raw('COALESCE(members.email, w_members.email) as email'),
    DB::raw('COALESCE(members.nickname, w_members.nickname) as nickname'),
    DB::raw('COALESCE(members.birthday_y, w_members.birthday_y) as birthday_y'),
    DB::raw('COALESCE(members.birthday_m, w_members.birthday_m) as birthday_m'),
    DB::raw('COALESCE(members.birthday_d, w_members.birthday_d) as birthday_d'),
    DB::raw('COALESCE(members.pref, w_members.pref) as pref'),
    DB::raw('COALESCE(members.addr1, w_members.addr1) as addr1')
);

return $query->paginate(\ShopConsts::PAGENATION_COUNT);



    }

     /**
     * スカウト登録をする
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


   public function findScoutShop($member_id)
    {

        $records = DB::table('scouts')
                   ->join('shops', 'scouts.shop_id', '=', 'shops.id')
                   ->where('scouts.member_id',$member_id)
                   ->groupby('shops.id')
                   ->select('shops.*')
                   ->get();

        return $records;

    }

    public function findScoutMember($shop_id)
    {

        $records = DB::table('scouts')
                   ->join('mmembers', 'scouts.member_id', '=', 'members.id')
                   ->where('scouts.shop_id',$shop_id)
                   ->select('members.*')
                   ->get();

        return $records;

    }

    public function findScoutByMemberId($member_id)
    {

        $records = DB::table('scouts')
                   ->join('shops', 'scouts.shop_id', '=', 'shops.id')
                   ->where('scouts.member_id',$member_id)
                   ->select('shops.*','shops.id as shop_id')
                   ->get();

        return $records;

    }

    public function findScoutByShopId($shop_id)
    {

        $records = DB::table('scouts')
                   ->join('mmembers', 'scouts.memeber_id', '=', 'members.id')
                   ->where('scouts.shop_id',$shop_id)
                   ->select('members.*')
                   ->get();

        return $records;

    }

}
