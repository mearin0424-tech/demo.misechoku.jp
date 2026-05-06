@extends('layouts.app')

@section('title', '通知設定')

@section('content')
<div class="setting-page">
    <div class="setting-header">
        <h1 class="setting-title">通知設定</h1>
        <p class="setting-lead">ミセチョクからのリマインダー通知の受け取り方法を設定します。</p>
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
        <h2 class="setting-section-title">ホーム追加（PWA）</h2>
        <div class="setting-row">
            <div class="setting-row-main">
                <div class="setting-row-label">アプリとしてホームに追加</div>
                <div class="setting-row-desc">追加後はブラウザUIなしで起動でき、通知やバッジ表示に対応します。</div>
            </div>
            <button type="button" id="pwa-install-inline-btn" class="setting-btn setting-btn-install" style="display:none;">ホームに追加</button>
        </div>
        <div id="pwa-ios-guide" class="setting-guide" style="display:none;">
            iPhone/iPad は Safari の「共有」→「ホーム画面に追加」からインストールできます。
        </div>
    </section>

    <section class="setting-section">
        <h2 class="setting-section-title">リマインダー通知</h2>
        <form method="POST" action="{{ route('setting.notification.update') }}">
            @csrf
            <div class="setting-row">
                <div class="setting-row-main">
                    <div class="setting-row-label">通知チャンネル（Push）</div>
                    <div class="setting-row-desc">PWAのプッシュ通知を使って面接日・期限を通知します。</div>
                </div>
                <label class="setting-check"><input type="checkbox" name="push_enabled" value="1" {{ !empty($notificationPrefs['push_enabled']) ? 'checked' : '' }}> ON</label>
            </div>
            <div class="setting-row">
                <div class="setting-row-main">
                    <div class="setting-row-label">通知チャンネル（LINE）</div>
                    <div class="setting-row-desc">LINE連携済みアカウントへ同内容の通知を送ります。</div>
                </div>
                <label class="setting-check"><input type="checkbox" name="line_enabled" value="1" {{ !empty($notificationPrefs['line_enabled']) ? 'checked' : '' }}> ON</label>
            </div>
            <div class="setting-row">
                <div class="setting-row-main">
                    <div class="setting-row-label">面接リマインダー</div>
                    <div class="setting-row-desc">面接24時間前と3時間前に通知します。</div>
                </div>
                <label class="setting-check"><input type="checkbox" name="interview_reminder_enabled" value="1" {{ !empty($notificationPrefs['interview_reminder_enabled']) ? 'checked' : '' }}> ON</label>
            </div>
            <div class="setting-row">
                <div class="setting-row-main">
                    <div class="setting-row-label">期限リマインダー</div>
                    <div class="setting-row-desc">請求の支払期限（前日/当日/超過）を通知します。</div>
                </div>
                <label class="setting-check"><input type="checkbox" name="deadline_reminder_enabled" value="1" {{ !empty($notificationPrefs['deadline_reminder_enabled']) ? 'checked' : '' }}> ON</label>
            </div>
            <div class="setting-row">
                <div class="setting-row-main">
                    <div class="setting-row-label">Push購読（端末登録）</div>
                    <div class="setting-row-desc">この端末を通知先として登録します。</div>
                </div>
                <button type="button" id="push-enable-btn" class="setting-btn setting-btn-push">通知を有効化</button>
            </div>
            <div class="setting-row">
                <div class="setting-row-main">
                    <div class="setting-row-label">Pushテスト</div>
                    <div class="setting-row-desc">現在ログイン中のアカウント宛にテスト通知を送信します。</div>
                </div>
                <button type="button" id="push-test-btn" class="setting-btn setting-btn-test">テスト通知</button>
            </div>
            <div class="setting-row" style="justify-content:flex-end;">
                <button type="submit" class="setting-btn setting-btn-test">設定を保存</button>
            </div>
        </form>
    </section>
    @else
    <p class="setting-guest-note">通知の設定はログイン後に利用できます。</p>
    @endif
</div>
@endsection

@push('styles')
<style>
.setting-page { padding: 24px 16px 32px; color: #f9f5f5; }
@media (min-width: 768px) { .setting-page { padding: 32px 24px 40px; } }
.setting-header { margin-bottom: 24px; }
.setting-title { font-family: 'Shippori Mincho','Noto Sans JP',sans-serif; font-size: 1.4rem; margin-bottom: 8px; color: var(--color-gold, #d4af37); }
.setting-lead { font-size: 0.9rem; line-height: 1.6; color: #d1c1c1; }
.setting-section { margin-bottom: 18px; background: rgba(20, 7, 15, 0.9); border-radius: 16px; padding: 14px 12px 10px; border: 1px solid rgba(212, 175, 55, 0.4); }
@media (min-width: 768px) { .setting-section { padding: 16px 16px 12px; } }
.setting-section-title { font-size: 0.95rem; margin-bottom: 8px; color: #f9f5f5; }
.setting-row { padding: 10px 4px; display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.setting-row + .setting-row { border-top: 1px solid rgba(255,255,255,0.05); }
.setting-row-main { flex: 1; min-width: 0; }
.setting-row-label { font-size: 0.9rem; margin-bottom: 2px; }
.setting-row-desc { font-size: 0.78rem; color: #b69f9f; }
.setting-check { font-size: 0.86rem; color: #f5f5f5; display: inline-flex; align-items: center; gap: 8px; white-space: nowrap; }
.setting-check input { width: 16px; height: 16px; }
.setting-alert { padding: 12px 14px; border-radius: 12px; margin-bottom: 16px; }
.setting-alert-success { background: rgba(22,163,74,0.2); border: 1px solid rgba(22,163,74,0.5); color: #bbf7d0; }
.setting-alert-error { background: rgba(185,28,28,0.2); border: 1px solid rgba(248,113,113,0.5); color: #fecaca; }
.setting-btn { display: inline-block; padding: 10px 18px; border-radius: 12px; font-size: 0.88rem; font-weight: 600; text-decoration: none; border: none; transition: opacity 0.2s; cursor: pointer; }
.setting-btn:hover { opacity: 0.9; }
.setting-btn-install { background: #8b5cf6; color: #fff; }
.setting-btn-push { background: #1f2937; color: #f9f5f5; border: 1px solid rgba(212,175,55,0.55); }
.setting-btn-test { background: #065f46; color: #ecfdf5; border: 1px solid rgba(52,211,153,0.65); }
.setting-guide { margin: 2px 4px 8px; font-size: 0.78rem; color: #d6c6c6; }
.setting-guest-note { color: #b69f9f; font-size: 0.9rem; margin-bottom: 20px; }
</style>
@endpush
