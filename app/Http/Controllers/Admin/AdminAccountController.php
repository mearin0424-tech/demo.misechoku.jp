<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemAccount;
use App\Services\AdminPermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminAccountController extends Controller
{
    public function __construct(
        private readonly AdminPermissionService $permissionService,
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
        $this->permissionService->setRolePermissions($role, $keys);

        return redirect()->route('admin.admin-accounts.roles.edit', $role)
            ->with('status', 'ロール権限を保存しました。');
    }
}
