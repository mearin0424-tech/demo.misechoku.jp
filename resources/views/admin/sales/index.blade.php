@extends('layouts.admin')

@section('title', '売上管理')
@section('admin_page_title', '売上管理')

@section('content')
@php
    // 月別グラフ（仲介料収益＋取引総額）SVG
    $chartWidth = 720;
    $chartHeight = 200;
    $padLeft = 56;
    $padRight = 24;
    $padTop = 20;
    $padBottom = 28;
    $months = $monthlyChart;
    $maxCommission = max(array_map(fn ($r) => (int) $r['commission'], $months) ?: [0]);
    $maxGmv = max(array_map(fn ($r) => (int) $r['gmv'], $months) ?: [0]);
    $maxScale = max($maxCommission, (int) round($maxGmv * 0.3)) ?: 1; // commissionを主軸
    $maxScale = $maxScale * 1.15;
    $count = max(count($months) - 1, 1);
    $plotLeft = $padLeft;
    $plotRight = $chartWidth - $padRight;
    $plotTop = $padTop;
    $plotBottom = $chartHeight - $padBottom;
    $plotWidth = $plotRight - $plotLeft;
    $plotHeight = $plotBottom - $plotTop;
    $getX = fn ($i) => $plotLeft + ($i / $count) * $plotWidth;
    $getYCommission = fn ($v) => $plotBottom - (($v / $maxScale) * $plotHeight);
    $getYGmv = fn ($v) => $plotBottom - ((($v / max($maxGmv, 1)) * 0.85) * $plotHeight);
    $commissionPoints = [];
    $gmvPoints = [];
    foreach ($months as $i => $row) {
        $commissionPoints[] = ['x' => $getX($i), 'y' => $getYCommission((int) $row['commission']), 'value' => (int) $row['commission']];
        $gmvPoints[] = ['x' => $getX($i), 'y' => $getYGmv((int) $row['gmv']), 'value' => (int) $row['gmv']];
    }
    $commissionPoly = implode(' ', array_map(fn ($p) => round($p['x'], 1) . ',' . round($p['y'], 1), $commissionPoints));
    $gmvPoly = implode(' ', array_map(fn ($p) => round($p['x'], 1) . ',' . round($p['y'], 1), $gmvPoints));
@endphp

