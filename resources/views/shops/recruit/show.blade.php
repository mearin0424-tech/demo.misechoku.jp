@extends('layouts.app')

@section('title', ($recruit['store_name'] ?? ($shop['name'] ?? '店舗')) . 'の求人情報')
@section('meta_description', trim((string) (($recruit['catch_copy'] ?? '') ?: ($recruit['message'] ?? 'ミセチョクの求人情報です。'))))
@section('meta_image', $shop['main_img'] ?? ($recruit['hero_image'] ?? asset('assets/images/common/no-image.png')))
@section('canonical', $shareUrl ?? url()->current())
@section('guide_message', empty($forCast) ? '表示の見え方をご確認いただきながら、時給・勤務条件・メッセージが適切に伝わっているかご確認ください。気になる点がございましたら、そのまま編集画面へお戻りいただけます。' : '')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/recruitment.css') }}">
<style>
    .recruit-ref-wrap { padding-bottom: calc(var(--footer-height) + 16px); }
    .recruit-ref-preview-bar { background:#1a1510; border:1px solid #3a2a18; border-radius:12px; padding:10px 12px; margin:12px 0; display:flex; justify-content:space-between; gap:10px; align-items:center; }
    .recruit-ref-preview-bar p { margin:0; font-size:11px; color:#d4af37; font-weight:700; }
    .recruit-ref-preview-bar a { color:#d4af37; border:1px solid #d4af37; border-radius:8px; padding:6px 10px; font-size:11px; text-decoration:none; }
    .recruit-ref-hero { position:relative; margin:0 calc(-1 * var(--content-padding-x)); height:320px; overflow:hidden; background:#111; }
    .recruit-ref-hero img { width:100%; height:100%; object-fit:cover; }
    .recruit-ref-hero-overlay { position:absolute; inset:0; background:linear-gradient(to top,#0a0a0a 0%,rgba(10,10,10,.4) 55%,transparent 100%); }
    .recruit-ref-float-actions { position:absolute; top:12px; left:12px; right:12px; display:flex; justify-content:space-between; z-index:3; }
    .recruit-ref-float-btn { width:34px; height:34px; border-radius:999px; border:1px solid rgba(255,255,255,.28); background:rgba(0,0,0,.45); color:#fff; display:flex; align-items:center; justify-content:center; text-decoration:none; }
    .recruit-ref-thumbs { position:absolute; right:12px; bottom:12px; display:flex; gap:6px; z-index:3; }
    .recruit-ref-thumbs img { width:42px; height:42px; border-radius:8px; border:1px solid rgba(212,175,55,.5); object-fit:cover; }
    .recruit-ref-head { padding:14px 0 0; border-bottom:1px solid #1f1a14; }
    .recruit-ref-chips { display:flex; flex-wrap:wrap; gap:6px; margin-bottom:8px; }
    .recruit-ref-chip { font-size:10px; padding:3px 8px; border-radius:999px; border:1px solid #2a2015; background:#111; color:#cfcfcf; }
    .recruit-ref-chip.gold { border-color:rgba(212,175,55,.4); color:#d4af37; background:rgba(212,175,55,.08); }
    .recruit-ref-title { margin:0 0 10px; font-size:1.6rem; color:#fff; }
    .recruit-ref-pay-grid { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:10px; }
    .recruit-ref-pay-card { background:#141210; border:1px solid #2a2015; border-radius:12px; padding:10px; }
    .recruit-ref-pay-card .label { font-size:10px; color:#9f9f9f; margin-bottom:4px; display:block; }
    .recruit-ref-pay-card .value { font-size:1.2rem; color:#fff; font-weight:700; }
    .recruit-ref-tags { display:flex; flex-wrap:wrap; gap:6px; margin-bottom:10px; }
    .recruit-ref-tags span { font-size:10px; padding:4px 8px; border-radius:999px; border:1px solid #2a2015; background:#0f0f0f; color:#d3d3d3; }
    .recruit-ref-tab { position:sticky; top:0; z-index:20; background:rgba(10,10,10,.94); backdrop-filter:blur(8px); border-bottom:1px solid #1f1a14; display:flex; }
    .recruit-ref-tab button { flex:1; border:none; background:transparent; color:#777; padding:12px 4px; font-size:11px; font-weight:700; cursor:pointer; }
    .recruit-ref-tab button.is-active { color:#d4af37; border-bottom:2px solid #d4af37; }
    .recruit-ref-section { padding:18px 0; }
    .recruit-ref-section-title { margin:0 0 12px; font-size:1rem; color:#fff; display:flex; align-items:center; gap:8px; }
</style>
@endpush

@section('content')
<div class="recruit-detail-page animate-fadeIn recruit-ref-wrap">
    @php
        $types = [];
        if (($recruit['hourly_wage_regular'] ?? 0) > 0) $types[] = '本入';
        if (!empty($recruit['trial_hourly_wage'])) $types[] = '体入';
        if (!empty($recruit['help_hourly_wage'])) $types[] = 'ヘルプ';
        $allTags = collect($recruit['store_features'] ?? [])->flatten()->filter()->unique()->values();
        $topTags = $allTags->take(6);
    @endphp
    @if(empty($forCast))
        <div class="recruit-ref-preview-bar">
            <p>店舗用プレビュー（キャスト側視点）</p>
            <a href="{{ route('shop.recruits.edit') }}">編集に戻る</a>
        </div>
    @endif

    <div class="recruit-ref-hero" id="top">
        <div class="recruit-ref-float-actions">
            <a href="javascript:history.back()" class="recruit-ref-float-btn" aria-label="戻る"><i class="fas fa-chevron-left"></i></a>
            @if(!empty($shareUrl))
                <button type="button" class="recruit-ref-float-btn" id="recruit-share-top-btn" aria-label="共有"><i class="fas fa-share-nodes"></i></button>
            @else
                <span></span>
            @endif
        </div>
        @if(!empty($shop['main_img'] ?? null))
            <img src="{{ $shop['main_img'] }}" alt="{{ $recruit['store_name'] ?? ($shop['name'] ?? '') }}" class="js-lightbox-target">
        @elseif(!empty($recruit['hero_image']))
            <img src="{{ $recruit['hero_image'] }}" alt="{{ $recruit['store_name'] ?? '' }}" class="js-lightbox-target">
        @else
            <div style="width:100%;height:100%;background:linear-gradient(135deg,#1a0c0e 0%,#2d1518 50%,#120405 100%);"></div>
        @endif
        <div class="recruit-ref-hero-overlay"></div>
        @if(!empty($shop['sub_images'] ?? []))
            <div class="recruit-ref-thumbs">
                @foreach(collect($shop['sub_images'])->take(3) as $img)
                    <img src="{{ $img }}" alt="thumb">
                @endforeach
            </div>
        @endif
    </div>

    <div class="recruit-ref-head">
        <div class="recruit-ref-chips">
            @if(!empty($shop['area'] ?? null))
                <span class="recruit-ref-chip">{{ $shop['area'] }}</span>
            @endif
            @if(!empty($recruit['store_genre'] ?? null))
                <span class="recruit-ref-chip gold">{{ $recruit['store_genre'] }}</span>
            @endif
        </div>
        <h1 class="recruit-ref-title">{{ $recruit['store_name'] ?? '—' }}</h1>
        <div class="recruit-ref-pay-grid">
            @if(($recruit['hourly_wage_regular'] ?? 0) > 0)
                <div class="recruit-ref-pay-card"><span class="label">入店時給</span><span class="value">¥{{ number_format($recruit['hourly_wage_regular']) }}</span></div>
            @endif
            @if(!empty($recruit['trial_hourly_wage']))
                <div class="recruit-ref-pay-card"><span class="label">体験時給</span><span class="value">¥{{ number_format($recruit['trial_hourly_wage']) }}</span></div>
            @endif
        </div>
        @if($topTags->isNotEmpty())
            <div class="recruit-ref-tags">
                @foreach($topTags as $tag)
                    <span>#{{ $tag }}</span>
                @endforeach
                @if($allTags->count() > $topTags->count())
                    <span>+他{{ $allTags->count() - $topTags->count() }}件</span>
                @endif
            </div>
        @endif
    </div>

    <div class="recruit-ref-tab" id="recruit-ref-tab">
        <button type="button" data-tab-target="top" class="is-active">トップ</button>
        <button type="button" data-tab-target="salary">給与・バック</button>
        <button type="button" data-tab-target="info">店舗情報</button>
        <button type="button" data-tab-target="access">アクセス</button>
    </div>

    <section class="recruit-ref-section">
        @if(!empty($recruit['message']) || !empty($recruit['job_content']))
            <h2 class="recruit-ref-section-title"><i class="fas fa-comment-dots"></i> お店からのメッセージ</h2>
            <div class="recruit-message-block-new">
                @if(!empty($recruit['message']))<p>{!! nl2br(e($recruit['message'])) !!}</p>@endif
                @if(!empty($recruit['job_content']))<p>{{ $recruit['job_content'] }}</p>@endif
            </div>
        @endif
    </section>

    <section class="recruit-ref-section" id="salary">
        <h2 class="recruit-ref-section-title"><i class="fas fa-wallet"></i> 給与・バック</h2>
        <div class="recruit-table-wrap">
            <table class="recruit-table">
                <tbody>
                    @if(($recruit['hourly_wage_regular'] ?? 0) > 0)<tr><th>入店時給</th><td>{{ number_format($recruit['hourly_wage_regular']) }}円〜</td></tr>@endif
                    @if(!empty($recruit['trial_hourly_wage']))<tr><th>体入時給</th><td>{{ number_format($recruit['trial_hourly_wage']) }}円〜</td></tr>@endif
                    @if(!empty($recruit['help_hourly_wage']))<tr><th>ヘルプ時給</th><td>{{ number_format($recruit['help_hourly_wage']) }}円〜</td></tr>@endif
                    @if(!empty($recruit['noruma_reward']))<tr><th>ボーナス金</th><td>{{ number_format($recruit['noruma_reward']) }}円</td></tr>@endif
                    @if(!empty($recruit['salary_text']))<tr><th>給与システム</th><td>{!! nl2br(e($recruit['salary_text'])) !!}</td></tr>@endif
                </tbody>
            </table>
        </div>
    </section>

    @if(!empty($shareUrl))
        @include('common.share-actions', [
            'shareUrl' => $shareUrl,
            'shareTitle' => $shareTitle ?? (($recruit['store_name'] ?? ($shop['name'] ?? '店舗')) . 'の求人情報'),
            'shareText' => $shareText ?? ($recruit['message'] ?? ''),
            'shareLabel' => 'この求人票をSNSで共有'
        ])
    @endif

    <section class="recruit-ref-section" id="info">
        <h2 class="recruit-ref-section-title"><i class="fas fa-building"></i> 店舗情報</h2>
        <div class="recruit-table-wrap">
            <table class="recruit-table"><tbody>
                @if(!empty($recruit['store_name']))<tr><th>店名</th><td>{{ $recruit['store_name'] }}</td></tr>@endif
                @if(!empty($recruit['store_genre']))<tr><th>業種</th><td>{{ $recruit['store_genre'] }}</td></tr>@endif
                @if(!empty($recruit['working_hours']))<tr><th>営業時間</th><td>{{ $recruit['working_hours'] }}</td></tr>@endif
                @if(!empty($recruit['working_days']))<tr><th>シフト</th><td>{{ $recruit['working_days'] }}</td></tr>@endif
                @if(!empty($recruit['regular_holiday']))<tr><th>定休日</th><td>{{ $recruit['regular_holiday'] }}</td></tr>@endif
                @if(!empty($recruit['qualification']))<tr><th>資格</th><td>{{ $recruit['qualification'] }}</td></tr>@endif
            </tbody></table>
        </div>
    </section>

    <section class="recruit-ref-section" id="access">
        <h2 class="recruit-ref-section-title"><i class="fas fa-map-marked-alt"></i> 交通アクセス</h2>
        @if(!empty($recruit['address']) || !empty($recruit['nearest_station']))
            <div class="recruit-location-block">
                @if(!empty($recruit['address']))<p class="recruit-location-address">{{ $recruit['address'] }}</p>@endif
                @if(!empty($recruit['nearest_station']))<p class="recruit-location-station"><i class="fas fa-train-subway"></i> {{ $recruit['nearest_station'] }}</p>@endif
                @if(!empty($recruit['address']))
                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($recruit['address']) }}" target="_blank" rel="noopener noreferrer" class="recruit-btn recruit-btn-preview">マップアプリで開く</a>
                @endif
            </div>
        @endif
    </section>

    @if(!empty($recruit['selected_benefits']) && is_array($recruit['selected_benefits']))
        <section class="recruit-ref-section">
            <h2 class="recruit-ref-section-title"><i class="fas fa-check-circle"></i> こんな条件で働けます</h2>
            <div class="recruit-info-pill-grid">
                @foreach($recruit['selected_benefits'] as $benefit)
                    <span class="recruit-info-pill"><i class="fas fa-check"></i> {{ $benefit }}</span>
                @endforeach
            </div>
        </section>
    @endif

    @if(empty($forCast))
        <div class="mt-8 text-center">
            <div class="recruit-preview-toolbar" style="justify-content:center;">
                <div class="recruit-preview-toolbar-actions" style="justify-content:center;">
                    <a href="{{ route('shop.recruits.edit') }}" class="recruit-ghost-btn">
                        <i class="fas fa-pen"></i> この内容を編集
                    </a>
                </div>
            </div>
        </div>
    @endif

    @if(!empty($forCast))
        {{-- キャスト向け：固定フッターで応募 --}}
        <div class="recruit-footer-cta">
            <button type="button" class="recruit-cta-heart" aria-label="キープ"><i class="far fa-heart"></i></button>
            <a href="#" class="recruit-cta-btn"><i class="fas fa-paper-plane"></i> 応募する</a>
        </div>
    @endif
</div>
{{-- 画像フルスクリーン用ライトボックス（profile と共通ID） --}}
<div id="lightbox-overlay" class="lightbox-overlay" onclick="closeLightbox(event)">
    <img id="lightbox-image" src="" alt="" class="lightbox-image">
    <button type="button" class="lightbox-close" aria-label="閉じる" onclick="closeLightbox(event)">
        <i class="fas fa-times"></i>
    </button>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var tabButtons = document.querySelectorAll('[data-tab-target]');
    tabButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var targetId = btn.getAttribute('data-tab-target');
            var target = document.getElementById(targetId);
            if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            tabButtons.forEach(function(b) { b.classList.remove('is-active'); });
            btn.classList.add('is-active');
        });
    });

    var topShareBtn = document.getElementById('recruit-share-top-btn');
    if (topShareBtn) {
        topShareBtn.addEventListener('click', function() {
            var trigger = document.querySelector('[data-share-trigger], .share-actions button, .share-actions a');
            if (trigger) trigger.click();
            else if (navigator.share && @json(!empty($shareUrl))) navigator.share({ url: @json($shareUrl) });
        });
    }

    var targets = document.querySelectorAll('.js-lightbox-target');
    var overlay = document.getElementById('lightbox-overlay');
    var img = document.getElementById('lightbox-image');
    if (!overlay || !img || targets.length === 0) return;

    targets.forEach(function (el) {
        el.style.cursor = 'zoom-in';
        el.addEventListener('click', function () {
            img.src = el.currentSrc || el.src;
            overlay.classList.add('is-open');
        });
    });
});

function closeLightbox(e) {
    if (e) {
        // 背景クリックか閉じるボタンのみで閉じる
        if (e.target && !e.target.classList.contains('lightbox-overlay') && !e.target.closest('.lightbox-close')) {
            return;
        }
        e.stopPropagation();
    }
    var overlay = document.getElementById('lightbox-overlay');
    var img = document.getElementById('lightbox-image');
    if (!overlay) return;
    overlay.classList.remove('is-open');
    if (img) img.src = '';
}
</script>
@endpush
