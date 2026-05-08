<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CharacterGuideService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CharacterGuideController extends Controller
{
    public function __construct(
        private readonly CharacterGuideService $characterGuideService,
    ) {
    }

    /**
     * オコジョガイド設定 一覧
     */
    public function index()
    {
        $rows = $this->characterGuideService->getCatalogWithSettings();
        $groupLabels = $this->characterGuideService->getGroupLabels();

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['group']][] = $row;
        }

        return view('admin.character_guide.index', [
            'grouped' => $grouped,
            'groupLabels' => $groupLabels,
        ]);
    }

    /**
     * オコジョガイド設定 一括保存
     */
    public function update(Request $request): RedirectResponse
    {
        $catalog = $this->characterGuideService->getCatalogRouteNames();

        $rules = [];
        foreach ($catalog as $route) {
            $rules['settings.' . $route . '.enabled'] = ['nullable'];
            $rules['settings.' . $route . '.message'] = ['nullable', 'string', 'max:500'];
        }

        $request->validate($rules);

        $inputs = [];
        $rawSettings = (array) $request->input('settings', []);
        foreach ($catalog as $route) {
            $entry = $rawSettings[$route] ?? [];
            $inputs[$route] = [
                'enabled' => !empty($entry['enabled']),
                'message' => isset($entry['message']) ? trim((string) $entry['message']) : '',
            ];
        }

        $this->characterGuideService->saveAll($inputs);

        return redirect()
            ->route('admin.character-guide.index')
            ->with('status', 'オコジョガイドの設定を保存しました。');
    }
}
