@extends('layouts.app')

@section('title', 'INTERACTION')

@push('styles')
{{-- 共通サブヘッダーおよび画面専用CSSの読み込み --}}
<link rel="stylesheet" href="{{ asset('assets/css/sub-header.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/interaction.css') }}">
@endpush

@section('content')
<div class="has-sub-header">
    @include('layouts.parts.sub-header', [
        'tabs' => [
            ['id' => 'pane-keep', 'label' => 'キープ', 'active' => true],
        ]
    ])
</div>

<div class="tab-content-container contents tab-page-body">

    {{-- タブ：キープ (KEEP) --}}
    <div id="pane-keep" class="tab-pane active">
        @if (empty($keepCasts))
            <div class="no-data-wrapper">
                <i class="fas fa-star opacity-10 text-5xl mb-3 block"></i>
                <p class="no-data-msg">
                    @if(!empty($isCastPortal))
                        お気に入り登録したお店はいません。
                    @else
                        お気に入り登録したキャストはいません。
                    @endif
                </p>
            </div>
        @else
            @foreach($keepCasts as $c)
                @include('shops.interaction.keep', ['c' => $c, 'profileRoute' => $profileRoute ?? 'shop.castprofileview.show'])
            @endforeach
        @endif

        {{-- おすすめ（条件が似ているお店／キャスト） --}}
        @php
            $recommendItems = $recommendItems ?? [];
            $recommendType = $recommendType ?? 'shop';
            $recommendLogic = $recommendLogic ?? [];
            $recommendDetailRoute = $recommendDetailRoute ?? 'cast.recruit.show';
            $recommendTitle = $recommendType === 'shop' ? '条件が似ているお店' : '条件が似ているキャスト';
            $recommendEmpty = $recommendType === 'shop'
                ? '今は条件に合うお店が見つかりませんでした。'
                : '今は条件に合うキャストが見つかりませんでした。';
        @endphp
        <section class="recommend-section" aria-labelledby="recommend-heading">
            <header class="recommend-section__head">
                <h2 id="recommend-heading" class="recommend-section__title">
                    <i class="fas fa-magic"></i> {{ $recommendTitle }}
                </h2>
                <button type="button" class="recommend-section__info-btn" aria-label="表示ロジックの説明" data-recommend-info-trigger>
                    <i class="fas fa-info-circle"></i>
                </button>
            </header>

            @if(empty($recommendItems))
                <p class="recommend-section__empty">{{ $recommendEmpty }}</p>
            @else
                <ul class="recommend-list">
                    @foreach($recommendItems as $r)
                        <li class="recommend-item">
                            <a href="{{ route($recommendDetailRoute, ['id' => $r['id']]) }}" class="recommend-item__link">
                                <img src="{{ $r['image'] }}" alt="" class="recommend-item__thumb">
                                <div class="recommend-item__body">
                                    <div class="recommend-item__name">
                                        {{ $r['name'] }}
                                        @if($recommendType === 'cast' && !empty($r['age']))
                                            <span class="recommend-item__age">{{ $r['age'] }}</span>
                                        @endif
                                    </div>
                                    <div class="recommend-item__meta">
                                        @if(!empty($r['pref']) || !empty($r['city']))
                                            <span><i class="fas fa-map-marker-alt"></i> {{ trim($r['pref'] . ' ' . $r['city']) }}</span>
                                        @endif
                                        @if($recommendType === 'shop' && !empty($r['hourly_wage']))
                                            <span class="recommend-item__wage"><i class="fas fa-yen-sign"></i> {{ number_format((int) $r['hourly_wage']) }}円〜</span>
                                        @endif
                                    </div>
                                </div>
                                <i class="fas fa-chevron-right recommend-item__chev"></i>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        {{-- ロジック説明モーダル --}}
        <div class="recommend-info-modal" data-recommend-info-modal hidden aria-hidden="true" role="dialog" aria-labelledby="recommend-info-title">
            <div class="recommend-info-modal__overlay" data-recommend-info-close></div>
            <div class="recommend-info-modal__panel">
                <header class="recommend-info-modal__head">
                    <h3 id="recommend-info-title" class="recommend-info-modal__title">表示ロジック</h3>
                    <button type="button" class="recommend-info-modal__close" data-recommend-info-close aria-label="閉じる">&times;</button>
                </header>
                <ul class="recommend-info-modal__list">
                    @foreach($recommendLogic as $line)
                        <li>{{ $line }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.recommend-section {
    margin: 28px 0 32px;
    padding: 16px 14px 18px;
    border-radius: 14px;
    background: linear-gradient(180deg, rgba(197,160,89,0.06), rgba(255,255,255,0.02));
    border: 1px solid var(--color-border);
}
.recommend-section__head {
    display: flex;
    align-items: center;
    gap: 6px;
    margin: 0 0 12px;
}
.recommend-section__title {
    flex: 1;
    margin: 0;
    font-family: var(--font-serif);
    font-size: 0.98rem;
    font-weight: 800;
    color: var(--color-text-header);
    letter-spacing: 0.04em;
}
.recommend-section__title i { color: var(--gold); margin-right: 6px; font-size: 0.86rem; }
.recommend-section__info-btn {
    flex: 0 0 auto;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    border: 1px solid var(--color-border);
    background: transparent;
    color: var(--gold);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.recommend-section__info-btn:hover { background: rgba(197,160,89,0.10); }
.recommend-section__empty {
    margin: 0;
    padding: 18px 4px;
    text-align: center;
    font-size: 0.82rem;
    color: var(--color-text-muted);
}
.recommend-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 8px; }
.recommend-item__link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    border-radius: 10px;
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--color-border);
    text-decoration: none;
    color: inherit;
    transition: border-color 0.15s ease, background 0.15s ease;
}
.recommend-item__link:hover { border-color: var(--color-border-strong); background: rgba(197,160,89,0.06); }
.recommend-item__thumb {
    width: 48px; height: 48px; flex: 0 0 auto;
    border-radius: 8px; object-fit: cover;
    background: var(--color-card-strong);
}
.recommend-item__body { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 2px; }
.recommend-item__name {
    font-size: 0.92rem;
    font-weight: 700;
    color: var(--color-text-header);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.recommend-item__age {
    margin-left: 4px;
    font-size: 0.74rem;
    color: var(--color-text-muted);
    font-weight: 600;
}
.recommend-item__meta {
    display: flex;
    flex-wrap: wrap;
    gap: 6px 10px;
    font-size: 0.7rem;
    color: var(--color-text-muted);
}
.recommend-item__meta i { color: var(--gold); margin-right: 2px; font-size: 0.62rem; }
.recommend-item__wage { color: var(--gold-light); font-weight: 700; }
.recommend-item__chev { flex: 0 0 auto; color: var(--color-text-muted); font-size: 0.72rem; }

/* ロジック説明モーダル */
.recommend-info-modal { position: fixed; inset: 0; z-index: 2000; display: none; align-items: center; justify-content: center; padding: 24px 16px; }
.recommend-info-modal:not([hidden]) { display: flex; }
.recommend-info-modal__overlay { position: absolute; inset: 0; background: rgba(0,0,0,0.78); backdrop-filter: blur(4px); }
.recommend-info-modal__panel {
    position: relative;
    width: min(420px, 100%);
    max-height: 80vh;
    overflow-y: auto;
    background: linear-gradient(180deg, var(--color-sub), var(--dark-bg));
    border: 1px solid var(--color-border-strong);
    border-radius: 16px;
    box-shadow: 0 24px 64px rgba(0,0,0,0.7);
    padding: 18px 16px 14px;
}
.recommend-info-modal__head { display: flex; align-items: center; gap: 8px; margin: 0 0 12px; }
.recommend-info-modal__title { flex: 1; margin: 0; font-family: var(--font-serif); font-size: 1rem; color: var(--color-text-header); }
.recommend-info-modal__close { background: transparent; border: 0; color: var(--color-text-muted); font-size: 1.4rem; cursor: pointer; padding: 4px 8px; }
.recommend-info-modal__list {
    margin: 0;
    padding: 0;
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.recommend-info-modal__list li {
    font-size: 0.82rem;
    line-height: 1.6;
    color: var(--color-text);
    padding: 8px 10px;
    border-radius: 8px;
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--color-border);
}
</style>
@endpush

@push('scripts')
{{-- 共通タブ切り替えJSの読み込み --}}
<script src="{{ asset('assets/js/sub-header.js') }}"></script>
<script>
(function () {
    var modal = document.querySelector('[data-recommend-info-modal]');
    if (!modal) return;
    function open() { modal.hidden = false; modal.setAttribute('aria-hidden', 'false'); document.body.style.overflow = 'hidden'; }
    function close() { modal.hidden = true; modal.setAttribute('aria-hidden', 'true'); document.body.style.overflow = ''; }
    document.querySelectorAll('[data-recommend-info-trigger]').forEach(function (b) { b.addEventListener('click', open); });
    modal.querySelectorAll('[data-recommend-info-close]').forEach(function (b) { b.addEventListener('click', close); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !modal.hidden) close(); });
})();
</script>
@endpush
