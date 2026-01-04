<?php
namespace App\Repositories\Scout;

use App\Models\Scount;
use Illuminate\Http\Request;
          
interface ScoutRepositoryInterface
{
    public function store(Request $request);
    public function findScoutShop($member_id);
    public function findScoutMember($shop_id);
    public function findScoutByMemberId($member_id);
    public function findScoutByShopId($shop_id);

}


