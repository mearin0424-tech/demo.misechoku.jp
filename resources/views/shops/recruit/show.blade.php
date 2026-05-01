@extends('layouts.app')

@section('title', ($recruit['store_name'] ?? ($shop['name'] ?? '店舗')) . 'の求人情報')
@section('meta_description', trim((string) (($recruit['catch_copy'] ?? '') ?: ($recruit['message'] ?? 'ミセチョクの求人情報です。'))))
@section('meta_image', $shop['main_img'] ?? ($recruit['hero_image'] ?? asset('assets/images/common/no-image.png')))
@section('canonical', $shareUrl ?? url()->current())
@section('guide_message', empty($forCast) ? '表示の見え方をご確認いただきながら、時給・勤務条件・メッセージが適切に伝わっているかご確認ください。気になる点がございましたら、そのまま編集画面へお戻りいただけます。' : '')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/recruitment.css') }}">
@endpush

@section('content')
<div class="recruit-detail-page animate-fadeIn">
    @if(empty($forCast))
        <div class="recruit-preview-toolbar">
            <div>
                <p class="recruit-preview-toolbar-title">店舗側プレビュー</p>
                <p class="recruit-preview-toolbar-text">キャストからどう見えるかを確認できます。必要があればそのまま編集へ戻れます。</p>
            </div>
            <div class="recruit-preview-toolbar-actions">
                <a href="{{ route('shop.recruits.edit') }}" class="recruit-ghost-btn">
                    <i class="fas fa-pen"></i> 編集する
                </a>
            </div>
        </div>
    @endif

    {{-- ヒーロー（画像 or グラデ＋店名・所在地） --}}
    <div class="recruit-hero-wrap">
        @if(!empty($shop['main_img'] ?? null))
            <img src="{{ $shop['main_img'] }}" alt="{{ $recruit['store_name'] ?? ($shop['name'] ?? '') }}" class="js-lightbox-target">
        @elseif(!empty($recruit['hero_image']))
            <img src="{{ $recruit['hero_image'] }}" alt="{{ $recruit['store_name'] ?? '' }}" class="js-lightbox-target">
        @else
            <div style="width:100%;height:100%;background:linear-gradient(135deg, #1a0c0e 0%, #2d1518 50%, #120405 100%);"></div>
        @endif
        <div class="recruit-hero-overlay"></div>
        <div class="recruit-hero-body">
            @if(!empty($recruit['open_date']))
                <span class="recruit-hero-badge">オープン {{ $recruit['open_date'] }}</span>
            @else
                <span class="recruit-hero-badge">{{ $recruit['store_genre'] ?? '求人' }}</span>
            @endif
            <h1 class="recruit-hero-title">{{ $recruit['store_name'] ?? '—' }}</h1>
            <p class="recruit-hero-location">
                <i class="fas fa-map-marker-alt"></i>
                <span>{{ $recruit['address'] ?? $recruit['nearest_station'] ?? '—' }}</span>
            </p>
        </div>
    </div>

    <section class="recruit-preview-summary">
        <div class="recruit-preview-summary-item">
            <span class="recruit-preview-summary-label">勤務形態</span>
            <span class="recruit-preview-summary-value">
                @php
                    $types = [];
                    if (($recruit['hourly_wage_regular'] ?? 0) > 0) {
                        $types[] = '本入';
                    }
                    if (!empty($recruit['trial_hourly_wage'])) {
                        $types[] = '体入';
                    }
                    if (!empty($recruit['help_hourly_wage'])) {
                        $types[] = 'ヘルプ';
                    }
                @endphp
                {{ $types ? implode(' / ', $types) : '未設定' }}
            </span>
        </div>
        <div class="recruit-preview-summary-item">
            <span class="recruit-preview-summary-label">最低時給</span>
            <span class="recruit-preview-summary-value">
                @php
                    $wages = [];
                    if (($recruit['hourly_wage_regular'] ?? 0) > 0) {
                        $wages[] = '本入: ¥' . number_format($recruit['hourly_wage_regular']);
                    }
                    if (!empty($recruit['trial_hourly_wage'])) {
                        $wages[] = '体入: ¥' . number_format($recruit['trial_hourly_wage']);
                    }
                    if (!empty($recruit['help_hourly_wage'])) {
                        $wages[] = 'ヘルプ: ¥' . number_format($recruit['help_hourly_wage']);
                    }
                @endphp
                {{ $wages ? implode(' / ', $wages) : '未設定' }}
            </span>
        </div>
        <div class="recruit-preview-summary-item">
            <span class="recruit-preview-summary-label">ボーナス金</span>
            <span class="recruit-preview-summary-value">
                {{ ($recruit['noruma_reward'] ?? 0) > 0 ? '¥' . number_format($recruit['noruma_reward']) : 'なし／未設定' }}
            </span>
        </div>
        <div class="recruit-preview-summary-item">
            <span class="recruit-preview-summary-label">応募資格</span>
            <span class="recruit-preview-summary-value">{{ $recruit['qualification'] ?: '未設定' }}</span>
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

    {{-- お店のギャラリー（プロフィールと連携） --}}
    @if(!empty($shop ?? null))
        <section class="recruit-shop-gallery-section">
            <div class="recruit-shop-gallery-head">
                <span class="label">SHOP GALLERY</span>
                <h2 class="title serif-font">{{ $shop['name'] ?? ($recruit['store_name'] ?? '') }}</h2>
                @if(!empty($shop['area'] ?? null))
                    <p class="area">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>{{ $shop['area'] }}</span>
                    </p>
                @endif
            </div>
            @if(!empty($shop['sub_images'] ?? []))
                <div class="recruit-shop-gallery-scroll">
                    @foreach($shop['sub_images'] as $img)
                        <div class="recruit-shop-gallery-item">
                            <img src="{{ $img }}" alt="{{ $shop['name'] ?? '' }}" loading="lazy" class="js-lightbox-target">
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    @endif

    <div class="px-0">
        {{-- 給与ハイライト --}}
        <section class="recruit-salary-block recruit-detail-section">
            @if(!empty($recruit['catch_copy']))
                <p class="recruit-salary-catch">
                    {!! nl2br(e($recruit['catch_copy'])) !!}
                    @if(!empty($recruit['open_date']))
                        <br><span class="highlight">オープン日 {{ $recruit['open_date'] }}</span>
                    @endif
                </p>
            @endif
            <div class="recruit-salary-main">
                <span class="recruit-salary-label">基本時給</span>
                <div class="recruit-salary-amount">
                    <span class="currency">¥</span>
                    <span class="value">{{ number_format($recruit['hourly_wage_regular']) }}</span>
                    <span class="range">~</span>
                </div>
                <span class="recruit-salary-sub">＋ 各種バック</span>
                @if(!empty($recruit['trial_hourly_wage']))
                    <p class="text-sm mt-2" style="color:#A89090;">体験時給 ¥{{ number_format($recruit['trial_hourly_wage']) }}〜</p>
                @endif
                @if(!empty($recruit['help_hourly_wage']))
                    <p class="text-sm mt-1" style="color:#C3B0D8;">ヘルプ時給 ¥{{ number_format($recruit['help_hourly_wage']) }}〜</p>
                @endif
                @if(!empty($recruit['noruma_reward']))
                    <p class="text-sm mt-2" style="color:#F8E7B0;">ボーナス金: ¥{{ number_format($recruit['noruma_reward']) }}</p>
                @endif
            </div>
            @if(!empty($recruit['selected_benefits']) && is_array($recruit['selected_benefits']))
                <div class="recruit-feature-tags">
                    @foreach(array_slice($recruit['selected_benefits'], 0, 6) as $benefit)
                        <span class="recruit-feature-tag-new"><i class="fas fa-check-circle"></i> {{ $benefit }}</span>
                    @endforeach
                </div>
            @endif
        </section>

        @if(!empty($recruit['store_features']) && is_array($recruit['store_features']))
            <section class="recruit-detail-section">
                <h3 class="recruit-block-title"><i class="fas fa-tags"></i> タグ情報</h3>
                <p class="recruit-detail-note">募集条件やお店の特徴を、項目ごとに見比べやすく整理しています。</p>
                <div class="recruit-shop-info-grid">
                    @foreach($recruit['store_features'] as $label => $items)
                        @if(!empty($items))
                            <div class="recruit-shop-info-item recruit-shop-info-wide">
                                <div class="label">{{ $label }}</div>
                                <div class="value">{{ implode(' / ', $items) }}</div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </section>
        @endif

        @if(!empty($recruit['bonus_condition']) || !empty($recruit['noruma_reward']) || !empty($recruit['bonus_working_days']) || !empty($recruit['bonus_working_hours']))
            <section class="recruit-detail-section">
                <h3 class="recruit-block-title"><i class="fas fa-award"></i> ボーナス金達成条件</h3>
                <div class="recruit-message-block-new">
                    @if(!empty($recruit['noruma_reward']))
                        <p>達成ボーナス: ¥{{ number_format($recruit['noruma_reward']) }}</p>
                    @endif
                    @if(!empty($recruit['bonus_working_days']) || !empty($recruit['bonus_working_hours']) || !empty($recruit['bonus_condition']))
                        <ul class="recruit-line-list">
                            @if(!empty($recruit['bonus_working_days']))
                                <li>勤務日数: {{ $recruit['bonus_working_days'] }}</li>
                            @endif
                            @if(!empty($recruit['bonus_working_hours']))
                                <li>勤務時間: {{ $recruit['bonus_working_hours'] }}</li>
                            @endif
                            @if(!empty($recruit['bonus_condition']))
                                <li>その他条件:<br>{!! nl2br(e($recruit['bonus_condition'])) !!}</li>
                            @endif
                        </ul>
                    @else
                        <p>条件は店舗との合意内容に従います。</p>
                    @endif
                </div>
            </section>
        @endif

        {{-- お店からのメッセージ --}}
        @if(!empty($recruit['message']) || !empty($recruit['job_content']))
            <section class="recruit-message-block-new recruit-detail-section">
                <h3 class="recruit-block-title"><i class="fas fa-sparkles"></i> お店からのメッセージ</h3>
                <div class="recruit-message-block-new">
                    @if(!empty($recruit['message']))
                        <p>{!! nl2br(e($recruit['message'])) !!}</p>
                    @endif
                    @if(!empty($recruit['job_content']))
                        <p>{{ $recruit['job_content'] }}</p>
                    @endif
                </div>
            </section>
        @endif

        {{-- 募集要項 --}}
        <section class="recruit-detail-section">
            <h3 class="recruit-block-title"><i class="fas fa-file-alt"></i> 募集要項</h3>
            <p class="recruit-detail-note">応募前に気になる基本条件を、読み飛ばしにくい形でまとめています。</p>
            <div class="recruit-table-wrap">
                <table class="recruit-table">
                    <tbody>
                        <tr>
                            <th>職種</th>
                            <td>フロアレディ <span class="sub">（キャバクラ・クラブ）</span></td>
                        </tr>
                        <tr>
                            <th>資格</th>
                            <td>{{ $recruit['qualification'] ?? '18歳以上（高校生不可）' }}<span class="sub">※未経験者大歓迎</span></td>
                        </tr>
                        <tr>
                            <th>営業時間</th>
                            <td>{{ $recruit['working_hours'] ?? '—' }}<span class="sub">（週1日・1日3h〜OK）</span></td>
                        </tr>
                        <tr>
                            <th>待遇</th>
                            <td>
                                @if(!empty($recruit['selected_benefits']) && is_array($recruit['selected_benefits']))
                                    <ul class="recruit-line-list">
                                        @foreach($recruit['selected_benefits'] as $b)
                                            <li>{{ $b }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    {{ $recruit['salary_text'] ?? '—' }}
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        {{-- 勤務時間・勤務日 --}}
        <div class="recruit-grid-2 recruit-detail-section">
            <div class="recruit-mini-card">
                <div class="label"><i class="fas fa-clock"></i> 勤務時間</div>
                <div class="value">{{ $recruit['working_hours'] ?? '—' }}</div>
            </div>
            <div class="recruit-mini-card">
                <div class="label"><i class="fas fa-calendar-day"></i> 勤務日</div>
                <div class="value">{{ $recruit['working_days'] ?? '—' }}<br><span class="text-sm" style="color:#A89090;">{{ $recruit['regular_holiday'] ?? '' }}</span></div>
            </div>
        </div>

        {{-- 勤務地 --}}
        <section class="recruit-location-block recruit-detail-section">
            <div class="label"><i class="fas fa-map-marker-alt"></i> 勤務地</div>
            <p class="recruit-location-address">{{ $recruit['address'] ?? '—' }}</p>
            @if(!empty($recruit['nearest_station']))
                <p class="recruit-location-station"><i class="fas fa-train-subway"></i> {{ $recruit['nearest_station'] }}</p>
            @endif
            @if(!empty($recruit['map_embed_src']))
                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($recruit['address'] ?? '') }}" target="_blank" rel="noopener noreferrer" class="recruit-map-link" style="display:inline-flex;align-items:center;gap:6px;font-size:0.8rem;color:var(--gold);margin-bottom:10px;">
                    <i class="fas fa-external-link-alt"></i> マップで開く
                </a>
                <div class="recruit-map-placeholder">
                    <iframe src="{{ $recruit['map_embed_src'] }}" style="position:absolute;inset:0;width:100%;height:100%;border:0;" allowfullscreen="" loading="lazy" title="勤務地の地図"></iframe>
                    <span class="pin"><i class="fas fa-map-marker-alt"></i></span>
                </div>
            @else
                <div class="recruit-map-placeholder">
                    <span class="pin"><i class="fas fa-map-marker-alt"></i></span>
                </div>
            @endif
        </section>

        @if(!empty($recruit['selected_benefits']) && is_array($recruit['selected_benefits']))
            <section class="recruit-detail-section">
                <h3 class="recruit-block-title"><i class="fas fa-circle-check"></i> こんな条件で働けます</h3>
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
    </div>

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
