<?php
namespace App\Repositories\Tag;

use App\Models\Tag;
use Illuminate\Http\Request;
          
interface TagRepositoryInterface
{
    public function store(Request $request);


}


