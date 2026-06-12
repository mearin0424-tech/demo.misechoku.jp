<?php

namespace App\Http\Controllers\Shops;

use App\Http\Controllers\Controller;
use App\Models\ShopManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * 店舗スタッフ（=店舗ログインアカウント）管理。
 * 1つの shops に対して複数の shop_managers を持たせるためのCRUD。
 * オーナー(role=1)のみ追加・削除が可能。
 */
class StaffController extends Controller
{
    public function index(): View
    {
        $current = $this->currentManager();

        $managers = ShopManager::query()
            ->where('shop_id', $current->shop_id)
            ->orderBy('role')              // オーナーを先頭に
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        return view('shops.mypage.staff.index', [
            'pageId'           => 'staff',
            'managers'         => $managers,
            'currentManagerId' => $current->id,
            'isOwner'          => $this->isOwner($current),
        ]);
    }

    public function create(): View
    {
        $this->authorizeOwner();

        return view('shops.mypage.staff.create', [
            'pageId' => 'staff',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeOwner();

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('shop_managers', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role'     => ['required', Rule::in([ShopManager::ROLE_OWNER, ShopManager::ROLE_STAFF])],
        ], [
            'email.unique' => 'このメールアドレスは既に登録されています。',
        ]);

        $current = $this->currentManager();
        $now = now();

        ShopManager::create([
            'id'         => $this->generateManagerId(),
            'shop_id'    => $current->shop_id,
            'name'       => (string) $data['name'],
            'email'      => (string) $data['email'],
            'password'   => Hash::make($data['password']),
            'role'       => (int) $data['role'],
            'status'     => ShopManager::STATUS_ACTIVE,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return redirect()
            ->route('shop.mypage.staff.index')
            ->with('message', 'スタッフを追加しました。');
    }

    public function destroy(string $id): RedirectResponse
    {
        $this->authorizeOwner();

        $current = $this->currentManager();

        if ($id === $current->id) {
            return back()->withErrors(['staff' => 'ログイン中の自分自身は削除できません。']);
        }

        $manager = ShopManager::query()
            ->where('shop_id', $current->shop_id)
            ->where('id', $id)
            ->firstOrFail();

        // 最後のオーナーは残す
        if ((int) $manager->role === ShopManager::ROLE_OWNER) {
            $remainingOwners = ShopManager::query()
                ->where('shop_id', $current->shop_id)
                ->where('id', '!=', $manager->id)
                ->where('role', ShopManager::ROLE_OWNER)
                ->where('status', ShopManager::STATUS_ACTIVE)
                ->count();
            if ($remainingOwners === 0) {
                return back()->withErrors(['staff' => '最後のオーナーは削除できません。先に別のオーナーを追加してください。']);
            }
        }

        $manager->delete();

        return redirect()
            ->route('shop.mypage.staff.index')
            ->with('message', 'スタッフを削除しました。');
    }

    private function currentManager(): ShopManager
    {
        /** @var ShopManager $user */
        $user = auth()->guard('shop')->user();
        return $user;
    }

    private function isOwner(ShopManager $manager): bool
    {
        return (int) $manager->role === ShopManager::ROLE_OWNER;
    }

    private function authorizeOwner(): void
    {
        if (!$this->isOwner($this->currentManager())) {
            abort(403, 'この操作にはオーナー権限が必要です。');
        }
    }

    /**
     * shop_managers.id (m00000001~) を採番。
     */
    private function generateManagerId(): string
    {
        $max = DB::table('shop_managers')
            ->where('id', 'like', 'm%')
            ->max('id');
        $next = $max ? ((int) substr($max, 1) + 1) : 1;
        return 'm' . str_pad((string) $next, 8, '0', STR_PAD_LEFT);
    }
}
