@extends('layouts.app-v2')

@section('title', 'マイページ')
@section('body-class', 'page-cast-mypage')

@php
    $displayName = $cast['nickname'] ?? $cast['name'] ?? '--';
    $birthdayText = (!empty($cast['birth_year']) && !empty($cast['birth_month']) && !empty($cast['birth_day']))
        ? $cast['birth_year'] . '年' . $cast['birth_month'] . '月' . $cast['birth_day'] . '日'
        : '--';
    $heightText  = !empty($cast['height']) ? ((string) $cast['height'] . ' cm') : '--';
    $weightText  = !empty($cast['weight']) ? ((string) $cast['weight'] . ' kg') : '--';
    $bwhParts    = [($cast['bust'] ?? '') ?: '--', ($cast['waist'] ?? '') ?: '--', ($cast['hip'] ?? '') ?: '--'];
    $bwhText     = implode(' / ', $bwhParts);
    $addressText = trim((string) ($cast['pref'] ?? '') . (string) ($cast['city'] ?? '') . (string) ($cast['addr1'] ?? ''));
    $addressText = $addressText !== '' ? $addressText : '--';
    $zipText     = !empty($cast['zip']) ? ('〒' . $cast['zip']) : '';
    $iconImage   = ($cast['img'] ?? null) ?: ($subImages[0]['url'] ?? asset('assets/images/common/no-image.png'));
    $bonusTotal  = number_format((int) ($cast['bonus_total'] ?? 0));
    $likeCount   = number_format((int) ($cast['like_cnt'] ?? 0));
    $photoCount  = count($subImages);
    $word        = trim((string) ($cast['word'] ?? ''));
    $wordPlaceholder = '今、何してる？（タイムラインに公開されます）';
@endphp

