<?php
namespace App\Repositories\Common;

use App\Models\Message;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Lib\StrUtil;
use App\Lib\FileUtil;
use App\Http\Requests\Shop\StoreRequest;
use Illuminate\Http\Request;
use App\Consts\ShopConsts;
use App\Models\ShopIndustry;
use App\Models\NearestStation;

class MessageRepository implements MessageRepositoryInterface
{
    /**
     * @var App\Models\Project
     */
    private $project;

    public function __construct(Message $project) {
        $this->project = $project;
    }


     /**
     * メッセージを登録する
     *
     * @param array $data
     * @return Project
     */
    public function store($data)
    {

        $project = $this->project->create($data);


    }


    public function message($member_id, $shop_id)
    {

        $records = Message::where("member_id",$member_id)->where('shop_id',$shop_id)->get();
        return $records;

    }

    public function findMessagedShop($member_id)
    {

        $records = DB::table('messages')
                   ->join('shops', 'messages.shop_id', '=', 'shops.id')
                   ->where('messages.member_id',$member_id)
                   ->groupby('shops.id')
                   ->select('shops.*')
                   ->get();

        return $records;

    }

    public function findMessagedMy($member_id,$shop_id)
    {

        $records = DB::table('messages')
                   ->join('shops', 'messages.shop_id', '=', 'shops.id')
                   ->where('messages.member_id',$member_id)
                   ->where('messages.shop_id',$shop_id)
                   ->select('shops.*','messages.*','shops.id as shop_id')
                   ->get();

        return $records;

    }


