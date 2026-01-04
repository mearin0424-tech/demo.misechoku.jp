<?php

namespace App\Repositories\Deposit;

use App\Models\Apply;
use App\Models\Deposit;
use App\Models\Review;
use App\Models\ReviewDetail;
use App\Models\ReviewContent;
use App\Consts\DepositConsts;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Lib\StrUtil;

class DepositRepository implements DepositRepositoryInterface
{
    /**
     * @var App\Models\Project
     */
    private $project;

    public function __construct(Deposit $project)
    {
        $this->project = $project;
    }

    // 入金依頼済み
    public function depositRequested()
    {
        return  Deposit::where('status', \DepositConsts::DEPOSITS_2)->count();
    }

    // 入金承認済み
    public function deposit_3()
    {
        return  Deposit::where('status', \DepositConsts::DEPOSITS_3)->count();
    }

    // 店舗請求済み
    public function shopBilledCnt()
    {
        return  Deposit::where('status', \DepositConsts::DEPOSITS_4)->count();
    }

    // 店舗入金済
    public function shopDepositedCnt()
    {
        return  Deposit::where('status', \DepositConsts::DEPOSITS_5)->count();
    }

    /**
     *
     * 入金依頼のメッセージを登録する
     *
     * @param array $data
     * @return Project
     */
    public function store($data)
    {
        $project = $this->project->create($data);
    }


    public function findByAppliedBk()
    {

        $records = DB::table('deposits')
            ->join('shops', 'deposits.shop_id', '=', 'shops.id')
            ->join('members', 'deposits.member_id', '=', 'members.id')
            ->where('deposits.status', DepositConsts::DEPOSIT_1)
            ->select(
                'shops.*',
                'members.*',
                'shops.pref as s_pref',
                'shops.addr1 as s_addr1',
                'deposits.created_at as d_created_at',
                'deposits.id as  deposit_id',
                'shops.id as shop_id',
                'members.id as member_id'
            )
            ->paginate(\ShopConsts::PAGENATION_COUNT);


        return $records;
    }

    public function findByBkRecruit($status)
    {

        $records = DB::table('deposits')
            ->join('shops', 'deposits.shop_id', '=', 'shops.id')
            ->join('members', 'deposits.member_id', '=', 'members.id')
            ->where('deposits.status', $status)
            ->orderBy('deposits.id', "desc")
            ->select(
                'shops.*',
                'members.*',
                'shops.pref as s_pref',
                'shops.addr1 as s_addr1',
                'deposits.created_at as d_created_at',
                'deposits.id as  deposit_id',
                'shops.id as shop_id',
                'members.id as member_id',
                'deposits.id as deposits_id'
            )
            ->paginate(\ShopConsts::PAGENATION_COUNT);


        return $records;
    }



    public function findByDepostiMember($member_id, $shop_id, $apply_id = null)
    {

        $records = Deposit::join('applies', 'applies.id', '=', 'deposits.apply_id')
                    ->when(!is_null($apply_id), function ($query) use ($apply_id) {
                        $query->where('deposits.apply_id', $apply_id);
                    })
                    ->where("deposits.member_id", $member_id)
                    ->where('deposits.shop_id', $shop_id)
                    ->where('applies.active', \RicruitConsts::ACTIVE)
                    ->where('applies.status', \RicruitConsts::INTERVIEW_DONE)
                    ->where('applies.adoption',\RicruitConsts::ADOPTION_OK)
                    ->select('deposits.*','applies.adoption','applies.status as applies_status')
                    ->get();
        return $records;
    }


    public function saveDeposit($member_id, $shop_id)
    {

        Deposit::updateOrCreate(
            ['member_id' => $member_id, 'shop_id' => $shop_id],
            ['status' => \DepositConsts::DEPOSITS_1]
        );
    }

    public function saveDepositRequest($request, $member_id, $shop_id, $apply_id)
    {
        $data = [
            'contents' => $request["contents"],
            'release' => \ReviewConsts::APPROVAL_OK_1,
        ];

        if (!is_null($request["anonymous"])) {
            $data['anonymous'] = $request["anonymous"];
        }

        $review = Review::updateOrCreate(
            ['apply_id' => $apply_id, 'member_id' => $member_id, 'shop_id' => $shop_id],
            $data
        );


        $reviewContents =  DB::table('review_contents')->where("del_flg",0)->get();

        foreach ($reviewContents as  $val) {
            if (empty($request["rating_" . $val->id])) continue;
            ReviewDetail::updateOrCreate(
                ['review_id' => $review->id, 'val' => $val->id],
                ['val' => $val->id, 'score' => $request["rating_" . $val->id]]
            );
        }

        $deposit = Deposit::updateOrCreate(
            ['apply_id' => $apply_id, 'member_id' => $member_id, 'shop_id' => $shop_id],
            ['status' => \DepositConsts::DEPOSITS_2]
        );

        // Create new history
        $deposit->histories()->create([
            'status' => \DepositConsts::DEPOSITS_2,
            'status_date' => now(),
        ]);
    }

    public function saveDeposit2($member_id, $shop_id, $apply_id)
    {
        $deposit = Deposit::updateOrCreate(
            ['apply_id' => $apply_id, 'member_id' => $member_id, 'shop_id' => $shop_id],
            ['status' => \DepositConsts::DEPOSITS_3]
        );

        // Create new history
        $deposit->histories()->create([
            'status' => \DepositConsts::DEPOSITS_3,
            'status_date' => now(),
        ]);
    }

    public function saveDepositStatus($request, $member_id, $shop_id, $apply_id)
    {
        if ($request->status == \DepositConsts::DEPOSITS_6) {
            Apply::updateOrCreate(
                [
                    'member_id' => $member_id,
                    'shop_id'   => $shop_id,
                    'active'    => \RicruitConsts::ACTIVE,
                ],
                [
                    'active'      => \RicruitConsts::END_ACTIVE
                ]
            );
        }
        $deposit = Deposit::updateOrCreate(
            ['apply_id' => $apply_id, 'member_id' => $member_id, 'shop_id' => $shop_id],
            ['status' => $request->status]
        );
        // Create new history
        $deposit->histories()->create([
            'status' => $request->status,
            'status_date' => now(),
        ]);
    }

    /**
     *    メンバーから店舗に入金依頼依頼
     */
    public function findDepositByMemberIdReadYet($shop_id)
    {

        $records = DB::table('deposits')
            ->join('members', 'deposits.member_id', '=', 'members.id')
            ->where('deposits.shop_id', $shop_id)
            ->where('deposits.status', \DepositConsts::DEPOSITS_2)
            ->where('readed', \CommonConsts::READ_YET)
            ->select('members.*')
            ->get();

        return $records;
    }

    public function depositToReadedByShop($member_id, $shop_id)
    {

        $records = Deposit::where('member_id', $member_id)->where('shop_id', $shop_id)->first();

        $records->readed = \CommonConsts::READ_DONE;
        $records->save();
    }
}
