@extends('layouts.app-v2')

@section('title', 'DISCOVERY')
@section('body-class', 'no-scroll page-home')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/home.css') }}?v=20260720-seg-tiny">
@endpush

@php
    $itemType = $itemType ?? 'cast';
    $isShop = ($itemType === 'shop');
    $isRecruit = ($itemType === 'recruit');
    $detailRoute = $isRecruit ? 'cast.shopprofile.show' : ($isShop ? 'cast.shopprofile.show' : 'shop.castprofileview.show');
    $talkRoute = ($isRecruit || $isShop) ? 'cast.talk.room' : 'shop.talk.room';
@endphp
@section('content')
<div id="home-screen" data-discovery-mode="{{ $itemType }}">
    {{-- メインスワイパー（上下） --}}
    <div class="main-swiper swiper">
        <div class="swiper-wrapper">
            @foreach($items as $item)
            <div
                class="swiper-slide cast-card glass-card {{ $isRecruit ? 'cast-card--recruit' : '' }}"
                @if($isRecruit)
                data-detail-url="{{ route('cast.shopprofile.show', $item['id']) }}"
                @endif
            >
                @php
                    $images = $item['images'] ?? [];
                    if (empty($images)) {
                        $images = [asset('assets/images/common/no-image.png')];
                    }
                    $imageCount = count($images);
                @endphp

                @if($isRecruit)
                {{-- ============================================================ --}}
                {{-- 求人カード（上部65%画像 / コンパクト情報 / タグなし） --}}
                {{-- ============================================================ --}}

                {{-- 1. 画像エリア（上部 65%） --}}
                <div class="rc-img-wrap home-photo-wrap">
                    <div class="photo-swiper swiper {{ $imageCount <= 1 ? 'photo-swiper--single' : '' }}">
                        <div class="swiper-wrapper">
                            @foreach($images as $index => $imgPath)
                            <div class="swiper-slide">
                                <img
                                    src="{{ $imgPath }}"
                                    alt="{{ $item['name'] }}"
                                    class="home-photo"
                                    loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                                >
                            </div>
                            @endforeach
                        </div>
                        @if($imageCount > 1)
                        <div class="photo-pagination swiper-pagination stop-propagation"></div>
                        @endif
                    </div>
                    @php $mo = $item['manager_overlay'] ?? ['show' => false]; @endphp
                    @if(!empty($mo['show']))
                    <div class="rc-manager-msg" aria-label="キャッチコピー">
                        <div class="rc-manager-msg__backdrop">
                            <div class="rc-manager-msg__inner">
                                @if(!empty($mo['line1_html']))
                                <p class="rc-manager-msg__line1">{!! $mo['line1_html'] !!}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                    {{-- 画像下端を黒に溶け込ませるグラデーション --}}
                    <div class="rc-img-gradient" aria-hidden="true"></div>
                </div>

                {{-- 下部の縦スワイプ透過ゾーン：photo-swiper の外に置き、
                     下半分のドラッグをメイン（縦）スワイパーへ直接届かせる --}}
                <div class="rc-vswipe-zone" aria-hidden="true"></div>


                {{-- 2. 下部スタック（4行構成）
                     1行目: 店名 + KEEP（右端） / 2行目: 業種・最寄り駅・距離・評価 /
                     3行目: 左=ボーナス金（大） 右=時給 / 4行目: トークCTA --}}
                <div class="rc-bottom-bar" aria-label="店舗情報">
                    <div class="rc-bottom-bar__stack">
                        @php
                            $trialR = $item['trial_hourly_range'] ?? null;
                            $helpR  = $item['help_hourly_range'] ?? null;
                            $bonusRg = $item['signup_bonus_range'] ?? null;
                            $hasRating = !empty($item['rating']) && $item['rating'] > 0;
                            $stationLine = trim((string) ($item['nearest_station'] ?? ''));
                            $areaLine = trim(($item['pref'] ?? '') . ' ' . ($item['city'] ?? ''));
                        @endphp

                        {{-- 優良店バッヂ：店名の真上（タップで達成条件モーダル） --}}
                        @if(!empty($item['is_premium']))
                            <div class="rc-premium-row">
                                <button type="button" class="premium-badge-btn stop-propagation" data-open-premium-info aria-haspopup="dialog" aria-controls="modal-premium-info" aria-label="優良店バッヂの達成条件">
                                    <x-ui.premium-badge />
                                </button>
                            </div>
                        @endif

                        {{-- 1行目：店名 + KEEP（右端） --}}
                        <div class="rc-name-row">
                            <h2 class="rc-shop-name serif-font">{{ $item['name'] }}</h2>
                            <button
                                type="button"
                                class="swipe-keep-corner swipe-keep-corner--inline stop-propagation {{ !empty($item['is_kept']) ? 'is-active' : '' }}"
                                data-fav-toggle
                                data-item-id="{{ $item['id'] }}"
                                data-item-type="shop"
                                data-action="keep"
                                aria-label="キープ"
                                aria-pressed="{{ !empty($item['is_kept']) ? 'true' : 'false' }}"
                            >
                                <i class="fas fa-bookmark" aria-hidden="true"></i>
                            </button>
                        </div>

                        {{-- 2行目：業種 → 最寄り駅 → 距離 → 評価レビュー数（右端） --}}
                        <div class="rc-line rc-line--meta">
                            @if(!empty($item['industry_name']))
                                <span class="rc-genre">{{ $item['industry_name'] }}</span>
                            @endif
                            <span class="rc-loc">
                                <i class="fas {{ $stationLine !== '' ? 'fa-train' : 'fa-map-marker-alt' }}" aria-hidden="true"></i>{{ $stationLine !== '' ? $stationLine : ($areaLine !== '' ? $areaLine : 'エリア未設定') }}
                            </span>
                            @if(!empty($item['distance_label']))
                                <span class="rc-dist"><i class="fas fa-route" aria-hidden="true"></i>自分から {{ $item['distance_label'] }}</span>
                            @endif
                            @if($hasRating)
                                <span class="rc-rating-inline">
                                    <span class="rc-star" aria-hidden="true">★</span>{{ number_format((float)$item['rating'], 1) }}@if((int)($item['review_count'] ?? 0) > 0)<span class="rc-review-cnt">({{ (int)$item['review_count'] }}件)</span>@endif
                                </span>
                            @endif
                        </div>

                        {{-- 4行目：Premium Slate（左=ボーナス金 / 右=体入・ヘルプ時給の横型一体スレート） --}}
                        <div class="pslate">
                            <div class="pslate__bonus">
                                <span class="pslate__label">ボーナス金</span>
                                <span class="pslate__amount">
                                    @if(!empty($bonusRg))<span class="pslate__yen">¥</span>{{ number_format((int)$bonusRg['lo']) }}@if((int)$bonusRg['hi'] > (int)$bonusRg['lo'])〜@endif
                                    @else —
                                    @endif
                                </span>
                            </div>
                            <span class="pslate__divider" aria-hidden="true"></span>
                            <div class="pslate__wages">
                                <div class="pslate__wage">
                                    <span class="pslate__label">体入時給</span>
                                    <span class="pslate__wage-amount">@if(!empty($trialR))¥{{ number_format((int)$trialR['lo']) }}〜@else —@endif</span>
                                </div>
                                <div class="pslate__wage">
                                    <span class="pslate__label">ヘルプ時給</span>
                                    <span class="pslate__wage-amount">@if(!empty($helpR))¥{{ number_format((int)$helpR['lo']) }}〜@else —@endif</span>
                                </div>
                            </div>
                        </div>

                        {{-- 5行目：トーク全幅CTA（採用フローの起点として最強調） --}}
                        <a href="{{ route($talkRoute, ['id' => $item['id'], 'talk_topic' => 'other', 'initiate' => 1]) }}"
                           class="swipe-talk-cta stop-propagation"
                           aria-label="トークを開始する">
                            <i class="fas fa-comment-dots" aria-hidden="true"></i> トークする
                        </a>
                    </div>
                </div>

                {{-- 4. スワイプ誘導ガイド（上向きキャレット・常時バウンス） --}}
                <div class="discovery-swipe-guide" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="18 15 12 9 6 15"/>
                    </svg>
                </div>

                @else
                {{-- ============================================================ --}}
                {{-- キャスト・店舗カード（既存レイアウト）                         --}}
                {{-- ============================================================ --}}

                {{-- メイン写真 --}}
                <div class="home-photo-wrap" data-detail-url="{{ route($detailRoute, $item['id']) }}">
                    <div class="photo-swiper swiper">
                        <div class="swiper-wrapper">
                            @foreach($images as $index => $imgPath)
                                <div class="swiper-slide">
                                    <img
                                        src="{{ $imgPath }}"
                                        alt="{{ $item['name'] }}の写真{{ $imageCount > 1 ? '（' . ($index + 1) . '枚目）' : '' }}"
                                        class="home-photo"
                                        loading="lazy"
                                    >
                                </div>
                            @endforeach
                        </div>
                        <div class="photo-pagination swiper-pagination stop-propagation"></div>
                    </div>
                </div>

                {{-- プロフィール情報（3行構成）
                     1行目: 名前(年齢) + KEEP（右端） / 2行目: 希望業種・位置情報・経験有無 /
                     3行目: 距離（設定時のみ）+ タグ --}}
                <div class="card-bottom-info">
                    {{-- 1行目：名前（年齢はかっこ） + KEEP --}}
                    <div class="cast-name-row">
                        <h2 class="cast-name serif-font">{{ $item['name'] }}@if(!$isShop && isset($item['age']))<span class="age">({{ $item['age'] }})</span>@endif</h2>
                        <button
                            type="button"
                            class="swipe-keep-corner swipe-keep-corner--inline stop-propagation {{ !empty($item['is_kept']) ? 'is-active' : '' }}"
                            data-fav-toggle
                            data-item-id="{{ $item['id'] }}"
                            data-item-type="{{ $itemType === 'recruit' ? 'shop' : 'cast' }}"
                            data-action="keep"
                            aria-label="キープ"
                            aria-pressed="{{ !empty($item['is_kept']) ? 'true' : 'false' }}"
                        >
                            <i class="fas fa-bookmark" aria-hidden="true"></i>
                        </button>
                    </div>

                    {{-- 2行目：希望業種 → 位置情報（パスポート時は設定位置、通常は登録住所）→ 経験有無 --}}
                    @php
                        $locationLabel = trim((string) ($item['location_label'] ?? ''));
                        $isPassport = ($item['location_mode'] ?? '') === 'passport';
                    @endphp
                    <div class="cc-line cc-line--job">
                        @if(!empty($item['industry_name']))
                            <span class="cc-genre">{{ $item['industry_name'] }}</span>
                        @endif
                        @if($locationLabel !== '')
                            <span class="rc-loc {{ $isPassport ? 'rc-loc--passport' : '' }}"
                                  @if($isPassport) title="パスポートモード：本人が指定した位置" @endif>
                                <i class="fas {{ $isPassport ? 'fa-plane-departure' : 'fa-map-marker-alt' }}" aria-hidden="true"></i>{{ $locationLabel }}
                            </span>
                        @endif
                        @if(!$isShop && !empty($item['night_work_label']))
                            <span class="cc-exp {{ $item['night_work_label'] === '経験あり' ? 'is-exp' : '' }}">{{ $item['night_work_label'] }}</span>
                        @endif
                    </div>

                    {{-- 3行目：距離（探索拠点が設定されているときのみ） --}}
                    @if(!empty($item['distance_label']))
                    <div class="cc-line cc-line--dist">
                        <span class="rc-dist"><i class="fas fa-route" aria-hidden="true"></i>自分から {{ $item['distance_label'] }}</span>
                    </div>
                    @endif

                    {{-- タグ --}}
                    <div class="card-tags-row">
                        @foreach($item['tags'] ?? [] as $tag)
                            <span class="tag-pill">#{{ $tag }}</span>
                        @endforeach
                    </div>

                    {{-- 5行目：トーク全幅CTA --}}
                    <a href="{{ route($talkRoute, ['id' => $item['id'], 'talk_topic' => 'other', 'initiate' => 1]) }}"
                       class="swipe-talk-cta stop-propagation"
                       aria-label="トークを開始する">
                        <i class="fas fa-comment-dots" aria-hidden="true"></i> トークする
                    </a>
                </div>
                <div class="discovery-swipe-guide" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="18 15 12 9 6 15"/>
                    </svg>
                </div>

                @endif
            </div>
            @endforeach
        </div>
    </div>

    {{-- 優良店バッヂの達成条件モーダル（スワイプカードのバッヂタップで開く） --}}
    <div id="modal-premium-info" class="premium-info-modal" hidden role="dialog" aria-modal="true" aria-labelledby="premium-info-title">
        <div class="premium-info-modal__overlay" data-close-premium-info></div>
        <div class="premium-info-modal__panel">
            <button type="button" class="premium-info-modal__close" data-close-premium-info aria-label="閉じる">×</button>
            <h3 id="premium-info-title" class="premium-info-modal__title">
                <i class="fas fa-crown" aria-hidden="true"></i> 優良店バッヂの獲得条件
            </h3>
            <ul class="premium-info-modal__list">
                <li>すべての案件が「店舗入金確認済み」まで完了している</li>
                <li>請求書発行から店舗入金確認までが10日以内である</li>
            </ul>
            <p class="premium-info-modal__note">※ 条件は毎月見直され、基準を満たさなくなった場合はバッヂ表示が外れることがあります。</p>
        </div>
    </div>

    @push('scripts')
    <script>
    (function () {
        var modal = document.getElementById('modal-premium-info');
        if (!modal) return;
        function open(e) { if (e) { e.preventDefault(); e.stopPropagation(); } modal.hidden = false; document.body.style.overflow = 'hidden'; }
        function close() { modal.hidden = true; document.body.style.overflow = ''; }
        document.addEventListener('click', function (e) {
            var trg = e.target.closest('[data-open-premium-info]');
            if (trg) open(e);
        }, true);
        modal.addEventListener('click', function (e) {
            if (e.target.closest('[data-close-premium-info]')) close();
        });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !modal.hidden) close(); });
    })();
    </script>
    @endpush

    {{-- 上下スワイプガイド：上向きに流れるシェブロン + ラベル。
         表示から数秒はしっかり見せ、その後は薄く残る（スワイプ操作で非表示） --}}
    <div class="swipe-updown-guide" id="swipe-updown-guide" aria-hidden="true">
        <span class="swipe-updown-guide__chevs">
            <i class="fas fa-chevron-up"></i>
            <i class="fas fa-chevron-up"></i>
        </span>
        <span class="swipe-updown-guide__label">上にスワイプで次へ</span>
    </div>

    @push('scripts')
    <script>
    {{-- 上下スワイプガイド：一度でも縦スワイプしたら消す（学習済みユーザーへのノイズ防止） --}}
    (function () {
        var guide = document.getElementById('swipe-updown-guide');
        var screen = document.getElementById('home-screen');
        if (!guide || !screen) return;
        var startY = null;
        function dismiss() {
            guide.classList.add('is-dismissed');
            screen.removeEventListener('touchstart', onStart);
            screen.removeEventListener('touchmove', onMove);
            screen.removeEventListener('wheel', dismiss);
        }
        function onStart(e) {
            startY = e.touches && e.touches[0] ? e.touches[0].clientY : null;
        }
        function onMove(e) {
            if (startY === null || !e.touches || !e.touches[0]) return;
            if (Math.abs(e.touches[0].clientY - startY) > 40) dismiss();
        }
        screen.addEventListener('touchstart', onStart, { passive: true });
        screen.addEventListener('touchmove', onMove, { passive: true });
        screen.addEventListener('wheel', dismiss, { passive: true });
    })();
    </script>
    @endpush

    {{-- スワイプ操作オンボーディング（初回・久しぶりのみ表示） --}}
    <div class="home-onboarding-overlay" id="home-swipe-onboarding" aria-hidden="true">
        <div class="home-onboarding-inner">
            <div class="home-onboarding-header">
                <span class="home-onboarding-kicker">DISCOVERY GUIDE</span>
                <h2 class="home-onboarding-title">スワイプで直感的にチェック</h2>
                <p class="home-onboarding-lead">まずはホームの操作だけ覚えればOKです。</p>
            </div>
            <div class="home-onboarding-body">
                <div class="home-onboarding-row">
                    <span class="home-onboarding-icon">↑↓</span>
                    <span class="home-onboarding-desc">上下で次 / 前のアカウントへ</span>
                </div>
                <div class="home-onboarding-row">
                    <span class="home-onboarding-icon">←→</span>
                    <span class="home-onboarding-desc">左右で同じアカウントの別写真へ</span>
                </div>
                <div class="home-onboarding-row">
                    <span class="home-onboarding-icon">TAP</span>
                    <span class="home-onboarding-desc">{{ $isRecruit ? 'タップで求人詳細を表示' : 'タップでプロフィール詳細を表示' }}</span>
                </div>
            </div>
            <div class="home-onboarding-footer">
                画面をタップしてガイドを閉じる
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/home.js') }}?v=20260720-seg-mini"></script>
{{-- LIKE / KEEP の共通トグル（全画面この1本に統一） --}}
<script src="{{ asset('assets/js/favorite-quick.js') }}?v=20260720-keep-confirm"></script>
@endpush
