<?php
namespace App\Repositories\Download;

use App\Models\Download;
use Illuminate\Http\Request;
          
interface DownloadRepositoryInterface
{
    public function store(Request $request);

}


