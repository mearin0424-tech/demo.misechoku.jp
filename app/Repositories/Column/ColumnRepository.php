<?php
namespace App\Repositories\Column;

use App\Models\Column;
use App\Models\ColumnCategory;
use App\Models\ColumnTag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Lib\StrUtil;
use App\Lib\FileUtil;
use Illuminate\Http\Request;
use App\Consts\ShopConsts;
use App\Consts\CommonConsts;

class ColumnRepository implements ColumnRepositoryInterface
{

    /**
     * @var App\Models\Project
     */
    private $project;
    private $project2;
    private $project3;

    public function __construct(Column $project,ColumnCategory $project2,ColumnTag $project3) {
        $this->project  = $project;
        $this->project2 = $project2;
        $this->project3 = $project3;
    }

    public function getColumns()
    {
        return  $this->project->where('del_flg',\CommonConsts::DEL_OFF)->paginate(\ShopConsts::PAGENATION_COUNT);
    }

    public function getColumnsForTop()
    {
         $records =  Column::leftJoin('column_categories', 'columns.column_category_id', '=', 'column_categories.id')
                     ->where('columns.del_flg',\CommonConsts::DEL_OFF)
                     ->where('posted_date_time', '<=', NOW())
                     ->select('columns.id as id','column_categories.name as name','columns.*')
                     ->get();

         return $records;
    }

    public function findColumnsWithCategoryDetails($category_id=null,$isAdmin=true)
    {
         $records =  Column::leftJoin('column_categories', 'columns.column_category_id', '=', 'column_categories.id')
                     ->where('columns.del_flg',\CommonConsts::DEL_OFF)
                     //->where('column_categories.del_flg',\CommonConsts::DEL_OFF);
                     ->where('posted_date_time', '<=', NOW())
                     ->orderBy('posted_date_time','desc');

                     if(!$isAdmin) {
                         $records->where('columns.release',\CommonConsts::RELEASE_ON);
                     }
                     if($category_id) {
                         $records->where('columns.column_category_id',$category_id);
                     }

                     $records->select('columns.id as id','column_categories.name as name','columns.*');

        return $records->paginate(\ShopConsts::PAGENATION_COUNT);
    }

    public function findColumnsWithTagDetails($id)
    {
         $records =  Column::leftJoin('column_categories', 'columns.column_category_id', '=', 'column_categories.id')
                     ->where('columns.del_flg',\CommonConsts::DEL_OFF)
                     //->where('column_categories.del_flg',\CommonConsts::DEL_OFF)
                     ->where('columns.release',\CommonConsts::RELEASE_ON)
                     ->where('posted_date_time', '<=', NOW())
                     ->whereIn('columns.column_tag_id', function($query) use ($id) {
                         $query->select('id')
                           ->from('column_tags')
                           ->where('id',$id);
                      //     ->where('column_tags.del_flg',\CommonConsts::DEL_OFF);
                      })
                     ->select('columns.id as id','column_categories.name as name','columns.*')
                     ->paginate(\ShopConsts::PAGENATION_COUNT);
        return $records;
    }

    public function findColumnById($id)
    {
        $records =  Column::find($id);
        return $records;

    }

    public function findCategoryName($name)
    {
        $records =  ColumnCategory::where('name',$name)->first();
        return $records;

    }

    public function findCategoryDirectory($name)
    {
        $records =  ColumnCategory::where('directory',$name)->first();
        return $records;

    }

    public function findTagName($name)
    {
        $records =  ColumnTag::where('name',$name)->first();
        return $records;

    }

    public function findTagDirectory($name)
    {
        $records =  ColumnTag::where('directory',$name)->first();
        return $records;

    }

