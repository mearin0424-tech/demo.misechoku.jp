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
}
