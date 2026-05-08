@extends('layouts.app')

@section('title', 'マイページ - プロフィール確認')
@section('body-class', 'page-cast-mypage')

@section('header')
<header id="global-header" class="cast-mypage-custom-header">
    <div class="header-left">
        <a href="javascript:history.back()" class="btn-back" aria-label="戻る">
            <i class="fas fa-chevron-left"></i>
        </a>
    </div>
    <div class="header-center-title">
        <span class="header-title-main header-title-serif">MyPage</span>
    </div>
    <div class="header-right">
        <button id="btn-header-notification" class="header-icon-btn" aria-label="通知">
            <i class="fas fa-bell"></i>
            @if(isset($unreadNewsCount) && $unreadNewsCount > 0)
                <span class="badge-notify">{{ $unreadNewsCount }}</span>
            @else
                <span class="badge-notify">1</span>
            @endif
        </button>
        <button id="btn-header-menu" class="header-icon-btn" aria-label="メニュー">
            <i class="fas fa-bars"></i>
        </button>
    </div>
</header>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/cast_profile.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
<style>
    /* ヘッダタイトルは global の gold-gradient を使うため上書きしない */
    .cast-mypage-custom-header .btn-back,
    .cast-mypage-custom-header .header-icon-btn { color: #dcb568; }
    .cast-mypage-custom-header #btn-header-notification .badge-notify { background: #ef4444; color: #fff; }
    .cast-mypage-v2 .mypage-shop-name { color: #fff; letter-spacing: 0.08em; font-size: clamp(1.7rem, 6.4vw, 2.1rem); }
    .cast-mypage-v2 .mypage-hero { align-items: flex-start; gap: 14px; margin-bottom: 20px; }
    .cast-mypage-v2 .shop-icon-wrapper { width: 84px; height: 84px; border-radius: 999px; background: #000; overflow: hidden; border: 3px solid rgba(220, 181, 104,0.8); box-shadow: 0 6px 16px rgba(0,0,0,0.35); }
    .cast-mypage-v2 .shop-icon-main { border: 0; border-radius: 999px; box-shadow: none; }
    .cast-mypage-v2 .btn-add-icon { display: none !important; }
    .cast-mypage-v2 .shop-word-bubble { border: 0; border-radius: 12px; background: #f5ebd6; color: #3a2f2b; box-shadow: 0 8px 16px rgba(0,0,0,0.2); }
    .cast-mypage-v2 .shop-word-bubble::after { left: -8px; top: 50%; margin-top: -6px; width: 0; height: 0; border-top: 6px solid transparent; border-right: 10px solid #f5ebd6; border-bottom: 6px solid transparent; border-left: 0; background: transparent; transform: none; }
    .cast-mypage-v2 .shop-word-text { color: #3a2f2b; font-weight: 700; font-size: 0.82rem; line-height: 1.55; }
    .cast-mypage-v2 .shop-word-bubble-updated { color: #b5a69d; font-size: 0.7rem; font-weight: 700; }
    .cast-mypage-v2 .btn-word-edit { width: 24px; height: 24px; color: #a89050; }
    .cast-flat-stats { display: flex; align-items: center; gap: 10px; margin: 4px 0 24px; padding: 0 6px; }
    .cast-bonus-panel { flex: 1; min-width: 0; }
    .cast-bonus-label { color: #dcb568; font-size: 12px; font-weight: 700; letter-spacing: 0.08em; margin-bottom: 4px; }
    .cast-bonus-value { display: flex; align-items: baseline; gap: 2px; }
    .cast-bonus-value .yen { color: #34d399; font-size: 18px; font-weight: 700; }
    .cast-bonus-value .amount { color: #fff; font-size: 32px; font-weight: 800; line-height: 1; }
    .cast-like-panel { width: 66px; flex-shrink: 0; text-align: center; opacity: 0.88; }
    .cast-like-panel i { color: rgba(244, 114, 182, 0.82); font-size: 16px; margin-bottom: 4px; }
    .cast-like-panel .n { color: #c9b6ae; font-size: 17px; font-weight: 700; line-height: 1; }
    .cast-profile-head { display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid rgba(220, 181, 104, 0.2); padding-bottom: 9px; margin-bottom: 16px; }
    .cast-profile-head h2 { margin: 0; color: #dcb568; font-size: 1.05rem; font-family: var(--font-serif); font-weight: 600; letter-spacing: 0.06em; }
    .cast-profile-card { background: rgba(26,10,14,0.6); border: 1px solid rgba(74,29,40,0.4); border-radius: 12px; overflow: hidden; }
    .cast-block { margin-bottom: 20px; }
    .cast-block-title { color: #dcb568; font-size: 0.92rem; font-weight: 700; margin: 0 0 9px; display: flex; align-items: center; gap: 6px; }
    .cast-block-title .lucide-icon { width: 14px; height: 14px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; flex-shrink: 0; }
    .cast-row { display: flex; gap: 10px; padding: 12px 14px; border-bottom: 1px solid rgba(74,29,40,0.4); }
    .cast-row:last-child { border-bottom: 0; }
    .cast-metrics-row { display: flex; flex-direction: column; gap: 8px; padding: 12px 14px; border-bottom: 1px solid rgba(74,29,40,0.4); }
    .cast-metrics-row:last-child { border-bottom: 0; }
    .cast-row.two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 0; padding: 0; }
    .cast-row.two-col > div { display: flex; gap: 8px; padding: 12px 14px; }
    .cast-row.two-col > div:first-child { border-right: 1px solid rgba(74,29,40,0.4); }
    .cast-k { width: 104px; flex-shrink: 0; color: #b5a69d; font-size: 0.8rem; }
    .cast-k.mini { width: 42px; }
    .cast-v { color: #fff; font-weight: 700; font-size: 0.86rem; line-height: 1.55; word-break: break-word; }
    .cast-v.muted { color: #7e6f69; font-weight: 600; }
    .cast-zip { margin-top: 2px; color: #b5a69d; font-size: 0.72rem; }
    .cast-badges { display: flex; flex-wrap: wrap; gap: 6px; }
    .cast-badge { display: inline-flex; padding: 6px 11px; border-radius: 6px; background: rgba(17, 12, 10, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #eae0d5; font-size: 0.76rem; font-weight: 700; }
    .cast-inline-action { margin-left: auto; font-size: 0.7rem; color: #dcb568; border: 1px solid rgba(220, 181, 104,0.35); border-radius: 999px; padding: 5px 9px; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
    .cast-inline-action .lucide-icon { width: 12px; height: 12px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
</style>
@endpush

@section('content')
@php $subImages = $subImages ?? []; @endphp
<div class="mypage-page contents inner animate-fadeIn cast-mypage-v2">
    <section class="mypage-area">
        @php
            $displayName = $cast['nickname'] ?? $cast['name'] ?? '--';
            $birthdayText = (!empty($cast['birth_year']) && !empty($cast['birth_month']) && !empty($cast['birth_day']))
                ? $cast['birth_year'] . '年' . $cast['birth_month'] . '月' . $cast['birth_day'] . '日'
                : '--';
            $heightText = !empty($cast['height']) ? ((string) $cast['height'] . ' cm') : '--';
            $weightText = !empty($cast['weight']) ? ((string) $cast['weight'] . ' kg') : '--';
            $bodyText = trim(implode(' / ', [
                $cast['bust'] ?: '--',
                $cast['waist'] ?: '--',
                $cast['hip'] ?: '--',
            ]));
            $addressText = trim((string) ($cast['pref'] ?? '') . (string) ($cast['city'] ?? '') . (string) ($cast['addr1'] ?? ''));
            $addressText = $addressText !== '' ? $addressText : '--';
            $zipText = !empty($cast['zip']) ? ('〒' . $cast['zip']) : '--';
        @endphp
        <h1 class="mypage-shop-name serif-font">{{ $displayName }}</h1>

        {{-- アイコン＋アピール（お店同様・編集可能） --}}
        <div class="mypage-hero">
            <div class="shop-icon-wrapper">
                <img src="{{ (isset($subImages[0]) ? $subImages[0]['url'] : null) ?? $cast['img'] ?? asset('assets/images/common/no-image.png') }}" class="shop-icon-main" id="main-icon-display" alt="">
            </div>
            <div class="shop-word-bubble glass-panel">
                <p id="display-word" class="shop-word-text {{ empty(trim($cast['word'] ?? '')) ? 'is-placeholder' : '' }}" data-placeholder="今、何してる？（タイムラインに公開されます）">{{ !empty(trim($cast['word'] ?? '')) ? $cast['word'] : '今、何してる？（タイムラインに公開されます）' }}</p>
                <div class="shop-word-bubble-footer">
                    <span id="display-word-updated" class="shop-word-bubble-updated">最終更新 {{ $cast['appeal_updated_at'] ?? '未設定' }}</span>
                    <span class="shop-word-bubble-timeline-icon" aria-hidden="true" title="タイムラインに公開されます">
                        <i class="fas fa-globe"></i>
                    </span>
                    <button type="button" class="btn-word-edit" id="open-word-edit-btn" aria-label="ひとことを編集">
                        <i class="fas fa-pen"></i>
                    </button>
                </div>
            </div>
        </div>
        <p class="shop-word-bubble-help">※更新するとタイムラインに反映されます</p>

        <div class="cast-flat-stats" aria-label="統計">
            <div class="cast-bonus-panel">
                <div class="cast-bonus-label">獲得したボーナス金合計</div>
                <div class="cast-bonus-value">
                    <span class="yen">¥</span>
                    <span class="amount">{{ number_format((int) ($cast['bonus_total'] ?? 0)) }}</span>
                </div>
            </div>
            <div class="cast-like-panel">
                <i class="fas fa-heart"></i>
                <div class="n">{{ number_format((int) ($cast['like_cnt'] ?? 0)) }}</div>
            </div>
        </div>

        <div class="mypage-detail-box">
            {{-- メニュー（プロフィール情報より上） --}}
            @include('casts.mypage.parts.menu', ['current' => 'profile', 'fullWidth' => false])

            {{-- プロフィール情報：編集ボタン＋5カテゴリ --}}
            <div class="mypage-section profile-info-section">
                <div class="cast-profile-head">
                    <h2>Profile Information</h2>
                    <a href="{{ route('cast.profile.edit') }}" class="btn-outline-gold">編集</a>
                </div>

                <div class="cast-block">
                    <h3 class="cast-block-title">
                        <svg class="lucide-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 21a8 8 0 0 0-16 0"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        基本情報
                    </h3>
                    <div class="cast-profile-card">
                        <div class="cast-row"><div class="cast-k">生年月日</div><div class="cast-v {{ $birthdayText === '--' ? 'muted' : '' }}">{{ $birthdayText }}</div></div>
                        <div class="cast-metrics-row">
                            <div class="metric-display-row">
                                <div class="metric-display-item">
                                    <span class="metric-display-label">Height</span>
                                    <span class="metric-display-value {{ empty($cast['height']) ? 'is-empty' : '' }}">
                                        {{ $cast['height'] ?: '--' }}<small>cm</small>
                                    </span>
                                </div>
                                <div class="metric-display-item">
                                    <span class="metric-display-label">Weight</span>
                                    <span class="metric-display-value {{ empty($cast['weight']) ? 'is-empty' : '' }}">
                                        {{ $cast['weight'] ?: '--' }}<small>kg</small>
                                    </span>
                                </div>
                            </div>
                            <div class="bwh-display" aria-label="3サイズ">
                                <div class="bwh-display-pill {{ empty($cast['bust']) ? 'is-empty' : '' }}">
                                    <b>B</b><span>{{ $cast['bust'] ?: '--' }}</span>
                                </div>
                                <div class="bwh-display-pill {{ empty($cast['waist']) ? 'is-empty' : '' }}">
                                    <b>W</b><span>{{ $cast['waist'] ?: '--' }}</span>
                                </div>
                                <div class="bwh-display-pill {{ empty($cast['hip']) ? 'is-empty' : '' }}">
                                    <b>H</b><span>{{ $cast['hip'] ?: '--' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="cast-row">
                            <div class="cast-k">住所</div>
                            <div class="cast-v {{ $addressText === '--' ? 'muted' : '' }}">
                                {{ $addressText }}
                                <div class="cast-zip {{ $zipText === '--' ? 'cast-v muted' : '' }}">{{ $zipText }}</div>
                            </div>
                        </div>
                        <div class="cast-row"><div class="cast-k">自己PR</div><div class="cast-v {{ empty($cast['pr'] ?? '') ? 'muted' : '' }}">{!! !empty($cast['pr'] ?? '') ? nl2br(e($cast['pr'])) : '--' !!}</div></div>
                    </div>
                </div>

                <div class="cast-block">
                    <h3 class="cast-block-title">
                        <svg class="lucide-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3l1.9 4.7L19 9.6l-4.1 2.9 1.6 5L12 14.8 7.5 17.5l1.6-5L5 9.6l5.1-1.9z"></path></svg>
                        接客タイプ・タグ
                    </h3>
                    <div class="cast-profile-card">
                        <div class="cast-row">
                            <div class="cast-k">接客タイプ診断</div>
                            <div class="cast-v">
                                <span class="cast-badge">{{ !empty($cast['personality_type']) ? $cast['personality_type'] : '--' }}</span>
                            </div>
                            <a href="{{ asset('personality-test') }}" target="_blank" rel="noopener noreferrer" class="cast-inline-action">
                                <svg class="lucide-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M14 3h7v7"></path><path d="M10 14L21 3"></path><path d="M21 14v7h-7"></path><path d="M3 10V3h7"></path><path d="M3 21l11-11"></path></svg>
                                接客タイプ診断を開く
                            </a>
                        </div>
                        <div class="cast-row"><div class="cast-k">ルックス</div><div class="cast-v {{ empty($cast['my_field'] ?? '') ? 'muted' : '' }}">{{ $cast['my_field'] ?? '--' }}</div></div>
                        <div class="cast-row"><div class="cast-k">性格・内面</div><div class="cast-v {{ empty($cast['my_inner_skills'] ?? '') ? 'muted' : '' }}">{{ $cast['my_inner_skills'] ?? '--' }}</div></div>
                    </div>
                </div>

                <div class="cast-block">
                    <h3 class="cast-block-title">
                        <svg class="lucide-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="7" width="18" height="13" rx="2"></rect><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><path d="M3 12h18"></path></svg>
                        経歴・スキル
                    </h3>
                    <div class="cast-profile-card">
                        <div class="cast-row"><div class="cast-k">ナイトワーク経験</div><div class="cast-v {{ empty($cast['night_work_label']) ? 'muted' : '' }}">{{ $cast['night_work_label'] ?: '--' }}</div></div>
                        <div class="cast-row"><div class="cast-k">現職業</div><div class="cast-v {{ empty(($cast['profession'] ?? ($cast['current_job'] ?? ''))) ? 'muted' : '' }}">{!! !empty($cast['profession'] ?? '') ? nl2br(e($cast['profession'])) : (!empty($cast['current_job'] ?? '') ? nl2br(e($cast['current_job'])) : '--') !!}</div></div>
                    </div>
                </div>

                <div class="cast-block">
                    <h3 class="cast-block-title">
                        <svg class="lucide-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"></rect><path d="M16 2v4"></path><path d="M8 2v4"></path><path d="M3 10h18"></path><path d="m9 16 2 2 4-4"></path></svg>
                        希望の職種・働き方
                    </h3>
                    <div class="cast-profile-card">
                        <div class="cast-row"><div class="cast-k">希望職種</div><div class="cast-v {{ empty($cast['industry_names'] ?? ($cast['desired_job'] ?? '')) ? 'muted' : '' }}">{{ $cast['industry_names'] ?? ($cast['desired_job'] ?? '--') }}</div></div>
                        <div class="cast-row"><div class="cast-k">シフト希望</div><div class="cast-v {{ empty($cast['memo_data']['work_where'] ?? ($cast['memo_data']['shift_hope'] ?? '')) ? 'muted' : '' }}">{{ $cast['memo_data']['work_where'] ?? ($cast['memo_data']['shift_hope'] ?? '--') }}</div></div>
                        <div class="cast-row"><div class="cast-k">勤務時間帯</div><div class="cast-v {{ empty($cast['work_time_label'] ?? '') ? 'muted' : '' }}">{{ $cast['work_time_label'] ?: '--' }}</div></div>
                    </div>
                </div>
            </div>

            {{-- Image Library（ドラッグで並び替え・お店同様） --}}
            <div class="mypage-section gallery-edit-section">
                <div class="gallery-section-header">
                    <h2 class="section-title section-title-gold">Image Library</h2>
                    <p class="gallery-section-hint">ドラッグで並び替え（スマホは長押し）</p>
                </div>
                <ul class="responsive-gallery gallery-grid" id="gallery-list" data-sort-save-url="{{ route('cast.mypage.images.order') }}" data-empty-image-url="{{ asset('assets/images/common/no-image.png') }}">
                    @for($i = 0; $i < 8; $i++)
                    @php $img = $subImages[$i] ?? null; @endphp
                    <li class="gallery-grid-item" data-slot-index="{{ $i }}">
                        <div class="photo-slot {{ $img ? 'has-img' : '' }}"
                             data-image-id="{{ $img['id'] ?? '' }}"
                             data-image-url="{{ $img['url'] ?? '' }}">
                            @if($img && !empty($img['url']))
                                <img src="{{ $img['url'] }}" alt="" loading="lazy">
                                @if($i === 0)
                                    <span class="photo-slot-badge">MAIN</span>
                                @endif
                            @else
                                <span class="photo-slot-empty"><i class="fas fa-image"></i></span>
                            @endif
                        </div>
                    </li>
                    @endfor
                </ul>
            </div>
        </div>
    </section>
</div>

{{-- 画像大表示モーダル（削除ボタンで削除） --}}
<div id="image-preview-modal" class="mypage-modal-overlay gallery-preview-overlay" role="dialog" aria-label="画像プレビュー">
    <div class="gallery-preview-inner">
        <img id="modal-img" src="" alt="" class="mypage-modal-preview-img">
        <div class="gallery-preview-actions">
            <button type="button" class="btn-action btn-action-secondary gallery-preview-btn-close" id="gallery-preview-close-btn">閉じる</button>
            <button type="button" id="gallery-preview-delete-btn" class="btn-action gallery-preview-btn-delete">削除</button>
        </div>
    </div>
</div>

{{-- 画像編集モーダル（推奨サイズに合わせてトリミング） --}}
<div id="image-edit-modal" class="mypage-modal-overlay gallery-preview-overlay" role="dialog" aria-label="画像編集" style="display:none;">
    <div class="gallery-preview-inner image-edit-inner">
        <div class="image-edit-header">
            <h3 class="mypage-modal-title serif-font">画像を調整してアップロード</h3>
            <p class="image-edit-guide">
                推奨サイズは <strong>3:4（例：1200×1600px、縦長）</strong> です。<br>
                画面に表示されている範囲で中央を基準に自動トリミングし、スマホ表示でも粗くなりすぎないよう約2MPまでサイズ調整してアップロードします。
            </p>
        </div>
        <div class="image-edit-preview-wrapper">
            <div class="image-edit-frame image-edit-frame--portrait">
                <img id="image-edit-preview" src="" alt="編集プレビュー" class="image-edit-preview-img">
                <div class="image-edit-frame-mask"></div>
            </div>
        </div>
        <div class="gallery-preview-actions image-edit-actions">
            <button type="button" class="btn-action btn-action-secondary" id="image-edit-cancel-btn">別の画像を選ぶ</button>
            <button type="button" class="btn-action btn-action-primary" id="image-edit-confirm-btn">この画像でアップロード</button>
        </div>
    </div>
</div>

{{-- アピール編集モーダル --}}
<div id="modal-word" class="mypage-modal-overlay modal-word-edit" style="display:none;">
    <div class="mypage-modal-panel glass-panel">
        <h3 class="mypage-modal-title serif-font"><i class="fas fa-globe" style="color:#dcb568; font-size:0.85rem; margin-right:6px;"></i>ひとことを編集</h3>
        <textarea id="word-input" rows="3" class="mypage-modal-textarea" placeholder="今、何してる？（タイムラインに公開されます）"></textarea>
        <p class="mypage-modal-help" style="margin:6px 0 12px; font-size:0.72rem; color:rgba(203,187,187,0.65);">※更新するとタイムラインに反映されます</p>
        <div class="mypage-modal-actions">
            <button type="button" class="btn-action btn-action-secondary" id="word-edit-cancel-btn">戻る</button>
            <button type="button" class="btn-action btn-action-primary" id="word-edit-save-btn">保存</button>
        </div>
    </div>
</div>

<input type="file" id="gallery-upload" class="sr-only" accept="image/*">
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.4.1/papaparse.min.js"></script>
<script src="{{ asset('assets/js/gallery-sortable.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
<script>
window.MYPAGE_GALLERY_CONFIG = {
    csrfToken: @json(csrf_token()),
    uploadUrl: @json(route('cast.mypage.images.upload')),
    deleteUrlTemplate: @json(route('cast.mypage.images.delete', ['id' => '__ID__'])),
    cropAspectW: 3,
    cropAspectH: 4,
    cropMaxWidth: 1200,
    cropMaxHeight: 1600
};
</script>
<script src="{{ asset('assets/js/mypage-gallery.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var placeholderText = '今、何してる？（タイムラインに公開されます）';
    var openWordBtn = document.getElementById('open-word-edit-btn');
    if (openWordBtn) openWordBtn.addEventListener('click', function() {
        var m = document.getElementById('modal-word');
        if (m) m.style.display = 'flex';
        var displayEl = document.getElementById('display-word');
        var wordInput = document.getElementById('word-input');
        if (displayEl && wordInput) wordInput.value = (displayEl.innerText.trim() === placeholderText) ? '' : displayEl.innerText.trim();
    });
    var cancelWord = document.getElementById('word-edit-cancel-btn');
    if (cancelWord) cancelWord.addEventListener('click', function() { var m = document.getElementById('modal-word'); if (m) m.style.display = 'none'; });
    var saveWordBtn = document.getElementById('word-edit-save-btn');
    if (saveWordBtn) saveWordBtn.addEventListener('click', function() {
        var wordInputEl = document.getElementById('word-input');
        var val = (wordInputEl && wordInputEl.value || '').trim();
        var displayEl = document.getElementById('display-word');
        var updatedEl = document.getElementById('display-word-updated');
        var m = document.getElementById('modal-word');
        if (saveWordBtn.disabled) return;
        saveWordBtn.disabled = true;
        fetch('{{ route('cast.mypage.word') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ word: val })
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                if (displayEl) {
                    displayEl.innerText = val || placeholderText;
                    displayEl.classList.toggle('is-placeholder', !val);
                }
                if (updatedEl && res.appeal_updated_at) {
                    updatedEl.innerText = '最終更新 ' + res.appeal_updated_at;
                }
                if (m) m.style.display = 'none';
            } else {
                alert(res.message || '保存に失敗しました');
            }
        })
        .catch(function() { alert('保存に失敗しました'); })
        .finally(function() { saveWordBtn.disabled = false; });
    });
});
</script>
@endpush
