<?php
namespace App\Repositories\Admin;

use App\Models\User;
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


class AdminRepository implements AdminRepositoryInterface
{
    /**
     * @var App\Models\Project
     */

    private $project;

    public function __construct(User $project) {
        $this->project = $project;
    }


    public function all()
    {
        $records =  User::select()->paginate(\ShopConsts::PAGENATION_COUNT);
        return $records;

    }

    public function findById($id)
    {
        $records =  User::find($id);
        return $records;

    }

/*
    public function findByMail($email)
    {
        $records =  Manager::where('email',$email)->get();
        return $records[0];

    }




    public function findByShopIdCnt($shop_id)
    {
        $records =  Job::where('shop_id',$shop_id)->count();
        return $records;

    }

    public function findByShop($shop_id)
    {
        $records =  Manager::where('shop_id',$shop_id)->paginate(\ShopConsts::PAGENATION_COUNT);;
        return $records;

    }
*/

    /**
     * 担当者情報を登録する
     *
     * @param array $data
     * @return Project
     */

    public function store(Request $request,$id)
    {
        $data = $request->all();
        $data['password'] = Hash::make($data['password']);
        User::updateOrCreate(
            ['id' => $id], 
            $data
        );

    }


    public function createUser($request)
    {
        $data = $request->all();
        $data['password'] = Hash::make($data['password']);

        $data = User::Create(
            $data
        );
        return $data->id;

    }

    public function delete(Request $request,$id)
    {

        return User::where('id', $id)->delete();

    }


}
