<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * 新規登録直後に表示する簡易チュートリアル。
 * キャスト・店舗の 2 ロールで共通ビュー（common.tutorial）を使い、
 * ロール別のスライド内容を配列で渡す。
 * 「はじめる」CTA で該当ロールの home へ遷移。
 */
class TutorialController extends Controller
{
    public function castShow(Request $request): View
    {
        return view('common.tutorial', [
            'role'      => 'cast',
            'title'     => 'ようこそ！',
            'startUrl'  => route('cast.home'),
            'skipUrl'   => route('cast.home'),
            'slides'    => $this->castSlides(),
        ]);
    }

    public function shopShow(Request $request): View
    {
        return view('common.tutorial', [
            'role'      => 'shop',
            'title'     => 'ようこそ！',
            'startUrl'  => route('shop.home'),
            'skipUrl'   => route('shop.home'),
            'slides'    => $this->shopSlides(),
        ]);
    }

    /**
     * @return array<int, array{icon:string, title:string, body:string}>
     */
    private function castSlides(): array
    {
        return [
            [
                'icon'  => 'fa-hand-sparkles',
                'title' => 'ミセチョクへようこそ',
                'body'  => "登録ありがとうございます。\nあなたに合うお店をこれから一緒に探していきます。",
            ],
            [
                'icon'  => 'fa-layer-group',
                'title' => 'SWIPE で店舗を発見',
                'body'  => "画面下 SWIPE から、お店の写真・時給・報酬を上下スワイプでチェック。\nキープ ⭐ で気になるお店を後で見返せます。",
            ],
            [
                'icon'  => 'fa-comment-dots',
                'title' => 'TALK でメッセージ',
                'body'  => "お店とのやり取りは TALK タブから。\n定型文が用意されているので、初心者でも安心してメッセージを送れます。",
            ],
            [
                'icon'  => 'fa-id-card',
                'title' => 'マイページで本人確認',
                'body'  => "本人確認とプロフィール完成度を上げると、お店から声がかかりやすくなります。\n最初にちょっと入力するのがおすすめ！",
            ],
            [
                'icon'  => 'fa-rocket',
                'title' => 'さあ、始めよう',
                'body'  => "準備は完了！\nまずは SWIPE で気になるお店を探してみましょう。",
            ],
        ];
    }

    /**
     * @return array<int, array{icon:string, title:string, body:string}>
     */
    private function shopSlides(): array
    {
        return [
            [
                'icon'  => 'fa-hand-sparkles',
                'title' => 'ミセチョクへようこそ',
                'body'  => "登録ありがとうございます。\nあなたのお店に合うキャストをこれから一緒に探していきます。",
            ],
            [
                'icon'  => 'fa-layer-group',
                'title' => 'SWIPE でキャストを発見',
                'body'  => "画面下 SWIPE から、条件に合いそうなキャストを上下スワイプでチェック。\nキープ ⭐ で候補リストに追加できます。",
            ],
            [
                'icon'  => 'fa-file-lines',
                'title' => '求人票を編集して公開',
                'body'  => "マイページ →「求人票の編集」から、時給・雰囲気・写真を設定。\n許可証の承認後に公開できます。",
            ],
            [
                'icon'  => 'fa-comment-dots',
                'title' => 'TALK でスカウト',
                'body'  => "気になるキャストには TALK からメッセージ。\n定型文と面談日程送信ですぐに面談セットアップができます。",
            ],
            [
                'icon'  => 'fa-rocket',
                'title' => 'さあ、始めよう',
                'body'  => "準備は完了！\nまずは SWIPE でキャストを探してみましょう。",
            ],
        ];
    }
}
