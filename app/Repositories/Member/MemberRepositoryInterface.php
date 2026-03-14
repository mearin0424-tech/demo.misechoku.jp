<?php
namespace App\Repositories\Member;

use App\Models\Cast;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

//use App\Http\Requests\Shop\StoreRequest;

interface MemberRepositoryInterface
{
    public function getAll();
    //public function store(array $data): Member;
    public function store(Request $request,$member_id);

    public function findById($id);
    public function findFollowMember($shop_id);
    public function findByIndustry($member_id);
}


