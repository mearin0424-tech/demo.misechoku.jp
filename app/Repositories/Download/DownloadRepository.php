<?php
namespace App\Repositories\Download;

use App\Models\Download;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Lib\StrUtil;
use App\Lib\FileUtil;
use Illuminate\Http\Request;


class DownloadRepository implements DownloadRepositoryInterface
{
    /**
     * @var App\Models\Project
     */
    private $project;

    public function __construct(Download $project) {
        $this->project = $project;
    }


    public function getAll()
    {
        return Download::where('del_flg',\CommonConsts::DEL_OFF)->orderBy('updated_at','desc')->paginate(\ShopConsts::PAGENATION_COUNT);
    }


    public function getAllForShop()
    {
        return  Download::where('del_flg',\CommonConsts::DEL_OFF)->where('posted_date_time','<=',now())->where('release',\CommonConsts::RELEASE_ON)->orderBy('updated_at','desc')->paginate(\ShopConsts::PAGENATION_COUNT);
    }


    public function getAllForMember()
    {
        return  DB::table('news')->where('type','<>',\NewsConsts::Type_3)->where('del_flg',\CommonConsts::DEL_OFF)->orderBy('updated_at','desc')->paginate(\ShopConsts::PAGENATION_COUNT);
    }


    public function findById($id)
    {
        $records =  Download::find($id);
        return $records;

    }

     /**
     * 資料を登録する
     *
     * @param array $data
     * @return Project
     */
    public function store(Request $request)
    {

        $f = new FileUtil();
        $file_name=$f->getOriginalFileName($request,"file");

        $f->uploade($request , "file" , \CommonConsts::DOWNLOAD_DIR.'/');
        $request['file_name'] = $file_name;
        $data = Download::Create(
            $request->all()
        );
/*
        $project = $this->project->create($data);
        return $project;
*/

    }

    public function save(Request $request,$id)
    {

        $f = new FileUtil();
        $file_name=$f->getOriginalFileName($request,"file");

        $f->uploade($request , "file" , \CommonConsts::DOWNLOAD_DIR.'/');
        $request['file_name'] = $file_name;

        Download::updateOrCreate(
            ['id' => $id], 
            $request->all()
        );


    }

    public function del($id)
    {
        Download::where('id', $id)->delete();
    }

/*
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

        $data = ReadedNews::Create(
           ['news_id' => $news_id,'shop_id'=>$shop_id],
        );

    }

    public function saveNewsForMember(Request $request,$news_id,$member_id)
    {

        $data = ReadedNews::Create(
           ['news_id' => $news_id,'member_id'=>$member_id],
        );

    }
*/
/*

   public function findNewsShop($member_id)
    {

        $records = DB::table('footprints')
                   ->join('shops', 'footprints.shop_id', '=', 'shops.id')
                   ->where('footprints.member_id',$member_id)
                   ->groupby('shops.id')
                   ->select('shops.*')
                   ->get();

        return $records;

    }

    public function findNewsMember($shop_id)
    {

        $records = DB::table('footprints')
                   ->join('mmembers', 'footprints.member_id', '=', 'members.id')
                   ->where('footprints.shop_id',$shop_id)
                   ->select('members.*')
                   ->get();

        return $records;

    }

    public function findNewsByMemberId($member_id)
    {

        $records = DB::table('footprints')
                   ->join('shops', 'footprints.shop_id', '=', 'shops.id')
                   ->where('footprints.member_id',$member_id)
                   ->select('shops.*')
                   ->get();

        return $records;

    }

    public function findNewsByShopId($shop_id)
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
