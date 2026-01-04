<?php
namespace App\Repositories\Review;

use App\Models\Review;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Lib\StrUtil;
use App\Lib\FileUtil;
use Illuminate\Http\Request;
use App\Consts\ShopConsts;
use App\Consts\CommonConsts;


class ReviewRepository implements ReviewRepositoryInterface
{
    /**
     * @var App\Models\Project
     */
    private $project;

    public function __construct(Review $project) {
        $this->project = $project;
    }

/*
    public function getAll()
    {
        return  DB::table('news')->where('del_flg',\CommonConsts::DEL_OFF)->orderBy('updated_at','desc')->paginate(\ShopConsts::PAGENATION_COUNT);
    }


    public function getAllForShop()
    {
        return  DB::table('news')->where('type','<>',\NewsConsts::Type_2)->where('del_flg',\CommonConsts::DEL_OFF)->orderBy('updated_at','desc')->paginate(\ShopConsts::PAGENATION_COUNT);
    }


    public function getAllForMember()
    {
        return  DB::table('news')->where('type','<>',\NewsConsts::Type_3)->where('del_flg',\CommonConsts::DEL_OFF)->orderBy('updated_at','desc')->paginate(\ShopConsts::PAGENATION_COUNT);
    }


    public function findById($id)
    {
        $records =  News::find($id);
        return $records;

    }
*/

    public function findReviewsByShopId($shop_id)
    {

        $records = DB::table('members')
                  ->join(
                    \DB::raw('(SELECT MAX(member_id) AS max_id FROM reviews where shop_id = '.$shop_id.' GROUP BY member_id) AS latest'),
                   'members.id', '=', 'latest.max_id',
                  )
                  ->select('members.*','members.id as member_id')
                  ->paginate(\ShopConsts::PAGENATION_COUNT);


        return $records;
    }

    public function getStoreReviews($shop_id)
    {

        $records = DB::table('members')
                  ->leftJoin('reviews','members.id','reviews.member_id')
                  ->join('review_details','reviews.id','review_details.review_id')
                  ->join('review_contents','review_contents.id','review_details.val')
                  ->select('reviews.*','reviews.id as reviews_id','review_details.score as score','members.*','members.id as member_id',
                           'review_contents.content as content' )
                  ->where('reviews.shop_id',$shop_id)
                  ->get();

        return $records;
    }

    public function getReviewsByMember($member_id,$shop_id,$apply_id = null)
    {

        //$records = DB::table('members')memb
        //        ->leftJoin('reviews','members.id','reviews.member_id')
        $records = DB::table('reviews')
            ->leftJoin('review_details', 'reviews.id', '=', 'review_details.review_id')
            ->leftJoin('review_contents', 'review_contents.id', '=', 'review_details.val')
            ->select(
                'reviews.*',
                'review_details.id as review_detail_id',
                'review_details.score as score',
                'review_contents.content as content'
            )
            ->when(!is_null($apply_id), function ($query) use ($apply_id) {
                $query->where('reviews.apply_id', $apply_id);
            })
            ->where('reviews.shop_id', $shop_id)
            ->where('reviews.member_id', $member_id)
            ->orderBy('review_contents.id')
            ->get();

        return $records;
    }


    public function getReviewContents() {

        return $records = DB::table('review_contents')->where("del_flg",0)->get();

   }


    public function postability($review_id){

       $record = Review::find($review_id);
       $release=\ReviewConsts::APPROVAL_OK_1;
       if($record->release==\ReviewConsts::APPROVAL_OK_1){
            $release=\ReviewConsts::APPROVAL_NG;
       }

        Review::updateOrCreate(
            ['id' => $review_id],
            ['release'=>$release]
        );


    }

    public function searchReviewMember(Request $request,$shop_id="")
    {
        $query = DB::table('reviews')
            ->join('members','members.id','=','reviews.member_id')
            ->join('shops','shops.id','=','reviews.shop_id')
            ->leftJoin('review_details', 'reviews.id', '=', 'review_details.review_id')
            ->leftJoin('review_contents', 'review_contents.id', '=', 'review_details.val')
            ->select(
                'members.id as member_id',
                'members.*',
                'shops.id as shop_id',
                'reviews.id as review_id',
                'reviews.*',
                'reviews.created_at as review_created_at',
            )
            ->distinct()
            ->where('reviews.shop_id', $shop_id)
            ->orderBy('reviews.id', 'desc');


//        $query = DB::table('members')
//           ->leftJoin('w_members', 'members.id', '=', 'w_members.id')
//           ->where(function($query) {
//
//             $query->where(function($subQuery) {
//                 $subQuery->where('members.approval', \FrontConsts::APPROVAL_ON)
//                          ->where('members.del_flg', \CommonConsts::DEL_OFF);
//             })
//             ->orWhere(function($subQuery) {
//                 $subQuery->where('w_members.del_flg', \CommonConsts::DEL_OFF);
//             })
//             ->orWhere(function($subQuery) {
//                 $subQuery->where('members.del_flg', \CommonConsts::DEL_OFF);
//             });
//          });
//
//
//                  $query ->join(
//                    \DB::raw('(SELECT MAX(member_id) AS max_id FROM reviews where shop_id = '.$shop_id.' GROUP BY member_id) AS latest'),
//                   'members.id', '=', 'latest.max_id',
//                  );
//
//        if ($request->id) {
//             $query->where('members.id',$request->id);
//        }
//
//        if ($request->nickname) {
//
//             $query->where('members.nickname','like','%'.$request->nickname.'%');
//        }
//
//        if ($request->pref) {
//             $query->where('members.pref', $request->pref);
//        }
//
//        if ($request->recruitment) {
//            $query->join('applies', function ($query)  {
//            $query->on('members.id', '=', 'applies.member_id');
//
//            });
//            $query->where('applies.type',$request->recruitment);
//        }
//
//        if ( !\StrUtil::is_empty($request->deposits) ) {
//            $query->join('deposits', function ($query)  {
//            $query->on('members.id', '=', 'deposits.member_id');
//	    });
//            $query->where('deposits.status',$request->deposits);
//        }
//
//        if ( !\StrUtil::is_empty($request->adoption) ) {
//            $query->join('adoptions', function ($query)  {
//            $query->on('members.id', '=', 'adoptions.member_id');
//	    });
//            $query->where('adoptions.result',$request->adoption);
//        }
//
//
//        $records = $query->select(
//              'members.*',
//               DB::raw('COALESCE(members.id, w_members.id) as member_id'),
//               DB::raw('COALESCE(w_members.name, members.name) as name'),
//               DB::raw('COALESCE(w_members.email, members.email) as email'),
//               DB::raw('COALESCE(w_members.nickname, members.nickname) as nickname'),
//               DB::raw('COALESCE(w_members.birthday_y, members.birthday_y) as birthday_y'),
//               DB::raw('COALESCE(w_members.birthday_m, members.birthday_m) as birthday_m'),
//               DB::raw('COALESCE(w_members.birthday_d, members.birthday_d) as birthday_d'),
//               DB::raw('COALESCE(w_members.pref, members.pref) as pref'),
//               DB::raw('COALESCE(w_members.addr1, members.addr1) as addr1')
//         );

        $records = $query->paginate(\ShopConsts::PAGENATION_COUNT);

        return $records;
    }



}
