<?php
namespace App\Repositories\Follow;

use App\Models\Follow;
use Illuminate\Http\Request;
          
interface FollowRepositoryInterface
{
    public function store(Request $request);
    public function findFollowShop($member_id);
    public function findFollowMember($shop_id);
    public function findFollowByMemberId($member_id);
    public function findFollowByShopId($shop_id);

}


