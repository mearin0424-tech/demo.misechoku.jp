<?php

namespace App\Repositories\Shop;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Requests\Shop\StoreRequest;
use App\Lib\StrUtil;
use App\Lib\FileUtil;
use App\Consts\ShopConsts;
use App\Models\Shop;
use App\Models\ShopSubImage;
use App\Models\ShopIndustry;
use App\Models\ShopManager;
use App\Models\WShop;
use App\Models\WShopSubImage;
use App\Models\WShopIndustry;
use App\Models\InformationByShop;
use App\Models\JOB;
use App\Models\Review;
use App\Models\Follow;
use App\Models\Good;
use App\Models\Like;
use App\Models\Report;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\TodayCentensByShop;

class ShopRepository implements ShopRepositoryInterface
{
    /**
     * @var App\Models\Project
     */
    private $project;
    private $distance_arr = [];
    public function __construct(Shop $project)
    {
        $this->project = $project;
    }

    /**
     * 店舗を全件取得する
     *
     * @return Collection
     */
    public function getAll()
    {
        //return $this->project->get();//->orderBy('updated_at', 'desc');
        return  DB::table('shops')->orderBy('updated_at', 'desc')->paginate(\ShopConsts::PAGENATION_COUNT);
    }

    /**
     * 更新情報を全件取得する
     *
     * @return Collection
     */
    /*
    public function getAllInformation()
    {
        //return  DB::table('information_by_shops')->orderBy('updated_at','desc')->paginate(\ShopConsts::PAGENATION_COUNT);
        $records =  Shop::Join('information_by_shops', 'shops.id', '=', 'information_by_shops.shop_id')
                   ->orderBy('information_by_shops.updated_at','desc')->select("shops.*","information_by_shops.*");
        return $records->paginate(\ShopConsts::PAGENATION_COUNT);

    }
*/
    public function getAllInformation($shop_id)
    {
        $records =  Shop::Join('information_by_shops', 'shops.id', '=', 'information_by_shops.shop_id')
            ->where('shops.id', $shop_id)
            ->orderBy('information_by_shops.updated_at', 'desc')
            ->select("shops.*", "information_by_shops.*");
        return $records->paginate(\ShopConsts::PAGENATION_COUNT);
    }
    public function getAllInformation2()
    {


        $records = DB::table('shops')
            ->join(
                \DB::raw('(SELECT shop_id , SUM(status = 0) AS status0_count, SUM(status = 1) AS status1_count , count(*) as cnt FROM information_by_shops GROUP BY shop_id) AS information_by_shops'),
                'shops.id',
                '=',
                'information_by_shops.shop_id'
            )
            ->select("shops.*", "information_by_shops.*", "shops.id as shop_id");

        return $records->paginate(\ShopConsts::PAGENATION_COUNT);
    }

    public function searchAllInformation2($request)
    {

        $query = DB::table('shops')
            ->join(
                \DB::raw('(SELECT MAX(seq) AS seq_id ,shop_id FROM information_by_shops GROUP BY seq,shop_id) AS latest'),
                'shops.id',
                '=',
                'latest.shop_id'
            );

        if ($request->id) {
            $query->where('shops.id', $request->id);
        }
        if ($request->shop_name) {
            $query->where('shops.shop_name', 'like', $request->shop_name . '%');
        }
        if ($request->pref) {
            $query->where('shops.pref', $request->pref);
        }

        if ($request->addr1) {
            $query->where('shops.addr1', 'like', $request->addr1 . '%');
        }
        if ($request->status) {
            $query->where('shops.status', $request->status);
        }
        if ($request->approval) {
            $query->where('shops.approval', $request->approval);
        }
        if ($request->release) {
            $query->where('shops.release', $request->release);
        }
        $records = $query->select("shops.*", "latest.*", "shops.id as shop_id", "seq_id");



        return $records->paginate(\ShopConsts::PAGENATION_COUNT);
    }

    /*
    public function getAllInformationBySeq($seq)
    {
        $records =  Shop::Join('information_by_shops', 'shops.id', '=', 'information_by_shops.shop_id')
                   ->where('information_by_shops.seq',$seq)
                   ->orderBy('information_by_shops.updated_at','desc')
                   ->select("shops.*","information_by_shops.*");f
        return $records->paginate(\ShopConsts::PAGENATION_COUNT);

    }
*/
    /*
    public function findInformationByID($id)
    {
        $records =  Shop::Join('information_by_shops', 'shops.id', '=', 'information_by_shops.shop_id')
                   ->where('information_by_shops.id',$id)
                   ->orderBy('information_by_shops.updated_at','desc')
                   ->select("shops.*","information_by_shops.*");
        return $records->paginate(\ShopConsts::PAGENATION_COUNT);

    }
*/


    public function getInformationById($id)
    {
        $records =  Information::find($id);
        return $records;
    }

    /**
     * レビュー情報を全件取得する
     *
     * @return Collection
     */
    public function getAllReview()
    {

        $records = DB::table('shops')
            ->join('reviews', 'shops.id', '=', 'reviews.shop_id')
            ->join(
                DB::raw('(SELECT shop_id, MAX(id) as max_id FROM reviews GROUP BY shop_id) as latest'),
                function ($join) {
                    $join->on('reviews.shop_id', '=', 'latest.shop_id')
                        ->on('reviews.id', '=', 'latest.max_id');
                }
            )
            ->select('shops.*', 'shops.id as shop_id');

        return $records->paginate(\ShopConsts::PAGENATION_COUNT);
    }


    public function searchAllReview($request)
    {

        $query = DB::table('shops')
            ->join(
                \DB::raw('(SELECT MAX(id) AS max_id FROM reviews GROUP BY id) AS latest'),
                'shops.id',
                '=',
                'latest.max_id'
            );
        if ($request->id) {
            $query->where('shops.id', $request->id);
        }
        if ($request->shop_name) {
            $query->where('shops.shop_name', 'like', $request->shop_name . '%');
        }
        if ($request->pref) {
            $query->where('shops.pref', $request->pref);
        }

        if ($request->addr1) {
            $query->where('shops.addr1', 'like', $request->addr1 . '%');
        }
        if ($request->status) {
            $query->where('shops.status', $request->status);
        }
        if ($request->approval) {
            $query->where('shops.approval', $request->approval);
        }
        if ($request->release) {
            $query->where('shops.release', $request->release);
        }

        $records = $query->select("shops.*", "shops.id as shop_id");

        return $records->paginate(\ShopConsts::PAGENATION_COUNT);
    }


    public function getReviewByShopId($shop_id)
    {
        $records =  DB::table('members')->join('reviews', 'members.id', '=', 'reviews.member_id')
            ->where('reviews.shop_id', $shop_id)->orderBy('reviews.updated_at', 'desc')
            ->select("members.*", "reviews.*", 'reviews.id as review_id', 'reviews.member_id as member_id');
        return $records->paginate(\ShopConsts::PAGENATION_COUNT);
    }

    public function getReviewShopAll($shop_id)
    {
        /*
        $records =  DB::table('members')->join('reviews', 'members.id', '=', 'reviews.member_id')
                   ->where('reviews.shop_id',$shop_id)->orderBy('reviews.updated_at','desc')
                   ->select("members.*","reviews.*",'reviews.id as review_id','reviews.member_id as member_id');
*/
        $records =  DB::table('reviews')->where('reviews.shop_id', $shop_id)->orderBy('reviews.updated_at', 'desc')
            ->get();

        return $records;
    }

    public function getReviewDetailShopAll($review_id)
    {

        $records =  DB::table('review_details')->where('review_id', $review_id)->orderBy('updated_at', 'desc')
            ->get();

        return $records;
    }

    public function getRecruitByShopId($shop_id)
    {
        $records =  DB::table('applies')
            ->join('members', 'applies.member_id', '=', 'members.id')
            ->join('shops', 'applies.shop_id', '=', 'shops.id')
            ->leftJoin('deposits', 'deposits.apply_id', '=', 'applies.id')
            ->select(
                'members.*',
                'applies.*',
                'members.id as member_id',
                'shops.id as shop_id',
                'applies.id as apply_id',
                'applies.about_recruit',
                'shops.pref as s_pref',
                'shops.addr1 as s_addr1',
                'deposits.created_at as d_created_at',
            )
            ->where('applies.shop_id', $shop_id)
            ->orderBy('applies.id', 'desc');
        return $records->paginate(\ShopConsts::PAGENATION_COUNT);
    }


    /**
     * 退会店舗を全件取得する
     *
     * @return Collection
     */
    public function getAllWithdrawal()
    {
        return  DB::table('shops')->where('withdrawal', \ShopConsts::WITHDRAWAL_YET)->orWhere('withdrawal', \ShopConsts::WITHDRAWAL_DONE)->orderBy('updated_at', 'desc')->paginate(\ShopConsts::PAGENATION_COUNT);
    }

    public function searchAllWithdrawal($request)
    {

        $query = DB::table('shops')->where('withdrawal', \ShopConsts::WITHDRAWAL_YET)->orWhere('withdrawal', \ShopConsts::WITHDRAWAL_DONE);

        if ($request->id) {
            $query->where('shops.id', $request->id);
        }
        if ($request->shop_name) {
            $query->where('shops.shop_name', 'like', $request->shop_name . '%');
        }
        if ($request->pref) {
            $query->where('shops.pref', $request->pref);
        }

        if ($request->addr1) {
            $query->where('shops.addr1', 'like', $request->addr1 . '%');
        }
        if ($request->status) {
            $query->where('shops.status', $request->status);
        }
        if ($request->approval) {
            $query->where('shops.approval', $request->approval);
        }
        if ($request->release) {
            $query->where('shops.release', $request->release);
        }
        $query->orderBy('updated_at', 'desc');
        $records = $query->select("shops.*", "shops.id as shop_id");

        return $records->paginate(\ShopConsts::PAGENATION_COUNT);
    }