    public function findMessagedMemberSummary($shop_id)
    {
/*
        $records = DB::table('members')
                  ->join(
                  \DB::raw('(SELECT MAX(id) AS max_id FROM messages where shop_id = '.$shop_id.' GROUP BY member_id) AS latest'),
                   'members.id', '=', 'latest.max_id',
                  )
                  ->limit(3)
                  ->select("members.*",'members.id as member_id')
*/
        $latest_messages = DB::table('messages')
            ->select(DB::raw('MAX(id) as id'))
            ->groupBy('member_id');

        $records = DB::table('messages')
            ->join('shops', 'messages.shop_id', '=', 'shops.id')
            ->joinSub($latest_messages, 'latest_messages', function($join) {
                $join->on('messages.id', '=', 'latest_messages.id');
            })
            ->join('w_members', 'messages.member_id', '=', 'w_members.id')
            ->join('members', function($join) {
                $join->on('messages.member_id', '=', 'members.id')
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
            ->limit(30)
            ->select(
                'messages.*',
                DB::raw('COALESCE(w_members.name, members.name) as name'),
                DB::raw('COALESCE(w_members.email, members.email) as email'),
                DB::raw('COALESCE(w_members.nickname, members.nickname) as nickname'),
                DB::raw('COALESCE(w_members.birthday_y, members.birthday_y) as birthday_y'),
                DB::raw('COALESCE(w_members.birthday_m, members.birthday_m) as birthday_m'),
                DB::raw('COALESCE(w_members.birthday_d, members.birthday_d) as birthday_d'),
                DB::raw('COALESCE(w_members.pref, members.pref) as pref'),
                DB::raw('COALESCE(w_members.addr1, members.addr1) as addr1'),
                'messages.id as messages_id',
                'members.id as members_id',
                'messages.updated_at as messages_updated_at'
            )
            ->paginate(\ShopConsts::PAGENATION_COUNT);

        return $records;

    }

    public function findMessagedMemberSummaryList($shop_id)
    {

        $latest_messages = DB::table('messages')
            ->select(DB::raw('MAX(id) as id'))
            ->where('shop_id', $shop_id)
            ->groupBy('member_id');

        $records = DB::table('messages')
            ->join('shops', 'messages.shop_id', '=', 'shops.id')
            ->joinSub($latest_messages, 'latest_messages', function($join) {
                $join->on('messages.id', '=', 'latest_messages.id');
            })
            ->leftJoin('w_members', 'messages.member_id', '=', 'w_members.id')
            ->join('members', function($join) {
                $join->on('messages.member_id', '=', 'members.id');
//                     ->where(function($query) {
//
//                       $query->where(function($subQuery) {
//                           $subQuery->where('members.approval', \FrontConsts::APPROVAL_ON)
//                                    ->where('members.del_flg', \CommonConsts::DEL_OFF);
//                       })
//                       ->orWhere(function($subQuery) {
//                           $subQuery->where('w_members.del_flg', \CommonConsts::DEL_OFF);
//                       })
//                       ->orWhere(function($subQuery) {
//                           $subQuery->where('members.del_flg', \CommonConsts::DEL_OFF);
//                       });
//
//
//
//                     });
            })
            ->where('shops.id', $shop_id)
/*
            ->where(function ($query) {
                $query->where('members.del_flg', \CommonConsts::DEL_OFF)
                      ->orWhere('w_members.del_flg', \CommonConsts::DEL_OFF)
                      ->orWhere('members.del_flg', \CommonConsts::DEL_OFF);
            })
*/
            ->select(
                'messages.*',
                DB::raw('COALESCE(w_members.name, members.name) as name'),
                DB::raw('COALESCE(w_members.email, members.email) as email'),
                DB::raw('COALESCE(w_members.nickname, members.nickname) as nickname'),
                DB::raw('COALESCE(w_members.birthday_y, members.birthday_y) as birthday_y'),
                DB::raw('COALESCE(w_members.birthday_m, members.birthday_m) as birthday_m'),
                DB::raw('COALESCE(w_members.birthday_d, members.birthday_d) as birthday_d'),
                DB::raw('COALESCE(w_members.pref, members.pref) as pref'),
                DB::raw('COALESCE(w_members.addr1, members.addr1) as addr1'),
                'messages.id as messages_id',
                'members.id as members_id'
            )
            ->paginate(\ShopConsts::PAGENATION_COUNT);

        return $records;

    }




    public function searchMessagedMemberSummaryList(Request $request,$shop_id)
    {

      $latest_messages = DB::table('messages')
          ->select(DB::raw('MAX(id) as id'))
          ->groupBy('member_id');

      $query = DB::table('messages')
          ->join('shops', 'messages.shop_id', '=', 'shops.id')
          ->joinSub($latest_messages, 'latest_messages', function($join) {
              $join->on('messages.id', '=', 'latest_messages.id');
          })
          ->leftJoin('w_members', 'messages.member_id', '=', 'w_members.id')
          ->leftJoin('members as approved_members', function($join) {
              $join->on('messages.member_id', '=', 'approved_members.id')
                   ->where('approved_members.approval', \FrontConsts::APPROVAL_ON);
          })
          ->leftJoin('members', 'messages.member_id', '=', 'members.id')
          ->where('shops.id', $shop_id);

      if ($request->member_id) {
          $query->where(function ($query) use ($request) {
              $query->where('approved_members.id', $request->member_id)
                    ->orWhere('w_members.id', $request->member_id)
                    ->orWhere('members.id', $request->member_id);
          });
      }

      if ($request->nickname) {
          $query->where(function ($query) use ($request) {
              $query->where('approved_members.nickname', 'like', '%' . $request->nickname . '%')
                    ->orWhere('w_members.nickname', 'like', '%' . $request->nickname . '%')
                    ->orWhere('members.nickname', 'like', '%' . $request->nickname . '%');
          });
      }

      if ($request->pref) {
          $query->where(function ($query) use ($request) {
              $query->where('approved_members.pref', $request->pref)
                    ->orWhere('w_members.pref', $request->pref)
                    ->orWhere('members.pref', $request->pref);
          });
      }

      if ($request->recruitment) {
          $query->join('applies', 'members.id', '=', 'applies.member_id')
                ->where('applies.type', $request->recruitment);
      }

      if (!StrUtil::is_empty($request->deposits)) {
          $query->join('deposits', 'members.id', '=', 'deposits.member_id')
                ->where('deposits.status', $request->deposits);
      }

      if (!StrUtil::is_empty($request->adoption)) {
          $query->join('adoptions', 'members.id', '=', 'adoptions.member_id')
                ->where('adoptions.result', $request->adoption);
      }

      $query->where(function ($query) use ($request) {
         $query->where('approved_members.del_flg', \CommonConsts::DEL_OFF)
               ->orWhere('w_members.del_flg', \CommonConsts::DEL_OFF)
               ->orWhere('members.del_flg', \CommonConsts::DEL_OFF);
      });


      $records = $query->select(
          'messages.*',
          DB::raw('COALESCE(approved_members.name, w_members.name, members.name) as name'),
          DB::raw('COALESCE(approved_members.email, w_members.email, members.email) as email'),
          DB::raw('COALESCE(approved_members.nickname, w_members.nickname, members.nickname) as nickname'),
          DB::raw('COALESCE(approved_members.birthday_y, w_members.birthday_y, members.birthday_y) as birthday_y'),
          DB::raw('COALESCE(approved_members.birthday_m, w_members.birthday_m, members.birthday_m) as birthday_m'),
          DB::raw('COALESCE(approved_members.birthday_d, w_members.birthday_d, members.birthday_d) as birthday_d'),
          DB::raw('COALESCE(approved_members.pref, w_members.pref, members.pref) as pref'),
          DB::raw('COALESCE(approved_members.addr1, w_members.addr1, members.addr1) as addr1'),
          'messages.id as messages_id',
          'members.id as members_id'
      )->paginate(ShopConsts::PAGENATION_COUNT);



      return $records;

    }


    public function findMessagedShopSummary($member_id)
    {

        $records = DB::table('shops')
                  ->join(
                  \DB::raw('(SELECT MAX(shop_id) AS max_id FROM messages where member_id = '.$member_id.' and type = 1 and readed= '.\CommonConsts::READ_YET.' GROUP BY shop_id) AS latest'),
                   'shops.id', '=', 'latest.max_id',
                  )
                  ->select("shops.*",'shops.id as shop_id')
                  ->get();

        return $records;

    }


    public function findMessagedMember($shop_id)
    {

        $records = DB::table('messages')
                   ->join('members', 'messages.shop_id', '=', 'members.id')
                   ->where('messages.shop_id',$shop_id)
                   ->select('members.*',"messages.id as message_id")
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
    *    メンバーから店舗にメッセージ送信
    */
    public function findMessageByMemberIdReadYet($shop_id)
    {

        $records = DB::table('messages')
                   ->join('members', 'messages.member_id', '=', 'members.id')
                   ->where('messages.shop_id',$shop_id)
                   ->where('readed',\CommonConsts::READ_YET)
                   ->select('members.*')
                   ->get();

        return $records;

    }


    // ショップが読んだ時
    public function messageToReadedByShop($member_id,$shop_id)
    {

        $records = Message::where('member_id',$member_id)->where('shop_id',$shop_id)->where('type',2)->get();
        foreach($records as $record) {
            $record->readed = \CommonConsts::READ_DONE;
            $record->save();
        }
    }


    // メンバーが読んだ時
    public function messageToReadedByMember($member_id,$shop_id)
    {

        $records = Message::where('member_id',$member_id)->where('shop_id',$shop_id)->where('type',1)->get();
        foreach($records as $record) {
            $record->readed = \CommonConsts::READ_DONE;
            $record->save();
        }
    }


    public function cancelTalk($id)
    {

          Message::where('id', $id)->update(['del_flg' => \CommonConsts::DEL_ON]);

    }

}
