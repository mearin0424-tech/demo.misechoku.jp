<?php
namespace App\Repositories\Common;

use App\Models\Message;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use App\Consts\ShopConsts;

interface MessageRepositoryInterface
{
    public function store($data);
    public function message($member_id,$shop_id);
    public function findMessagedShop($member_id);
    public function findMessagedMember($shop_id);
    public function findMessageByMemberId($member_id);
    public function findMessageByShopId($shop_id);

}


