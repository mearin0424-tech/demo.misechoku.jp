<?php
namespace App\Repositories\Ng;

use App\Models\BankAccounts;
use Illuminate\Http\Request;
          
interface NgRepositoryInterface
{
    public function store(Request $request);

}


