<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminMasterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MasterController extends Controller
{
    public function __construct(
        private readonly AdminMasterService $adminMasterService
    ) {
    }

    /**
     * マスタ設定 管理トップ
     *
     * レビュー項目・検索タグなどへのリンク集をまとめる。
     */
    public function index(Request $request)
    {
        return view('admin.master.index', $this->adminMasterService->getMasterIndexData());
    }

    public function storeCatalog(Request $request, string $catalogKey): RedirectResponse
    {
        $catalog = $this->adminMasterService->getCatalogDefinition($catalogKey);

        abort_unless($catalog, 404);

        $rules = [];
        foreach ($catalog['fields'] as $field) {
            $rules[$field['input']] = ['required', 'string', 'max:255'];
            if ($field['input'] === 'directory') {
                $rules[$field['input']][] = 'alpha_dash';
            }
            $rules[$field['input']][] = Rule::unique($catalog['table'], $field['column']);
        }

        $data = $request->validate($rules);
        $this->adminMasterService->createCatalogRecord($catalogKey, $data);

        return redirect()
            ->route('admin.masters.index')
            ->with('status', $catalog['title'] . 'を登録しました。');
    }
}