    public function BeroreApprova()
    {
        return  DB::table('shops')->where('approval', '<=', \ShopConsts::APPROVAL_OFF2)->orderBy('updated_at', 'desc')->paginate(\ShopConsts::PAGENATION_COUNT);
    }

    public function newApprovaCnt()
    {
        return  Shop::where('approval','<=', \ShopConsts::APPROVAL_OFF2)->count();
    }


    public function findByBkShop($status)
    {
        return  DB::table('shops')->where('approval', \ShopConsts::APPROVAL_OFF2)->orderBy('updated_at', 'desc')->paginate(\ShopConsts::PAGENATION_COUNT);
    }

    /**
     *
     * 店舗を新規登録する
     *
     * @param array $data
     * @return Project
     */
    public function store(array $data, Request $request): Shop
    {


        $s = new StrUtil();

        // 店舗登録
        if ($request['open_year'] != "" && $request['open_month'] != "" && $request['open_day'] != "") {
            $data['open_day'] = $s->yyyymmdd_to_date($request['open_year'], $request['open_month'], $request['open_day']);
        }
        // ファイル名
        $f = new FileUtil();
        foreach (ShopConsts::SHOP_FILE_NAMES as $index => $name) {
            if (empty($_FILES[$name]['tmp_name'])) continue;
            $data[$name] = $f->getOriginalFileName($request, $name);
        }

        list($latitude, $longitude) = \ShopInfoUtil::latLng($request['pref'], $request['city'], $request['addr2'], $request['addr3']);

        if ($request['status'] == 1) {
            $data['approval'] = \ShopConsts::APPROVAL_ON;
            //$data['release']=\ShopConst::APPROVAL_ON;

        } else {
            $data['approval'] = \ShopConsts::APPROVAL_OFF;
        }
        $data['latitude'] = $latitude;
        $data['longitude'] = $longitude;

        $data['station1'] = $request['station1'];
        $data['station2'] = $request['station2'];
        $data['station3'] = $request['station3'];
        $data['station4'] = $request['station4'];
        $data['station5'] = $request['station5'];
        $data['message'] = $request['message'];


        $project = $this->project->create($data);

        $shop_id = $project['id'];

        // ファイルアップロード　メイン、営業、風営法許可証
        foreach (ShopConsts::SHOP_FILE_NAMES as $index => $name) {
            if (empty($_FILES[$name]['tmp_name'])) continue;
            $f->uploade($request, $name, ShopConsts::SHOP_MAIN_IMG_DIR . $shop_id . '/' . ShopConsts::SHOP_DIR_NAMES[$index]);
        }
        // サブファイルアップロード
        $this->saveShopSubImage($request, $shop_id);

        // 業種登録
        $shopindustry = new ShopIndustry();
        foreach ($data['industry'] as $key => $val) {
            $shopindustry->create([
                'shop_id' => $shop_id,
                'industry_id' => $key,
                'name' => $val,
            ]);
        }

        ShopManager::create(array('shop_id' => $shop_id, 'email' => $request['email'], 'password' => Hash::make($request['password']), 'plain_password' => $request['password']));


        return $project;
    }


    public function save(Request $request, $shop_id)
    {

        $res = $this->saveShop($request, $shop_id);
        $this->saveShopIdentity($request, $shop_id);

        return $res;
    }


    public function saveShop($request, $shop_id)
    {
        $s = new StrUtil();

        if ($request['open_year'] != "" && $request['open_month'] != "" && $request['open_day'] != "") {
            $data['open_day'] = $s->yyyymmdd_to_date($request['open_year'], $request['open_month'], $request['open_day']);
        }
        $f = new FileUtil();
        $data = [];

        foreach (ShopConsts::SHOP_FILE_NAMES as $index => $name) {
            if (empty($_FILES[$name]['tmp_name'])) continue;
            if (empty(ShopConsts::SHOP_FILE_NAMES_HID[$index])) continue;
            $data[$name] = $f->getOriginalFileName($request, $name);
            $f->uploade($request, $name, ShopConsts::SHOP_MAIN_IMG_DIR . $shop_id . '/' . ShopConsts::SHOP_DIR_NAMES[$index]);
            if ($index == 0) {
                $request->session()->put('shop_main_img', $data[$name]);
            }
        }

        // サブファイルアップロード
        $this->saveShopSubImage($request, $shop_id);

        list($latitude, $longitude) = \ShopInfoUtil::latLng($request['pref'], $request['city'], $request['addr2'], $request['addr3']);

        $data['latitude'] = $latitude;
        $data['longitude'] = $longitude;
        $arr = $request->all();

        if (!empty($request['hid_shop_main_img'])) $arr['shop_main_img'] = $request['hid_shop_main_img'];
        if (!empty($request['hid_shop_license_img'])) $arr['shop_license_img'] = $request['hid_shop_license_img'];
        if (!empty($request['hid_shop_license2_img'])) $arr['shop_license2_img'] = $request['hid_shop_license2_img'];

        $request->session()->put('shop_name', $arr['shop_name']);
        $request->session()->put('pref', $arr['pref']);
        $request->session()->put('city', $arr['city']);
        $request->session()->put('addr1', $arr['addr1']);
        $request->session()->put('addr2', $arr['addr2']);
        $request->session()->put('addr3', $arr['addr3']);
        $request->session()->put('approval', $arr['approval']);

        Shop::updateOrCreate(
            ['id' => $shop_id],
            array_merge($arr, $data)
        );
    }

    public function saveShopW($request, $shop_id)
    {
        $s = new StrUtil();
        /*
        $request['open_day'] = $s->yyyymmdd_to_date($request['open_y'],$request['open_m'],$request['open_d']);

        $f = new FileUtil();
        $data = [];

        foreach(ShopConsts::SHOP_FILE_NAMES as $index => $name){
            if(empty($_FILES[$name]['tmp_name'])) continue;
            if(empty(ShopConsts::SHOP_FILE_NAMES_HID[$index])) continue;
            $data[$name] = $f->getOriginalFileName($request,$name);
            $f->uploade($request , $name , ShopConsts::SHOP_MAIN_IMG_DIR.$shop_id.'/'.ShopConsts::SHOP_DIR_NAMES[$index]);
        }


        // サブファイルアップロード
        $this->saveShopSubImageW($request,$shop_id);
        list($latitude,$longitude) = \ShopInfoUtil::latLng($request['pref'], $request['city'], $request['addr2'], $request['addr3']);

        $data['latitude'] = $latitude;
        $data['longitude'] = $longitude;

        if(!empty($request['hid_shop_main_img'])) $arr['shop_main_img'] = $request['hid_shop_main_img'];
        if(!empty($request['hid_shop_license_img'])) $arr['shop_license_img'] = $request['hid_shop_license_img'];
        if(!empty($request['hid_shop_license2_img'])) $arr['shop_license2_img'] = $request['hid_shop_license2_img'];
*/

        $w_file_name_pre = "w_file_name_pre_";
        $shop_data = Shop::find($shop_id);
        $shop_data_arr = [];
        if (!empty($shop_data->attributesToArray()['shop_main_img'])) {
            $shop_data_arr['shop_main_img'] = $w_file_name_pre . $shop_data->attributesToArray()['shop_main_img'];
        }


        if (!empty($shop_data->attributesToArray()['shop_licence_img'])) {
            $shop_data_arr['shop_licence_img'] = $w_file_name_pre . $shop_data->attributesToArray()['shop_licence_img'];
        }


        if (!empty($shop_data->attributesToArray()['shop_licence2_img'])) {
            $shop_data_arr['shop_licence2_img'] = $w_file_name_pre . $shop_data->attributesToArray()['shop_licence2_img'];
        }

        list($latitude, $longitude) = \ShopInfoUtil::latLng($request['pref'], $request['city'], $request['addr2'], $request['addr3']);
        $shop_data_arr['latitude'] = $latitude;
        $shop_data_arr['longitude'] = $longitude;

        $res = WShop::updateOrCreate(
            ['id' => $shop_id],
            array_merge($shop_data_arr, $shop_data->attributesToArray())
        );

        if (Storage::exists(ShopConsts::SHOP_MAIN_IMG_DIR . $shop_id . '/' . ShopConsts::SHOP_DIR_NAMES_MAIN . '/' . $shop_data->attributesToArray()['shop_main_img'])) {
            Storage::copy(
                ShopConsts::SHOP_MAIN_IMG_DIR . $shop_id . '/' . ShopConsts::SHOP_DIR_NAMES_MAIN . '/' . $shop_data->attributesToArray()['shop_main_img'],
                ShopConsts::SHOP_MAIN_IMG_DIR . $shop_id . '/' . ShopConsts::SHOP_DIR_NAMES_MAIN . '/' . $w_file_name_pre . $shop_data->attributesToArray()['shop_main_img']
            );
        }

        if (Storage::exists(ShopConsts::SHOP_MAIN_IMG_DIR . $shop_id . '/' . ShopConsts::SHOP_DIR_NAMES_MIC1 . '/' . $shop_data->attributesToArray()['shop_license_img'])) {
            Storage::copy(
                ShopConsts::SHOP_MAIN_IMG_DIR . $shop_id . '/' . ShopConsts::SHOP_DIR_NAMES_MIC1 . '/' . $shop_data->attributesToArray()['shop_license_img'],
                ShopConsts::SHOP_MAIN_IMG_DIR . $shop_id . '/' . ShopConsts::SHOP_DIR_NAMES_MAIN . '/' . $w_file_name_pre . $shop_data->attributesToArray()['shop_license_img']
            );
        }

        if (Storage::exists(ShopConsts::SHOP_MAIN_IMG_DIR . $shop_id . '/' . ShopConsts::SHOP_DIR_NAMES_MIC2 . '/' . $shop_data->attributesToArray()['shop_license2_img'])) {
            Storage::copy(
                ShopConsts::SHOP_MAIN_IMG_DIR . $shop_id . '/' . ShopConsts::SHOP_DIR_NAMES_MIC2 . '/' . $shop_data->attributesToArray()['shop_license2_img'],
                ShopConsts::SHOP_MAIN_IMG_DIR . $shop_id . '/' . ShopConsts::SHOP_DIR_NAMES_MIC2 . '/' . $w_file_name_pre . $shop_data->attributesToArray()['shop_license2_img']
            );
        }


        $image_data = ShopSubImage::where("shop_id", $shop_id)->get();
        if (!$image_data->isEmpty()) {

            WShopSubImage::where('shop_id', $shop_id)->delete();
            foreach ($image_data as $val) {
                WShopSubImage::updateOrCreate(
                    ['id' => $shop_id],
                    $val->toArray()
                );
            }
        }

        $wshop_sub_image = WShopSubImage::where('shop_id', $shop_id)->get();
        foreach ($wshop_sub_image as $val) {

            if (Storage::exists(ShopConsts::SHOP_MAIN_IMG_DIR . $shop_id . '/' . ShopConsts::SHOP_DIR_NAMES_MAIN . '/' . $val->sub_img)) {
                Storage::copy(
                    ShopConsts::SHOP_MAIN_IMG_DIR . $shop_id . '/' . ShopConsts::SHOP_DIR_NAMES_MAIN . '/' . $val->sub_img,
                    ShopConsts::SHOP_MAIN_IMG_DIR . $shop_id . '/' . ShopConsts::SHOP_DIR_NAMES_MAIN . '/' . $w_file_name_pre . $val->sub_img
                );
            }
        }

        $industry_data = ShopIndustry::where("shop_id", $shop_id)->get();
        if (!$industry_data->isEmpty()) {

            WShopIndustry::where('shop_id', $shop_id)->delete();
            foreach ($industry_data as $val) {

                $res = WShopIndustry::updateOrCreate(
                    ['id' => $shop_id],
                    $val->toArray()
                );
            }
        }
    }

