<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminMasterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NgWordController extends Controller
{
    public function __construct(
        private readonly AdminMasterService $adminMasterService
    ) {
    }

    /**
     * NGワード管理一覧
     */
    public function index(Request $request)
    {
        $editingId = $request->filled('edit') ? (int) $request->query('edit') : null;
        return view('admin.ngwords.index', $this->adminMasterService->getNgWordData($editingId));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'word' => ['required', 'string', 'max:255'],
        ]);
        $this->adminMasterService->createNgWord(trim($data['word']));
        return redirect()->route('admin.ngwords.index')->with('status', 'NGワードを追加しました。');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $existing = $this->adminMasterService->getNgWord($id);
        abort_unless($existing, 404);

        $data = $request->validate([
            'word' => ['required', 'string', 'max:255'],
        ]);
        $this->adminMasterService->updateNgWord(
            $id,
            trim($data['word']),
            (bool) ($existing->is_active ?? true)
        );
        return redirect()->route('admin.ngwords.index')->with('status', 'NGワードを更新しました。');
    }

    public function destroy(int $id): RedirectResponse
    {
        $existing = $this->adminMasterService->getNgWord($id);
        abort_unless($existing, 404);

        $this->adminMasterService->deleteNgWord($id);
        return redirect()->route('admin.ngwords.index')->with('status', 'NGワードを削除しました。');
    }
}
