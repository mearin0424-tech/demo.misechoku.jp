<?php
namespace App\Repositories\Shop;

use App\Models\Manager;
use App\Models\ShopTreatment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Lib\StrUtil;
use App\Lib\FileUtil;
use App\Http\Requests\Shop\JobRequest;
use Illuminate\Http\Request;
use App\Consts\ShopConsts;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ManagerRepository implements ManagerRepositoryInterface
{
    /**
     * @var App\Models\Project
     */
    private $project;

    public function __construct(Manager $project) {
        $this->project = $project;
    }


    public function findByMail($email)
    {

        $records =  Manager::where('email',$email)->get();
        
        foreach($records as $record){
            $record;
            break;
        }
        return $record;

    }


    public function findById($manager_id)
    {
        $records =  Manager::find($manager_id);
        return $records;

    }

    public function findByShopIdCnt($shop_id)
    {
        $records =  Job::where('shop_id',$shop_id)->count();
        return $records;

    }

    public function getAccountCnt($shop_id)
    {
        $records =  Manager::where('shop_id',$shop_id)->count();
        return $records;

    }

    public function findByShop($shop_id)
    {
        $records =  Manager::where('shop_id',$shop_id)->paginate(\ShopConsts::PAGENATION_COUNT);
        return $records;

    }

    /**
     * 担当者情報を登録する
     *
     * @param array $data
     * @return Project
     */
    public function store(array $data,Request $request)
    {

        $req_arr = $request->all(); 
        $shop_id = $request['shop_id'];
        $email = $request['email'];

        Manager::updateOrCreate(
            ['shop_id' => $shop_id,'email' => $email], 
            $request->all()
        );


    }

    public function createManageer($request,$shop_id)
    {
        $req_all = $request->all(); 
        $req_all['shop_id'] = $shop_id;
        $req_all['password'] =  Hash::make($req_all['password']);

        $data = Manager::Create(
            $req_all
        );
        return $data->id;

    }

    public function createManageer2($request,$shop_id)
    {

        $req_all = $request->all(); 
/*
        $req_all['shop_id'] = $shop_id;
        $req_all['email'] = $email;
        $req_all['password'] =  Hash::make($req_all['password']);
        $req_all['status'] = \ShopConsts::REGISTER;

        $data = Manager::Create(
            $req_all
        );
*/

        Manager::where('shop_id',$shop_id)->where('email', $req_all['email'])
          ->update(['password' =>  Hash::make($req_all['password']),'status'=> \ShopConsts::REGISTER]);

    }

    public function edit(Request $request,$id)
    {

        $data = $request->all(); 
        $data['password'] =  Hash::make($data['password']);
        Manager::updateOrCreate(
            ['id' => $id], 
            $data
        );


    }
/*
    public function delete(Request $request)
    {

        $shop_id    = $request['shop_id'];
        $manager_id = $request['manager_id'];
        return Manager::where('shop_id', $shop_id)->where('manager_id', $manager_id)->delete();

    }
*/

    public function delete(Request $request,$enc_id)
    {

        $id    = \StrUtil::dec($enc_id);
        return Manager::where('id', $id)->delete();

    }

}
