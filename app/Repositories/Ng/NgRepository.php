<?php
namespace App\Repositories\Ng;

use App\Models\NgWord;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Lib\StrUtil;
use App\Lib\FileUtil;
use Illuminate\Http\Request;
use App\Consts\ShopConsts;
use App\Consts\CommonConsts;


class NgRepository implements NgRepositoryInterface
{    
    /**
     * @var App\Models\Project
     */
    private $project;

    public function __construct(NgWord $project) {
        $this->project = $project;
    }


    public function getAll()
    {
        return  DB::table('ng_words')->first();
    }

    public function findById($id)
    {
        $records =  NgWord::find($id);
        return $records;

    }

     /**
     * NGワードを登録する
     *
     * @param array $data
     * @return Project
     */
    public function store(Request $request)
    {
/*
        $project = $this->project->create($data);
        $member_id = $request['member_id'];

        News::updateOrCreate(
            ['member_id' => $member_id], 
            $request->all()
        );
*/
        NgWord::query()->delete();

        $data = NgWord::Create(
            $request->all()
        );
/*
        $project = $this->project->create($data);
        return $project;
*/

    }
/*
    public function save(Request $request,$id)
    {

        News::updateOrCreate(
            ['id' => $id], 
            $request->all()
        );


    }

    public function del(Request $request,$id)
    {
        News::where('id', $id)->delete();
    }



   public function findNewsShop($member_id)
    {

        $records = DB::table('footprints')
                   ->join('shops', 'footprints.shop_id', '=', 'shops.id')
                   ->where('footprints.member_id',$member_id)
                   ->groupby('shops.id')
                   ->select('shops.*')
                   ->get();

        return $records;

    }

    public function findNewsMember($shop_id)
    {

        $records = DB::table('footprints')
                   ->join('mmembers', 'footprints.member_id', '=', 'members.id')
                   ->where('footprints.shop_id',$shop_id)
                   ->select('members.*')
                   ->get();

        return $records;

    }

    public function findNewsByMemberId($member_id)
    {

        $records = DB::table('footprints')
                   ->join('shops', 'footprints.shop_id', '=', 'shops.id')
                   ->where('footprints.member_id',$member_id)
                   ->select('shops.*')
                   ->get();

        return $records;

    }

    public function findNewsByShopId($shop_id)
    {

        $records = DB::table('footprints')
                   ->join('mmembers', 'footprints.memeber_id', '=', 'members.id')
                   ->where('footprints.shop_id',$shop_id)
                   ->select('members.*')
                   ->get();

        return $records;

    }
*/

}
