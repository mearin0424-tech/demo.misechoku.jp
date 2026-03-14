<?php
namespace App\Repositories\Member;

use App\Models\Cast;
use App\Models\Identity;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use  App\Lib\StrUtil;

class IdentityRepository implements MemberRepositoryInterface
{
    /**
     * @var App\Models\Project
     */
    private $project;

    public function __construct(Cast $project) {
        $this->project = $project;
    }


    /**
     * 本人確認画像を登録する
     *
     * @param array $data
     * @return Project
     */
    public function store(array $data): Identity
    {


        //$request->file('shop_main_img')->store('');


        $project = $this->project->create($data);
        // ラベル更新
        //$this->updateLabels($project, $data);

        return $project;
    }

    public function findByMemberId($id): Identity
    {
        $records =  Identity::find($id);
        return $records;

    }



}


