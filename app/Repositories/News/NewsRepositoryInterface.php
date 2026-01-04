<?php
namespace App\Repositories\News;

use App\Models\BankAccounts;
use Illuminate\Http\Request;
          
interface NewsRepositoryInterface
{
    public function store(Request $request);

}