    public function createShop($request)
    {

        // ファイル名
        $f = new FileUtil();
        foreach (ShopConsts::SHOP_FILE_NAMES as $index => $name) {
            if (empty($_FILES[$name]['tmp_name'])) continue;
            $data[$name] = $f->getOriginalFileName($request, $name);
        }

        list($latitude, $longitude) = \ShopInfoUtil::latLng($request['pref'], $request['city'], $request['addr2'], $request['addr3']);
        $data['latitude'] = $latitude;
        $data['longitude'] = $longitude;

        $project = Shop::Create(
            array_merge($request->all(), $data)
        );
        $shop_id = $project['id'];

        // ファイルアップロード　メイン、営業、風営法許可証
        foreach (ShopConsts::SHOP_FILE_NAMES as $index => $name) {
            if (empty($_FILES[$name]['tmp_name'])) continue;
            $f->uploade($request, $name, ShopConsts::SHOP_MAIN_IMG_DIR . $shop_id . '/' . ShopConsts::SHOP_DIR_NAMES[$index]);
        }
        return $shop_id;
    }

    public function createShopW($request, $shop_id)
    {
        WShop::updateOrCreate(
            ['id' => $shop_id],
            $request->all()
        );
    }
    public function saveShopSubImage($request, $shop_id)
    {

        $imageOrder = $request->input('image_order') ? explode(',', $request->input('image_order')) : range(0, \ShopConsts::SHOP_SUB_IMAGE_FILE_CNT - 1);

        ShopSubImage::where('shop_id', $shop_id)->delete();

        $f = new FileUtil();
        $ssi = new ShopSubImage();

        foreach ($imageOrder as $position => $index) {

            $fileInputName = ShopConsts::SHOP_FILE_NAME_MULTIPLE . $index;

            if (!empty($_FILES[$fileInputName]['tmp_name'])) {
                // ファイルがアップロードされている場合
                $sub_img = $f->getOriginalFileName($request, $fileInputName);

                // 画像ファイルを指定ディレクトリにアップロード
                $f->uploade($request, $fileInputName, ShopConsts::SHOP_MAIN_IMG_DIR . $shop_id . '/' . ShopConsts::SHOP_DIR_NAMES_MAIN);
                $is_main = 0;
                $main_order = null;
            } elseif (!empty($request['hidd_file_name' . $index])) {
                // ファイルが既存の場合（hiddenフィールドから取得）
                $sub_img = $request['hidd_file_name' . $index];
                $is_main = $request['hidd_is_main' . $index];
                $main_order = $request['hidd_main_order' . $index];

            } else {
                // ファイルがない場合はスキップ
                continue;
            }
            if( $request['hidd_main_order' . $index]==1) {
                Shop::find($shop_id)->update(['shop_main_img' => $sub_img]);
            }
            $ssi->create(["shop_id" => $shop_id, "sub_img" => $sub_img,"main_order" => $main_order,"is_main"=>$is_main]);
            if (empty($_FILES[ShopConsts::SHOP_FILE_NAME_MULTIPLE . $index]['tmp_name'])) continue;
            $f->uploade($request, ShopConsts::SHOP_FILE_NAME_MULTIPLE . $index, ShopConsts::SHOP_MAIN_IMG_DIR . $shop_id . '/' . ShopConsts::SHOP_DIR_NAMES_MAIN);
        }
    }

    public function saveShopSubImageOrder($request, $shop_id)
    {

        // order_data を JSON からデコード
        $orderData = json_decode($request->input('order_data'), true);

        ShopSubImage::where('shop_id', $shop_id)->update(['main_order' => null,'is_main'=>'0']);

        foreach ($orderData as $data) {
            ShopSubImage::where('shop_id', $shop_id)
                ->where('sub_img', $data['sub_img'])
                ->update(['main_order' => $data['display_order'],'is_main'=>'1']);
            if($data['display_order']=='1') {
                Shop::find($shop_id)->update(['shop_main_img' => $data['sub_img']]);
            }

        }

    }



    public function saveShopSubImageW($request, $shop_id)
    {

        $w_file_name_pre = "w_file_name_pre_";

        WShopSubImage::where('shop_id', $shop_id)->delete();

        $f = new FileUtil();
        $ssi = new WShopSubImage();
        for ($i = 0; $i < ShopConsts::SHOP_SUB_IMAGE_FILE_CNT; $i++) {

            if (!empty($_FILES[ShopConsts::SHOP_FILE_NAME_MULTIPLE . $i]['tmp_name'])) {
                $sub_img = $f->getOriginalFileName($request, ShopConsts::SHOP_FILE_NAME_MULTIPLE . $i);
            } else if (!empty($request['hidd_file_name' . $i])) {
                $sub_img = $request['hidd_file_name' . $i];
            } else {
                continue;
            }

            $ssi->create(["shop_id" => $shop_id, "sub_img" => $w_file_name_pre . $sub_img,]);

            //if(empty($_FILES[ShopConsts::SHOP_FILE_NAME_MULTIPLE.$i]['tmp_name'])) continue;
            //$f->uploade($request , ShopConsts::SHOP_FILE_NAME_MULTIPLE.$i , ShopConsts::SHOP_MAIN_IMG_DIR.$shop_id.'/'.ShopConsts::SHOP_DIR_NAMES_MAIN);
        }
    }

    public function saveShopIdentity(Request $request, $shop_id)
    {
        ShopIndustry::where('shop_id', $shop_id)->delete();

        $industrys = $request->all()['industry'];

        foreach ($industrys as $key => $val) {
            ShopIndustry::Create(
                ['shop_id' => $shop_id, 'industry_id' => $key + 1],
                array('industry_id' => $val + 1)
            );
        }
    }

    public function saveShopIdentityW(Request $request, $shop_id)
    {

        $industrys = $request->all()['industry'];
        foreach ($industrys as $key => $val) {
            WShopIndustry::updateOrCreate(
                ['shop_id' => $shop_id, 'industry_id' => $key],
                array('industry_id' => $val)
            );
        }
    }

    public function findById($shop_id)
    {
        $records =  Shop::find($shop_id);
        return $records;
    }

    public function requestCancelMembership($request, $shop_id)
    {
        // Get data from request
        $data = $request->only(['request_cancel_member']);

        // Shop update
        Shop::where('id', $shop_id)->update([
            'withdrawal' => \ShopConsts::WITHDRAWAL_YET,
            'request_cancel_member' => $data['request_cancel_member'],
        ]);

        // Can return updated model (if needed)
        return Shop::find($shop_id);
    }


    public function getShopWithJobs($shop_id)
    {
       $records = Shop::leftJoin('jobs', 'shops.id', '=', 'jobs.shop_id')
           ->where('shops.id', $shop_id)
           ->select('shops.*', 'jobs.*', 'shops.id as id', 'jobs.id as jobs_id')
           ->first();
        return $records;

    }

    public function findShopWithManagerById($shop_id)
    {
        $records = Shop::join('shop_managers', 'shops.id', '=', 'shop_managers.shop_id')
            ->where('shops.id', $shop_id)
            ->select('shops.*', 'shop_managers.*', 'shop_managers.id as managers_id')
            ->get();
        return $records;
    }

