@extends('layouts.app-v2')

@section('title', 'ご利用ガイド')

@section('content')
<div class="support-htu-page">
    <div class="support-htu-header">
        <h1 class="support-htu-title">
            ご利用ガイド
            <span class="support-htu-badge">
                {{ $isCast ? 'キャスト向け' : '店舗向け' }}
            </span>
        </h1>
        <p class="support-htu-lead">
            初めてミセチョクを利用する{{ $isCast ? 'キャスト' : '店舗' }}の方向けに、基本的な使い方の流れをステップ形式でまとめました。<br>
            デモ環境のため、画面・文言は一部実際と異なる場合があります。
        </p>
    </div>

    <div class="support-htu-feature-grid">
        <section class="support-htu-feature-card">
            <h2 class="support-htu-feature-title"><i class="fas fa-bolt"></i> 直感的にさがせる検索体験</h2>
            <p class="support-htu-feature-body">
                タイムライン形式の検索画面で、雰囲気や条件を直感的に比較できます。
                {{ $isCast ? '自分に合いそうなお店を感覚的に見つけやすい設計です。' : '理想のキャスト像に近い人をスムーズに探せるUIです。' }}
            </p>
        </section>
        <section class="support-htu-feature-card">
            <h2 class="support-htu-feature-title"><i class="fas fa-comments"></i> アプリ内で完結するやりとり</h2>
            <p class="support-htu-feature-body">
                トーク機能を使って、個人の連絡先を教えずにやりとりできます。
                {{ $isCast ? 'お店とのやりとり履歴も1つの画面で確認できます。' : '複数キャストとの候補調整もアプリ内で完結できます。' }}
            </p>
        </section>
        <section class="support-htu-feature-card">
            <h2 class="support-htu-feature-title"><i class="fas fa-shield-alt"></i> 使い方ガイドとサポート</h2>
            <p class="support-htu-feature-body">
                ご利用ガイド・問い合わせ窓口・お役立ちコラムを通じて、利用中の不明点を確認できます。
            </p>
        </section>
    </div>

    <ol class="support-htu-steps">
        <li class="support-htu-step">
            <div class="support-htu-step-header">
                <span class="support-htu-step-number">STEP 1</span>
                <h2 class="support-htu-step-title">アカウント登録・ログイン</h2>
            </div>
            <p class="support-htu-step-body">
                サイドメニューやトップ画面からログイン画面へ進み、{{ $isCast ? 'キャスト用のアカウント' : '店舗用のアカウント' }}を作成します。
                登録後は同じメールアドレスとパスワードでログインできます。
            </p>
        </li>

        <li class="support-htu-step">
            <div class="support-htu-step-header">
                <span class="support-htu-step-number">STEP 2</span>
                <h2 class="support-htu-step-title">
                    {{ $isCast ? 'プロフィールを整える' : '店舗情報・求人票を整える' }}
                </h2>
            </div>
            <p class="support-htu-step-body">
                マイページから{{ $isCast ? 'プロフィールや希望条件' : '店舗プロフィールや求人票' }}を編集できます。
                写真・自己紹介・エリアなど、ミスマッチを防ぐための情報をできるだけ充実させましょう。
            </p>
        </li>

        <li class="support-htu-step">
            <div class="support-htu-step-header">
                <span class="support-htu-step-number">STEP 3</span>
                <h2 class="support-htu-step-title">
                    {{ $isCast ? 'お店をさがす・オファーを確認する' : 'キャストをさがす・オファーを送る' }}
                </h2>
            </div>
            <p class="support-htu-step-body">
                「SEARCH」や「LIKES」タブから、気になる{{ $isCast ? 'お店' : 'キャスト' }}をチェックします。
                気になった相手には「いいね」やメッセージでコンタクトを取ることができます。
            </p>
        </li>

        <li class="support-htu-step">
            <div class="support-htu-step-header">
                <span class="support-htu-step-number">STEP 4</span>
                <h2 class="support-htu-step-title">トークで条件をすり合わせる</h2>
            </div>
            <p class="support-htu-step-body">
                トーク画面から、具体的な日程や条件の相談を行います。
                個人の連絡先を開示する前に、アプリ上で大枠のイメージをすり合わせることをおすすめしています。
            </p>
        </li>
    </ol>
</div>
@endsection

@push('styles')
<style>
.support-htu-page {
    padding: 24px 16px 32px;
    color: #f9f5f5;
}
@media (min-width: 768px) {
    .support-htu-page {
        padding: 32px 24px 40px;
    }
}

.support-htu-header {
    margin-bottom: 24px;
}

.support-htu-title {
    font-family: var(--font-sans);
    font-size: 1.4rem;
    margin-bottom: 8px;
    color: var(--color-gold, #eba8c8);
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}

.support-htu-badge {
    font-size: 0.75rem;
    padding: 3px 8px;
    border-radius: 999px;
    border: 1px solid rgba(var(--accent-rgb, 214, 112, 162), 0.6);
    color: #f9f5f5;
}

.support-htu-lead {
    font-size: 0.9rem;
    line-height: 1.6;
    color: #d1c1c1;
}

.support-htu-steps {
    list-style: none;
    padding: 0;
    margin: 18px 0 0;
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.support-htu-feature-grid { display: flex; flex-direction: column; gap: 12px; }
@media (min-width: 768px) {
    .support-htu-feature-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
}
.support-htu-feature-card { background: rgba(20, 7, 15, 0.9); border-radius: 16px; padding: 14px 12px; border: 1px solid rgba(var(--accent-rgb, 214, 112, 162), 0.4); }
.support-htu-feature-title { font-size: 0.95rem; display: flex; align-items: center; gap: 8px; margin-bottom: 6px; }
.support-htu-feature-title i { color: var(--color-gold, #eba8c8); }
.support-htu-feature-body { font-size: 0.82rem; line-height: 1.7; color: #efe3e3; }

.support-htu-step {
    background: rgba(20, 7, 15, 0.9);
    border-radius: 16px;
    padding: 16px 14px;
    border: 1px solid rgba(var(--accent-rgb, 214, 112, 162), 0.4);
}
@media (min-width: 768px) {
    .support-htu-step {
        padding: 18px 20px;
    }
}

.support-htu-step-header {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 10px;
    margin-bottom: 6px;
}

.support-htu-step-number {
    font-size: 0.72rem;
    padding: 3px 8px;
    border-radius: 999px;
    background: rgba(var(--accent-rgb, 214, 112, 162), 0.15);
    color: var(--color-gold, #eba8c8);
}

.support-htu-step-title {
    font-size: 1.0rem;
}

.support-htu-step-body {
    margin-top: 4px;
    font-size: 0.85rem;
    line-height: 1.7;
    color: #efe3e3;
}
</style>
@endpush

