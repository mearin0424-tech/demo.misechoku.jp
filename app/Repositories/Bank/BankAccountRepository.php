<?php
namespace App\Repositories\Bank;

use App\Models\BankAccount;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Lib\StrUtil;
use App\Lib\FileUtil;
use Illuminate\Http\Request;
use App\Consts\ShopConsts;
use App\Models\BankAccountShop;


class BankAccountRepository implements BankAccountRepositoryInterface
{
    /**
     * @var App\Models\Project
     */
    private $project;

    public function __construct(BankAccount $project) {
        $this->project = $project;
    }


     /**
     * 口座情報を登録する
     *
     * @param array $data
     * @return Project
     */
    public function store(Request $request,$member_id)
    {


        BankAccount::updateOrCreate(
            ['member_id' => $member_id], 
            $request->all()
        );

/*
        $project = $this->project->create($data);
        return $project;
*/

    }

    public function findBankAccountByMemberId($member_id)
    {

        $records = DB::table('bank_accounts')
                   ->where('member_id',$member_id)
                   ->first();

        return $records;

    }


    public function storeByShopId(Request $request,$shop_id)
    {


        BankAccountShop::updateOrCreate(
            ['shop_id' => $shop_id], 
            $request->all()
        );

/*
        $project = $this->project->create($data);
        return $project;
*/

    }

    public function findBankAccountByShopId($shop_id)
    {

        $records = DB::table('bank_account_shops')
                   ->where('shop_id',$shop_id)
                   ->first();

        return $records;

    }


/*

   public function findBankAccountShop($member_id)
    {

        $records = DB::table('footprints')
                   ->join('shops', 'footprints.shop_id', '=', 'shops.id')
                   ->where('footprints.member_id',$member_id)
                   ->groupby('shops.id')
                   ->select('shops.*')
                   ->get();

        return $records;

    }

    public function findBankAccountMember($shop_id)
    {

        $records = DB::table('footprints')
                   ->join('mmembers', 'footprints.member_id', '=', 'members.id')
                   ->where('footprints.shop_id',$shop_id)
                   ->select('members.*')
                   ->get();

        return $records;

    }



    public function findBankAccountByShopId($shop_id)
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
