<?php
namespace App\Repositories\Apply;

use App\Models\Apply;
use App\Models\Shop;
use App\Models\Job;
use App\Models\Deposit;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Lib\StrUtil;
use App\Lib\FileUtil;
use Illuminate\Http\Request;
use  App\Consts\MemberConsts;
use DateTime;

class ApplyRepository implements ApplyRepositoryInterface
{
    /**
     * @var App\Models\Project
     */
    private $project;

    public function __construct(Apply $project) {
        $this->project = $project;
    }


     /**
     * 登録する
     *
     * @param array $data
     * @return Project
     */
    public function store($request)
    {

        $shop_id = $request['shop_id'];
        $member_id = $request['member_id'];

        Job::updateOrCreate(
            ['member_id' => $member_id,'shop_id' => $shop_id],
            $request->all()
        );

    }

    public function entry($member_id,$shop_id)
    {

        Apply::updateOrCreate(
            ['member_id' => $member_id,'shop_id' => $shop_id],
            ['apply_way'=>\RicruitConsts::ENTRY]
        );

    }


    public function findApply($member_id, $shop_id)
    {
        $records = Apply::where("member_id",$member_id)->where('shop_id',$shop_id)->orderBy('result_date','desc')->orderBy('updated_at','desc')->get();
        return $records;

    }

    public function findOneApply($member_id, $shop_id)
    {
        return Apply::with(['deposit.histories' => function($q) {
                $q->whereIn('id', function($sub) {
                    $sub->selectRaw('MAX(id)')
                        ->from('deposit_status_histories')
                        ->groupBy('status', 'deposit_id');
                })
                    ->orderBy('status', 'asc');
            }])
            ->leftJoin('deposits', 'applies.id', '=', 'deposits.apply_id')
            ->where('applies.member_id', $member_id)
            ->where('applies.shop_id', $shop_id)
            ->where('applies.active', \RicruitConsts::ACTIVE)
            ->select('applies.*', 'deposits.status as deposit_status', 'deposits.updated_at as deposits_updated_at')
            ->first();
    }

    public function findApplyHistory($member_id, $shop_id)
    {
        $records = Apply::with(['deposit.histories' => function($q) {
                $q->whereIn('id', function($sub) {
                    $sub->selectRaw('MAX(id)')
                        ->from('deposit_status_histories')
                        ->groupBy('status', 'deposit_id');
                })
                    ->orderBy('status', 'asc');
            }])
            ->leftJoin('deposits', 'applies.id', '=', 'deposits.apply_id')
            ->where('applies.member_id',$member_id)
            ->where('applies.shop_id',$shop_id)
            ->select('applies.*','deposits.status as deposits_status','deposits.updated_at as deposits_updated_at')
            ->orderBy('created_at','desc')->get();
        return $records;

    }

    public function findApplyHelpFirst($member_id, $shop_id)
    {
        $records = Apply::where("member_id",$member_id)->where('shop_id',$shop_id)
            //->where('result',\RicruitConsts::ADOPTION_HELP)
            ->orderBy('result_date','desc')->first();

        return $records;

    }

    public function applied($member_id, $shop_id)
    {

        $records = Apply::where("member_id",$member_id)->where('shop_id',$shop_id)->where('apply_way',\RicruitConsts::ENTRY)->get();
        return $records;

    }

    public function findApplyAdoptedShop($member_id)
    {
        $records = DB::table('applies')
                   ->join('members', 'applies.member_id', '=', 'members.id')
                   ->join('shops', 'applies.shop_id', '=', 'shops.id')
                   ->join('jobs', 'jobs.shop_id', '=', 'shops.id')
                   ->where('members.id',$member_id)
                   ->whereIn('applies.active',[\RicruitConsts::ACTIVE, \RicruitConsts::END_ACTIVE])
                   ->where('applies.status',\RicruitConsts::INTERVIEW_DONE)
                   ->where('applies.adoption',\RicruitConsts::ADOPTION_OK)
                   ->select('applies.*','members.*','shops.*' ,'shops.id as shop_id','applies.status as applies_status', 'applies.id as apply_id')
                   ->get();

        return $records; // ユニークなコレクションを配列に変換して返す

    }

