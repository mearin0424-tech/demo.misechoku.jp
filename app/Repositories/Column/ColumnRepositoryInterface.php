<?php
namespace App\Repositories\Column;

use App\Models\BankAccounts;
use Illuminate\Http\Request;
          
interface ColumnRepositoryInterface
{
    public function store(Request $request);

}