    public function storeColumn(Request $request,$id=null){


        try{
            $data = $request->all();
            //$data['release'] = $data['publish'] ;
            $content = $request->body;

            if (!empty($content)) {

                $dom = new \DomDocument();
                libxml_use_internal_errors( true );
                $dom->loadHtml($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                libxml_clear_errors();
                $imageFile = $dom->getElementsByTagName('img');

                foreach($imageFile as $item => $image){

                  try{

                       $data2 = $image->getAttribute('src');
                       if ( strpos($data2,'assets') > 0) {
                            continue;
                       }
                       list($type, $data2) = explode(';', $data2);
                       list(, $data2)      = explode(',', $data2);
                       $imgeData = base64_decode($data2);
                       $image_name = \CommonConsts::COLUMN_DIR. time().'.png';
                       Storage::put(\CommonConsts::COLUMN_DIR. time().'.png', $imgeData);
                       $image->removeAttribute('src');
                       $image->setAttribute('src', $image_name);

                   }catch(Exception $e){
                       return false;
                   }

                }

            }

            $f = new FileUtil();
            if (!empty($_FILES['file_1']['tmp_name'])) {
                $sub_img = $f->getOriginalFileName($request,'file_1');
                $f->uploade($request , 'file_1', \CommonConsts::COLUMN_DIR);
                $data['file'] = $sub_img;
            }else {
                $data['file'] = $data['hidd_file_1'];
            }

            if (!empty($data['column_tag_id']) && is_array($data['column_tag_id'])) {
                $data['column_tag_id'] = implode(',', $data['column_tag_id']);
            } else {
                $data['column_tag_id'] = null; // hoặc chuỗi rỗng ''
            }

            if(empty($id)) {
                $this->project->create($data);
            }else{
                Column::updateOrCreate(
                    ['id' => $id],
                    $data
                );
            }

        }catch(Exception $e){
           return false;
        }
        return true;
    }


    public function deleteColumn(Request $request,$id){

        try{
            $data = $request->all();
            Column::updateOrCreate(
                ['id' => $id],
                ['del_flg' => \CommonConsts::DEL_ON]
            );

        }catch(Exception $e){
           return false;
        }
        return true;
    }

    /**
    *
    * カテゴリ
    *
    */
    public function getCategories($usePagination = true)
    {
        $query = $this->project2->where('del_flg',\CommonConsts::DEL_OFF);
        return $usePagination ? $query->paginate(\ShopConsts::PAGENATION_COUNT) : $query->get();

    }

    public function findCategoryById($id)
    {
        $records =  ColumnCategory::find($id);
        return $records;

    }

    public function storeColumnCategory(Request $request){

        try{
            $data = $request->all();
            $this->project2->create($data);
        }catch(Exception $e){
           return false;
        }
        return true;
    }

    public function updateColumnCategory(Request $request,$id){

        try{

            $data = $request->all();
            ColumnCategory::updateOrCreate(
                ['id' => $id],
                $data
            );

        }catch(Exception $e){
           return false;
        }
        return true;
    }

    public function deleteColumnCategory(Request $request,$id){

        try{
            $data = $request->all();
            ColumnCategory::updateOrCreate(
                ['id' => $id],
                ['del_flg' => \CommonConsts::DEL_ON]
            );

        }catch(Exception $e){
           return false;
        }
        return true;
    }


    /**
    *
    * タグ
    *
    */
    public function getTags($usePagination=true)
    {
        $query = $this->project3->where('del_flg',\CommonConsts::DEL_OFF);
        return $usePagination ? $query->paginate(\ShopConsts::PAGENATION_COUNT) : $query->get();
    }

    public function findTagById($id)
    {
        $records =  ColumnTag::find($id);
        return $records;

    }

    public function storeColumnTag(Request $request){

        try{
            $data = $request->all();
            $this->project3->create($data);
        }catch(Exception $e){
           return false;
        }
        return true;
    }

    public function updateColumnTag(Request $request,$id){

        try{

            $data = $request->all();
            ColumnTag::updateOrCreate(
                ['id' => $id],
                $data
            );

        }catch(Exception $e){
           return false;
        }
        return true;
    }

    public function deleteColumnTag(Request $request,$id){

        try{
            $data = $request->all();
            ColumnTag::updateOrCreate(
                ['id' => $id],
                ['del_flg' => \CommonConsts::DEL_ON]
            );

        }catch(Exception $e){
           return false;
        }
        return true;
    }

    /**
    * ユーザー
    */
    public function sameCategoryColumne(Request $request,$column_category_id){
        return Column::where('del_flg',\CommonConsts::DEL_OFF)->where('column_category_id',$column_category_id)->get();
    }




























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

    }
}