    public function findApplyAdoptedMember($shop_id)
    {

/*
        $records = DB::table('applies')
                   ->join('members', 'applies.member_id', '=', 'members.id')
                   ->join('shops', 'applies.shop_id', '=', 'shops.id')
                   ->where('shops.id',$shop_id)
                   //->where('applies.result',\CommonConsts::ADOPTED)
                   ->select('applies.*','members.*','applies.id as apply_id','applies.status as apply_status','members.id as members_id')
                   ->get();
*/

        $latest_applies = DB::table('applies')
            ->select(DB::raw('MAX(id) as id'))
            ->groupBy('member_id');

        $records = DB::table('applies')
            ->join('shops', 'applies.shop_id', '=', 'shops.id')
            ->joinSub($latest_applies, 'latest_applies', function($join) {
                $join->on('applies.id', '=', 'latest_applies.id');
            })
            ->leftJoin('w_members', 'applies.member_id', '=', 'w_members.id')
            ->leftJoin('members', function($join) {
                $join->on('applies.member_id', '=', 'members.id')
                     ->where(function($query) {
                         $query->where('members.approval', \FrontConsts::APPROVAL_ON)
                               ->orWhereNull('w_members.id');
                     });
            })
            ->where('shops.id', $shop_id)
            // ->where('applies.result', \CommonConsts::ADOPTED)
            ->orderBy('updated_at','desc')

            ->select(
                'applies.*',
                DB::raw('COALESCE(w_members.name, members.name) as name'),
                DB::raw('COALESCE(w_members.email, members.email) as email'),
                DB::raw('COALESCE(w_members.nickname, members.nickname) as nickname'),
                DB::raw('COALESCE(w_members.birthday_y, members.birthday_y) as birthday_y'),
                DB::raw('COALESCE(w_members.birthday_m, members.birthday_m) as birthday_m'),
                DB::raw('COALESCE(w_members.birthday_d, members.birthday_d) as birthday_d'),
                DB::raw('COALESCE(w_members.pref, members.pref) as pref'),
                DB::raw('COALESCE(w_members.addr1, members.addr1) as addr1'),
                'applies.id as apply_id',
                'applies.status as apply_status',
                'members.id as members_id',
                'applies.updated_at as applies_updated_at'
            )
            ->get();

        return $records;

    }


    public function findApplyAdoptedMemberPagenation($shop_id)
    {


        $records = DB::table('applies')
                   ->join('members', 'applies.member_id', '=', 'members.id')
                   ->join('shops', 'applies.shop_id', '=', 'shops.id')
                   ->where('shops.id',$shop_id)
                   //->where('applies.result',\CommonConsts::ADOPTED)
                   ->select('applies.*','members.*','applies.id as apply_id','applies.status as apply_status','members.id as members_id');


        return $records->paginate(\ShopConsts::PAGENATION_COUNT);
    }
//REJECTED


