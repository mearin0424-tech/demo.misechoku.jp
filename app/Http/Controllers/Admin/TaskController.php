<?php

namespace App\Http\Controllers\Admin;

use App\Services\BillingManagementService;
use App\Http\Controllers\Controller;

class TaskController extends Controller
{
    public function __construct(private readonly BillingManagementService $billingManagementService)
    {
    }

    /**
     * 請求・振込タスク管理一覧
     */
    public function index()
    {
        $tasks = $this->billingManagementService->getPendingTasks();

        return view('admin.tasks.index', [
            'tasks' => $tasks,
        ]);
    }
}

