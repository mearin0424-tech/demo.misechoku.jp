<?php
namespace App\Repositories\Keep;

use App\Models\Good2;
use Illuminate\Http\Request;
          
interface KeepRepositoryInterface
{
//    public function store(Request $request);
//    public function findScoutShop($member_id);
    public function findScoutMember($shop_id);
//    public function findScoutByMemberId($member_id);
//    public function findScoutByShopId($shop_id);

}