   public function findByShopRecruit($shop_id,$status="")
    {


        $latest_messages = DB::table('deposits')
            ->select(DB::raw('MAX(id) as id'))
            ->groupBy('member_id','shop_id');

        $records = DB::table('deposits')

                   ->join('shops', 'deposits.shop_id', '=', 'shops.id')
                   ->joinSub($latest_messages, 'latest_messages', function($join) {
                       $join->on('deposits.id', '=', 'latest_messages.id');
                   })
                   ->leftJoin('w_members', 'deposits.member_id', '=', 'w_members.id')
                   ->leftJoin('members', function($join) {
                       $join->on('deposits.member_id', '=', 'members.id')
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
                   ->join('applies', 'applies.shop_id', '=', 'shops.id')
                   ->where('shops.id',$shop_id)

                   ->where('deposits.status',$status)
                   ->select(
                   'applies.*','members.*','applies.id as apply_id','applies.status as apply_status',
                    DB::raw('COALESCE(w_members.nickname, members.nickname) as nickname'),
                    DB::raw('COALESCE(w_members.pref, members.pref) as pref'),
                    DB::raw('COALESCE(w_members.addr1, members.addr1) as addr1'),
                   'members.id as members_id','deposits.id as deposits_id')
                   //->select('applies.*','members.*','applies.id as apply_id','applies.status as apply_status','members.id as members_id')
                   ->paginate(\ShopConsts::PAGENATION_COUNT);


        return $records;

    }


   public function findByShopRecruit2($shop_id,$status="")
    {

        $latest_messages = DB::table('deposits')
            ->select(DB::raw('MAX(id) as id'))
            ->groupBy('member_id');


        $records = DB::table('deposits')
                   ->join('shops', 'deposits.shop_id', '=', 'shops.id')
                   ->joinSub($latest_messages, 'latest_messages', function($join) {
                       $join->on('deposits.id', '=', 'latest_messages.id');
                   })
                   ->leftJoin('w_members', 'deposits.member_id', '=', 'w_members.id')
                   ->leftJoin('members', function($join) {
                       $join->on('deposits.member_id', '=', 'members.id')
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



                   ->join('applies', 'applies.shop_id', '=', 'shops.id')
                   ->where('shops.id',$shop_id)
                   ->select(
                   'applies.*','members.*','applies.id as apply_id','applies.status as apply_status',
                    DB::raw('COALESCE(w_members.nickname, members.nickname) as nickname'),
                    DB::raw('COALESCE(w_members.pref, members.pref) as pref'),
                    DB::raw('COALESCE(w_members.addr1, members.addr1) as addr1'),
                   'members.id as members_id','deposits.id as deposits_id')
                   ->paginate(\ShopConsts::PAGENATION_COUNT);


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


    public function scheduleInterview($member_id, $shop_id, $request)
    {
        Apply::updateOrCreate(
            [
                'member_id' => $member_id,
                'shop_id'   => $shop_id,
                'active'    => \RicruitConsts::ACTIVE,
            ],
            [
                'status'      => \RicruitConsts::INTERVIEW_YET,
                'result_date' => $request->result_date,
            ]
        );
    }


    public function adoption($member_id, $shop_id, $request)
    {
        $job = Job::where('shop_id', $shop_id)->first();
        $adoption = (int)$request->input('adoption');
        $data = [];
        if ($adoption === \RicruitConsts::ADOPTION_OK) {
            $data = [
                'status'                => \RicruitConsts::INTERVIEW_DONE,
                'adoption'              => $adoption,
                'active'                => \RicruitConsts::ACTIVE,
                'about_recruit'         => $request->input('about_recruit'),
                'bonus_rewards'         => $request->input('bonus_rewards'),
                'real_start_date'       => $request->input('real_start_date') ?: null,
                'reason_rejection'      => null,

                // 本入店
                'hourly_wage_regular'   => $job->hourly_wage_regular ?? null,
                'normal_time'           => $job->normal_time ?? null,
                'noruma_reward'         => $job->noruma_reward ?? null,
                'noruma_reward2'        => $job->noruma_reward2 ?? null,
                'hours_day'             => $job->hours_day ?? null,

                // 体験入店
                'trial_hourly_wage'     => $job->trial_hourly_wage ?? null,
                'normal_time_trial'     => $job->normal_time_trial ?? null,
                'noruma_reward_trial'   => $job->noruma_reward_trial ?? null,
                'noruma_reward2_trial'  => $job->noruma_reward2_trial ?? null,
                'hours_day_trial'       => $job->hours_day_trial ?? null,

                // ヘルプ求人
                'help_hourly_wage'      => $job->help_hourly_wage ?? null,
                'normal_time_help'      => 1,
                'noruma_reward_help'    => $job->noruma_reward_help ?? null,
                'noruma_reward2_help'   => $job->noruma_reward2_help ?? null,
                'hours_day_help'        => $job->hours_day_help ?? null,
            ];

            // 👉 Save application and get ID
            if (!empty($request->adoptionChange) && $request->adoptionChange == \RicruitConsts::ADOPTION_OK) {
                // Active record priority
                $apply = Apply::where('member_id', $member_id)
                    ->where('shop_id', $shop_id)
                    ->where('active', \RicruitConsts::ACTIVE)
                    ->first();

                // If there is no active record, get the latest adoption record = 2
                if (!$apply) {
                    $apply = Apply::where('member_id', $member_id)
                        ->where('shop_id', $shop_id)
                        ->where('adoption', \RicruitConsts::ADOPTION_REJECTED)
                        ->orderBy('updated_at', 'desc')
                        ->first();
                }

                // If found, update.
                if ($apply) {
                    $apply->update($data);
                } else {
                    // If there is no record, create a new one.
                    $apply = Apply::create(array_merge([
                        'member_id' => $member_id,
                        'shop_id'   => $shop_id,
                        'active'    => \RicruitConsts::ACTIVE
                    ], $data));
                }
            } else {
                // Old logic
                $apply = Apply::updateOrCreate(
                    [
                        'member_id' => $member_id,
                        'shop_id'   => $shop_id,
                        'active'    => \RicruitConsts::ACTIVE
                    ],
                    $data
                );
            }

            // 👉 Update or create deposit associated with new application
            if ((int)$request->input('bonus_rewards') > 0) {
                $deposit = Deposit::updateOrCreate(
                    [
                        'member_id' => $member_id,
                        'shop_id'   => $shop_id,
                        'apply_id'  => $apply->id,
                    ],
                    [
                        'status'    => \DepositConsts::DEPOSITS_1,
                    ]
                );

                // Delete all old history of this deposit
                $deposit->histories()->delete();

                // Create new history
                $deposit->histories()->create([
                    'status' => \DepositConsts::DEPOSITS_1,
                    'status_date' => now(),
                ]);
            }

        }

        return $data;
    }



    public function adoptionNg($member_id, $shop_id, $request)
    {
        $job = Job::where('shop_id', $shop_id)->first();
        $adoption = (int)$request->input('adoption');

        if ($adoption === \RicruitConsts::ADOPTION_REJECTED) {
            $data = [
                'status' => \RicruitConsts::INTERVIEW_DONE,
                'adoption' => $adoption,
                'active' => \RicruitConsts::END_ACTIVE,
                'about_recruit' => null,
                'bonus_rewards' => null,
                'real_start_date' => null,
                'reason_rejection' => $request->input('reason_rejection'),

                // 本入店
                'hourly_wage_regular' => null,
                'normal_time' => null,
                'noruma_reward' => null,
                'noruma_reward2' => null,
                'hours_day' => null,

                // 体験入店
                'trial_hourly_wage' => null,
                'normal_time_trial' => null,
                'noruma_reward_trial' => null,
                'noruma_reward2_trial' => null,
                'hours_day_trial' => null,

                // ヘルプ求人
                'help_hourly_wage' => null,
                'normal_time_help' => null,
                'noruma_reward_help' => null,
                'noruma_reward2_help' => null,
                'hours_day_help' => null,
            ];

            if (!empty($request->adoptionChange) && $request->adoptionChange == \RicruitConsts::ADOPTION_REJECTED) {
                // Active record priority
                $apply = Apply::where('member_id', $member_id)
                    ->where('shop_id', $shop_id)
                    ->where('active', \RicruitConsts::ACTIVE)
                    ->first();

                // If there is no active record, get the latest adoption record = 2
                if (!$apply) {
                    $apply = Apply::where('member_id', $member_id)
                        ->where('shop_id', $shop_id)
                        ->where('adoption', \RicruitConsts::ADOPTION_REJECTED)
                        ->orderBy('updated_at', 'desc')
                        ->first();
                }

                // If found, update.
                if ($apply) {
                    $apply->update($data);
                } else {
                    // If there is no record, create a new one.
                    $apply = Apply::create(array_merge([
                        'member_id' => $member_id,
                        'shop_id'   => $shop_id,
                        'active'    => \RicruitConsts::ACTIVE
                    ], $data));
                }
            } else {
                // Old logic
                $apply = Apply::updateOrCreate(
                    [
                        'member_id' => $member_id,
                        'shop_id'   => $shop_id,
                        'active'    => \RicruitConsts::ACTIVE
                    ],
                    $data
                );
            }

            Deposit::where('apply_id', $apply->id)->delete();

        }
    }

    public function adoptionNgReason($member_id,$shop_id,$ng)
    {

        $today = new DateTime();

        Apply::updateOrCreate(
            ['member_id' => $member_id,'shop_id' => $shop_id],
            ['ng'=>$ng,'result'=>\RicruitConsts::ADOPTION_NG,'result_date'=>$today->format('Y-m-d'),'type'=>\RicruitConsts::ADOPTION_NG]
        );
    }

    public function getNgReason($member_id,$shop_id)
    {
          $ng = Apply::where("member_id",$member_id)->where("shop_id",$shop_id)
              //->where('result',\RicruitConsts::ADOPTION_NG)->where('type',\RicruitConsts::ADOPTION_NG)
              ->first();
          return $ng;

    }

    public function adoptionHelp($member_id,$shop_id,$request)
    {
/*
        Apply::updateOrCreate(
            ['member_id' => $member_id,'shop_id' => $shop_id],
            ['result'=>\RicruitConsts::ADOPTION_HELP,'result_date'=>$request->result_date,'type'=>\RicruitConsts::ADOPTION_HELP]
        );
*/


        $job = Job::where('shop_id',$shop_id)->first();
        $noruma_reward_help = $job->noruma_reward_help;

        $apply = new Apply;
        $apply->member_id = $member_id;
        $apply->shop_id = $shop_id;
        $apply->result = \RicruitConsts::ADOPTION_HELP;
        $apply->result_date = $request->result_date;
        $apply->type = \RicruitConsts::ADOPTION_HELP;
        $apply->noruma_reward_help = $noruma_reward_help;
        $apply->save();

        if(Shop::find($shop_id)->noruma_reward_help>0){
            Deposit::updateOrCreate(
                ['member_id' => $member_id,'shop_id' => $shop_id],
                ['status'=>\DepositConsts::DEPOSITS_1]
            );
        }
    }

    public function helpDelete($id)
    {
         Apply::find($id)->delete();
    }
}
