<?php

namespace App\Repositories\Tag;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Lib\StrUtil;
use App\Lib\FileUtil;
use Illuminate\Http\Request;

class TagRepository implements TagRepositoryInterface
{
    /**
     * @var App\Models\Project
     */
    private $project;

    public function __construct(Tag $project)
    {
        $this->project = $project;
    }

    public function getAll()
    {
        return  DB::table('tags')->get();
    }

    // 報酬
    public function findSalary()
    {
        return  DB::table('tags')->where('type', \TreatmentConsts::VARIABLE_1)->get();
    }

    // 働き方
    public function findHowTo()
    {
        return  DB::table('tags')->where('type', \TreatmentConsts::VARIABLE_2)->get();
    }


    // メリット
    public function findMerit()
    {
        return  DB::table('tags')->where('type', \TreatmentConsts::VARIABLE_3)->get();
    }

    // 特徴
    public function findFeacture()
    {
        return  DB::table('tags')->where('type', \TreatmentConsts::VARIABLE_4)->get();
    }

    // 設備
    public function findFacility()
    {
        return  DB::table('tags')->where('type', \TreatmentConsts::VARIABLE_5)->get();
    }

    // キャストタグ ご自分の系統
    public function findCasttag()
    {
        return  DB::table('tags')->where('type', \TreatmentConsts::VARIABLE_6)->get();
    }

    // キャストタグ ご自分の内面•特技
    public function findCasttag2()
    {
        return  DB::table('tags')->where('type', \TreatmentConsts::VARIABLE_7)->get();
    }

    public function findAtmosphere()
    {
        return  DB::table('tags')->where('type', \TreatmentConsts::VARIABLE_8)->get();
    }

    /**
     * 登録する
     *
     * @param array $data
     * @return Project
     */
    public function store(Request $request)
    {

        $data = $request->all();
        Tag::truncate();

        foreach (\TreatmentConsts::VARIABLE_NAME as $vari) {
            foreach ($data[$vari] as $var) {

                if (empty($var)) continue;
                Tag::create(['type' => $vari, 'content' => $var]);

                /*
                 Tag::updateOrCreate(
                     ['type' => $vari,'content' => $var], 
                     ['type' => $vari,'content' => $var], 
                 );
*/
            }
        }



        /*
        $project = $this->project->create($data);
        return $project;
*/
    }


}