    public function findByIdFront($shop_id)
    {

/*
        if (Shop::Join('jobs', 'shops.id', '=', 'jobs.shop_id')->where('shops.id', $shop_id)->where('approval', \ShopConsts::APPROVAL_ON)->exists()) {
            $records = Shop::Join('jobs', 'shops.id', '=', 'jobs.shop_id')->where('shops.id', $shop_id)->where('approval', \ShopConsts::APPROVAL_ON)->select('shops.*', 'jobs.*', 'shops.id as id')->first();
        } else {
            if (WShop::Join('jobs', 'w_shops.id', '=', 'jobs.shop_id')->where('w_shops.id', $shop_id)->exists()) {
                $records = WShop::Join('jobs', 'w_shops.id', '=', 'jobs.shop_id')->where('w_shops.id', $shop_id)->select('w_shops.*', 'jobs.*', 'w_shops.id as id')->first();
            } else {
                $records = Shop::where('id', $shop_id)->first();
            }
        }
*/

        if (Shop::Join('jobs', 'shops.id', '=', 'jobs.shop_id')->where('shops.id', $shop_id)->exists()) {
            $records = Shop::Join('jobs', 'shops.id', '=', 'jobs.shop_id')->where('shops.id', $shop_id)->select('shops.*', 'jobs.*', 'shops.id as id')->first();
        } else {
            $records = Shop::where('id', $shop_id)->first();
        }

        return $records;
    }

    public function findJobByShopId($shop_id)
    {
        $records =  DB::table('jobs')->where('shop_id', $shop_id)->first();
        return $records;
    }


    public function findHelpShop()
    {
        //      $records =  Shop::where("help",\RicruitConsts::RECRUITMENT_HELP)->where('release',\ShopConsts::RELEASE_ON)->get();

        $records =  Shop::Join('jobs', 'shops.id', '=', 'jobs.shop_id')
            ->where("help", \RicruitConsts::RECRUITMENT_HELP)
            ->where('release', \ShopConsts::RELEASE_ON)
            ->where('approval', \ShopConsts::APPROVAL_ON)
            ->select("shops.*", "jobs.*", "shops.id as id")->get();

        return $records;
    }


    public function rewarding()
    {

        $records =  Shop::Join('jobs', 'shops.id', '=', 'jobs.shop_id')
            ->where("jobs.noruma_reward", '>=', \ShopConsts::REWARDING)
            ->where('release', \ShopConsts::RELEASE_ON)
            ->where('approval', \ShopConsts::APPROVAL_ON)
            ->orderBy("jobs.noruma_reward", "desc")
            ->select("shops.*", "jobs.*", "shops.id as id")->get();

        return $records;
    }


    public function short()
    {

        $records =  Shop::Join('jobs', 'shops.id', '=', 'jobs.shop_id')
            ->join('shop_treatments', 'shops.id', '=', 'shop_treatments.shop_id')
            ->where('release', \ShopConsts::RELEASE_ON)
            ->where('approval', \ShopConsts::APPROVAL_ON)
            ->whereIn("shop_treatments.treatment", [\TreatmentConsts::HOWTO_1, \TreatmentConsts::HOWTO_2, \TreatmentConsts::HOWTO_3])
            ->select("shops.*", "jobs.*", "shops.id as id")->get();

        return $records;
    }


    public function inexperienced()
    {

        $records =  Shop::Join('jobs', 'shops.id', '=', 'jobs.shop_id')
            ->join('shop_treatments', 'shops.id', '=', 'shop_treatments.shop_id')
            ->where('release', \ShopConsts::RELEASE_ON)
            ->where('approval', \ShopConsts::APPROVAL_ON)
            ->where("shop_treatments.treatment", \TreatmentConsts::HOWTO_4)
            ->select("shops.*", "jobs.*", "shops.id as id")->get();

        return $records;
    }

    public function findByRelease(): Collection
    {
        $records =  Shop::LeftJoin('jobs', 'shops.id', '=', 'jobs.shop_id')
            ->where('shops.del_flg', \CommonConsts::DEL_OFF)
            ->where('release', \ShopConsts::RELEASE_ON)
            ->whereIn('approval', [\ShopConsts::PUBLISH_ON, \ShopConsts::APPROVAL_ON, \ShopConsts::APPROVAL_OFF2])
            ->orderBy('shops.updated_at', "desc")->select("shops.*", "jobs.*", "shops.id as id")->get();

        return $records;
    }

    public function findByReleaseCnt()
    {
        $records =  Shop::LeftJoin('jobs', 'shops.id', '=', 'jobs.shop_id')
            ->where('shops.del_flg', \CommonConsts::DEL_OFF)
            ->where('release', \ShopConsts::RELEASE_ON)
            ->whereIn('approval', [\ShopConsts::PUBLISH_ON, \ShopConsts::APPROVAL_ON, \ShopConsts::APPROVAL_OFF2])
            ->select("shops.*", "jobs.*", "shops.id as id")->count();

        return $records;
    }


    public function findByNotRelease(): Collection
    {
        $records =  Shop::where('release', \ShopConsts::RELEASE_OFF)->get();
        return $records;
    }

    public function findByNew(): Collection
    {
        $records =  Shop::where('release', \ShopConsts::RELEASE_ON)->where('approval', \ShopConsts::APPROVAL_ON)->orderBy('update_at', 'desc')->limit(\ShopConsts::LATEST_DISPLAY_COUNT)->get();
        return $records;
    }



    public function industryfindByShopId($shop_id): Collection
    {
        $records =  ShopIndustry::where('shop_id', $shop_id)->get();
        return $records;
    }

    public function findMatchingById($shop_id)
    {
        $records = DB::table('messages')
            ->join('shops', 'messages.shop_id', '=', 'shops.id')
            ->join('members', 'messages.member_id', '=', 'members.id')
            ->where('messages.shop_id', $shop_id)
            ->select('members.*', 'shops.id as shop_id', 'shops.status as shop_status')
            ->distinct();


        return $records->paginate(\ShopConsts::PAGENATION_COUNT);
    }

    public function findMatchingByAdmin()
    {
        $records =  Shop::Join('matchings', 'shops.id', '=', 'matchings.shop_id')
            ->join('members', 'matchings.member_id', '=', 'members.id')
            ->select("members.*", "shops.*", 'members.id as member_id', 'shops.id as shop_id');
        return $records->paginate(\ShopConsts::PAGENATION_COUNT);
    }

    public function searchMatchingByAdmin($request)
    {
        $query =  Shop::Join('matchings', 'shops.id', '=', 'matchings.shop_id')
            ->join('members', 'matchings.member_id', '=', 'members.id');


        if ($request->shop_name) {
            $query->where('shops.shop_name', $request->shop_name);
        }
        if ($request->nickame) {

            $query->where('member.nickame', 'like', $request->nickame . '%');
        }

        if ($request->pref) {
            $query->where('member.pref', $request->pref);
        }

        if ($request->matching_status) {

            $query->where('matchings.matching_status', $request->matching_status);
        }

        if ($request->status) {

            $query->join('deposits', function ($query) {
                $query->on('deposits.member_id', '=', 'members.id');
            });
            $query->where('deposits.status', $request->status);
        }

        if ($request->adoption) {

            $query->join('applies', function ($query) {
                $query->on('applies.member_id', '=', 'members.id');
            });
            $query->where('applies.result', $request->adoption);
        }

        if ($request->search_jobtype) {

            $query->where('shops.helpjob', $request->search_jobtype);
        }



        $records = $query->select("members.*", "shops.*", 'members.id as member_id', 'shops.id as shop_id');
        return $records->paginate(\ShopConsts::PAGENATION_COUNT);
    }




    public function searchMatching(Request $request, $shop_id = "")
    {

        $query =  DB::table('messages')
            ->join('shops', 'messages.shop_id', '=', 'shops.id')
            ->join('members', 'messages.member_id', '=', 'members.id')
            ->where('messages.shop_id', $shop_id)
            ->select('members.*', 'shops.id as shop_id', 'shops.status as shop_status')
            ->distinct();


        if ($request->filled('member_id')) {
            $query->where('members.id', $request->member_id);
        }
        if ($request->filled('nickname')) {
            $query->where('members.nickname', 'like', '%' . $request->nickname . '%');
        }

        if ($request->filled('pref')) {
            $query->where('members.pref', $request->pref);
        }

        $records = $query->paginate(\ShopConsts::PAGENATION_COUNT);

        return $records;
    }


