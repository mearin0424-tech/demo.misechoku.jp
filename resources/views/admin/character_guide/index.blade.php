@extends('layouts.admin')

@section('title', 'オコジョガイド設定')

@section('content')
<div class="admin-page">
    @include('admin.parts.page-title', [
        'eyebrow' => 'CHARACTER GUIDE',
        'title' => 'オコジョガイド設定',
        'info' => '
            <p>各画面の右下に表示されるオコジョガイドの<strong>表示／非表示</strong>と<strong>セリフ</strong>を画面ごとに設定できます。</p>
            <ul>
                <li>表示ON かつセリフが入力されている画面のみ、オコジョと吹き出しが表示されます。</li>
                <li>セリフが空欄、または表示OFF の画面では、オコジョは出ません（デフォルト文言は持ちません）。</li>
            </ul>
        ',
    ])

    @if (session('status'))
        <div class="admin-alert admin-alert-success">{{ session('status') }}</div>
    @endif

    @php
        $totalScreens = 0;
        $enabledScreens = 0;
        $emptyMessageScreens = 0;
        foreach ($grouped as $rows) {
            foreach ($rows as $row) {
                $totalScreens++;
                if (!empty($row['enabled'])) $enabledScreens++;
                if (empty(trim((string)($row['message'] ?? '')))) $emptyMessageScreens++;
            }
        }
    @endphp
    <section class="dashboard-kpi-grid character-guide-kpis">
        <article class="dashboard-kpi-card">
            <div class="dashboard-kpi-head">
                <div class="dashboard-kpi-title">対応画面（合計）</div>
                <i class="fas fa-window-restore"></i>
            </div>
            <div class="dashboard-kpi-main">
                <span class="dashboard-kpi-value">{{ number_format($totalScreens) }}</span>
                <span class="dashboard-kpi-unit">画面</span>
            </div>
        </article>
        <article class="dashboard-kpi-card">
            <div class="dashboard-kpi-head">
                <div class="dashboard-kpi-title">表示ON</div>
                <i class="fas fa-toggle-on"></i>
            </div>
            <div class="dashboard-kpi-main">
                <span class="dashboard-kpi-value">{{ number_format($enabledScreens) }}</span>
                <span class="dashboard-kpi-unit">画面</span>
            </div>
            <div class="dashboard-kpi-trend is-up">オコジョ登場</div>
        </article>
        <article class="dashboard-kpi-card {{ $emptyMessageScreens > 0 ? 'character-guide-kpi--attention' : '' }}">
            <div class="dashboard-kpi-head">
                <div class="dashboard-kpi-title">セリフ未設定</div>
                <i class="fas fa-comment-slash"></i>
            </div>
            <div class="dashboard-kpi-main">
                <span class="dashboard-kpi-value">{{ number_format($emptyMessageScreens) }}</span>
                <span class="dashboard-kpi-unit">画面</span>
            </div>
            <div class="dashboard-kpi-trend">空欄でも保存可</div>
        </article>
    </section>

    <form method="POST" action="{{ route('admin.character-guide.update') }}">
        @csrf
        @method('PUT')

        @foreach($grouped as $groupKey => $rows)
            <section class="admin-card admin-card-wide">
                <div class="admin-card-head">
                    <div>
                        <h2>{{ $groupLabels[$groupKey] ?? $groupKey }}（{{ count($rows) }}画面）</h2>
                        <p>表示するセリフは 1〜2 文程度がおすすめです（最長 500 文字）。改行は反映されます。</p>
                    </div>
                </div>
                <div class="cg-list">
                    @foreach($rows as $row)
                        <div class="cg-item">
                            <div class="cg-item__head">
                                <label class="cg-item__toggle">
                                    <input type="hidden" name="settings[{{ $row['route_name'] }}][enabled]" value="0">
                                    <input type="checkbox"
                                           name="settings[{{ $row['route_name'] }}][enabled]"
                                           value="1"
                                           @checked($row['enabled'])>
                                    <span class="cg-item__toggle-track" aria-hidden="true">
                                        <span class="cg-item__toggle-thumb"></span>
                                    </span>
                                    <span class="cg-item__toggle-label">表示する</span>
                                </label>
                                <div class="cg-item__heading">
                                    <span class="cg-item__label">{{ $row['label'] }}</span>
                                    <code class="cg-item__route">{{ $row['route_name'] }}</code>
                                </div>
                            </div>
                            <label class="cg-item__field">
                                <span class="cg-item__field-label">セリフ</span>
                                <textarea name="settings[{{ $row['route_name'] }}][message]"
                                          rows="2"
                                          maxlength="500"
                                          placeholder="この画面で表示するセリフを入力（空欄なら吹き出しを表示しません）">{{ $row['message'] }}</textarea>
                            </label>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach

        <div class="cg-save-bar">
            <button type="submit" class="btn-action manage">
                <i class="fas fa-floppy-disk"></i> 設定を保存する
            </button>
        </div>
    </form>
