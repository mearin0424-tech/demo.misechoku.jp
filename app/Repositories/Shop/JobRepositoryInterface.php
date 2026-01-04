<?php
namespace App\Repositories\Shop;

use App\Models\Job;
use App\Models\Treatment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Lib\StrUtil;
use App\Lib\FileUtil;
use App\Http\Requests\Shop\JobRequest;
use Illuminate\Http\Request;
use App\Consts\ShopConsts;
use Illuminate\Support\Facades\Auth;

interface JobRepositoryInterface
{

    public function findById($id);
    public function store(array $data, Request $request);

}