<div class="admin-page sales-page">
    <div class="u-flex-between u-flex-wrap u-gap-12">
        @include('admin.parts.page-title', [
            'eyebrow' => 'SALES & REVENUE',
            'title' => '売上管理',
            'info' => '
                <p><strong>この画面の役割：</strong>運営の収益サイドを可視化します。</p>
                <ul>
                    <li>仲介料収益・取引総額（GMV）の<strong>推移と内訳</strong>を確認</li>
                    <li>サブスクリプション収益（連携後）の確認</li>
                </ul>
                <p>「今この瞬間の対応すべきタスク」は <a href="' . route('admin.dashboard') . '">ダッシュボード</a> をご確認ください。</p>
            ',
        ])
        @include('admin.parts.back-link', ['url' => route('admin.dashboard'), 'label' => 'ダッシュボードへ戻る'])
    </div>

    {{-- 期間切替 --}}
    <div class="sales-period-bar">
        <span class="sales-period-bar__label">期間：</span>
        <div class="sales-period-tabs" role="tablist">
            @foreach($periodOptions as $key => $label)
                <a href="{{ route('admin.sales.index', ['period' => $key]) }}"
                   class="sales-period-tab {{ $period === $key ? 'is-active' : '' }}"
                   role="tab" aria-selected="{{ $period === $key ? 'true' : 'false' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
        <span class="sales-period-bar__range">
            ({{ $periodStart->format('Y/n/j') }} 〜 {{ $periodEnd->format('Y/n/j') }})
        </span>
    </div>

    {{-- KPI カード --}}
    <div class="sales-kpi-grid">
        @foreach($kpis as $kpi)
            <article class="sales-kpi-card {{ !empty($kpi['is_primary']) ? 'is-primary' : '' }}">
                <div class="sales-kpi-card__head">
                    <span class="sales-kpi-card__icon"><i class="fas {{ $kpi['icon'] }}"></i></span>
                    <span class="sales-kpi-card__title">{{ $kpi['title'] }}</span>
                </div>
                <p class="sales-kpi-card__sub">{{ $kpi['subtitle'] }}</p>
                <p class="sales-kpi-card__value">
                    {{ $kpi['value'] }}<span class="sales-kpi-card__unit">{{ $kpi['unit'] }}</span>
                </p>
                <p class="sales-kpi-card__trend {{ $kpi['is_up'] ? 'is-up' : 'is-down' }}">
                    @if($kpi['trend_label'] !== '—')
                        <i class="fas {{ $kpi['is_up'] ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i>
                    @endif
                    {{ $kpi['trend_label'] }} <span>{{ $kpi['trend_caption'] }}</span>
                </p>
                <p class="sales-kpi-card__desc">{{ $kpi['description'] }}</p>
            </article>
        @endforeach
    </div>

    {{-- サブスクリプション情報（未連携の説明） --}}
    @if(!$subscriptionAvailable)
        <div class="admin-alert admin-alert-warning">
            <i class="fas fa-info-circle"></i>
            <strong>サブスクリプション収益</strong>は課金システム連携前のため未表示です。連携後にこの画面の指標として追加されます。
        </div>
    @endif

    {{-- 月別推移チャート --}}
    <section class="admin-panel">
        <div class="u-flex-between u-mb-12">
            <h2 class="admin-panel-title u-mb-0">月別推移（直近12ヶ月）</h2>
            <div class="sales-chart-legend">
                <span><i class="legend-dot" style="background:#dcb568;"></i> 仲介料収益（円）</span>
                <span><i class="legend-dot" style="background:#7f1d1d;"></i> 取引総額（円）</span>
            </div>
        </div>
        <div class="sales-chart-wrap">
            <svg viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}" preserveAspectRatio="none" class="sales-chart">
                <line x1="{{ $plotLeft }}" y1="{{ $plotTop }}" x2="{{ $plotLeft }}" y2="{{ $plotBottom }}" stroke="#e7d4d8" stroke-width="1"/>
                <line x1="{{ $plotLeft }}" y1="{{ $plotBottom }}" x2="{{ $plotRight }}" y2="{{ $plotBottom }}" stroke="#e7d4d8" stroke-width="1"/>

                {{-- グリッド線 --}}
                @for($g = 0; $g <= 4; $g++)
                    @php $gy = $plotTop + ($plotHeight * $g / 4); @endphp
                    <line x1="{{ $plotLeft }}" y1="{{ $gy }}" x2="{{ $plotRight }}" y2="{{ $gy }}" stroke="#f3e7ea" stroke-width="1" stroke-dasharray="3,4"/>
                @endfor

                {{-- 取引総額（折れ線） --}}
                <polyline points="{{ $gmvPoly }}" fill="none" stroke="#7f1d1d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" opacity="0.55"/>
                @foreach($gmvPoints as $p)
                    <circle cx="{{ round($p['x'], 1) }}" cy="{{ round($p['y'], 1) }}" r="2.5" fill="#7f1d1d" opacity="0.55"/>
                @endforeach

                {{-- 仲介料収益（折れ線・主） --}}
                <polyline points="{{ $commissionPoly }}" fill="none" stroke="#b8860b" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                @foreach($commissionPoints as $i => $p)
                    <circle cx="{{ round($p['x'], 1) }}" cy="{{ round($p['y'], 1) }}" r="3.5" fill="#dcb568" stroke="#fff" stroke-width="1.5"/>
                @endforeach

                {{-- 月ラベル --}}
                @foreach($months as $i => $row)
                    <text x="{{ round($getX($i), 1) }}" y="{{ $plotBottom + 18 }}" text-anchor="middle" font-size="10" fill="#6b4f55">{{ $row['month'] }}</text>
                @endforeach
            </svg>
        </div>

        {{-- 月別テーブル（小さめ） --}}
        <details class="sales-monthly-detail">
            <summary>月別の数値を表で確認</summary>
            <div class="table-wrapper u-mt-12">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>月</th>
                            <th>仲介料収益</th>
                            <th>取引総額（GMV）</th>
                            <th>件数</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($months as $row)
                            <tr>
                                <td>{{ $row['year_month'] }}</td>
                                <td>{{ number_format((int) $row['commission']) }} 円</td>
                                <td>{{ number_format((int) $row['gmv']) }} 円</td>
                                <td>{{ number_format((int) $row['count']) }} 件</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </details>
    </section>

    {{-- Top 店舗・キャスト --}}
    <div class="sales-top-grid">
        <section class="admin-panel">
            <h2 class="admin-panel-title">店舗別 仲介料貢献（{{ $periodLabel }}・上位{{ count($topShops) }}店舗）</h2>
            @if(empty($topShops))
                <p class="admin-note u-mb-0">対象期間に取引のある店舗はありません。</p>
            @else
                <div class="table-wrapper">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th class="u-w-60">#</th>
                                <th>店舗</th>
                                <th>件数</th>
                                <th>仲介料</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topShops as $i => $shop)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        <a href="{{ route('admin.shops.show', $shop['id']) }}">{{ $shop['name'] }}</a>
                                        <code class="u-text-muted u-fs-xs">{{ $shop['id'] }}</code>
                                    </td>
                                    <td>{{ number_format($shop['count']) }} 件</td>
                                    <td><strong>{{ number_format($shop['commission']) }}</strong> 円</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="admin-panel">
            <h2 class="admin-panel-title">キャスト別 仲介料貢献（{{ $periodLabel }}・上位{{ count($topCasts) }}名）</h2>
            @if(empty($topCasts))
                <p class="admin-note u-mb-0">対象期間に取引のあるキャストはいません。</p>
            @else
                <div class="table-wrapper">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th class="u-w-60">#</th>
                                <th>キャスト</th>
                                <th>件数</th>
                                <th>仲介料</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topCasts as $i => $cast)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        <a href="{{ route('admin.casts.show', $cast['id']) }}">{{ $cast['name'] }}</a>
                                        <code class="u-text-muted u-fs-xs">{{ $cast['id'] }}</code>
                                    </td>
                                    <td>{{ number_format($cast['count']) }} 件</td>
                                    <td><strong>{{ number_format($cast['commission']) }}</strong> 円</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
