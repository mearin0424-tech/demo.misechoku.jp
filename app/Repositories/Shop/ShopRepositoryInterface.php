<?php
namespace App\Repositories\Shop;

use App\Models\Shop;
use Illuminate\Database\Eloquent\Collection;
use App\Http\Requests\Shop\StoreRequest;
use Illuminate\Http\Request;
use App\Consts\ShopConsts;

interface ShopRepositoryInterface
{
    public function getAll();
    public function store(array $data, Request $request): Shop;
    public function findById($id);
    public function findByRelease(): Collection;
    public function findByReleaseCnt();

    public function IndustryfindByShopId($shop_id): Collection;
    public function search(Request $reques);

    //public function nearestStationfindByShopId($id): NearestStations;

    //public function uploade(Request $request,$name,$dir);


}


