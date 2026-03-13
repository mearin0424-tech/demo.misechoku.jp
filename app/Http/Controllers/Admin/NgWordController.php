<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminMasterService;

class NgWordController extends Controller
{
    public function __construct(
        private readonly AdminMasterService $adminMasterService
    ) {
    }

    /**
     * NGワード管理一覧
     */
    public function index()
    {
        return view('admin.ngwords.index', $this->adminMasterService->getNgWordData());
    }
}