    public function searchRecruitAdmin(Request $request, $deposit_status = "")
    {
        $latestApply = DB::table('applies')
            ->select(
                'member_id',
                'shop_id',
                DB::raw('MAX(id) as latest_apply_id')
            )
            ->groupBy('member_id', 'shop_id'); // 👈 group theo cả member_id và shop_id

        $query = DB::table('applies')
//            ->joinSub($latestApply, 'latest', function ($join) {
//                $join->on('applies.id', '=', 'latest.latest_apply_id');
//            })
            ->join('members', 'applies.member_id', '=', 'members.id')
            ->join('shops', 'applies.shop_id', '=', 'shops.id')
            ->leftJoin('deposits', 'deposits.apply_id', '=', 'applies.id')
            ->select(
                'members.*',
                'applies.*',
                'members.id as member_id',
                'shops.id as shop_id',
                'applies.id as apply_id',
                'applies.about_recruit',
                'shops.shop_name as shop_name',
                'shops.pref as s_pref',
                'shops.city as s_city',
                'deposits.created_at as d_created_at',
            )
            ->orderBy('applies.id', 'desc');

        if (isset($request->active) && !empty($request->active)) {
            if (is_array($request->active)) {
                $query->whereIn('applies.active', $request->active);
            } else {
                $query->where('applies.active', $request->active);
            }
        }
        if (!empty($deposit_status)) {
            $query->where('deposits.status', $deposit_status);
        }

        if (isset($request->nickname) && !empty($request->nickname)) {
            $query->where('members.nickname', 'like', '%' . $request->nickname . '%');
        }

        if (isset($request->shop_name) && !empty($request->shop_name)) {
            $query->where('shops.shop_name', 'like', '%' . $request->shop_name . '%');
        }

        if ($request->deposits) {
            $query->where('deposits.status', $request->deposits);
        }

        $records = $query->paginate(\ShopConsts::PAGENATION_COUNT);

        return $records;

    }
    public function searchRicruit(Request $request, $shop_id = "", $deposit_status = "")
    {
        $latestApply = DB::table('applies')
            ->select('member_id', DB::raw('MAX(id) as latest_apply_id'))
            ->where('shop_id', $shop_id)
            ->groupBy('member_id');

        $query = DB::table('applies')
//            ->joinSub($latestApply, 'latest', function ($join) {
//                $join->on('applies.id', '=', 'latest.latest_apply_id');
//            })
            ->join('members', 'applies.member_id', '=', 'members.id')
            ->join('shops', 'applies.shop_id', '=', 'shops.id')
            ->leftJoin('deposits', 'deposits.apply_id', '=', 'applies.id')
            ->select(
                'members.*',
                'applies.*',
                'members.id as member_id',
                'shops.id as shop_id',
                'applies.id as apply_id',
                'applies.about_recruit',
                'shops.pref as s_pref',
                'shops.addr1 as s_addr1',
                'deposits.created_at as d_created_at',
            )
            ->where('applies.shop_id', $shop_id)
            ->orderBy('applies.id', 'desc');

        if (isset($request->active) && !empty($request->active)) {
            if (is_array($request->active)) {
                $query->whereIn('applies.active', $request->active);
            } else {
                $query->where('applies.active', $request->active);
            }
        }
        if (!empty($deposit_status)) {
            $query->where('deposits.status', $deposit_status);
        }

        if (isset($request->nickname) && !empty($request->nickname)) {
            $query->where('members.nickname', 'like', '%' . $request->nickname . '%');
        }

        if (isset($request->shop_name) && !empty($request->shop_name)) {
            $query->where('shops.shop_name', 'like', '%' . $request->shop_name . '%');
        }

        $records = $query->paginate(\ShopConsts::PAGENATION_COUNT);

        return $records;

//        $latest_messages = DB::table('deposits')
//            ->select(DB::raw('MAX(id) as id'))
//            ->groupBy('member_id','shop_id');
//        $query = DB::table('deposits')
//                ->join('shops', 'deposits.shop_id', '=', 'shops.id')
//                ->joinSub($latest_messages, 'latest_messages', function($join) {
//                       $join->on('deposits.id', '=', 'latest_messages.id');
//                   })
//                   //->leftJoin('w_members', 'deposits.member_id', '=', 'w_members.id')
//                   ->leftJoin('members', function($join) {
//                       $join->on('deposits.member_id', '=', 'members.id')
//                            ->where(function($query) {
//                                $query->where(function($subQuery) {
//                                //    $subQuery->where('members.approval', \FrontConsts::APPROVAL_ON)
//                                      $subQuery->where('members.del_flg', \CommonConsts::DEL_OFF);
//                                //})
//                                //->orWhere(function($subQuery) {
//                                //    $subQuery->where('w_members.del_flg', \CommonConsts::DEL_OFF);
//                                //})
//                                //->orWhere(function($subQuery) {
//                                //    $subQuery->where('members.del_flg', \CommonConsts::DEL_OFF);
//                                });
//                            });
//                   })
//                   ->join('applies', 'applies.shop_id', '=', 'shops.id')
//                   ->where('shops.id',$shop_id);
//
//        if (!empty($deposit)) {
//            $query->where('deposits.status', $deposit);
//        }
//        if ($request->member_id) {
//            $query->where('members.id', $request->member_id);
//        }
//        if ($request->shop_id) {
//            $query->where('shops.id', $request->shop_id);
//        }
//
//        if ($request->shop_name) {
//            $query->where('shops.shop_name', 'like', $request->shop_name . '%');
//        }
//
//        if ($request->nickame) {
//            $query->where('members.nickame', 'like', $request->nickame . '%');
//        }
//        if ($request->pref) {
//            $query->where('members.pref', $request->pref);
//        }
//
//        if ($request->recruitment) {
//            $query->where('applies.about_recruit', $request->recruitment);
//        }
//
//        if ($request->deposits) {
//            $query->where('deposits.status', $request->deposits);
//        }
//
//        if ($request->adoption) {
//            $query->where('applies.result', $request->adoption);
//        }
//
//
//        $records =  $query->select(
//                   'applies.*','members.*','applies.id as apply_id','applies.status as apply_status',
//                    //DB::raw('COALESCE(w_members.nickname, members.nickname) as nickname'),
//                    //DB::raw('COALESCE(w_members.pref, members.pref) as pref'),
//                    //DB::raw('COALESCE(w_members.addr1, members.addr1) as addr1'),
//                   'members.id as members_id','deposits.id as deposits_id');
    }

    public function findInformationByShopId($shop_id)
    {

        $records =  Shop::Join('information_by_shops', 'shops.id', '=', 'information_by_shops.shop_id')
            ->where('shops.id', $shop_id)
            ->orderBy('information_by_shops.updated_at', 'desc')
            ->select(
                "information_by_shops.*",
                'information_by_shops.release as information_by_shops_release',
                "shops.*",
                "information_by_shops.id as information_by_shops_id"
            );

        return $records->paginate(\ShopConsts::PAGENATION_COUNT);
    }



    public function getAllInformationBySeq($seq)
    {
        $records =  InformationByShop::where('seq', $seq)->get();
        return $records;
    }

    public function getAllInformationByShopId($shop_id)
    {
        $records =  InformationByShop::where('shop_id', $shop_id)->orderBy('updated_at', 'desc')->get();
        return $records;
    }


    public function getAllInformationByShopIdGroup($shop_id)
    {
        $records =  InformationByShop::where('release', \ShopConsts::RELEASE_ON)->where('shop_id', $shop_id)->groupBy('seq')->groupBy('title')->groupBy('body')->groupBy('type')->select('seq', 'title', 'body', 'type')->get();
        return $records;
    }

    public function getAllInformationBySeqIdGroup($seq)
    {
        $records =  InformationByShop::where('seq', $seq)->groupBy('seq')->groupBy('title')->groupBy('body')->groupBy('type')->select('seq', 'title', 'body', 'type')->get();
        return $records;
    }

    public function getAllInformationByShopIdLimited($shop_id)
    {

        $InformationByShops =  InformationByShop::where('shop_id', $shop_id)->where('release', \ShopConsts::RELEASE_ON)->groupBy('seq')->limit(30)->select('seq')->get();
        /*
        $records = DB::table('information_by_shops')
                   ->select('seq', DB::raw('max(id) as id') , DB::raw('max(type) as type') , DB::raw('max(title) as title') )
                   ->where('shop_id',$shop_id)
                   ->where('release',\ShopConsts::RELEASE_ON)
                   ->groupBy('seq')
                   ->get();
*/


        $records = [];
        foreach ($InformationByShops as $val) {
            $records[] = InformationByShop::where('seq', $val->seq)->orderBy('updated_at', "desc")->limit(1)->get();
        }

        $records = InformationByShop::where('shop_id', $shop_id)->where('release', \ShopConsts::RELEASE_ON)->get();

        return $records;
    }


    public function getAllInformationByShopIdGroup2()
    {
        $records =  InformationByShop::groupBy('seq')->groupBy('title')->groupBy('body')->groupBy('type')->groupBy('shop_id')->select('seq', 'title', 'body', 'type', 'shop_id')->get();

        return $records;
    }


    public function findInformationById($id)
    {

        $records =  InformationByShop::find($id);
        return $records;
    }

    public function findInformationByShopId2($id)
    {

        $records =  InformationByShop::where('shop_id', $id)->get();
        return $records;
    }


    public function readInformation($id)
    {

        $info = $this->findInformationById($id);
        if (!empty($info)) {
            $info->status = \ShopConsts::STATUS_ON;
            $info->save();
        }
    }


    public function search(Request $request, $shop_id = "")
    {

        $search = $request->search;

        $query = Shop::query();

        if ($search) {
            // 全角スペースを半角に変換
            $spaceConversion = mb_convert_kana($search, 's');
            // 単語を半角スペースで区切り、配列にする（例："テス ト" → ["テス", "ト"]）
            $wordArraySearched = preg_split('/[\s,]+/', $spaceConversion, -1, PREG_SPLIT_NO_EMPTY);
            // 単語をループで回し、ユーザーネームと部分一致するものがあれば、$queryとして保持される
            foreach ($wordArraySearched as $value) {
                $query->where('pref', 'like', '%' . $value . '%')->orWhere('shop_name', 'like', '%' . $value . '%');
            }
        }

        if ($request->id) {

            $query->where('id', $request->id);
        }
        if ($request->shop_name) {
            $query->where('shop_name', 'like', '%' . $request->shop_name . '%');
        }

        if ($request->pref) {
            $query->where('pref', $request->pref);
        }

        if ($request->addr1) {
            $query->where('addr1', 'like', '%' . $request->addr1 . "%");
        }

        if ($request->approval) {
            $query->where('approval', $request->approval);
        }

        if ($request->matching) {

            $query->where('matching', $request->matching);
        }
        if ($request->release) {
            $query->where('release', $request->release);
        }

        if ($request->beroreapproval) {
            $query->where('approval', '<', \ShopConsts::APPROVAL_ON);
        }

        if ($shop_id != "") {
            $query->where('shop_id', $shop_id);
        }

        $records =  $query->get();

        $records = $query->paginate(\ShopConsts::PAGENATION_COUNT);

        return $records;
    }

