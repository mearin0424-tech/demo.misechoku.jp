{{-- ============================================================
     接客タイプ表示（プロフィール共通パーシャル）
     - 登録済み: 目立つカード → タップで詳細解説モーダル
     - 未登録:   自分のプロフィールなら診断への導線、他者表示なら「--」行
     引数:
       $typeCode  : 4文字コード（例 LCIR）または空
       $canRetest : true なら診断（やり直し）導線を表示（自分のプロフィール用）
       $retestUrl : 診断ページ URL（canRetest 時のみ使用）
     ============================================================ --}}
@php
    $ptInfo = \App\Services\PersonalityTypeCatalog::get($typeCode ?? null);
    $ptRetestUrl = $retestUrl ?? (asset('personality-test') . '?' . http_build_query(['return_to' => url()->current()]));
@endphp

@if($ptInfo)
    {{-- 目立たせたタイプカード（タップで解説） --}}
    <button type="button" id="open-personality-type-modal"
            aria-haspopup="dialog" aria-controls="personality-type-modal"
            class="w-full flex items-center gap-3 px-4 py-3.5 rounded-xl border border-line-accent/40 bg-gradient-to-br from-accent/15 via-surface-from to-base shadow-card-3d hover:border-accent/70 active:scale-[0.99] transition-all text-left">
        <span class="shrink-0 w-11 h-11 rounded-full flex items-center justify-center bg-gradient-to-br from-accent-grad-from to-accent-grad-to text-on-accent-strong shadow-[inset_0_2px_3px_rgba(255,255,255,0.3),0_3px_8px_rgba(0,0,0,0.4)]">
            <i class="fas fa-wand-magic-sparkles text-[16px]"></i>
        </span>
        <span class="flex-1 min-w-0 flex flex-col gap-0.5">
            <span class="text-[10px] font-extrabold tracking-[0.18em] text-accent-text uppercase">接客タイプ <span class="app-title">{{ $ptInfo['code'] }}</span></span>
            <span class="text-[14px] font-extrabold text-text-main leading-snug truncate">{{ $ptInfo['title'] }}</span>
        </span>
        <span class="shrink-0 inline-flex items-center gap-1 text-[10px] font-bold text-text-sub">
            解説を見る <i class="fas fa-chevron-right text-[9px]"></i>
        </span>
    </button>

    {{-- 詳細解説モーダル --}}
    <div id="personality-type-modal" role="dialog" aria-modal="true" aria-label="接客タイプの解説"
         class="fixed inset-0 z-[1100] hidden items-center justify-center bg-black/60 backdrop-blur-sm p-5">
        <div class="w-full max-w-[560px] max-h-[82vh] overflow-y-auto rounded-2xl border border-line-accent/40 bg-gradient-to-br from-surface-from to-base shadow-card-3d">
            <div class="sticky top-0 flex items-start justify-between gap-3 px-5 pt-5 pb-3 bg-gradient-to-b from-surface-from to-surface-from/80 backdrop-blur-sm border-b border-line">
                <div class="min-w-0">
                    <p class="text-[10px] font-extrabold tracking-[0.18em] text-accent-text uppercase mb-0.5">接客タイプ <span class="app-title">{{ $ptInfo['code'] }}</span></p>
                    <h3 class="text-[17px] font-extrabold text-text-main leading-snug">{{ $ptInfo['title'] }}</h3>
                </div>
                <button type="button" id="close-personality-type-modal" aria-label="閉じる"
                        class="shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-text-sub hover:text-text-main hover:bg-accent/10 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="px-5 py-4 flex flex-col gap-4">
                {{-- 強み --}}
                <div class="px-4 py-3 rounded-xl bg-accent/10 border border-line-accent/40">
                    <p class="text-[10px] font-extrabold tracking-widest text-accent-text mb-1">STRENGTH — 強み</p>
                    <p class="text-[13px] font-bold text-text-main leading-relaxed">{{ $ptInfo['strength'] }}</p>
                </div>

                {{-- 解説 --}}
                <div>
                    <p class="text-[10px] font-extrabold tracking-widest text-accent-text mb-1.5">ABOUT — このタイプについて</p>
                    <p class="text-[13px] text-text-main leading-relaxed">{{ $ptInfo['description'] }}</p>
                </div>

                {{-- 4軸の内訳 --}}
                <div>
                    <p class="text-[10px] font-extrabold tracking-widest text-accent-text mb-2">4つの軸</p>
                    <ul class="flex flex-col gap-2">
                        @foreach($ptInfo['axes'] as $axis)
                            <li class="flex items-start gap-2.5">
                                <span class="shrink-0 w-6 h-6 rounded-md flex items-center justify-center bg-accent/15 border border-line-accent/40 text-accent-text text-[11px] font-extrabold app-title">{{ $axis['code'] }}</span>
                                <span class="min-w-0">
                                    <span class="block text-[12px] font-bold text-text-main">{{ $axis['label'] }}</span>
                                    <span class="block text-[11px] text-text-sub leading-relaxed">{{ $axis['text'] }}</span>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- 気をつけたいこと --}}
                <div class="px-4 py-3 rounded-xl border border-line">
                    <p class="text-[10px] font-extrabold tracking-widest text-text-sub mb-1">POINT — 気をつけたいこと</p>
                    <p class="text-[12px] text-text-sub leading-relaxed">{{ $ptInfo['weakness'] }}</p>
                </div>

                @if(!empty($canRetest))
                    <a href="{{ $ptRetestUrl }}"
                       class="mt-1 inline-flex items-center justify-center gap-2 w-full px-4 py-3 rounded-full border border-line-accent/40 bg-accent/10 text-accent-text text-[13px] font-bold hover:bg-accent/20 active:scale-[0.99] transition-all">
                        <i class="fas fa-arrow-rotate-left text-[12px]"></i> 診断をやり直す
                    </a>
                @endif
            </div>
        </div>
    </div>

    <script>
    (function () {
        'use strict';
        var openBtn = document.getElementById('open-personality-type-modal');
        var modal = document.getElementById('personality-type-modal');
        var closeBtn = document.getElementById('close-personality-type-modal');
        if (!openBtn || !modal) return;
        function show() { modal.classList.remove('hidden'); modal.classList.add('flex'); }
        function hide() { modal.classList.add('hidden'); modal.classList.remove('flex'); }
        openBtn.addEventListener('click', show);
        if (closeBtn) closeBtn.addEventListener('click', hide);
        modal.addEventListener('click', function (e) { if (e.target === modal) hide(); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') hide(); });
    })();
    </script>
@elseif(!empty($canRetest))
    {{-- 未登録（自分のプロフィール）：診断への導線 --}}
    <a href="{{ $ptRetestUrl }}"
       class="w-full flex items-center gap-3 px-4 py-3.5 rounded-xl border border-dashed border-line-accent/40 hover:border-accent/70 active:scale-[0.99] transition-all">
        <span class="shrink-0 w-11 h-11 rounded-full flex items-center justify-center bg-accent/10 border border-line-accent/40 text-accent-text">
            <i class="fas fa-wand-magic-sparkles text-[16px]"></i>
        </span>
        <span class="flex-1 min-w-0 flex flex-col gap-0.5">
            <span class="text-[10px] font-extrabold tracking-[0.18em] text-accent-text uppercase">接客タイプ</span>
            <span class="text-[13px] font-bold text-text-main">診断を受けてタイプを登録する</span>
        </span>
        <i class="fas fa-chevron-right text-[10px] text-text-sub shrink-0"></i>
    </a>
@else
    {{-- 未登録（他者からの表示）：従来どおりの行表示 --}}
    <div class="flex justify-between items-center border-b border-line pb-2">
        <span class="text-[12px] text-text-sub font-medium">接客タイプ</span>
        <span class="text-[13px] font-bold text-text-main">--</span>
    </div>
@endif
