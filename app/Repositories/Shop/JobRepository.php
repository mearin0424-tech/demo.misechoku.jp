<?php

namespace App\Repositories\Shop;

use App\Models\Job;
use App\Models\ShopTreatment;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Lib\StrUtil;
use App\Lib\FileUtil;
use App\Http\Requests\Shop\JobRequest;
use Illuminate\Http\Request;
use App\Consts\ShopConsts;
use Illuminate\Support\Facades\Auth;
use App\Models\ShopIndustry;

class JobRepository implements JobRepositoryInterface
{
    /**
     * @var App\Models\Project
     */
    private $project;

    public function __construct(Job $project)
    {
        $this->project = $project;
    }


    public function findById($shop_id)
    {
        $records =  Job::where('shop_id', $shop_id)->first();
        return $records;
    }

    public function findByIdByAdmin($shop_id)
    {
        $records =  Job::where('shop_id', $shop_id)->get();
        return $records;
    }

    public function findByIdForShop($shop_id)
    {
        $records =  Job::where('shop_id', $shop_id)->get();
        return $records;
    }

    public function findByIdTreatment($shop_id)
    {
        $records =  DB::table('shop_treatments')->where('shop_id', $shop_id)->get();
        return $records;
    }

    public function findByIdForShopWithJob($shop_id)
    {

        $record = DB::table('shops')
                   ->leftJoin('jobs', 'shops.id', '=', 'jobs.shop_id')
                   ->where('shops.id',$shop_id)
                   ->select('shops.*', 'jobs.*')
                   ->first();


        return $record;
    }

    public function  getTodayCentensWithJobsByShop($shop_id)
    {

        $record = DB::table('shops')
                   ->leftJoin('jobs', 'shops.id', '=', 'jobs.shop_id')
                   ->leftJoin('today_centens_by_shops', 'shops.id', '=', 'today_centens_by_shops.shop_id')
                   ->where('shops.id',$shop_id)
                   ->select('shops.*', 'jobs.*', 'today_centens_by_shops.*')
                   ->first();


        return $record;

    }
    /*

    public function findByIdFront($shop_id)
    {
        $records = Job::where('id',$shop_id)->where('approval',\ShopConsts::APPROVAL_ON)->first();
        if(!Job::where('id',$shop_id)->where('approval',\ShopConsts::APPROVAL_ON)->exists()) {
            $records = WShop::where('id',$shop_id)->first();
        }
        return $records;

    }
*/

    /**
     * 求人情報を登録する
     *
     * @param array $data
     * @return Project
     */
    public function store(array $data, Request $request)
    {



        $req_arr = $request->all();
        $shop_id = $request['shop_id'];

        Shop::updateOrCreate(
            ['id' => $shop_id],
            ['approval' => \ShopConsts::APPROVAL_OFF2]
        );

        Job::updateOrCreate(
            ['shop_id' => $shop_id],
            $request->all()
        );

        ShopTreatment::where('shop_id', $shop_id)->delete();

        if (!empty($request->all()['treatment'])) {
            $treatments = $request->all()['treatment'];
            foreach ($treatments as $treatment) {

                ShopTreatment::updateOrCreate(
                    ['shop_id' => $shop_id, 'treatment' => $treatment],
                    array('treatment' => $treatment)
                );
            }
        }
    }

    /**
     * 求人情報を登録する
     *
     * @param array $data
     * @return Project
     */
    public function store_admin(Request $request)
    {

        $req_arr = $request->all();
        $shop_id = $request['shop_id'];

        Shop::updateOrCreate(
            ['id' => $shop_id],
            ['approval' => ShopConsts::APPROVAL_ON]
        );

        Job::updateOrCreate(
            ['shop_id' => $shop_id],
            $request->all()
        );

        ShopTreatment::where('shop_id', $shop_id)->delete();

        if (!empty($request->all()['treatment'])) {
            $treatments = $request->all()['treatment'];
            foreach ($treatments as $treatment) {

                ShopTreatment::updateOrCreate(
                    ['shop_id' => $shop_id, 'treatment' => $treatment],
                    array('treatment' => $treatment)
                );
            }
        }
    }
}
