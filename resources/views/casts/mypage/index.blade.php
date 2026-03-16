@extends('layouts.app')

@section('title', 'マイページ - プロフィール確認')
@section('body-class', 'page-cast-mypage')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/cast_profile.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
@endpush

@section('content')
@php $subImages = $subImages ?? []; @endphp
<div class="mypage-page contents inner animate-fadeIn">
    <section class="mypage-area">
        {{-- ヒーロー：キャスト名（お店マイページと同じ位置） --}}
        <h1 class="mypage-shop-name serif-font gold-gradient">{{ $cast['nickname'] ?? $cast['name'] }}</h1>

        {{-- アイコン＋アピール（お店同様・編集可能） --}}
        <div class="mypage-hero">
            <div class="shop-icon-wrapper">
                <img src="{{ (isset($subImages[0]) ? $subImages[0]['url'] : null) ?? $cast['img'] ?? asset('assets/images/common/no-image.png') }}" class="shop-icon-main" id="main-icon-display" alt="">
                <button type="button" class="btn-add-icon" onclick="document.getElementById('gallery-upload').click()" aria-label="写真を追加">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <div class="shop-word-bubble glass-panel">
                <p id="display-word" class="shop-word-text {{ empty(trim($cast['word'] ?? '')) ? 'is-placeholder' : '' }}" data-placeholder="ひとことを入力すると、タイムラインに表示されます。">{{ !empty(trim($cast['word'] ?? '')) ? $cast['word'] : 'ひとことを入力すると、タイムラインに表示されます。' }}</p>
                <div class="shop-word-bubble-footer">
                    <span id="display-word-updated" class="shop-word-bubble-updated">最終更新 {{ $cast['appeal_updated_at'] ?? '未設定' }}</span>
                    <button type="button" class="btn-word-edit" id="open-word-edit-btn" aria-label="ひとことを編集">
                        <i class="fas fa-pen"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- LIKE・マッチ件数・ボーナス金合計 --}}
        <div class="mypage-stats-row" aria-label="統計">
            <div class="mypage-stat-panel">
                <span class="mypage-stat-icon"><i class="fas fa-heart"></i></span>
                <span class="mypage-stat-label">LIKE</span>
                <span class="mypage-stat-value">{{ number_format((int) ($cast['like_cnt'] ?? 0)) }}</span>
            </div>
            <div class="mypage-stat-panel">
                <span class="mypage-stat-icon"><i class="fas fa-handshake"></i></span>
                <span class="mypage-stat-label">マッチ件数</span>
                <span class="mypage-stat-value">{{ number_format((int) ($cast['match_cnt'] ?? 0)) }}</span>
            </div>
            <div class="mypage-stat-panel">
                <span class="mypage-stat-icon"><i class="fas fa-yen-sign"></i></span>
                <span class="mypage-stat-label">ボーナス金合計</span>
                <span class="mypage-stat-value">¥{{ number_format((int) ($cast['bonus_total'] ?? 0)) }}</span>
            </div>
        </div>

        <div class="mypage-detail-box">
            {{-- メニュー（プロフィール情報より上） --}}
            @include('casts.mypage.parts.menu', ['current' => 'profile', 'fullWidth' => false])

            {{-- プロフィール情報：編集ボタン＋5カテゴリ --}}
            <div class="mypage-section profile-info-section">
                <div class="section-title-row">
                    <h2 class="section-title">プロフィール情報</h2>
                    <a href="{{ route('cast.profile.edit') }}" class="btn-outline-gold">編集</a>
                </div>
                <p class="shop-access-text">
                    <i class="fas fa-map-marker-alt"></i> @if(!empty($cast['pref']) || !empty($cast['city'])){{ implode(' ', array_filter([$cast['pref'] ?? null, $cast['city'] ?? null])) }} / @endifキャスト
                </p>

                {{-- 基本情報（生年月日、身長体重、サイズ） --}}
                <div class="mypage-profile-block">
                    <h3 class="section-title section-title-gold">基本情報</h3>
                    <div class="mypage-cast-other other-info-detail-body">
                        @if(!empty($cast['birth_year']) && !empty($cast['birth_month']) && !empty($cast['birth_day']))
                            <div class="detail-row"><span class="detail-label">生年月日</span><span class="detail-value">{{ $cast['birth_year'] }}年{{ $cast['birth_month'] }}月{{ $cast['birth_day'] }}日</span></div>
                        @endif
                        <div class="detail-row"><span class="detail-label">身長</span><span class="detail-value">{{ $cast['height'] ?? '--' }}cm</span></div>
                        <div class="detail-row"><span class="detail-label">体重</span><span class="detail-value">{{ $cast['weight'] ?? '--' }}kg</span></div>
                        <div class="detail-row"><span class="detail-label">B / W / H</span><span class="detail-value">{{ $cast['bust'] ?? '--' }} / {{ $cast['waist'] ?? '--' }} / {{ $cast['hip'] ?? '--' }}</span></div>
                    </div>
                </div>

                {{-- 接客タイプ・系統 --}}
                <div class="mypage-profile-block">
                    <h3 class="section-title section-title-gold">接客タイプ・系統</h3>
                    <div class="mypage-cast-other other-info-detail-body">
                        <div class="detail-row" id="personality-type-row" style="{{ !empty($cast['personality_type']) ? '' : 'display:none;' }}">
                            <span class="detail-label">接客タイプ診断結果</span>
                            <span class="detail-value" id="personality-type-display">{{ $cast['personality_type'] ?? '' }}</span>
                        </div>
                        <div class="detail-row"><span class="detail-label">ご自分の系統</span><span class="detail-value">{{ $cast['my_field'] ?? '--' }}</span></div>
                        <div class="detail-row"><span class="detail-label">ご自分の内面・特技</span><span class="detail-value">{{ $cast['my_inner_skills'] ?? '--' }}</span></div>
                    </div>
                    <a href="{{ asset('personality-test') }}" target="_blank" rel="noopener noreferrer" class="btn-personality-test">
                        <i class="fas fa-up-right-from-square"></i>
                        <span>接客タイプ診断を開く</span>
                    </a>
                </div>

                {{-- 経歴・スキル --}}
                <div class="mypage-profile-block">
                    <h3 class="section-title section-title-gold">経歴・スキル</h3>
                    <div class="mypage-cast-other other-info-detail-body">
                        <div class="detail-row"><span class="detail-label">ナイトワーク経験</span><span class="detail-value">{{ $cast['night_work_label'] ?? '--' }}</span></div>
                        <div class="detail-row detail-row-block"><span class="detail-label">現職業</span><div class="detail-value">@if(!empty($cast['current_job'])){!! nl2br(e($cast['current_job'])) !!}@else—@endif</div></div>
                    </div>
                </div>

                {{-- 希望の職種・働き方 --}}
                <div class="mypage-profile-block">
                    <h3 class="section-title section-title-gold">希望の職種・働き方</h3>
                    <div class="mypage-cast-other other-info-detail-body">
                        <div class="detail-row"><span class="detail-label">希望職種</span><span class="detail-value">{{ $cast['desired_job'] ?? '--' }}</span></div>
                        <div class="detail-row"><span class="detail-label">シフト希望</span><span class="detail-value">{{ $cast['shift_hope'] ?? '--' }}</span></div>
                        <div class="detail-row"><span class="detail-label">勤務時間</span><span class="detail-value">{{ $cast['work_time_label'] ?? '--' }}</span></div>
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

{{-- アピール編集モーダル --}}
<div id="modal-word" class="mypage-modal-overlay modal-word-edit" style="display:none;">
    <div class="mypage-modal-panel glass-panel">
        <h3 class="mypage-modal-title serif-font">ひとことを編集</h3>
        <textarea id="word-input" rows="3" class="mypage-modal-textarea" placeholder="例：明るく楽しく接客します！"></textarea>
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
<script>
window.MYPAGE_GALLERY_CONFIG = {
    csrfToken: @json(csrf_token()),
    uploadUrl: @json(route('cast.mypage.images.upload')),
    deleteUrlTemplate: @json(route('cast.mypage.images.delete', ['id' => '__ID__']))
};
</script>
<script src="{{ asset('assets/js/mypage-gallery.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var placeholderText = 'ひとことを入力すると、タイムラインに表示されます。';
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
