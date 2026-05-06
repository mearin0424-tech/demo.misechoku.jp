<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\PolicyDocument;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return $this->policyDocumentView(PolicyDocument::KEY_ABOUT);
    }

    public function terms(): View
    {
        return $this->policyDocumentView(PolicyDocument::KEY_TERMS);
    }

    public function privacy(): View
    {
        return $this->policyDocumentView(PolicyDocument::KEY_PRIVACY);
    }

    private function policyDocumentView(string $key): View
    {
        $document = PolicyDocument::query()
            ->with(['chapters' => fn ($q) => $q->orderBy('sort_order')->orderBy('id')])
            ->where('key', $key)
            ->first();

        if ($document === null) {
            return view('common.maintenance');
        }

        return view('common.policy-document', [
            'document' => $document,
            'metaSchema' => PolicyDocument::defaultMetaSchema(),
        ]);
    }

    /**
     * ご利用ガイド（キャスト／店舗共通）
     */
    public function htu()
    {
        $isCast = request()->is('cast/*');
        return view('common.support.htu', compact('isCast'));
    }

    /**
     * お問い合わせ窓口（デモ用ダミーページ）
     */
    public function supportForm()
    {
        $isCast = request()->is('cast/*');
        return view('common.support.form', compact('isCast'));
    }
}

