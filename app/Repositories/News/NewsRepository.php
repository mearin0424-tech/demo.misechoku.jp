<?php
namespace App\Repositories\News;

use App\Models\News;
use App\Models\ReadedNews;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Lib\StrUtil;
use App\Lib\FileUtil;
use Illuminate\Http\Request;
use App\Consts\ShopConsts;
use App\Consts\CommonConsts;
use App\Models\ReadedMemberNews;
use App\Models\ReadedShopNews;

class NewsRepository implements NewsRepositoryInterface
{
    /**
     * @var App\Models\Project
     */
    private $project;

    public function __construct(News $project) {
        $this->project = $project;
    }


    public function getAll()
    {
        return  DB::table('news')->where('del_flg',\CommonConsts::DEL_OFF)->orderBy('updated_at','desc')->paginate(\ShopConsts::PAGENATION_COUNT);
    }


    public function getAllForShop()
    {
        return  DB::table('news')->where('type','<>',\NewsConsts::Type_2)->where('submit_date','<=',now())->where('del_flg',\CommonConsts::DEL_OFF)->orderBy('updated_at','desc')->paginate(\ShopConsts::PAGENATION_COUNT);
    }


    public function getAllForMember()
    {
        return  DB::table('news')->where('type','<>',\NewsConsts::Type_3)->where('submit_date','<=',now())->where('del_flg',\CommonConsts::DEL_OFF)->orderBy('updated_at','desc')->paginate(\ShopConsts::PAGENATION_COUNT);
    }


    public function findById($id)
    {
        $records =  News::find($id);
        return $records;

    }

     /**
     * ニュースを登録する
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

        $data = News::Create(
            $request->all()
        );
/*
        $project = $this->project->create($data);
        return $project;
*/

    }

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


    public function findNewsForShopTop()
    {
        $records = DB::table('news')->where('type','<>',\NewsConsts::Type_2)
                                    ->where('del_flg',\CommonConsts::DEL_OFF)
                                    ->where('submit_date','>=',now())
                                    ->orderBy('updated_at','desc')
                                    ->get();


        return $records;
    }

    public function findNewsForShopList()
    {
        $records = DB::table('news')->where('type',)
                                    ->where('del_flg',\CommonConsts::DEL_OFF)
                                    ->orderBy('updated_at','desc')->paginate(\ShopConsts::PAGENATION_COUNT);

        return $records;
    }


    public function saveNewsForShop(Request $request,$news_id,$shop_id)
    {

          ReadedShopNews::updateOrCreate(
              ['news_id' => $news_id,'shop_id' => $shop_id], 
              ['is_readed'=>1]
          );

    }

    public function saveNewsForMember(Request $request,$news_id,$member_id)
    {


          ReadedMemberNews::updateOrCreate(
              ['news_id' => $news_id,'member_id' => $member_id], 
              ['is_readed'=>1]
          );




    }
}
