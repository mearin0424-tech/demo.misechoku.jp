@extends('layouts.app')

@section('title', 'サービスの特徴')

@section('content')
<div class="support-feature-page">
    <div class="support-feature-header">
        <h1 class="support-feature-title">
            サービスの特徴
            <span class="support-feature-badge">
                {{ $isCast ? 'キャスト向け' : '店舗向け' }}
            </span>
        </h1>
        <p class="support-feature-lead">
            ミセチョクが{{ $isCast ? 'キャスト' : '店舗' }}の皆さまに提供する価値を、3つのポイントでまとめました。<br>
            デモ環境のため、以下はイメージ用のダミーコンテンツです。
        </p>
    </div>

    <div class="support-feature-grid">
        <section class="support-feature-card">
            <h2 class="support-feature-card-title">
                <i class="fas fa-bolt"></i>
                直感的にさがせる検索体験
            </h2>
            <p class="support-feature-card-body">
                タイムライン形式の検索画面で、雰囲気や条件を直感的に比較できます。
                {{ $isCast ? '自分に合いそうなお店を「感覚」で見つけられるように設計されています。' : '理想のキャスト像に近い人をスムーズに探せるUIです。' }}
            </p>
        </section>

        <section class="support-feature-card">
            <h2 class="support-feature-card-title">
                <i class="fas fa-comments"></i>
                アプリ内で完結する安全なやりとり
            </h2>
            <p class="support-feature-card-body">
                トーク機能を使って、個人の連絡先を教えずにやりとりができます。
                {{ $isCast ? 'お店とのやりとり履歴も1つの画面で確認できます。' : '複数キャストとの候補調整もアプリ内で完結できます。' }}
            </p>
        </section>

        <section class="support-feature-card">
            <h2 class="support-feature-card-title">
                <i class="fas fa-shield-alt"></i>
                安心して使えるサポート
            </h2>
            <p class="support-feature-card-body">
                ガイド・FAQ・コラムなど、いつでも確認できるサポートコンテンツを用意しています。
                {{ $isCast ? '困ったときはサイドメニューの「SUPPORT」からご確認ください。' : '店舗アカウント向けの運用のコツも順次追加予定です。' }}
            </p>
        </section>
    </div>
</div>
@endsection

@push('styles')
<style>
.support-feature-page {
    padding: 24px 16px 32px;
    color: #f9f5f5;
}
@media (min-width: 768px) {
    .support-feature-page {
        padding: 32px 24px 40px;
    }
}

.support-feature-header {
    margin-bottom: 24px;
}

.support-feature-title {
    font-family: 'Shippori Mincho', 'Noto Sans JP', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
    font-size: 1.4rem;
    margin-bottom: 8px;
    color: var(--color-gold, #d4af37);
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}

.support-feature-badge {
    font-size: 0.75rem;
    padding: 3px 8px;
    border-radius: 999px;
    border: 1px solid rgba(212, 175, 55, 0.6);
    color: #f9f5f5;
}

.support-feature-lead {
    font-size: 0.9rem;
    line-height: 1.6;
    color: #d1c1c1;
}

.support-feature-grid {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
@media (min-width: 768px) {
    .support-feature-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }
}

.support-feature-card {
    background: rgba(20, 7, 15, 0.9);
    border-radius: 16px;
    padding: 16px 14px;
    border: 1px solid rgba(212, 175, 55, 0.4);
}
@media (min-width: 768px) {
    .support-feature-card {
        padding: 18px 20px;
    }
}

.support-feature-card-title {
    font-size: 1.0rem;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.support-feature-card-title i {
    color: var(--color-gold, #d4af37);
}

.support-feature-card-body {
    font-size: 0.85rem;
    line-height: 1.7;
    color: #efe3e3;
}
</style>
@endpush

