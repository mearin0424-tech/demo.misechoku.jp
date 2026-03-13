<?php
namespace App\Repositories\Bank;

use App\Models\BankAccount;
use Illuminate\Http\Request;
          
interface BankAccountRepositoryInterface
{
    public function store(Request $request,$member_id);

}


