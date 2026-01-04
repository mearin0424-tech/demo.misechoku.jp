<?php
namespace App\Repositories\Footprints;

use App\Models\Footprints;
use Illuminate\Http\Request;
          
interface FootprintsRepositoryInterface
{
    public function store(Request $request);
    public function findFootprintsShop($member_id);
    public function findFootprintsMember($shop_id);
    public function findFootprintsByMemberId($member_id);
    public function findFootprintsByShopId($shop_id);

}


