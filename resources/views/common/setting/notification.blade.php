@extends('layouts.app')

@section('title', '通知設定')

@section('content')
<div class="setting-page">
    <div class="setting-header">
        <h1 class="setting-title">通知設定</h1>
        <p class="setting-lead">
            ミセチョクからのお知らせやトーク通知の受け取り方法を設定します。
        </p>
    </div>

    @if (session('message'))
        <div class="setting-alert setting-alert-success">{{ session('message') }}</div>
    @endif
    @if ($errors->any())
        <div class="setting-alert setting-alert-error">
            @foreach ($errors->all() as $error) {{ $error }} @endforeach
        </div>
    @endif

    @if ($isLoggedIn ?? false)
    <section class="setting-section">
        <h2 class="setting-section-title">LINE連携</h2>
        <div class="setting-row">
            <div class="setting-row-main">
                <div class="setting-row-label">LINEでログイン・通知</div>
                <div class="setting-row-desc">
                    LINEと連携すると、LINEでログインでき、お知らせをLINEに届けられます。
                    @if ($lineLinked ?? false)
                        <strong class="setting-linked">連携済み</strong>
                    @endif
                </div>
            </div>
            @if ($lineLinked ?? false)
                <span class="setting-badge">連携済み</span>
            @else
                <a href="{{ $lineLinkUrl ?? '#' }}" class="setting-btn setting-btn-line">LINEと連携</a>
            @endif
        </div>
    </section>

    @else
    <p class="setting-guest-note">通知の設定はログイン後に利用できます。</p>
    @endif

    <section class="setting-section">
        <h2 class="setting-section-title">ホーム追加（PWA）</h2>
        <div class="setting-row">
            <div class="setting-row-main">
                <div class="setting-row-label">アプリとしてホームに追加</div>
                <div class="setting-row-desc">追加後はブラウザUIなしで起動でき、通知やバッジ表示に対応します。</div>
            </div>
            <button type="button" id="pwa-install-inline-btn" class="setting-btn setting-btn-install" style="display:none;">
                ホームに追加
            </button>
        </div>
        <div id="pwa-ios-guide" class="setting-guide" style="display:none;">
            iPhone/iPad は Safari の「共有」→「ホーム画面に追加」からインストールできます。
        </div>
    </section>

    <section class="setting-section">
        <h2 class="setting-section-title">プッシュ通知</h2>
        <div class="setting-row">
            <div class="setting-row-main">
                <div class="setting-row-label">新着メッセージ</div>
                <div class="setting-row-desc">トークで新しいメッセージを受信したときに通知します。</div>
            </div>
            <button type="button" id="push-enable-btn" class="setting-btn setting-btn-push">
                通知を有効化
            </button>
        </div>

        <div class="setting-row">
            <div class="setting-row-main">
                <div class="setting-row-label">{{ $isCast ? 'お店からのオファー' : 'キャストからの応募・問い合わせ' }}</div>
                <div class="setting-row-desc">
                    {{ $isCast ? '気になるお店からオファーが届いたときに通知します。' : '候補のキャストから応募やメッセージが届いたときに通知します。' }}
                </div>
            </div>
            <button type="button" id="push-test-btn" class="setting-btn setting-btn-test">
                テスト通知
            </button>
        </div>
    </section>

    <section class="setting-section">
        <h2 class="setting-section-title">メール通知</h2>
        <div class="setting-row">
            <div class="setting-row-main">
                <div class="setting-row-label">重要なお知らせ</div>
                <div class="setting-row-desc">メンテナンス情報や重要なお知らせのみメールで受け取ります。</div>
            </div>
            <button type="button" class="setting-toggle setting-toggle--demo" aria-pressed="false">
                OFF
            </button>
        </div>
    </section>
</div>
@endsection

@push('styles')
<style>
.setting-page {
    padding: 24px 16px 32px;
    color: #f9f5f5;
}
@media (min-width: 768px) {
    .setting-page {
        padding: 32px 24px 40px;
    }
}

.setting-header {
    margin-bottom: 24px;
}

.setting-title {
    font-family: 'Shippori Mincho', 'Noto Sans JP', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
    font-size: 1.4rem;
    margin-bottom: 8px;
    color: var(--color-gold, #d4af37);
}

.setting-lead {
    font-size: 0.9rem;
    line-height: 1.6;
    color: #d1c1c1;
}

.setting-section {
    margin-bottom: 18px;
    background: rgba(20, 7, 15, 0.9);
    border-radius: 16px;
    padding: 14px 12px 10px;
    border: 1px solid rgba(212, 175, 55, 0.4);
}
@media (min-width: 768px) {
    .setting-section {
        padding: 16px 16px 12px;
    }
}

.setting-section-title {
    font-size: 0.95rem;
    margin-bottom: 8px;
    color: #f9f5f5;
}

.setting-row {
    padding: 10px 4px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.setting-row + .setting-row {
    border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.setting-row-main {
    flex: 1;
    min-width: 0;
}

.setting-row-label {
    font-size: 0.9rem;
    margin-bottom: 2px;
}

.setting-row-desc {
    font-size: 0.78rem;
    color: #b69f9f;
}

.setting-toggle {
    min-width: 70px;
    border-radius: 999px;
    border: none;
    padding: 6px 10px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: default;
    text-align: center;
}

.setting-toggle--demo[aria-pressed="true"] {
    background: linear-gradient(135deg, #15803d, #16a34a);
    color: #f9f5f5;
}

.setting-toggle--demo[aria-pressed="false"] {
    background: rgba(55, 65, 81, 0.8);
    color: #e5e7eb;
}

.setting-alert { padding: 12px 14px; border-radius: 12px; margin-bottom: 16px; }
.setting-alert-success { background: rgba(22, 163, 74, 0.2); border: 1px solid rgba(22, 163, 74, 0.5); color: #bbf7d0; }
.setting-alert-error { background: rgba(185, 28, 28, 0.2); border: 1px solid rgba(248, 113, 113, 0.5); color: #fecaca; }
.setting-linked { color: var(--color-gold, #d4af37); }
.setting-badge { font-size: 0.8rem; padding: 6px 12px; border-radius: 999px; background: rgba(22, 163, 74, 0.3); color: #bbf7d0; }
.setting-btn { display: inline-block; padding: 10px 18px; border-radius: 12px; font-size: 0.88rem; font-weight: 600; text-decoration: none; transition: opacity 0.2s; }
.setting-btn:hover { opacity: 0.9; }
.setting-btn-line { background: #06c755; color: #fff; }
.setting-btn-install { background: #8b5cf6; color: #fff; border: none; cursor: pointer; }
.setting-btn-push { background: #1f2937; color: #f9f5f5; border: 1px solid rgba(212, 175, 55, 0.55); cursor: pointer; }
.setting-btn-test { background: #065f46; color: #ecfdf5; border: 1px solid rgba(52, 211, 153, 0.65); cursor: pointer; }
.setting-guide { margin: 2px 4px 8px; font-size: 0.78rem; color: #d6c6c6; }
.setting-guest-note { color: #b69f9f; font-size: 0.9rem; margin-bottom: 20px; }
</style>
@endpush

