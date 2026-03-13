<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminMasterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Unique;
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
        return view('admin.master.index', $this->adminMasterService->getMasterIndexData(
            $request->query('catalog'),
            $request->filled('edit') ? (int) $request->query('edit') : null,
            $request->query('sort', 'created_desc')
        ));
    }

    public function storeCatalog(Request $request, string $catalogKey): RedirectResponse
    {
        $catalog = $this->adminMasterService->getCatalogDefinition($catalogKey);

        abort_unless($catalog, 404);

        $data = $request->validate($this->buildCatalogRules($catalog));
        $this->adminMasterService->createCatalogRecord($catalogKey, $data);

        return redirect()
            ->route('admin.masters.index', [
                'catalog' => $catalogKey,
                'sort' => $request->input('current_sort', 'created_desc'),
            ])
            ->with('status', $catalog['title'] . 'を登録しました。');
    }

    public function updateCatalog(Request $request, string $catalogKey, int $recordId): RedirectResponse
    {
        $catalog = $this->adminMasterService->getCatalogDefinition($catalogKey);

        abort_unless($catalog, 404);
        abort_unless($this->adminMasterService->getCatalogRecord($catalogKey, $recordId), 404);

        $data = $request->validate($this->buildCatalogRules($catalog, $recordId));
        $this->adminMasterService->updateCatalogRecord($catalogKey, $recordId, $data);

        return redirect()
            ->route('admin.masters.index', [
                'catalog' => $catalogKey,
                'sort' => $request->input('current_sort', 'created_desc'),
            ])
            ->with('status', $catalog['title'] . 'を更新しました。');
    }

    private function buildCatalogRules(array $catalog, ?int $ignoreId = null): array
    {
        $rules = [];

        foreach ($catalog['fields'] as $field) {
            $fieldRules = ['required', 'string', 'max:255'];

            if ($field['input'] === 'directory') {
                $fieldRules[] = 'alpha_dash';
            }

            $unique = Rule::unique($catalog['table'], $field['column']);
            if ($ignoreId !== null) {
                /** @var Unique $unique */
                $unique = $unique->ignore($ignoreId);
            }
            $fieldRules[] = $unique;
            $rules[$field['input']] = $fieldRules;
        }

        return $rules;
    }
}

