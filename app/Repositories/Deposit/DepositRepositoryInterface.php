<?php
namespace App\Repositories\Deposit;

use App\Models\Deposit;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use App\Consts\ShopConsts;

interface DepositRepositoryInterface
{
    public function store($data);
    public function findByAppliedBk();

}


