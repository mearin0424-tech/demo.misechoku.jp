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
<div class="pt-14 pb-24">

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
                            class="absolute top-2 right-2 w-7 h-7 rounded-full flex items-center justify-center bg-accent/20 text-accent-text border border-line-accent/40 hover:bg-accent/30 transition-colors">
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

        {{-- ===== Stats ===== --}}
        <div class="flex justify-around py-3 mb-5 rounded-panel border border-line-accent/40 bg-gradient-to-br from-surface-from to-base shadow-card-3d">
            <div class="flex flex-col items-center">
                <span class="font-bold text-[18px] text-text-main tracking-tight">{{ $likeCount }}</span>
                <span class="text-[10px] text-text-sub">ライク</span>
            </div>
            <div class="flex flex-col items-center">
                <span class="font-bold text-[18px] text-accent-text tracking-tight">¥{{ $bonusTotal }}</span>
                <span class="text-[10px] text-text-sub">ボーナス</span>
            </div>
            <div class="flex flex-col items-center">
                <span class="font-bold text-[18px] text-text-main tracking-tight">{{ $photoCount }}</span>
                <span class="text-[10px] text-text-sub">写真</span>
            </div>
        </div>

        {{-- ===== Name ===== --}}
        <div class="mb-4">
            <h1 class="app-title text-[24px] text-text-main leading-tight">{{ $displayName }}</h1>
        </div>

        {{-- ===== Bonus badge ===== --}}
        <div class="mb-5">
            <x-ui.badge variant="gold">
                <span class="text-[10px] tracking-wider opacity-90 mr-2">獲得ボーナス金合計</span>
                <span class="text-[16px] tracking-wider font-extrabold">¥{{ $bonusTotal }}</span>
            </x-ui.badge>
        </div>

        {{-- ===== Sub-page menu ===== --}}
        <div class="flex flex-col gap-3">
            <x-ui.menu-card icon="settings"
                            sub="EMPLOYMENT & PAYMENT"
                            title="採用・入金管理"
                            href="{{ route('cast.mypage.management') }}" />
            <x-ui.menu-card icon="super"
                            sub="REVIEWS"
                            title="レビュー一覧"
                            href="{{ route('cast.mypage.reviews') }}" />
            <x-ui.menu-card icon="check"
                            sub="IDENTITY"
                            title="本人確認"
                            href="{{ route('cast.mypage.identity') }}" />
            <x-ui.menu-card icon="mypage"
                            sub="PROFILE"
                            title="プロフィールを編集"
                            href="{{ route('cast.profile.edit') }}" />
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

        {{-- Gallery panel --}}
        <div data-tab-panel="gallery" class="is-active">
            <div class="grid grid-cols-3 gap-[2px]">
                @for($i = 0; $i < 9; $i++)
                    @php $img = $subImages[$i] ?? null; @endphp
                    <div class="aspect-[4/5] relative bg-surface-from overflow-hidden">
                        @if($img && !empty($img['url']))
                            <img src="{{ $img['url'] }}" alt="" class="w-full h-full object-cover">
                            @if($i === 0)
                                <span class="absolute top-1 left-1 text-[9px] font-bold text-on-accent-strong bg-gradient-to-r from-accent-grad-from to-accent-grad-to px-1.5 py-0.5 rounded">MAIN</span>
                            @endif
                        @else
                            <div class="absolute inset-0 flex items-center justify-center text-text-sub/60">
                                <x-ui.icon name="plus" class="text-2xl" />
                            </div>
                        @endif
                    </div>
                @endfor
            </div>
            <div class="px-5 pt-5 flex justify-center">
                <x-ui.button variant="grad" as="a" href="{{ route('cast.profile.edit') }}" icon="plus">
                    ギャラリーを編集
                </x-ui.button>
            </div>
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

                <div class="flex justify-center pt-1">
                    <x-ui.button variant="grad" as="a" href="{{ route('cast.profile.edit') }}">
                        プロフィールを編集
                    </x-ui.button>
                </div>

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
                alert((res && res.message) || '保存に失敗しました');
            }
        })
        .catch(function () { alert('保存に失敗しました'); })
        .finally(function () { saveBtn.disabled = false; });
    });
})();
</script>
@endpush