</div>
@endsection

@push('admin-styles')
<style>
.cg-list { display: flex; flex-direction: column; gap: 14px; }
.cg-item {
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 12px;
    padding: 14px 16px;
    background: rgba(255, 255, 255, 0.02);
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.cg-item__head {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
}
.cg-item__heading {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
    flex: 1 1 auto;
}
.cg-item__label {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--admin-text);
    line-height: 1.4;
}
.cg-item__route {
    font-size: 0.7rem;
    color: #a1a1aa;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.08);
    padding: 2px 8px;
    border-radius: 6px;
    width: fit-content;
}

/* トグル */
.cg-item__toggle {
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    user-select: none;
    flex: 0 0 auto;
}
.cg-item__toggle input[type="checkbox"] {
    position: absolute;
    width: 0;
    height: 0;
    opacity: 0;
    pointer-events: none;
}
.cg-item__toggle-track {
    width: 44px;
    height: 24px;
    background: rgba(255,255,255,0.18);
    border-radius: 999px;
    position: relative;
    transition: background 0.2s ease;
    flex: 0 0 auto;
}
.cg-item__toggle-thumb {
    position: absolute;
    top: 2px;
    left: 2px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.25);
    transition: transform 0.2s ease;
}
.cg-item__toggle input[type="checkbox"]:checked ~ .cg-item__toggle-track {
    background: #8b5cf6;
}
.cg-item__toggle input[type="checkbox"]:checked ~ .cg-item__toggle-track .cg-item__toggle-thumb {
    transform: translateX(20px);
}
.cg-item__toggle input[type="checkbox"]:focus-visible ~ .cg-item__toggle-track {
    outline: 2px solid #a78bfa;
    outline-offset: 2px;
}
.cg-item__toggle-label {
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--admin-text);
}

/* テキストエリア */
.cg-item__field {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.cg-item__field-label {
    font-size: 0.72rem;
    font-weight: 700;
    color: #a1a1aa;
    letter-spacing: 0.05em;
}
.cg-item__field textarea {
    width: 100%;
    min-height: 60px;
    padding: 10px 12px;
    border: 1px solid rgba(168, 85, 247, 0.30);
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.05);
    font-size: 0.92rem;
    color: var(--admin-text);
    resize: vertical;
    font-family: inherit;
    line-height: 1.5;
    box-sizing: border-box;
}
.cg-item__field textarea:focus {
    outline: none;
    border-color: #a78bfa;
    box-shadow: 0 0 0 3px rgba(168, 85, 247, 0.18);
}

/* 保存バー */
.cg-save-bar {
    position: sticky;
    bottom: 0;
    background: linear-gradient(180deg, rgba(10,10,10,0), rgba(10,10,10,0.96) 35%);
    padding: 16px 0 8px;
    margin-top: 8px;
    text-align: right;
    z-index: 5;
}

@media (max-width: 720px) {
    .cg-item__head { flex-direction: column; align-items: flex-start; gap: 10px; }
}
</style>
@endpush
