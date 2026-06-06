@extends('layouts.app-v2')

@section('title', 'マイページ')
@section('body-class', 'page-shop-mypage')

@php
    $displayName       = $shopData['shop_name'] ?? '--';
    $word              = trim($shopData['word'] ?? '');
    $wordPlaceholder   = '今、何してる？（タイムラインに公開されます）';
    $iconImage         = ($subImages[0]['url'] ?? null) ?? asset('assets/images/common/no-image.png');
    $hasGoodPayerBadge = !empty($shopData['badges']['good_payer'] ?? false);
    $reviewAvg         = number_format($shopData['review_avg'] ?? 0, 1);
@endphp

@section('content')
<div>

    <div class="px-5 pt-4 pb-6">

        {{-- ===== アイコン + ひとこと吹き出し ===== --}}
        <div class="flex items-start gap-3 mb-5">
            <div class="w-[84px] h-[84px] rounded-full overflow-hidden border-2 border-line-accent/40 shadow-card-3d bg-surface-from shrink-0">
                <img src="{{ $iconImage }}" alt="" class="w-full h-full object-cover" id="main-icon-display">
            </div>

            <div class="flex-1 min-w-0">
                <div class="relative bg-gradient-to-br from-surface-from to-base border border-line-accent/40 rounded-2xl shadow-card-3d p-3 pr-9">
                    {{-- 吹き出しのしっぽ --}}
                    <span class="absolute top-5 -left-[8px] w-0 h-0 border-y-[8px] border-y-transparent border-r-[10px] border-r-line-accent/40"></span>
                    <span class="absolute top-5 -left-[6px] w-0 h-0 border-y-[7px] border-y-transparent border-r-[9px] border-r-surface-from"></span>

                    <p id="display-word"
                       data-placeholder="{{ $wordPlaceholder }}"
                       class="text-[13px] leading-relaxed {{ $word === '' ? 'text-text-sub' : 'text-text-main' }}">
                        {{ $word !== '' ? $word : $wordPlaceholder }}
                    </p>

                    <button type="button" id="open-word-edit-btn"
                            aria-label="ひとことを編集"
                            class="absolute top-2 right-2 w-7 h-7 rounded-full flex items-center justify-center bg-accent/20 text-accent-text border border-line-accent/40 hover:bg-accent/30 transition-colors">
                        <x-ui.icon name="edit" class="text-xs" />
                    </button>

                    <div class="mt-2 pt-2 border-t border-line">
                        <span id="display-word-updated" class="text-[10px] text-text-sub">
                            最終更新 {{ $shopData['appeal_updated_at'] ?? '未設定' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== 優良店バッヂ + レビュー（2カラム）===== --}}
        <div class="grid grid-cols-2 gap-3 mb-5">
            {{-- 優良店バッヂ（未付与なら opacity を落としてグレー寄りに） --}}
            <button type="button" id="open-good-payer-badge-modal"
                    aria-haspopup="dialog" aria-controls="modal-good-payer-badge"
                    aria-label="優良店バッヂの説明を開く"
                    class="flex flex-col items-center justify-center gap-1 p-3 rounded-panel bg-gradient-to-br from-surface-from to-base shadow-card-3d transition-all duration-300 {{ $hasGoodPayerBadge ? 'border border-amber-400/50' : 'border border-line opacity-70' }}">
                <i class="fas fa-crown text-[20px] {{ $hasGoodPayerBadge ? 'text-amber-400' : 'text-text-sub' }}"></i>
                <span class="app-title text-[9px] tracking-widest text-text-sub">優良店</span>
                <span class="text-[12px] font-bold {{ $hasGoodPayerBadge ? 'text-text-main' : 'text-text-sub' }}">{{ $hasGoodPayerBadge ? '優良店' : '未付与' }}</span>
            </button>

            {{-- レビュー一覧へのリンク（cast/mypage と違い shop は残す） --}}
            <a href="{{ route('shop.mypage.review.index') }}"
               class="flex flex-col items-center justify-center gap-1 p-3 rounded-panel border border-line-accent/40 bg-gradient-to-br from-surface-from to-base shadow-card-3d transition-all duration-300">
                <i class="fas fa-star text-[20px] text-amber-400"></i>
                <span class="app-title text-[9px] tracking-widest text-text-sub">レビュー</span>
                <span class="text-[12px] font-bold text-text-main">{{ $reviewAvg }} <i class="fas fa-chevron-right ml-1 text-[10px] text-accent-text"></i></span>
            </a>
        </div>

        {{-- ===== 店舗名 ===== --}}
        <div class="mb-5">
            <h1 class="app-title text-[24px] text-text-main leading-tight">{{ $displayName }}</h1>
        </div>

        {{-- ===== Sub-page menu（採用・入金管理のみ） ===== --}}
        <div class="flex flex-col gap-3">
            <x-ui.menu-card icon="settings"
                            sub="EMPLOYMENT & PAYMENT"
                            title="採用・入金管理"
                            href="{{ route('shop.mypage.management') }}"
                            class="shop-mypage-menu-card" />
        </div>
    </div>

    {{-- ===== Tabs ===== --}}
    <div data-tabs-scope>
        <div data-tabs class="border-t border-b border-line-accent/40 bg-base/90 backdrop-blur-md">
            <div class="flex">
                <button type="button" data-tab="gallery"
                        class="is-active flex-1 py-3 flex justify-center items-center transition-colors border-b-2 border-transparent text-text-sub [&.is-active]:text-accent-text [&.is-active]:border-accent">
                    <span class="app-title text-[12px] tracking-widest">GALLERY</span>
                </button>
                <button type="button" data-tab="details"
                        class="flex-1 py-3 flex justify-center items-center transition-colors border-b-2 border-transparent text-text-sub [&.is-active]:text-accent-text [&.is-active]:border-accent">
                    <span class="app-title text-[12px] tracking-widest">DETAILS</span>
                </button>
            </div>
        </div>

        {{-- Gallery panel：元の機能を維持 --}}
        <div data-tab-panel="gallery" class="is-active">
            <ul id="gallery-list"
                data-sort-save-url="{{ route('shop.profile.images.order') }}"
                data-empty-image-url="{{ asset('assets/images/common/no-image.png') }}">
                @for($i = 0; $i < 8; $i++)
                @php $img = $subImages[$i] ?? null; @endphp
                <li class="gallery-grid-item" data-slot-index="{{ $i }}">
                    <div class="photo-slot {{ $img ? 'has-img' : '' }}"
                         data-image-id="{{ $img['id'] ?? '' }}"
                         data-image-url="{{ $img['url'] ?? '' }}">
                        @if($img)
                            <img src="{{ $img['url'] }}" alt="" loading="lazy">
                            @if($i === 0)<span class="photo-slot-badge">MAIN</span>@endif
                        @else
                            <span class="photo-slot-empty" aria-label="画像を追加"><i class="fas fa-plus"></i></span>
                        @endif
                    </div>
                </li>
                @endfor
            </ul>
        </div>

        {{-- Details panel：jobdescription + ShopInformation + Licenses をまとめる --}}
        <div data-tab-panel="details">
            <div class="p-4 flex flex-col gap-4">

                {{-- 求人票の管理 --}}
                <x-ui.menu-card icon="settings"
                                sub="JOB DESCRIPTION"
                                title="求人票の管理"
                                href="{{ route('shop.jobdescription') }}"
                                class="shop-mypage-menu-card" />

                {{-- Shop Information --}}
                <x-ui.card class="p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="app-title text-[13px] tracking-widest text-accent-text flex items-center gap-2">
                            <i class="fas fa-store text-lg"></i> SHOP INFORMATION
                        </h3>
                        <button type="button" id="open-profile-edit-btn"
                                class="text-[11px] font-bold text-accent-text border border-line-accent/40 rounded-full px-3 py-1 hover:bg-accent/10 transition-colors">編集</button>
                    </div>

                    <div class="flex flex-col gap-3">
                        <div class="flex justify-between items-start border-b border-line pb-2 gap-3">
                            <span class="text-[12px] text-text-sub font-medium shrink-0">店舗名</span>
                            <span class="text-[13px] font-bold text-text-main text-right">{{ $shopInfo['shop_name'] ?: '—' }}</span>
                        </div>
                        <div class="flex justify-between items-start border-b border-line pb-2 gap-3">
                            <span class="text-[12px] text-text-sub font-medium shrink-0">業種</span>
                            <span class="text-[13px] font-bold text-text-main text-right">{{ $shopInfo['industry'] ?? '未設定' }}</span>
                        </div>
                        <div class="flex justify-between items-start border-b border-line pb-2 gap-3">
                            <span class="text-[12px] text-text-sub font-medium shrink-0">郵便番号</span>
                            <span class="text-[13px] font-bold text-text-main text-right">{{ $shopInfo['zip'] ?: '—' }}</span>
                        </div>
                        <div class="flex justify-between items-start border-b border-line pb-2 gap-3">
                            <span class="text-[12px] text-text-sub font-medium shrink-0">住所</span>
                            <span class="text-[13px] font-bold text-text-main text-right">{{ trim(($shopInfo['pref'] ?? '') . ($shopInfo['city'] ?? '') . ($shopInfo['addr1'] ?? '')) ?: '—' }}</span>
                        </div>
                        @if(!empty($shopInfo['tel'] ?? null))
                        <div class="flex justify-between items-start border-b border-line pb-2 gap-3">
                            <span class="text-[12px] text-text-sub font-medium shrink-0">電話</span>
                            <span class="text-[13px] font-bold text-text-main text-right">{{ $shopInfo['tel'] }}</span>
                        </div>
                        @endif
                        @if(!empty($shopInfo['business_hours_shop'] ?? null))
                        <div class="flex justify-between items-start border-b border-line pb-2 gap-3">
                            <span class="text-[12px] text-text-sub font-medium shrink-0">店舗の営業時間</span>
                            <span class="text-[13px] font-bold text-text-main text-right">{{ $shopInfo['business_hours_shop'] }}</span>
                        </div>
                        @endif
                        @if(!empty($shopInfo['nearest_stations'] ?? []))
                        <div class="flex justify-between items-start border-b border-line pb-2 gap-3">
                            <span class="text-[12px] text-text-sub font-medium shrink-0">最寄り駅</span>
                            <span class="text-[13px] font-bold text-text-main text-right">{!! nl2br(e(implode("\n", $shopInfo['nearest_stations']))) !!}</span>
                        </div>
                        @elseif(!empty($shopInfo['nearest_station'] ?? null))
                        <div class="flex justify-between items-start border-b border-line pb-2 gap-3">
                            <span class="text-[12px] text-text-sub font-medium shrink-0">最寄り</span>
                            <span class="text-[13px] font-bold text-text-main text-right">{{ $shopInfo['nearest_station'] }}</span>
                        </div>
                        @endif
                        @if(!empty($shopInfo['working_hours'] ?? null) || !empty($shopInfo['working_days'] ?? null) || !empty($shopInfo['regular_holiday'] ?? null))
                        <div class="flex justify-between items-start border-b border-line pb-2 gap-3">
                            <span class="text-[12px] text-text-sub font-medium shrink-0">勤務・休日</span>
                            <span class="text-[13px] font-bold text-text-main text-right">
                                @if(!empty($shopInfo['working_hours'])){{ $shopInfo['working_hours'] }}@else時間未設定@endif
                                ／
                                @if(!empty($shopInfo['working_days'])){{ $shopInfo['working_days'] }}@else勤務日未設定@endif
                                @if(!empty($shopInfo['regular_holiday']))
                                    <br>定休: {{ $shopInfo['regular_holiday'] }}
                                @endif
                            </span>
                        </div>
                        @endif
                    </div>

                    {{-- タググループ --}}
                    @php $tagGroups = $shopInfo['tag_groups'] ?? []; @endphp
                    @if(!empty($tagGroups))
                        <div class="mt-4 pt-4 border-t border-line flex flex-col gap-3">
                            @foreach($tagGroups as $group)
                                @php
                                    $gLabel = (string) ($group['label'] ?? '');
                                    if (str_contains($gLabel, 'ご利用プラン')) continue;
                                    $gTags = array_values(array_filter(
                                        (array) ($group['tags'] ?? []),
                                        static fn ($t) => ! str_contains((string) $t, 'ご利用プラン')
                                    ));
                                @endphp
                                @if($gTags !== [])
                                    <div>
                                        <div class="text-[10px] text-text-sub uppercase tracking-wider mb-1">{{ $gLabel }}</div>
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach($gTags as $t)
                                                <span class="inline-flex items-center px-2 py-1 rounded bg-accent/10 border border-line-accent/30 text-[11px] text-text-main">{{ $t }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    {{-- Licenses（同じデザインで色違い：amber/champagne 系。partial の構造は触らず外側だけ薄く違う色味の枠で包む） --}}
                    <div class="mt-5 pt-5 border-t border-amber-400/30 shop-mypage-license-wrap">
                        @include('shops.mypage.partials.shop-license-documents', ['documents' => $documents ?? []])
                    </div>
                </x-ui.card>

            </div>
        </div>
    </div>

</div>

{{-- ============================================================
     モーダル群
     ============================================================ --}}

{{-- ひとこと編集モーダル（Tailwind） --}}
<div id="modal-word"
     class="fixed inset-0 z-[1100] hidden items-center justify-center bg-black/60 backdrop-blur-sm p-5">
    <div class="w-full max-w-md bg-gradient-to-br from-surface-from to-base border border-line-accent/40 rounded-card shadow-card-3d p-5">
        <h3 class="app-title text-[13px] text-accent-text tracking-widest mb-3">ひとことを編集</h3>
        <textarea id="word-input" rows="3"
                  placeholder="{{ $wordPlaceholder }}"
                  class="w-full px-3 py-2 rounded-panel bg-accent/10 border border-line-accent/40 text-text-main placeholder-gray-400 shadow-input-dark outline-none resize-none"></textarea>
        <p class="text-[10px] text-text-sub mt-2 mb-4">※更新するとタイムラインに反映されます</p>
        <div class="flex justify-end gap-2">
            <button type="button" id="word-edit-cancel-btn"
                    class="px-5 py-2 rounded-full border border-line-accent/40 text-text-main text-[13px] font-bold">戻る</button>
            <button type="button" id="word-edit-save-btn"
                    class="px-5 py-2 rounded-full bg-gradient-to-r from-accent-grad-from to-accent-grad-to text-on-accent-strong text-[13px] font-bold shadow-btn-3d">保存</button>
        </div>
    </div>
</div>

{{-- 優良店バッヂの仕様モーダル（Tailwind） --}}
<div id="modal-good-payer-badge"
     class="fixed inset-0 z-[1100] hidden items-center justify-center bg-black/60 backdrop-blur-sm p-5"
     role="dialog" aria-modal="true" aria-labelledby="good-payer-badge-modal-title">
    <div class="w-full max-w-md bg-gradient-to-br from-surface-from to-base border border-amber-400/40 rounded-card shadow-card-3d p-5 relative">
        <button type="button" id="good-payer-badge-modal-close-top"
                aria-label="閉じる"
                class="absolute top-3 right-3 w-7 h-7 rounded-full flex items-center justify-center text-text-sub hover:text-text-main hover:bg-white/5 transition-colors">×</button>
        <h3 id="good-payer-badge-modal-title" class="app-title text-[13px] text-amber-400 tracking-widest mb-3 flex items-center gap-2">
            <i class="fas fa-crown text-base"></i> 優良店バッヂの獲得条件
        </h3>
        <ul class="text-[13px] text-text-main leading-relaxed list-disc pl-5 space-y-1">
            <li>すべての案件が「店舗入金確認済み」まで完了している</li>
            <li>請求書発行から店舗入金確認までが10日以内である</li>
        </ul>
        <p class="text-[11px] text-text-sub mt-3">※ 条件は毎月見直され、基準を満たさなくなった場合はバッヂ表示が外れることがあります。</p>
    </div>
</div>

{{-- 画像大表示モーダル（旧構造を維持：mypage-gallery.js が依存） --}}
<div id="image-preview-modal" class="mypage-modal-overlay gallery-preview-overlay" role="dialog" aria-label="画像プレビュー">
    <div class="gallery-preview-inner">
        <img id="modal-img" src="" alt="" class="mypage-modal-preview-img">
        <div class="gallery-preview-actions">
            <button type="button" class="btn-action btn-action-secondary gallery-preview-btn-close" id="gallery-preview-close-btn">閉じる</button>
            <button type="button" id="gallery-preview-recrop-btn" class="btn-action">再切り抜き</button>
            <button type="button" id="gallery-preview-delete-btn" class="btn-action gallery-preview-btn-delete">削除</button>
        </div>
    </div>
</div>

{{-- 画像編集モーダル --}}
<div id="image-edit-modal" class="mypage-modal-overlay gallery-preview-overlay" role="dialog" aria-label="画像編集" style="display:none;">
    <div class="gallery-preview-inner image-edit-inner">
        <div class="image-edit-header">
            <h3 class="mypage-modal-title serif-font">画像を調整してアップロード</h3>
            <p class="image-edit-guide">
                推奨サイズは <strong>4:5（例：1080×1350px の縦長）</strong> です。<br>
                ピンチ・ドラッグで拡大縮小・位置調整できます。範囲枠内に収めたい部分を合わせてアップロードしてください。
            </p>
        </div>
        <div class="image-edit-preview-wrapper">
            <div class="image-edit-frame image-edit-frame--portrait">
                <img id="image-edit-preview" src="" alt="編集プレビュー" class="image-edit-preview-img">
                <div class="image-edit-frame-mask"></div>
            </div>
        </div>
        <div class="gallery-preview-actions image-edit-actions flex gap-2 justify-end">
            <button type="button" id="image-edit-cancel-btn"
                    class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-full font-bold bg-gray-700 text-gray-200 border border-gray-600 shadow-md hover:bg-gray-600 active:translate-y-1 transition-all duration-300">
                キャンセル
            </button>
            <x-ui.button id="image-edit-confirm-btn" variant="grad" icon="check">アップロード</x-ui.button>
        </div>
    </div>
</div>

<input type="file" id="gallery-upload" class="sr-only" accept="image/*">
@endsection

@push('head-styles')
{{-- 旧 CSS deps（ギャラリーモーダル / cropper / licenses） --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/shop-license-documents.css') }}?v=20260505">

<style>
    /* ===== Instagram風グリッド ===== */
    #gallery-list {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2px;
        padding: 0;
        margin: 0;
        list-style: none;
    }
    #gallery-list .gallery-grid-item {
        aspect-ratio: 1 / 1;
        padding: 0;
        margin: 0;
        overflow: hidden;
        position: relative;
    }
    #gallery-list .photo-slot {
        position: relative;
        width: 100%;
        height: 100%;
        padding: 0;
        border-radius: 0;
        overflow: hidden;
        cursor: pointer;
        box-sizing: border-box;
    }
    #gallery-list .photo-slot:not(.has-img) {
        border: 2px dashed rgba(255, 255, 255, 0.22);
        background: transparent;
    }
    #gallery-list .photo-slot > img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    #gallery-list .photo-slot-empty {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        opacity: 0.45;
    }
    #gallery-list .photo-slot-badge {
        position: absolute;
        top: 4px;
        left: 4px;
        font-size: 9px;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 4px;
        line-height: 1;
    }

    /* menu-card の右端「＞」をアクセント紫の矢印だけにする（cast と同じ） */
    .shop-mypage-menu-card > span:last-child {
        width: auto !important;
        height: auto !important;
        border: 0 !important;
        border-radius: 0 !important;
        color: var(--color-accent-text) !important;
        opacity: 1 !important;
    }
    .shop-mypage-menu-card > span:last-child > i {
        font-size: 1.3rem !important;
        color: var(--color-accent-text) !important;
    }
