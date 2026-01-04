<?php
namespace App\Repositories\Apply;

use App\Models\Apply;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

interface ApplyRepositoryInterface
{
    public function store($data);
    public function findApply($member_id,$shop_id);
    public function findApplyAdoptedShop($shop_id);
    public function findApplyAdoptedMember($member_id);
    public function findMessageByMemberId($member_id);
    public function findMessageByShopId($shop_id);

}