    public function userSearchCond(Request $request, &$location_mode = null)
    {
        $query = Shop::query()
            ->join('jobs', 'shops.id', '=', 'jobs.shop_id')
            ->where('shops.del_flg', \CommonConsts::DEL_OFF);

        // =======================
        // Search từ khóa
        // =======================
        if ($request->search) {
            $spaceConversion = mb_convert_kana($request->search, 's');
            $wordArraySearched = preg_split('/[\s,]+/', $spaceConversion, -1, PREG_SPLIT_NO_EMPTY);

            $query->where(function ($q) use ($wordArraySearched) {
                foreach ($wordArraySearched as $value) {
                    $q->orWhere('shops.pref', 'like', "%{$value}%")
                        ->orWhere('shops.shop_name', 'like', "%{$value}%")
                        ->orWhere('shops.station1', 'like', "%{$value}%")
                        ->orWhere('shops.station2', 'like', "%{$value}%")
                        ->orWhere('shops.station3', 'like', "%{$value}%")
                        ->orWhere('shops.station4', 'like', "%{$value}%")
                        ->orWhere('shops.station5', 'like', "%{$value}%");
                }
            });
        }

        // =======================
        // 業種フィルタ
        // Lọc industry
        // =======================
        if ($request->industry) {
            $query = $this->bildIndustry($query)
                ->whereIn('shop_industries.industry_id', $request->industry);
        }

        // =======================
        // 都道府県フィルタ
        // Lọc prefecture
        // =======================
        $cond_pref = [];
        if ($request->pref) $cond_pref = array_merge($cond_pref, explode(',', $request->pref));
        if ($request->hpref) $cond_pref = array_merge($cond_pref, explode(',', $request->hpref));
        $cond_pref = array_unique($cond_pref);
        if (!empty($cond_pref)) $query->whereIn('shops.pref', $cond_pref);

        // =======================
        // 都道府県フィルタ
        // Lọc city
        // =======================
        $cond_city = [];
        if ($request->city) $cond_city = array_merge($cond_city, explode(',', $request->city));
        if ($request->hcity) $cond_city = array_merge($cond_city, explode(',', $request->hcity));
        $cond_city = array_unique($cond_city);
        if (!empty($cond_city)) $query->whereIn('shops.city', $cond_city);

        // =======================
// 現在地検索
// Vị trí (current / custom)
// =======================
        if (empty($request->area2)) $request->area2 = '2';
        $enable_location_filter = false;
        $start_latitude = null;
        $start_longitude = null;

        if ($request->location_info == '1' && isset($_COOKIE['lat'], $_COOKIE['lng'])) {
            // ✅ 現在地から探す
            $start_latitude = (float)$_COOKIE['lat'];
            $start_longitude = (float)$_COOKIE['lng'];
            $enable_location_filter = true;
            $location_mode = 'current';
        } elseif ($request->location_info == '2' && !empty($request->location_info_text)) {
            // ✅ 位置情報から探す
            $address = urlencode($request->location_info_text . ', Japan');
            $google_api_key = config('services.google-map.apikey');
            $res = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address={$address}&region=jp&key={$google_api_key}");
            $data = json_decode($res, true);
            if (!empty($data['results'][0])) {
                $start_latitude = (float)$data['results'][0]['geometry']['location']['lat'];
                $start_longitude = (float)$data['results'][0]['geometry']['location']['lng'];
                $enable_location_filter = true;
                $location_mode = 'custom';
            }
        }

// =======================
// Cập nhật lat/lng trước khi search
// =======================
        $updatedShopIds = []; // lưu lại shop nào vừa update

        if ($enable_location_filter) {
            $shopsForUpdate = Shop::select('id', 'latitude', 'longitude', 'pref', 'city', 'addr2', 'addr3')->where('release',\ShopConsts::RELEASE_ON)->get();

            foreach ($shopsForUpdate as $shop) {
                if (empty($shop->latitude) || empty($shop->longitude) ||
                    (float)$shop->latitude === 0.0 || (float)$shop->longitude === 0.0) {

                    if (empty($shop->pref)) {
                        continue; // ❌ skip shop không có pref
                    }

                    // Build full address
                    if (!empty($shop->city)) {
                        $fullAddress = $shop->pref . $shop->city . $shop->addr2 . $shop->addr3;
                    } else {
                        $fullAddress = $shop->pref;
                    }

                    $address = urlencode($fullAddress . ', Japan');
                    $google_api_key = config('services.google-map.apikey');
                    $res = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address={$address}&region=jp&key={$google_api_key}");
                    $data = json_decode($res, true);

                    if (!empty($data['results'][0])) {
                        $shop->latitude = (float)$data['results'][0]['geometry']['location']['lat'];
                        $shop->longitude = (float)$data['results'][0]['geometry']['location']['lng'];
                        $shop->save();

                        $updatedShopIds[$shop->id] = true; // đánh dấu updated
                    }
                }
            }

            // =======================
// Sau khi update, mới tính distance và filter theo radius
// =======================
            $areaDistanceMap = [
                1 => ['op' => '<=', 'value' => 5],
                2 => ['op' => '<=', 'value' => 20],
                3 => ['op' => '<=', 'value' => 30],
                4 => ['op' => '>=', 'value' => 40], // từ 40km trở đi
            ];

            $areaOption = $areaDistanceMap[$request->area2] ?? ['op' => '<=', 'value' => PHP_INT_MAX];

            $earth_r = 6378.137;

            $distanceExpr = $this->haversineSql($start_latitude, $start_longitude, 'shops.latitude', 'shops.longitude');
            $query->addSelect('shops.id', 'shops.latitude', 'shops.longitude', DB::raw("$distanceExpr AS distance"));

// Áp dụng filter theo option
            if ($areaOption['value'] < PHP_INT_MAX) {
                $query->havingRaw("distance {$areaOption['op']} ?", [$areaOption['value']]);
            }

            $query->orderBy('distance', 'asc');

// 🔹 Log kết quả
            $tempShops = $query->get();
            $shop_ids_distance = [];
            foreach ($tempShops as $shop) {
                $lat1 = deg2rad($start_latitude);
                $lng1 = deg2rad($start_longitude);
                $lat2 = deg2rad($shop->latitude);
                $lng2 = deg2rad($shop->longitude);

                $dLat = $lat2 - $lat1;
                $dLng = $lng2 - $lng1;
                $a = pow(sin($dLat / 2), 2) +
                    cos($lat1) * cos($lat2) * pow(sin($dLng / 2), 2);
                $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
                $distance_km = $earth_r * $c;

                // Nếu shop vừa được update thì thêm emoji 📝
                $updateFlag = isset($updatedShopIds[$shop->id]) ? ' 📝' : '';

                $shop_ids_distance[] = "📍{$updateFlag} Shop ID=" . $shop->id .
                    " | Distance (km)=" . $distance_km .
                    " | Lat/Lng:" . $shop->latitude . "," . $shop->longitude;
            }

            $radius_label = ($request->area2 == 4)
                ? ">= {$areaOption['value']} km"
                : (($areaOption['value'] == PHP_INT_MAX) ? "Unlimited" : "<= {$areaOption['value']} km");

            $location_info_text = ($location_mode === 'custom') ? $request->location_info_text : 'N/A';

            Log::info("🔎 Search Filter Distance
                            - Location Mode: {$location_mode}
                            - Location Text: {$location_info_text}
                            - Start Lat/Lng: {$start_latitude}, {$start_longitude}
                            - Radius: {$radius_label}
                            - Results:
                                " . implode("\n                                ", $shop_ids_distance));
        }



        // =======================
        // 報酬フィルタ
        // Reward filter
        // =======================
        if (!empty($request->reward)) {
            $map = [
                'lt_50000' => ['col' => 'jobs.noruma_reward2', 'op' => '<=', 'val' => 50000], // 〜50,000円以下
                'gte_50000' => ['col' => 'jobs.noruma_reward', 'op' => '>=', 'val' => 50000], // 50,000円〜
                'gte_100000' => ['col' => 'jobs.noruma_reward', 'op' => '>=', 'val' => 100000], //50,000円〜
                'gte_200000' => ['col' => 'jobs.noruma_reward', 'op' => '>=', 'val' => 200000], //200,000円〜
                'gte_300000' => ['col' => 'jobs.noruma_reward', 'op' => '>=', 'val' => 300000],  //300,000円〜
            ];
            if (isset($map[$request->reward])) {
                $r = $map[$request->reward];
                $query->where($r['col'], $r['op'], $r['val']);
            }
        }

        // 給与(時給)
        if (!empty($request->hourly_wage)) $query->where('jobs.hourly_wage_regular', '>=', (int)$request->hourly_wage);

        // ヘルプフラグ
        if ($request->help) $query->where('shops.help', $request->help == "1" ? "1" : "0");

        // 待遇フィルタ
        if ($request->treatment) {
            $query = $this->bildTreatment($query)
                ->whereIn('shop_treatments.treatment', $request->treatment);
        }

        // 並び順
        if ($request->str1 == 2) $query->orderBy("shops.updated_at", "desc");

        Log::info("Final SQL: " . $query->toSql());
        Log::info("Bindings: ", $query->getBindings());

        return $query;
    }

    /**
     * Trả về Haversine SQL expression để tính khoảng cách (km)
     *  Haversine式を使って距離（km）を計算するSQL式を返します
     *
     * @param float $startLat  Vĩ độ điểm bắt đầu / 開始地点の緯度
     * @param float $startLng  Kinh độ điểm bắt đầu / 開始地点の経度
     * @param string $colLat   Tên cột latitude của bảng / テーブルの緯度カラム名
     * @param string $colLng   Tên cột longitude của bảng / テーブルの経度カラム名
     * @param float $earthRadius Bán kính trái đất (km) / 地球の半径（km）
     * @return string Haversine SQL expression / Haversine式のSQL
     */
    function haversineSql(float $startLat, float $startLng, string $colLat = 'latitude', string $colLng = 'longitude', float $earthRadius = 6378.137): string
    {
        return "(
        $earthRadius * 2 *
        ASIN(
            SQRT(
                POWER(SIN(RADIANS($colLat - $startLat) / 2), 2) +
                COS(RADIANS($startLat)) * COS(RADIANS($colLat)) *
                POWER(SIN(RADIANS($colLng - $startLng) / 2), 2)
            )
        )
    )";
    }