@section('content')
<div>

    <div class="px-5 pt-4 pb-6">

        {{-- ===== アイコン + ひとこと吹き出し ===== --}}
        <div class="flex items-start gap-3 mb-5">
            <div class="w-[84px] h-[84px] rounded-full overflow-hidden border-2 border-line-accent/40 shadow-card-3d bg-surface-from shrink-0">
                <img src="{{ $iconImage }}" alt="" class="w-full h-full object-cover">
            </div>

            <div class="flex-1 min-w-0">
                <div class="relative bg-gradient-to-br from-surface-from to-base border border-line-accent/40 rounded-2xl shadow-card-3d p-3 pr-9">
                    {{-- 吹き出しのしっぽ（アイコン側に向く） --}}
                    <span class="absolute top-5 -left-[8px] w-0 h-0 border-y-[8px] border-y-transparent border-r-[10px] border-r-line-accent/40"></span>
                    <span class="absolute top-5 -left-[6px] w-0 h-0 border-y-[7px] border-y-transparent border-r-[9px] border-r-surface-from"></span>

                    <p id="display-word"
                       data-placeholder="{{ $wordPlaceholder }}"
                       class="text-[13px] leading-relaxed {{ $word === '' ? 'text-text-sub' : 'text-text-main' }}">
                        {{ $word !== '' ? $word : $wordPlaceholder }}
                    </p>

                    <button type="button" id="open-word-edit-btn"
                            aria-label="ひとことを編集"
                            class="absolute top-2 right-2 w-7 h-7 rounded-full flex items-center justify-center bg-accent text-on-accent shadow-[0_3px_8px_rgba(0,0,0,0.4),inset_0_1px_0_rgba(255,255,255,0.2),inset_0_-1px_0_rgba(0,0,0,0.18)] hover:brightness-110 active:scale-95 transition-all">
                        <x-ui.icon name="edit" class="text-xs" />
                    </button>

                    <div class="mt-2 pt-2 border-t border-line">
                        <span id="display-word-updated" class="text-[10px] text-text-sub">
                            最終更新 {{ $cast['appeal_updated_at'] ?? '未設定' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Name + Likes（横並び。ライクのラベル無し） ===== --}}
        <div class="flex items-center justify-between gap-3 mb-5">
            <h1 class="app-title text-[24px] text-text-main leading-tight truncate min-w-0">{{ $displayName }}</h1>
            <div class="flex items-center gap-1.5 shrink-0">
                <x-ui.icon name="like" class="text-[18px] text-discovery-pink" />
                <span class="font-bold text-[14px] text-text-main">{{ $likeCount }}</span>
            </div>
        </div>

        {{-- 獲得ボーナス金合計バッジは「採用・入金管理」画面に移設 --}}

        {{-- ===== Sub-page menu（採用・入金管理のみ。本人確認/プロフィール編集は DETAILS 内、レビューは採用・入金管理から店舗別導線へ） ===== --}}
        <div class="flex flex-col gap-3">
            <x-ui.menu-card icon="settings"
                            sub="EMPLOYMENT & PAYMENT"
                            title="採用・入金管理"
                            href="{{ route('cast.mypage.management') }}"
                            class="mypage-menu-card" />
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

        {{-- Gallery panel：元の機能を維持（並び替え / アップロード / 再切り抜き / 削除） --}}
        <div data-tab-panel="gallery" class="is-active">
            <ul id="gallery-list"
                data-sort-save-url="{{ route('cast.mypage.images.order') }}"
                data-empty-image-url="{{ asset('assets/images/common/no-image.png') }}">
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
                            <span class="photo-slot-empty" aria-label="画像を追加"><i class="fas fa-plus"></i></span>
                        @endif
                    </div>
                </li>
                @endfor
            </ul>
        </div>

        {{-- Details panel --}}
        <div data-tab-panel="details">
            <div class="p-4 flex flex-col gap-4">

                <x-ui.card class="p-5">
                    <h3 class="app-title text-[13px] tracking-widest text-accent-text mb-4 flex items-center gap-2">
                        <x-ui.icon name="mypage" class="text-lg" /> BASIC
                    </h3>
                    <div class="flex flex-col gap-3">
                        <div class="flex justify-between items-center border-b border-line pb-2">
                            <span class="text-[12px] text-text-sub font-medium">生年月日</span>
                            <span class="text-[13px] font-bold text-text-main">{{ $birthdayText }}</span>
                        </div>
                        <div class="flex justify-between items-center border-b border-line pb-2">
                            <span class="text-[12px] text-text-sub font-medium">身長 / 体重</span>
                            <span class="text-[13px] font-bold text-text-main">{{ $heightText }} / {{ $weightText }}</span>
                        </div>
                        <div class="flex justify-between items-center border-b border-line pb-2">
                            <span class="text-[12px] text-text-sub font-medium">B / W / H</span>
                            <span class="text-[13px] font-bold text-text-main">{{ $bwhText }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-[12px] text-text-sub font-medium">住所</span>
                            <span class="text-[13px] font-bold text-text-main">{{ $addressText }}</span>
                            @if($zipText !== '')
                                <span class="text-[11px] text-text-sub">{{ $zipText }}</span>
                            @endif
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-[12px] text-text-sub font-medium">自己PR</span>
                            <span class="text-[13px] font-medium text-text-main leading-relaxed">
                                {!! !empty($cast['pr'] ?? '') ? nl2br(e($cast['pr'])) : '<span class="text-text-sub">--</span>' !!}
                            </span>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card class="p-5">
                    <h3 class="app-title text-[13px] tracking-widest text-accent-text mb-4 flex items-center gap-2">
                        <x-ui.icon name="super" class="text-lg" /> STYLE &amp; TAGS
                    </h3>
                    <div class="flex flex-col gap-3">
                        <div class="flex justify-between items-center border-b border-line pb-2">
                            <span class="text-[12px] text-text-sub font-medium">接客タイプ</span>
                            <span class="text-[13px] font-bold text-text-main">{{ $cast['personality_type'] ?: '--' }}</span>
                        </div>
                        <div class="flex justify-between items-center border-b border-line pb-2">
                            <span class="text-[12px] text-text-sub font-medium">ルックス</span>
                            <span class="text-[13px] font-bold text-text-main">{{ $cast['my_field'] ?? '--' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-[12px] text-text-sub font-medium">性格・内面</span>
                            <span class="text-[13px] font-bold text-text-main">{{ $cast['my_inner_skills'] ?? '--' }}</span>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card class="p-5">
                    <h3 class="app-title text-[13px] tracking-widest text-accent-text mb-4 flex items-center gap-2">
                        <x-ui.icon name="settings" class="text-lg" /> CAREER
                    </h3>
                    <div class="flex flex-col gap-3">
                        <div class="flex justify-between items-center border-b border-line pb-2">
                            <span class="text-[12px] text-text-sub font-medium">ナイトワーク経験</span>
                            <span class="text-[13px] font-bold text-text-main">{{ $cast['night_work_label'] ?: '--' }}</span>
                        </div>
                        <div class="flex justify-between items-center border-b border-line pb-2">
                            <span class="text-[12px] text-text-sub font-medium">現職業</span>
                            <span class="text-[13px] font-bold text-text-main">{{ $cast['profession'] ?? ($cast['current_job'] ?? '--') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-[12px] text-text-sub font-medium">希望職種</span>
                            <span class="text-[13px] font-bold text-text-main">{{ $cast['industry_names'] ?? ($cast['desired_job'] ?? '--') }}</span>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.menu-card icon="check"
                                sub="IDENTITY"
                                title="本人確認"
                                href="{{ route('cast.mypage.identity') }}"
                                class="mypage-menu-card" />
                <x-ui.menu-card icon="mypage"
                                sub="PROFILE"
                                title="プロフィールを編集"
                                href="{{ route('cast.profile.edit') }}"
                                class="mypage-menu-card" />

            </div>
        </div>

    </div>

</div>

{{-- ============================================================
     ひとこと編集モーダル
     ============================================================ --}}
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

{{-- ============================================================
     ギャラリー：元の機能のモーダル群
     ============================================================ --}}
{{-- 画像大表示モーダル（削除・再切り抜き） --}}
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

{{-- 画像編集モーダル（推奨サイズに合わせてトリミング） --}}
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
            {{-- キャンセル：副次アクションなのでグレー --}}
            <button type="button" id="image-edit-cancel-btn"
                    class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-full font-bold bg-gray-700 text-gray-200 border border-gray-600 shadow-md hover:bg-gray-600 active:translate-y-1 transition-all duration-300">
                キャンセル
            </button>
            {{-- アップロード：主要アクション。x-ui.button variant="grad" を踏襲 --}}
            <x-ui.button id="image-edit-confirm-btn" variant="grad" icon="check">アップロード</x-ui.button>
        </div>
    </div>
</div>

<input type="file" id="gallery-upload" class="sr-only" accept="image/*">
@endsection

@push('scripts')
<script>
(function () {
    'use strict';
    var placeholderText = @json($wordPlaceholder);
    var openBtn = document.getElementById('open-word-edit-btn');
    var modal = document.getElementById('modal-word');
    var displayEl = document.getElementById('display-word');
    var input = document.getElementById('word-input');
    var saveBtn = document.getElementById('word-edit-save-btn');
    var cancelBtn = document.getElementById('word-edit-cancel-btn');
    var updatedEl = document.getElementById('display-word-updated');

    function showModal() {
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        if (displayEl && input) {
            var cur = displayEl.innerText.trim();
            input.value = (cur === placeholderText) ? '' : cur;
        }
        setTimeout(function () { if (input) input.focus(); }, 50);
    }
    function hideModal() {
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    if (openBtn) openBtn.addEventListener('click', showModal);
    if (cancelBtn) cancelBtn.addEventListener('click', hideModal);
    if (modal) modal.addEventListener('click', function (e) {
        if (e.target === modal) hideModal();
    });

    if (saveBtn) saveBtn.addEventListener('click', function () {
        if (saveBtn.disabled) return;
        var val = (input ? input.value : '').trim();
        saveBtn.disabled = true;
        fetch(@json(route('cast.mypage.word')), {
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
                hideModal();
            } else {
                (window.appToast || window.alert)((res && res.message) || '保存に失敗しました', 'error');
            }
        })
        .catch(function () { (window.appToast || window.alert)('保存に失敗しました', 'error'); })
        .finally(function () { saveBtn.disabled = false; });
    });
})();
</script>

{{-- ===== ギャラリー機能：元のスクリプト群 ===== --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.4.1/papaparse.min.js"></script>
<script src="{{ asset('assets/js/gallery-sortable.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
<script>
window.MYPAGE_GALLERY_CONFIG = {
    csrfToken: @json(csrf_token()),
    uploadUrl: @json(route('cast.mypage.images.upload')),
    deleteUrlTemplate: @json(route('cast.mypage.images.delete', ['id' => '__ID__'])),
    cropAspectW: 4,
    cropAspectH: 5,
    cropMaxWidth: 1080,
    cropMaxHeight: 1350
};
</script>
<script src="{{ asset('assets/js/mypage-gallery.js') }}"></script>
@endpush

@push('head-styles')
{{-- ===== ギャラリー機能の依存CSS（モーダル・並び替え・cropper） ===== --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
<link rel="stylesheet" href="{{ asset('assets/css/cast_profile.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
{{-- ギャラリーグリッドへの上書き（#gallery-list 配下のみ。色は触らずレイアウトだけ） --}}
<style>
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
    /* 画像未登録の枠：点線で「ここに追加できる」を視覚化 */
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

    /* menu-card の右端「＞」を紫アクセントの矢印だけにする（丸枠を撤去） */
    .mypage-menu-card > span:last-child {
        width: auto !important;
        height: auto !important;
        border: 0 !important;
        border-radius: 0 !important;
        color: var(--color-accent-text) !important;
        opacity: 1 !important;
    }
    .mypage-menu-card > span:last-child > i {
        font-size: 1.3rem !important;
        color: var(--color-accent-text) !important;
    }
</style>
@endpush