</style>
@endpush

@push('scripts')
{{-- ===== ギャラリー機能：元のスクリプト群 ===== --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="{{ asset('assets/js/gallery-sortable.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
<script>
window.MYPAGE_GALLERY_CONFIG = {
    csrfToken: @json(csrf_token()),
    uploadUrl: @json(route('shop.profile.upload.image')),
    deleteUrlTemplate: @json(route('shop.profile.image.delete', ['id' => '__ID__'])),
    cropAspectW: 4,
    cropAspectH: 5,
    cropMaxWidth: 1080,
    cropMaxHeight: 1350
};
</script>
<script src="{{ asset('assets/js/mypage-gallery.js') }}"></script>

{{-- ひとこと編集 + 優良店バッヂモーダル + プロフィール編集導線 --}}
<script>
(function () {
    'use strict';
    var placeholderText = @json('今、何してる？（タイムラインに公開されます）');

    // === ひとこと編集 ===
    var openWordBtn = document.getElementById('open-word-edit-btn');
    var wordModal = document.getElementById('modal-word');
    var displayEl = document.getElementById('display-word');
    var input = document.getElementById('word-input');
    var saveBtn = document.getElementById('word-edit-save-btn');
    var cancelBtn = document.getElementById('word-edit-cancel-btn');
    var updatedEl = document.getElementById('display-word-updated');

    function showWordModal() {
        if (!wordModal) return;
        wordModal.classList.remove('hidden');
        wordModal.classList.add('flex');
        if (displayEl && input) {
            var cur = displayEl.innerText.trim();
            input.value = (cur === placeholderText) ? '' : cur;
        }
        setTimeout(function () { if (input) input.focus(); }, 50);
    }
    function hideWordModal() {
        if (!wordModal) return;
        wordModal.classList.add('hidden');
        wordModal.classList.remove('flex');
    }

    if (openWordBtn) openWordBtn.addEventListener('click', showWordModal);
    if (cancelBtn) cancelBtn.addEventListener('click', hideWordModal);
    if (wordModal) wordModal.addEventListener('click', function (e) {
        if (e.target === wordModal) hideWordModal();
    });
    if (saveBtn) saveBtn.addEventListener('click', function () {
        if (saveBtn.disabled) return;
        var val = (input ? input.value : '').trim();
        saveBtn.disabled = true;
        fetch(@json(route('shop.mypage.word')), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': @json(csrf_token())
            },
            body: JSON.stringify({ word: val })
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res && res.success) {
                if (displayEl) {
                    displayEl.innerText = val || placeholderText;
                    displayEl.classList.toggle('text-text-sub', !val);
                    displayEl.classList.toggle('text-text-main', !!val);
                }
                if (updatedEl && res.appeal_updated_at) {
                    updatedEl.innerText = '最終更新 ' + res.appeal_updated_at;
                }
                hideWordModal();
            } else {
                alert((res && res.message) || '保存に失敗しました');
            }
        })
        .catch(function () { alert('保存に失敗しました'); })
        .finally(function () { saveBtn.disabled = false; });
    });

    // === 優良店バッヂモーダル ===
    var openBadge = document.getElementById('open-good-payer-badge-modal');
    var badgeModal = document.getElementById('modal-good-payer-badge');
    var closeBadgeTop = document.getElementById('good-payer-badge-modal-close-top');
    function showBadgeModal() {
        if (!badgeModal) return;
        badgeModal.classList.remove('hidden');
        badgeModal.classList.add('flex');
        if (closeBadgeTop) closeBadgeTop.focus();
    }
    function hideBadgeModal() {
        if (!badgeModal) return;
        badgeModal.classList.add('hidden');
        badgeModal.classList.remove('flex');
        if (openBadge) openBadge.focus();
    }
    if (openBadge && badgeModal) {
        openBadge.addEventListener('click', showBadgeModal);
        badgeModal.addEventListener('click', function (e) {
            if (e.target === badgeModal) hideBadgeModal();
        });
    }
    if (closeBadgeTop) closeBadgeTop.addEventListener('click', hideBadgeModal);

    // === プロフィール編集導線（既存挙動） ===
    var profileEditBtn = document.getElementById('open-profile-edit-btn');
    if (profileEditBtn) profileEditBtn.addEventListener('click', function () {
        location.href = @json(route('shop.profile.edit'));
    });
})();
</script>
@endpush