    public function userSearchCount(Request $request)
    {
//echo $this->userSearchCond($request)->distinct('shops.id')->count();
        return $this->userSearchCond($request)->distinct('shops.id')->count();
    }

    // --- Controller ---
    public function userSearch(Request $request)
    {
        $location_mode = null;
        $query = $this->userSearchCond($request, $location_mode);

        $records = $query->distinct('shops.id')
            ->addSelect('shops.*', 'jobs.id as job_id', 'jobs.*', 'shops.id as id', 'jobs.helpjob as helpjob')
            ->get();

        foreach ($records as $record) {
            if (isset($record->distance)) {
                $distance = $record->distance;

                if ($distance < 1) {
                    // <1 km -> meter display
                    $distance_label = round($distance * 1000) . "m";
                } else {
                    // >=1 km -> display km with 1 decimal place
                    $distance_label = round($distance, 1) . "km";
                }

                $record->distance_km = round($distance, 1);
                $record->distance_label = ($location_mode === 'custom')
                    ? "指定位置から {$distance_label}"
                    : "現在地から {$distance_label}";
            } else {
                $record->distance_km = null;
                $record->distance_label = null;
            }
        }

        return $records;
    }



    public function userSearch2(Request $request)
    {
        return $this->userSearchCond($request)->select('shops.*', 'jobs.*', 'shops.id as id')->get();
    }

    function bildIndustry($query)
    {
        $query->join('shop_industries', function ($query) {
            $query->on('shops.id', '=', 'shop_industries.shop_id');
        });
        return $query;
    }

    function bildTreatment($query)
    {
        $query->join('shop_treatments', function ($query) {
            $query->on('shops.id', '=', 'shop_treatments.shop_id');
        });
        return $query;
    }

    // 管理者からの更新
    public function saveShopByAdmin(Request $request, $shop_id)
    {
        if ($request['status'] == \ShopConsts::TENP_REG) {
            $request['approval'] = \ShopConsts::APPROVAL_OFF;
            $request['release'] = \ShopConsts::RELEASE_OFF;
        } else {
            $request['approval'] = \ShopConsts::APPROVAL_ON;
            $request['release'] = \ShopConsts::RELEASE_ON;
        }

        $res = $this->saveShop($request, $shop_id);
        $this->saveShopIdentity($request, $shop_id);


        /*
        $res = Shop::updateOrCreate(
            ['id' => $shop_id],
            $request->all()
        );
*/
        return $res;
    }


    // 店舗からの更新
    public function saveShopByShop(Request $request, $shop_id)
    {
        /*
        $shop_data = Shop::find($shop_id);
        $res = WShop::updateOrCreate(
            ['id' => $shop_id],
            $shop_data->attributesToArray()
        );

*/
        // 承認前の一時データ
        $res = $this->saveShopW($request, $shop_id);

        // マスター
        $request['approval'] = \ShopConsts::APPROVAL_OFF2;
        $res = $this->saveShop($request, $shop_id);

        $this->saveShopIdentity($request, $shop_id);
        $managers = ShopManager::where('shop_id', $shop_id)->orderBy('created_at', 'desc')->limit(1)->get();
        $id = "";
        foreach ($managers as $manager) {
            $id = $manager->id;
        }
        ShopManager::updateOrCreate(
            ['id' => $id],
            ['email' => $request->email]
        );

        return $res;
    }

    // 新規申込
    public function newApplication(Request $request)
    {

        $request['approval'] = \ShopConsts::APPROVAL_ON;
        $shop_id = $this->createShop($request);
        $this->saveShopIdentity($request, $shop_id);

        $res = $this->createShopW($request, $shop_id);

        $this->saveShopIdentityW($request, $shop_id);

        //$request['shop_id'] = $shop_id;
        //Manager::create( $request->all() );

        return $shop_id;
    }

    public function saveApproval($request, $shop_id)
    {

        if (!empty($request['ng_reason'])) {
            $request['approval'] = ShopConsts::APPROVAL_NG;
        } else {
            $request['approval'] = ShopConsts::APPROVAL_ON;
            $request['ng_reason'] = "";
        }

        Shop::updateOrCreate(
            ['id' => $shop_id],
            $request->all()
        );

        WShop::updateOrCreate(
            ['id' => $shop_id],
            $this->findById($shop_id)->toArray()
        );

        if (!empty($request['ng_reason'])) {
            $request['approval'] = ShopConsts::APPROVAL_NG;
        } else {
            $request['approval'] = ShopConsts::APPROVAL_OFF2;
        }

        WShop::updateOrCreate(
            ['id' => $shop_id],
            ['approval' => $request['approval']]
        );
    }

    public function saveShopStatus($status, $shop_id)
    {

        try {
            $shop = Shop::find($shop_id);
            $shop->status = $status;
            $shop->save();
        } catch (Exception $e) {
            Log::debug($e->getMessage());
        }
        return $shop;
    }

    public function saveShopMatching($matching, $shop_id)
    {
        try {
            $shop = Shop::find($shop_id);
            $shop->matching = $matching;
            $shop->save();
        } catch (Exception $e) {
            Log::debug($e->getMessage());
        }
        return $shop;
    }

    public function saveShopRelease($release, $shop_id)
    {
        try {
            $shop = Shop::find($shop_id);
            $shop->release = $release;
            $shop->save();
        } catch (Exception $e) {
            Log::debug($e->getMessage());
        }
        return $shop;
    }

    public function saveShopWithdrawal($shop_id)
    {
        try {
            $shop = Shop::find($shop_id);
            $shop->withdrawal = \ShopConsts::WITHDRAWAL_DONE;
            $shop->save();
        } catch (Exception $e) {
            Log::debug($e->getMessage());
        }
        return $shop;
    }

    public function saveShopMemo($memo, $shop_id)
    {
        try {
            $shop = Shop::find($shop_id);
            $shop->memo = $memo;
            $shop->save();
        } catch (Exception $e) {
            Log::debug($e->getMessage());
        }
        return $shop;
    }

    public function saveInformationRelease($request, $shop_id, $id)
    {
        $informationbyshop = InformationByShop::find($id);
        $release = $informationbyshop->release;
        if ($release == \ShopConsts::RELEASE_ON) $informationbyshop->release = \ShopConsts::RELEASE_OFF;
        else $informationbyshop->release =  \ShopConsts::RELEASE_ON;
        $informationbyshop->save();
    }

    public function saveReviewRelease($request, $shop_id, $id)
    {
        $review = Review::find($id);
        $release = $review->release;
        if ($release == \ShopConsts::RELEASE_ON) $review->release = \ShopConsts::RELEASE_OFF;
        else $review->release =  \ShopConsts::RELEASE_ON;
        $review->save();
    }

    public function doFollowByMember($member_id, $shop_id)
    {
        /*
        $request['type'] = \CommonConsts::FORROW_BY_MEMBER;
        $request = DB::table('follows')->where('member_id',$member_id)->where('shop_id',$shop_id)->get();
        $res = $request->fill($request->except('_token', '_method'))-> save();*/
        $res = Follow::updateOrCreate(
            ['member_id' => $member_id, 'shop_id' => $shop_id],
            ['type' => \CommonConsts::FORROW_BY_MEMBER]
        );
    }


    public function doBlockByMember($member_id, $shop_id)
    {
        $res = Follow::updateOrCreate(
            ['member_id' => $member_id, 'shop_id' => $shop_id, 'blocked_by' => 'member'],
            ['blocked_by' => 'member', 'type' => \CommonConsts::FORROW_BY_MEMBER_NG]
        );
    }

    public function doBlockCancelByMember($member_id, $shop_id)
    {

        $res = Follow::updateOrCreate(
            ['member_id' => $member_id, 'shop_id' => $shop_id, 'blocked_by' => 'member'],
            ['blocked_by' => 'member', 'type' => \CommonConsts::FORROW_BY_SHOP]
        );
    }


