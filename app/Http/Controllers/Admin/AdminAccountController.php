<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemAccount;
use App\Services\AdminOperationLogService;
use App\Services\AdminPermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminAccountController extends Controller
{
    public function __construct(
        private readonly AdminPermissionService $permissionService,
        private readonly AdminOperationLogService $opLog,
    ) {
    }

    /**
     * 運営（管理者）アカウント管理一覧。
     * 実際の system_accounts テーブルを表示し、ロールごとの権限編集ページへの導線を提供する。
     */
    public function index()
    {
        $admins = collect();
        if (Schema::hasTable('system_accounts')) {
            $admins = SystemAccount::query()
                ->orderByDesc('id')
                ->get(['id', 'name', 'email', 'role', 'is_active', 'created_at', 'updated_at']);
        }

        $rolesCatalog = $this->permissionService->rolesCatalog();
        $rolePermissionCounts = [];
        foreach ($rolesCatalog as $role => $_meta) {
            $rolePermissionCounts[$role] = count($this->permissionService->getRolePermissions($role));
        }
        $allCount = count($this->permissionService->allPermissionKeys());

        return view('admin.admin_accounts.index', [
            'admins' => $admins,
            'rolesCatalog' => $rolesCatalog,
            'rolePermissionCounts' => $rolePermissionCounts,
            'allPermissionCount' => $allCount,
        ]);
    }

    /**
     * ロールの権限編集画面（admin は閲覧のみ／staff は編集可）
     */
    public function editRole(string $role)
    {
        $rolesCatalog = $this->permissionService->rolesCatalog();
        abort_unless(isset($rolesCatalog[$role]), 404);

        $catalog = $this->permissionService->permissionCatalog();
        $granted = $this->permissionService->getRolePermissions($role);
        $defaults = $this->permissionService->defaultPermissions()[$role] ?? [];

        return view('admin.admin_accounts.role_edit', [
            'role' => $role,
            'roleMeta' => $rolesCatalog[$role],
            'permissionCatalog' => $catalog,
            'grantedKeys' => $granted,
            'defaultKeys' => $defaults,
            'isLocked' => (bool) ($rolesCatalog[$role]['locked'] ?? false),
        ]);
    }

    /**
     * 運営操作ログ一覧（管理者のみ閲覧）
     */
    public function operationLog(Request $request)
    {
        $action = (string) $request->query('action', '');
        $targetType = (string) $request->query('target_type', '');
        $logs = $this->opLog->search($action !== '' ? $action : null, $targetType !== '' ? $targetType : null, 500);

        return view('admin.admin_accounts.operation_log', [
            'logs' => $logs,
            'filters' => [
                'action' => $action,
                'target_type' => $targetType,
            ],
            'actionOptions' => [
                '' => '全て',
                'cast.suspend' => 'キャスト停止',
                'cast.unsuspend' => 'キャスト停止解除',
                'shop.suspend' => '店舗停止',
                'shop.unsuspend' => '店舗停止解除',
                'cast.private_unlock' => 'キャスト非公開情報の解除',
                'shop.private_unlock' => '店舗非公開情報の解除',
                'role.update' => 'ロール権限変更',
                'verification.cast.approve' => '本人確認 承認',
                'verification.cast.reject' => '本人確認 差戻し',
                'verification.shop.approve' => '店舗書類 承認',
                'verification.shop.reject' => '店舗書類 差戻し',
            ],
            'actionLabel' => fn (string $a) => $this->opLog->actionLabel($a),
        ]);
    }

    public function updateRole(Request $request, string $role): RedirectResponse
    {
        $rolesCatalog = $this->permissionService->rolesCatalog();
        abort_unless(isset($rolesCatalog[$role]), 404);

        if (!empty($rolesCatalog[$role]['locked'])) {
            return redirect()->route('admin.admin-accounts.roles.edit', $role)
                ->with('status', 'このロールはロックされており編集できません。');
        }

        $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ]);

        $keys = (array) $request->input('permissions', []);
        $beforeKeys = $this->permissionService->getRolePermissions($role);
        $this->permissionService->setRolePermissions($role, $keys);
        $afterKeys = $this->permissionService->getRolePermissions($role);

        $this->opLog->record(
            'role.update',
            'role',
            $role,
            'ロール権限を変更: ' . $role,
            [
                'before' => array_values($beforeKeys),
                'after' => array_values($afterKeys),
            ]
        );

        return redirect()->route('admin.admin-accounts.roles.edit', $role)
            ->with('status', 'ロール権限を保存しました。');
    }
}
