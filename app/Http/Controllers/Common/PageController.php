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
     * サービスの特徴（キャスト／店舗共通）
     */
    public function feature()
    {
        $isCast = request()->is('cast/*');
        return view('common.support.feature', compact('isCast'));
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
     * よくある質問（FAQ）（キャスト／店舗共通）
     */
    public function faq()
    {
        $isCast = request()->is('cast/*');
        return view('common.support.faq', compact('isCast'));
    }

    /**
     * お問い合わせ窓口（デモ用ダミーページ）
     */
    public function supportForm()
    {
        return view('common.support.form');
    }
}