    public function saveShopImformationImageW($request, $shop_id)
    {
        //WShopSubImage::where('shop_id', $shop_id)->delete();

        $f = new FileUtil();
        $ssi = new InformationByShop();

        $str = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $rand_str = substr(str_shuffle(str_repeat($str, 10)), 0, 10);
        $data = $request->all();
        $data['shop_id'] = $shop_id;
        $data['seq'] = $rand_str;
        $data['release'] = $data['publish'];

        $content = $request->body;

        if (!empty($content)) {

            $dom = new \DomDocument();
            libxml_use_internal_errors(true);
            $dom->loadHtml($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            libxml_clear_errors();
            $imageFile = $dom->getElementsByTagName('img');

            foreach ($imageFile as $item => $image) {

                try {

                    $data2 = $image->getAttribute('src');
                    if (strpos($data2, 'assets') > 0) {
                        continue;
                    }
                    list($type, $data2) = explode(';', $data2);
                    list(, $data2)      = explode(',', $data2);
                    $imgeData = base64_decode($data2);
                    $image_name = ShopConsts::SHOP_DISP_IMG_DIR . $shop_id . '/' . ShopConsts::SHOP_DIR_NAMES_MAIN . '/' . time() . '.png';
                    Storage::put($shop_id . '/' . ShopConsts::SHOP_DIR_NAMES_MAIN . '/' . time() . '.png', $imgeData);
                    $image->removeAttribute('src');
                    $image->setAttribute('src', $image_name);
                } catch (Exception $e) {
                }
            }
        }

        $isFileUploaded = false;

        for ($i = 0; $i < ShopConsts::SHOP_SUB_IMAGE_FILE_CNT; $i++) {

            if (empty($_FILES[ShopConsts::SHOP_FILE_NAME_MULTIPLE . $i]['tmp_name'])) continue;

            $isFileUploaded = true;

            $sub_img = $f->getOriginalFileName($request, ShopConsts::SHOP_FILE_NAME_MULTIPLE . $i);
            //$ssi->create(["shop_id"=>$shop_id,"sub_img"=>$sub_img,]);
            $f->uploade($request, ShopConsts::SHOP_FILE_NAME_MULTIPLE . $i, ShopConsts::SHOP_MAIN_IMG_DIR . $shop_id . '/' . ShopConsts::SHOP_DIR_NAMES_MAIN);
            $data['sub_img'] = $sub_img;
            $data['num'] = $i + 1;

            //$data['posted_date_time'] =  date("Y-m-d H:i:s");

            $ssi->create($data);
        }

        if (!$isFileUploaded) {
            // ファイルが一つもアップロードされていない場合の処理
            $data['type'] = 9; // デフォルト値を設定
            $data['sub_img'] = ''; // デフォルト値を設定
            $data['num'] = 1; // 適当なデフォルトの番号

            $ssi->create($data);
        }
    }

    public function editInformation(Request $request, $shop_id, $id)
    {


        $f = new FileUtil();
        $ssi = new InformationByShop();
        InformationByShop::where('seq', $id)->delete();

        $data = $request->all();
        $data['shop_id'] = $shop_id;
        $data['release'] = $data['publish'];
        $data['seq'] = $id;
        $data['num'] = 1;



        $content = $request->body;
        $dom = new \DomDocument();
        libxml_use_internal_errors(true);
        $dom->loadHtml($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        $imageFile = $dom->getElementsByTagName('img');

        foreach ($imageFile as $item => $image) {

            try {

                $data2 = $image->getAttribute('src');
                if (strpos($data2, 'assets') > 0) {
                    continue;
                }
                list($type, $data2) = explode(';', $data2);
                list(, $data2)      = explode(',', $data2);
                $imgeData = base64_decode($data2);
                $image_name = ShopConsts::SHOP_DISP_IMG_DIR . $shop_id . '/' . ShopConsts::SHOP_DIR_NAMES_MAIN . '/' . time() . '.png';
                Storage::put($shop_id . '/' . ShopConsts::SHOP_DIR_NAMES_MAIN . '/' . time() . '.png', $imgeData);
                $image->removeAttribute('src');
                $image->setAttribute('src', $image_name);
            } catch (Exception $e) {
            }
        }

        $res = InformationByShop::create(
            //['seq' => $id],
            $data
        );

        for ($i = 0; $i < ShopConsts::SHOP_SUB_IMAGE_FILE_CNT; $i++) {

            //if(empty($_FILES[ShopConsts::SHOP_FILE_NAME_MULTIPLE.$i]['tmp_name'])) continue;

            if (!empty($_FILES[ShopConsts::SHOP_FILE_NAME_MULTIPLE . $i]['tmp_name'])) {

                $sub_img = $f->getOriginalFileName($request, ShopConsts::SHOP_FILE_NAME_MULTIPLE . $i);
                //$ssi->create(["shop_id"=>$shop_id,"sub_img"=>$sub_img,]);
                $f->uploade($request, ShopConsts::SHOP_FILE_NAME_MULTIPLE . $i, ShopConsts::SHOP_MAIN_IMG_DIR . $shop_id . '/' . ShopConsts::SHOP_DIR_NAMES_MAIN);
                $data['sub_img'] = $sub_img;
            } else if (!empty($request['hidd_file_name' . $i])) {
                $data['sub_img'] = $request['hidd_file_name' . $i];
            } else {
                continue;
            }

            $isFileUploaded = true;

            $res = InformationByShop::updateOrCreate(
                ['seq' => $id, 'num' => $i + 1],
                $data
            );
        }

        if (!$isFileUploaded) {
            // ファイルが一つもアップロードされていない場合の処理
            $data['type'] = 9; // デフォルト値を設定
            $data['sub_img'] = ''; // デフォルト値を設定
            $data['num'] = 1; // 適当なデフォルトの番号

            $ssi->create($data);
        }
    }

    public function deleteInformation($seq)
    {

        DB::table('information_by_shops')->where('seq', $seq)->delete();
    }


    public function storeInformation(Request $request, $shop_id)
    {


        // サブファイルアップロード
        $this->saveShopImformationImageW($request, $shop_id);
    }


    public function goodCntUp($member_id, $shop_id, $seq)
    {
        try {
            Good::Create(
                ['member_id' => $member_id, 'shop_id' => $shop_id, 'seq' => $seq,]
            );
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    public function goodCntDown($member_id, $shop_id, $seq)
    {
        try {
            Good::where("member_id", $member_id)->where("shop_id", $shop_id)->where("seq", $seq)->delete();
        } catch (Exception $e) {
            return false;
        }
        return true;
    }


    public static function goodCnt($seq)
    {
        $records =  Good::where('seq', $seq)->count();
        return $records;
    }


    public static function goodShopCnt($shop_id)
    {
        $records =  Good::where('shop_id', $shop_id)->count();
        return $records;
    }

    public function good2($shop_id, $member_id)
    {
        try {
            Good::Create(
                ['shop_id' => $shop_id, 'member_id' => $member_id,]
            );
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    public function good3($shop_id, $member_id)
    {
        try {

            $like = Good::where("shop_id", $shop_id)
                ->where("member_id", $member_id)
                ->where("seq", null)
                ->first();

            if ($like) {
                $like->delete();
            }

        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    public static function likeShopCnt($shop_id)
    {
        $records =  Like::where('shop_id', $shop_id)->count();
        return $records;
    }

    public function like($shop_id, $member_id)
    {
        try {
            Like::Create(
                ['shop_id' => $shop_id, 'member_id' => $member_id,]
            );
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    public function unLike($shop_id, $member_id)
    {
        try {
            //            Like::where("shop_id",$shop_id)->where("member_id",$member_id)->delete();
            $like = Like::where("shop_id", $shop_id)
                ->where("member_id", $member_id)
                ->first();

            if ($like) {
                $like->delete();
            }
        } catch (Exception $e) {
            return false;
        }
        return true;
    }



    public function saveReport(Request $request, $shop_id, $member_id , $shop_flg=false )
    {
        Report::Create(
            ['shop_id' => $shop_id, 'member_id' => $member_id, "comment" => $request->comment,'shop_flg'=>$shop_flg]
        );
    }


    public function deleteShopImageFile(Request $request, $shop_id, $column)
    {

        Shop::where('id', $shop_id)->update([$column => '']);
    }

    public function deleteShopSubImageFile(Request $request, $shop_id, $file_name)
    {

        ShopSubImage::where("shop_id", $shop_id)->where("sub_img", $file_name)->delete();
    }

    public function deleteInformationFile(Request $request, $shop_id, $file_name)
    {

        $cnt = InformationByShop::where("shop_id", $shop_id)->count();
        if ($cnt < 2) {
            InformationByShop::where("shop_id", $shop_id)->update(["sub_img" => '']);
        } else {
            InformationByShop::where("shop_id", $shop_id)->where("sub_img", $file_name)->delete();
        }
    }


    public function getTodayCentens($shop_id)
    {
        return TodayCentensByShop::where("shop_id", $shop_id)->first();
    }

    public function storeTodayCentens($shop_id, $word)
    {

        $res = TodayCentensByShop::updateOrCreate(
            ['shop_id' => $shop_id],
            ["word" => $word]
        );
    }


    public function addMemberToShop(Request $request)
    {
        $memberId = $request->input('member_id');
        $shopId = $request->input('shop_id');

        // 関係を作成または取得
        $relationship = ShopMemberRelationship::firstOrCreate([
            'member_id' => $memberId,
            'shop_id' => $shopId,
        ]);

        // 店舗側の追加フラグを更新
        $relationship->update(['shop_added' => true]);

        return response()->json(['message' => 'Member added by shop']);
    }

    public function goodByMyself($shop_id)
    {
        try {
            Good::Create(
                ['shop_id' => $shop_id,]
            );
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    public function unGoodByMyself($shop_id)
    {
        try {

            $like = Good::where("shop_id", $shop_id)
                ->first();
            if ($like) {
                $like->delete();
            }
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    public function saveAvater(Request $request, $shop_id)
    {

        $f = new FileUtil();


        if (!empty($_FILES["file_1"]['tmp_name'])) {

            $sub_img = $f->getOriginalFileName($request, "file_1");
            $f->uploade($request, "file_1", ShopConsts::SHOP_MAIN_IMG_DIR . $shop_id . '/' . ShopConsts::SHOP_DIR_NAMES_MAIN);

        } else if (!empty($request['hidd_file_name1'])) {
            $sub_img = $request['hidd_file_name1'];
        } else {
            return "";
        }

        $data = ['shop_main_img' => $sub_img];

        Shop::updateOrCreate(
            ['id' => $shop_id],
            $data
        );
    }


    public function deleteSubImage(Request $request, $shop_id, $file_name)
    {

        ShopSubImage::where("shop_id", $shop_id)->where("sub_img", $file_name)->delete();
        //WMemberImage::where("member_id", $member_id)->where("img", $file_name)->delete();
    }



    public function checkAvater($request, $shop_id)
    {


       $records = ShopSubImage::where('shop_id', $shop_id)->count();

       if ($records == 0) {
           Shop::find($shop_id)->update(['shop_main_img' => null]);

       }



    }

}
