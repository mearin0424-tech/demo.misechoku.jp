<?php

namespace App\Repositories\Scout;

use App\Models\Scout;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Lib\StrUtil;
use App\Lib\FileUtil;
use Illuminate\Http\Request;
use App\Consts\ShopConsts;


class ScoutRepository implements ScoutRepositoryInterface
{
     /**
      * @var App\Models\Project
      */
     private $project;

     public function __construct(Scout $project)
     {
          $this->project = $project;
     }


    public function getAll($shop_id, $release = "")
    {
        $scouted_id = DB::table('follows')
            ->where('shop_id', $shop_id)
            ->pluck('member_id');

        $block_ids = DB::table('blocks')
            ->where('shop_id', $shop_id)
            ->pluck('member_id');

        $query = DB::table('members')
            ->where(function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('members.del_flg', \CommonConsts::DEL_OFF);
                });
            })
            ->whereNotIn('members.id', $block_ids)
            ->leftJoin('today_centens_by_members', 'members.id', '=', 'today_centens_by_members.member_id')
            ->leftJoin('member_tags', 'members.id', '=', 'member_tags.member_id')
            ->leftJoin('cast_tags', 'member_tags.tag_id', '=', 'cast_tags.id')
            ->leftJoin('tags', 'member_tags.tag_id', '=', 'tags.id')
            ->leftJoin('member_industries', 'members.id', '=', 'member_industries.member_id')
            ->leftJoin('industries', 'industries.id', '=', 'member_industries.industry_id')
            ->select(
                'members.id as member_id',
                'today_centens_by_members.word as word',
                DB::raw('GROUP_CONCAT(cast_tags.name SEPARATOR ", ") as cast_tag_names'),
                DB::raw('GROUP_CONCAT(tags.content SEPARATOR ", ") as tags_names'),
                DB::raw('GROUP_CONCAT(industries.name SEPARATOR ", ") as industries_names'),
                'members.*'
            )
            ->groupBy('members.id', 'today_centens_by_members.word')
            ->orderBy('members.updated_at', 'desc');

        if (!empty($release)) {
            $query->where('members.release', $release);
        }

        return $query->paginate(\ShopConsts::PAGENATION_COUNT);
    }


    public function search(Request $request, $shop_id, $release = "")
     {

          //$query = DB::table('members');

         $query = DB::table('members')
           //->leftJoin('w_members', 'members.id', '=', 'w_members.id')
           ->where(function($query) {

             $query->where(function($subQuery) {
                 //$subQuery->where('members.approval', \FrontConsts::APPROVAL_ON)
                 $subQuery->where('members.del_flg', \CommonConsts::DEL_OFF);
             });
             //->orWhere(function($subQuery) {
             //    $subQuery->where('w_members.del_flg', \CommonConsts::DEL_OFF);
             //})
             //->orWhere(function($subQuery) {
             //    $subQuery->where('members.del_flg', \CommonConsts::DEL_OFF);
             //});



          });

         if (!empty($release)) {
             $query->where('members.release', $release);
         }

          $query->leftJoin('today_centens_by_members', 'members.id', '=', 'today_centens_by_members.member_id');
          $query->leftJoin('member_tags', 'members.id', '=', 'member_tags.member_id');
          $query->leftJoin('cast_tags', 'member_tags.tag_id', '=', 'cast_tags.id');
          $query->leftJoin('tags', 'member_tags.tag_id', '=', 'tags.id');
          $query->leftJoin('member_industries', 'members.id', '=', 'member_industries.member_id');
          $query->leftJoin('industries', 'industries.id', '=', 'member_industries.industry_id');

          $scouted_id = [];
          /*
          $records = DB::table('follows')->where('shop_id', $shop_id)->select('member_id')->get();

          foreach ($records as $record) {
               $scouted_id[] = $record->member_id;
          }
*/
          $records = DB::table('blocks')->where('shop_id', $shop_id)->select('member_id')->get();
          $block_id = [];
          foreach ($records as $record) {
               $block_id[] = $record->member_id;
          }

          if ($request->industry) {

//               $query->join('member_industries', function ($query) {
//                    $query->on('members.id', '=', 'member_industries.member_id');
//               });

//               $cond = [];
//               foreach ($request->industry as $industry) {
//                    $cond[] = $industry;
//               }

               $query->whereIn('member_industries.industry_id', $request->industry);
          }

          if ($request->cast_tag) {
               // $query->join('member_tags', 'member_tags.member_id', 'members.id');
//               $query->join('tags', 'member_tags.tag_id', 'tags.id');
               $query->whereIn('tags.id', $request->cast_tag);
          }

         $prefSelected = $request->hpref ? explode(',', $request->hpref) : [];
         $citySelected = $request->hcity ? explode(',', $request->hcity) : [];

         $query->where(function($q) use ($prefSelected, $citySelected) {

             // 1 All prefs are selected → all members with pref = hpref, including empty cities
             // 1 Pref toàn bộ được chọn → tất cả member có pref = hpref, bao gồm city trống
             if (!empty($prefSelected)) {
                 $q->where(function($q1) use ($prefSelected) {
                     $q1->whereIn('members.pref', $prefSelected)
                         ->where(function($q2) {
                             $q2->orWhereNotNull('members.city')
                                 ->orWhere('members.city', '')
                                 ->orWhereNull('members.city');
                         });
                 });
             }

             // 2 Selected private city (independent of pref)
             // 2 City riêng được chọn (không phụ thuộc pref)
             if (!empty($citySelected)) {
                 $q->orWhere(function($q3) use ($citySelected) {
                     $q3->whereIn('members.city', $citySelected);
                 });
             }

         });



         if ($request->area) {
               $start_latutude = (float)$request->lat;
               $start_longitude = (float)$request->log;
               $earth_r = 6378.137;
               $cond = [];

               $shops = DB::table('shops')->find($shop_id);
               if ($start_latutude == "") $start_latutude  = $shops->latutude;
               if ($start_longitude == "") $start_longitude = $shops->longitude;

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
                    ->where('members.approval', \FrontConsts::APPROVAL_ON)
                    ->where('members.del_flg', \CommonConsts::DEL_OFF)
                    ->get();
               foreach ($members as $data) {

                    if (\StrUtil::is_emtpy($data->latitude) || \StrUtil::is_emtpy($data->longitude)) continue;

                    $end_latitude  = (float)$data->latitude;
                    $end_longitude = (float)$data->longitude;

                    $latitude_margin = deg2rad($end_latitude - $start_latutude);
                    $longitide_margin = deg2rad($end_longitude - $start_longitude);
                    $south_north = $earth_r * $latitude_margin;
                    $west_east = cos(deg2rad($start_latutude)) * $earth_r * $longitide_margin;
                    $distance = sqrt(pow($west_east, 2) + pow($south_north, 2));
                    $this->distance_arr[] = $distance;
                    if ($request->location == 1 && $distance <= 1000) {
                         $cond[] = $data->id;
                    } else if ($request->location == 2  && $distance <= 5000) {
                         $cond[] = $data->id;
                    } else if ($request->location == 3  && $distance <= 10000) {
                         $cond[] = $data->id;
                    } else if ($request->location == 4  && $distance > 10000) {
                         $cond[] = $data->id;
                    }
                    if (($request->area <= 10 && $distance <= $request->area * 1000)) {
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
               $query->whereBetween('members.age', [$request->age_min, $request->age_max]);
          }

          if (!\StrUtil::is_empty($request->age_min)) {
               $query->where('members.age', '>=', $request->age_min);
          }
          if (!\StrUtil::is_empty($request->age_max)) {
               $query->where('members.age', '<=', $request->age_max);
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

          if ($request->gender) {
               $query->where('members.gender', $request->gender);
          }

         if ($request->has('worktime')) {
             $worktime = $request->worktime;

             if (is_array($worktime)) {
                 $query->whereIn('members.worktime', $worktime);
             } else {
                 $query->where('members.worktime', $worktime);
             }
         }


         //$query->where('members.approval', \FrontConsts::APPROVAL_ON);
          //$query->where('members.del_flg', \CommonConsts::DEL_OFF);
          //$query->whereNotIn('members.id', $scouted_id);
          $query->whereNotIn('members.id', $block_id);

          $query->orderBy('members.updated_at', 'desc');
//          $query->select('members.*', 'members.id as id', 'today_centens_by_members.word as word');

          $query->select(
               'members.id as member_id',
               'today_centens_by_members.word as word',
               DB::raw('GROUP_CONCAT(cast_tags.name SEPARATOR ", ") as cast_tag_names'),
               DB::raw('GROUP_CONCAT(tags.content SEPARATOR ", ") as tags_names'),
               DB::raw('GROUP_CONCAT(industries.name SEPARATOR ", ") as industries_names'),
//               DB::raw('COALESCE(members.id, w_members.id) as id'),
               'members.*'
/*
               DB::raw('COALESCE(w_members.name, members.name) as name'),
               DB::raw('COALESCE(w_members.email, members.email) as email'),
               DB::raw('COALESCE(w_members.nickname, members.nickname) as nickname'),
               DB::raw('COALESCE(w_members.birthday_y, members.birthday_y) as birthday_y'),
               DB::raw('COALESCE(w_members.birthday_m, members.birthday_m) as birthday_m'),
               DB::raw('COALESCE(w_members.birthday_d, members.birthday_d) as birthday_d'),
               DB::raw('COALESCE(w_members.pref, members.pref) as pref'),
               DB::raw('COALESCE(w_members.addr1, members.addr1) as addr1')
*/
           );
           $query->groupBy('members.id', 'today_centens_by_members.word');

/*
          $sql = $query->toSql();
          // バインディングパラメータを取得
          $bindings = $query->getBindings();

          // SQL文とバインディングパラメータを表示
          dd($sql, $bindings);
*/
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
               ->where('scouts.member_id', $member_id)
               ->groupby('shops.id')
               ->select('shops.*')
               ->get();

          return $records;
     }

     public function findScoutMember($shop_id)
     {

          $records = DB::table('scouts')
               ->join('mmembers', 'scouts.member_id', '=', 'members.id')
               ->where('scouts.shop_id', $shop_id)
               ->select('members.*')
               ->get();

          return $records;
     }

     public function findScoutByMemberId($member_id)
     {

          $records = DB::table('scouts')
               ->join('shops', 'scouts.shop_id', '=', 'shops.id')
               ->where('scouts.member_id', $member_id)
               ->select('shops.*', 'shops.id as shop_id')
               ->get();

          return $records;
     }

     public function findScoutByShopId($shop_id)
     {

          $records = DB::table('scouts')
               ->join('mmembers', 'scouts.memeber_id', '=', 'members.id')
               ->where('scouts.shop_id', $shop_id)
               ->select('members.*')
               ->get();

          return $records;
     }
}
