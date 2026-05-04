@extends('layouts.admin')

@section('title', 'ダッシュボード')
@section('admin_page_title', 'ダッシュボード')

@section('content')
    @php
        $taskCount = count($tasks);
        $buildChart = function (array $data, array $series, int $width = 600, int $height = 180, int $padX = 20, int $padY = 20) {
            $allValues = [];
            foreach ($data as $row) {
                foreach ($series as $item) {
                    $allValues[] = $row[$item['key']];
                }
            }
            $max = max($allValues) * 1.1;
            $count = max(count($data) - 1, 1);
            $getX = fn ($index) => $padX + ($index / $count) * ($width - ($padX * 2));
            $getY = fn ($value) => $height - $padY - (($value / $max) * ($height - ($padY * 2)));
            $grid = [
                $padY,
                $padY + (($height - ($padY * 2)) * 0.5),
                $height - $padY,
            ];
            $seriesData = [];
            foreach ($series as $item) {
                $points = [];
                foreach ($data as $index => $row) {
                    $points[] = ['x' => $getX($index), 'y' => $getY($row[$item['key']]), 'value' => $row[$item['key']]];
                }
                $polyline = implode(' ', array_map(fn ($point) => round($point['x'], 2) . ',' . round($point['y'], 2), $points));
                $area = round($getX(0), 2) . ',' . ($height - $padY) . ' ' . $polyline . ' ' . round($getX(count($data) - 1), 2) . ',' . ($height - $padY);
                $seriesData[] = $item + ['points' => $points, 'polyline' => $polyline, 'area' => $area];
            }

            return ['width' => $width, 'height' => $height, 'padX' => $padX, 'padY' => $padY, 'grid' => $grid, 'series' => $seriesData];
        };
        $registrationChart = $buildChart($chartData, [
            ['key' => 'cast', 'label' => 'キャスト', 'color' => '#E6D080'],
            ['key' => 'shop', 'label' => '店舗', 'color' => '#A78BFA'],
        ]);
        $countChart = $buildChart($chartData, [
            ['key' => 'count', 'label' => '件数', 'color' => '#60A5FA'],
        ]);
        $amountChart = $buildChart($chartData, [
            ['key' => 'amount', 'label' => '金額', 'color' => '#34D399'],
        ]);
    @endphp

    <div class="dashboard-page">
        @if (session('status'))
            <div class="dashboard-alert">
                {{ session('status') }}
            </div>
        @endif

        <section class="dashboard-section">
            <div class="dashboard-section-head">
                <div class="dashboard-section-title-wrap">
                    <h2 class="dashboard-section-title">プラットフォーム分析</h2>
                    <div class="dashboard-date-chip">
                        <i class="fas fa-calendar"></i>
                        <span>今月 (Oct 2026)</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>
                <button type="button" class="dashboard-export-button">
                    <i class="fas fa-download"></i>
                    <span>エクスポート</span>
                </button>
            </div>

            <div class="bi-panel">
                <div class="bi-tabs">
                    <button type="button" class="bi-tab-button is-active" data-bi-tab="registration">登録状況</button>
                    <button type="button" class="bi-tab-button" data-bi-tab="transaction">取引・売上状況</button>
                </div>

                <div class="bi-tab-panel is-active" data-bi-panel="registration">
                    <div class="dashboard-kpi-grid">
                        @foreach ($registrationKpis as $kpi)
                            <article class="dashboard-kpi-card">
                                <div class="dashboard-kpi-head">
                                    <div class="dashboard-kpi-title">{{ $kpi['title'] }}</div>
                                    <i class="fas {{ $kpi['icon'] }}"></i>
                                </div>
                                <div class="dashboard-kpi-main">
                                    <span class="dashboard-kpi-value">{{ $kpi['value'] }}</span>
                                    <span class="dashboard-kpi-unit">{{ $kpi['unit'] }}</span>
                                    @if (!empty($kpi['sub_value']))
                                        <span class="dashboard-kpi-sub-value">({{ $kpi['sub_value'] }})</span>
                                        <span class="dashboard-kpi-unit">{{ $kpi['unit'] }}</span>
                                    @endif
                                </div>
                                <div class="dashboard-kpi-trend {{ $kpi['is_up'] ? 'is-up' : 'is-down' }}">
                                    <i class="fas fa-arrow-trend-up {{ $kpi['is_up'] ? '' : 'is-down' }}"></i>
                                    <span>{{ $kpi['trend'] }}</span>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="dashboard-chart-card">
                        <div class="dashboard-chart-head">
                            <h3>キャスト・店舗 登録推移</h3>
                            <div class="dashboard-chart-legend">
                                @foreach ($registrationChart['series'] as $series)
                                    <span><i style="background: {{ $series['color'] }}"></i>{{ $series['label'] }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="dashboard-chart-scroll">
                            <div class="dashboard-chart-canvas">
                                <svg viewBox="0 0 {{ $registrationChart['width'] }} {{ $registrationChart['height'] }}" preserveAspectRatio="none" class="dashboard-chart-svg">
                                    <defs>
                                        @foreach ($registrationChart['series'] as $series)
                                            <linearGradient id="grad-{{ $series['key'] }}" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="0%" stop-color="{{ $series['color'] }}" stop-opacity="0.4"></stop>
                                                <stop offset="100%" stop-color="{{ $series['color'] }}" stop-opacity="0"></stop>
                                            </linearGradient>
                                        @endforeach
                                    </defs>
                                    @foreach ($registrationChart['grid'] as $lineY)
                                        <line x1="{{ $registrationChart['padX'] }}" y1="{{ $lineY }}" x2="{{ $registrationChart['width'] - $registrationChart['padX'] }}" y2="{{ $lineY }}" class="dashboard-chart-grid"></line>
                                    @endforeach
                                    @foreach ($registrationChart['series'] as $series)
                                        <polygon points="{{ $series['area'] }}" fill="url(#grad-{{ $series['key'] }})"></polygon>
                                        <polyline points="{{ $series['polyline'] }}" fill="none" stroke="{{ $series['color'] }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></polyline>
                                        @foreach ($series['points'] as $point)
                                            <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="3" fill="#241116" stroke="{{ $series['color'] }}" stroke-width="1.5"></circle>
                                        @endforeach
                                    @endforeach
                                </svg>
                                <div class="dashboard-chart-labels">
                                    @foreach ($chartData as $point)
                                        <span>{{ $point['month'] }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bi-tab-panel" data-bi-panel="transaction">
                    <div class="dashboard-kpi-grid">
                        @foreach ($transactionKpis as $kpi)
                            <article class="dashboard-kpi-card">
                                <div class="dashboard-kpi-head">
                                    <div class="dashboard-kpi-title">{{ $kpi['title'] }}</div>
                                    <i class="fas {{ $kpi['icon'] }}"></i>
                                </div>
                                <div class="dashboard-kpi-main">
                                    <span class="dashboard-kpi-value">{{ $kpi['value'] }}</span>
                                    <span class="dashboard-kpi-unit">{{ $kpi['unit'] }}</span>
                                </div>
                                <div class="dashboard-kpi-trend {{ $kpi['is_up'] ? 'is-up' : 'is-down' }}">
                                    <i class="fas fa-arrow-trend-up {{ $kpi['is_up'] ? '' : 'is-down' }}"></i>
                                    <span>{{ $kpi['trend'] }}</span>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="dashboard-subchart-grid">
                        <div class="dashboard-chart-card">
                            <div class="dashboard-chart-head">
                                <h3>取引件数推移</h3>
                                <div class="dashboard-chart-legend"><span><i style="background: #60A5FA"></i>件数</span></div>
                            </div>
                            <div class="dashboard-chart-scroll">
                                <div class="dashboard-chart-canvas is-compact">
                                    <svg viewBox="0 0 {{ $countChart['width'] }} {{ $countChart['height'] }}" preserveAspectRatio="none" class="dashboard-chart-svg">
                                        <defs>
                                            <linearGradient id="grad-count" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="0%" stop-color="#60A5FA" stop-opacity="0.4"></stop>
                                                <stop offset="100%" stop-color="#60A5FA" stop-opacity="0"></stop>
                                            </linearGradient>
                                        </defs>
                                        @foreach ($countChart['grid'] as $lineY)
                                            <line x1="{{ $countChart['padX'] }}" y1="{{ $lineY }}" x2="{{ $countChart['width'] - $countChart['padX'] }}" y2="{{ $lineY }}" class="dashboard-chart-grid"></line>
                                        @endforeach
                                        <polygon points="{{ $countChart['series'][0]['area'] }}" fill="url(#grad-count)"></polygon>
                                        <polyline points="{{ $countChart['series'][0]['polyline'] }}" fill="none" stroke="#60A5FA" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></polyline>
                                        @foreach ($countChart['series'][0]['points'] as $point)
                                            <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="3" fill="#241116" stroke="#60A5FA" stroke-width="1.5"></circle>
                                        @endforeach
                                    </svg>
                                    <div class="dashboard-chart-labels">
                                        @foreach ($chartData as $point)
                                            <span>{{ $point['month'] }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="dashboard-chart-card">
                            <div class="dashboard-chart-head">
                                <h3>取引金額推移 (単位: M円)</h3>
                                <div class="dashboard-chart-legend"><span><i style="background: #34D399"></i>金額</span></div>
                            </div>
                            <div class="dashboard-chart-scroll">
                                <div class="dashboard-chart-canvas is-compact">
                                    <svg viewBox="0 0 {{ $amountChart['width'] }} {{ $amountChart['height'] }}" preserveAspectRatio="none" class="dashboard-chart-svg">
                                        <defs>
                                            <linearGradient id="grad-amount" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="0%" stop-color="#34D399" stop-opacity="0.4"></stop>
                                                <stop offset="100%" stop-color="#34D399" stop-opacity="0"></stop>
                                            </linearGradient>
                                        </defs>
                                        @foreach ($amountChart['grid'] as $lineY)
                                            <line x1="{{ $amountChart['padX'] }}" y1="{{ $lineY }}" x2="{{ $amountChart['width'] - $amountChart['padX'] }}" y2="{{ $lineY }}" class="dashboard-chart-grid"></line>
                                        @endforeach
                                        <polygon points="{{ $amountChart['series'][0]['area'] }}" fill="url(#grad-amount)"></polygon>
                                        <polyline points="{{ $amountChart['series'][0]['polyline'] }}" fill="none" stroke="#34D399" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></polyline>
                                        @foreach ($amountChart['series'][0]['points'] as $point)
                                            <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="3" fill="#241116" stroke="#34D399" stroke-width="1.5"></circle>
                                        @endforeach
                                    </svg>
                                    <div class="dashboard-chart-labels">
                                        @foreach ($chartData as $point)
                                            <span>{{ $point['month'] }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="dashboard-section">
            <div class="dashboard-section-head">
                <h2 class="dashboard-section-title">要対応タスク一覧</h2>
            </div>

            <div class="task-panel">
                <div class="task-summary-row">
                    <button type="button" class="task-filter-chip is-active" data-filter="all">
                        <span>すべて</span>
                        <strong>{{ $taskCount }}</strong>
                    </button>
                    @foreach ($taskSummary as $summary)
                        <button type="button" class="task-filter-chip" data-filter="{{ $summary['id'] }}">
                            <span>{{ $summary['title'] }}</span>
                            <strong>{{ $summary['count'] }}</strong>
                        </button>
                    @endforeach
                </div>

                <div class="task-table-wrap">
                    <table class="task-table">
                        <thead>
                            <tr>
                                <th>カテゴリ</th>
                                <th>対象</th>
                                <th>ステータス</th>
                                <th>金額</th>
                                <th>申請日時</th>
                                <th class="text-right">アクション</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tasks as $task)
                                <tr class="task-row" data-category="{{ $task['cat_id'] }}">
                                    <td data-label="カテゴリ">
                                        <span class="task-category-badge tone-{{ $task['cat_id'] }}">{{ $task['category'] }}</span>
                                    </td>
                                    <td data-label="対象">
                                        <div class="task-target">
                                            <div class="task-target-avatar">{{ $task['type'] === '店舗' ? '店' : 'C' }}</div>
                                            <div>
                                                <div class="task-target-name">{{ $task['target'] }}</div>
                                                <div class="task-target-meta">{{ $task['id'] }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td data-label="ステータス">
                                        <div class="task-status {{ $task['urgency'] === 'critical' ? 'is-critical' : ($task['urgency'] === 'high' ? 'is-high' : '') }}">
                                            <i class="fas {{ $task['urgency'] === 'critical' ? 'fa-circle-exclamation' : 'fa-clock' }}"></i>
                                            <span>{{ $task['status'] }}</span>
                                        </div>
                                    </td>
                                    <td data-label="金額" class="task-amount">{{ $task['amount'] ?: '-' }}</td>
                                    <td data-label="申請日時" class="task-date">{{ $task['date'] }}</td>
                                    <td data-label="アクション" class="text-right">
                                        @if(!empty($task['url']))
                                            <a href="{{ $task['url'] }}" class="task-action-button {{ $task['urgency'] === 'critical' ? 'is-critical' : '' }}">{{ $task['action'] }}</a>
                                        @else
                                            <button type="button" class="task-action-button {{ $task['urgency'] === 'critical' ? 'is-critical' : '' }}">{{ $task['action'] }}</button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            <tr id="task-empty-row" hidden>
                                <td colspan="6">
                                    <div class="task-empty">
                                        <i class="fas fa-circle-check"></i>
                                        <div>このカテゴリの対応タスクはありません</div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>

    <style>
        .dashboard-page {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        .dashboard-alert {
            padding: 12px 14px;
            border-radius: 14px;
            background: rgba(229, 193, 88, 0.1);
            border: 1px solid rgba(229, 193, 88, 0.28);
            box-shadow: var(--admin-shadow);
            color: var(--admin-text);
            font-size: 0.88rem;
        }
        .dashboard-section {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .dashboard-section-head {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .dashboard-section-title-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .dashboard-section-title {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
            font-family: "Noto Serif JP", "Yu Mincho", serif;
            color: var(--admin-accent);
            letter-spacing: 0.04em;
        }
        .dashboard-date-chip,
        .dashboard-export-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 34px;
            padding: 0 12px;
            border-radius: 10px;
            border: 1px solid rgba(229, 193, 88, 0.28);
            background: rgba(255, 255, 255, 0.05);
            box-shadow: var(--admin-shadow);
            color: var(--admin-sub);
            font-size: 0.72rem;
            font-weight: 700;
        }
        .dashboard-date-chip {
            color: var(--admin-gold);
        }
        .dashboard-export-button {
            cursor: pointer;
        }
        .dashboard-export-button:hover {
            color: var(--admin-gold);
            background: rgba(229, 193, 88, 0.12);
            border-color: rgba(229, 193, 88, 0.45);
        }
        .bi-panel,
        .task-panel {
            background: var(--admin-card);
            border: 1px solid rgba(229, 193, 88, 0.18);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: var(--admin-shadow-lg);
        }
        .bi-tabs {
            display: flex;
            border-bottom: 1px solid var(--admin-line-soft);
        }
        .bi-tab-button {
            border: 0;
            border-bottom: 2px solid transparent;
            background: transparent;
            color: var(--admin-muted);
            padding: 14px 16px;
            font-size: 0.82rem;
            font-weight: 700;
            cursor: pointer;
        }
        .bi-tab-button.is-active {
            border-bottom-color: var(--admin-gold);
            color: var(--admin-gold-strong);
        }
        .bi-tab-panel {
            display: none;
            padding: 16px;
        }
        .bi-tab-panel.is-active {
            display: block;
        }
        .dashboard-kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }
        .dashboard-kpi-card,
        .dashboard-chart-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(229, 193, 88, 0.12);
            border-radius: 16px;
            box-shadow: var(--admin-shadow);
        }
        .dashboard-kpi-card {
            padding: 14px;
            min-height: 112px;
        }
        .dashboard-kpi-head,
        .dashboard-chart-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .dashboard-kpi-head {
            margin-bottom: 8px;
        }
        .dashboard-kpi-head i {
            color: var(--admin-gold);
        }
        .dashboard-kpi-title {
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--admin-muted);
        }
        .dashboard-kpi-main {
            display: flex;
            align-items: baseline;
            gap: 4px;
            flex-wrap: wrap;
            margin-bottom: 8px;
        }
        .dashboard-kpi-value,
        .dashboard-kpi-sub-value {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--admin-text);
            letter-spacing: 0.02em;
        }
        .dashboard-kpi-sub-value {
            color: var(--admin-gold-strong);
        }
        .dashboard-kpi-unit {
            font-size: 0.62rem;
            font-weight: 700;
            color: var(--admin-muted);
        }
        .dashboard-kpi-trend {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.68rem;
            font-weight: 700;
        }
        .dashboard-kpi-trend.is-up {
            color: #34d399;
        }
        .dashboard-kpi-trend.is-down {
            color: #f87171;
        }
        .dashboard-kpi-trend .is-down {
            transform: rotate(180deg);
        }
        .dashboard-chart-card {
            padding: 16px;
        }
        .dashboard-chart-head h3 {
            margin: 0;
            font-size: 0.86rem;
            font-weight: 600;
            font-family: "Noto Serif JP", "Yu Mincho", serif;
            color: var(--admin-accent);
        }
        .dashboard-chart-legend {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            font-size: 0.66rem;
            font-weight: 700;
            color: var(--admin-sub);
        }
        .dashboard-chart-legend span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .dashboard-chart-legend i {
            width: 8px;
            height: 8px;
            border-radius: 2px;
            display: inline-block;
        }
        .dashboard-chart-scroll {
            overflow-x: auto;
            padding-top: 14px;
        }
        .dashboard-chart-canvas {
            min-width: 600px;
            position: relative;
            height: 210px;
        }
        .dashboard-chart-canvas.is-compact {
            min-width: 400px;
        }
        .dashboard-chart-svg {
            width: 100%;
            height: 180px;
            overflow: visible;
        }
        .dashboard-chart-grid {
            stroke: rgba(255, 255, 255, 0.1);
            stroke-dasharray: 3 3;
            stroke-width: 1;
        }
        .dashboard-chart-labels {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            justify-content: space-between;
            padding: 0 20px;
            font-size: 0.62rem;
            color: var(--admin-muted);
        }
        .dashboard-subchart-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }
        .task-summary-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 14px 16px;
            border-bottom: 1px solid var(--admin-line-soft);
            background: rgba(255, 255, 255, 0.04);
        }
        .task-filter-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 10px;
            border: 0;
            background: transparent;
            color: var(--admin-muted);
            font-size: 0.72rem;
            font-weight: 700;
            cursor: pointer;
        }
        .task-filter-chip strong {
            min-width: 20px;
            padding: 2px 6px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
            color: var(--admin-text);
            font-size: 0.62rem;
            text-align: center;
        }
        .task-filter-chip.is-active {
            color: var(--admin-gold);
            border: 1px solid rgba(229, 193, 88, 0.35);
            background: rgba(229, 193, 88, 0.1);
            box-shadow: var(--admin-shadow);
        }
        .task-filter-chip.is-active strong {
            background: linear-gradient(135deg, #e5c158, #b38a22);
            color: #190509;
        }
        .task-table-wrap {
            overflow-x: auto;
        }
        .task-table {
            width: 100%;
            min-width: 800px;
            border-collapse: collapse;
        }
        .task-table thead th {
            padding: 10px 16px;
            text-align: left;
            font-size: 0.64rem;
            font-weight: 700;
            color: var(--admin-muted);
            background: rgba(255, 255, 255, 0.04);
            border-bottom: 1px solid var(--admin-line-soft);
        }
        .task-table tbody td {
            padding: 12px 16px;
            border-top: 1px solid var(--admin-line-soft);
            vertical-align: middle;
            font-size: 0.78rem;
            color: var(--admin-text);
        }
        .task-row:hover {
            background: var(--admin-card-hover);
        }
        .task-category-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 999px;
            border: 1px solid transparent;
            font-size: 0.66rem;
            font-weight: 700;
        }
        .task-category-badge.tone-kyc {
            color: #60a5fa;
            border-color: rgba(96, 165, 250, 0.3);
        }
        .task-category-badge.tone-doc {
            color: #a78bfa;
            border-color: rgba(167, 139, 250, 0.3);
        }
        .task-category-badge.tone-deposit {
            color: #34d399;
            border-color: rgba(52, 211, 153, 0.3);
        }
        .task-category-badge.tone-transfer {
            color: #fbbf24;
            border-color: rgba(251, 191, 36, 0.3);
        }
        .task-category-badge.tone-error {
            color: #f87171;
            border-color: rgba(248, 113, 113, 0.3);
        }
        .task-target {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .task-target-avatar {
            width: 24px;
            height: 24px;
            border-radius: 8px;
            background: rgba(229, 193, 88, 0.16);
            color: var(--admin-gold);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            font-size: 0.65rem;
            font-weight: 700;
        }
        .task-target-name {
            margin-bottom: 2px;
            font-weight: 700;
        }
        .task-target-meta {
            font-size: 0.62rem;
            color: var(--admin-muted);
        }
        .task-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--admin-text);
        }
        .task-status.is-high {
            color: #fbbf24;
        }
        .task-status.is-critical {
            color: #f87171;
        }
        .task-amount {
            font-weight: 700;
            letter-spacing: 0.02em;
        }
        .task-date {
            color: var(--admin-sub);
            font-size: 0.72rem;
        }
        .text-right {
            text-align: right;
        }
        .task-action-button {
            padding: 8px 12px;
            border-radius: 10px;
            border: 1px solid rgba(229, 193, 88, 0.35);
            background: rgba(229, 193, 88, 0.12);
            color: var(--admin-gold);
            font-size: 0.72rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow: var(--admin-shadow);
        }
        .task-action-button:hover {
            background: linear-gradient(135deg, #e5c158, #b38a22);
            color: #190509;
            border-color: transparent;
        }
        .task-action-button.is-critical {
            border-color: rgba(248, 113, 113, 0.3);
            background: rgba(248, 113, 113, 0.1);
            color: #f87171;
        }
        .task-action-button.is-critical:hover {
            background: #f87171;
            color: #fff;
        }
        .task-empty {
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            color: var(--admin-sub);
            font-weight: 700;
        }
        .task-empty i {
            color: #34d399;
        }
        .task-row.is-hidden {
            display: none;
        }
        @media (max-width: 1023px) {
            .dashboard-kpi-grid,
            .dashboard-subchart-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 767px) {
            .dashboard-page {
                gap: 20px;
            }
            .dashboard-section-title-wrap,
            .dashboard-section-head {
                align-items: flex-start;
            }
            .bi-tab-panel {
                padding: 14px;
            }
            .dashboard-kpi-grid,
            .dashboard-subchart-grid {
                grid-template-columns: 1fr;
            }
            .dashboard-chart-card {
                padding: 14px;
            }
            .task-summary-row {
                padding: 12px;
            }
            .task-filter-chip {
                width: calc(50% - 4px);
                justify-content: space-between;
            }
            .task-table {
                min-width: 0;
            }
            .task-table thead {
                display: none;
            }
            .task-table tbody {
                display: block;
                padding: 12px;
            }
            .task-table tbody tr {
                display: block;
                margin-bottom: 12px;
                border: 0;
                border-radius: 14px;
                overflow: hidden;
                background: var(--admin-card);
                box-shadow: var(--admin-shadow);
            }
            .task-table tbody td {
                display: grid;
                grid-template-columns: 86px minmax(0, 1fr);
                gap: 8px;
                padding: 11px 12px;
                white-space: normal;
            }
            .task-table tbody td::before {
                content: attr(data-label);
                font-size: 0.64rem;
                font-weight: 700;
                color: var(--admin-muted);
            }
            .text-right {
                text-align: left;
            }
            .task-action-button {
                width: 100%;
            }
            #task-empty-row td {
                display: block;
                padding: 0;
            }
            #task-empty-row td::before {
                content: none;
            }
        }
        @media (max-width: 560px) {
            .dashboard-date-chip,
            .dashboard-export-button,
            .task-filter-chip {
                width: 100%;
            }
            .dashboard-chart-canvas,
            .dashboard-chart-canvas.is-compact {
                min-width: 360px;
            }
            .task-filter-chip {
                width: 100%;
            }
            .task-table tbody td {
                grid-template-columns: 1fr;
                gap: 5px;
            }
        }
    </style>
@endsection

@push('admin-scripts')
<script>
    (function () {
        var biButtons = document.querySelectorAll('[data-bi-tab]');
        var biPanels = document.querySelectorAll('[data-bi-panel]');
        var filterButtons = document.querySelectorAll('[data-filter]');
        var taskRows = document.querySelectorAll('.task-row');
        var emptyRow = document.getElementById('task-empty-row');

        biButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                var target = button.getAttribute('data-bi-tab');
                biButtons.forEach(function (item) {
                    item.classList.toggle('is-active', item === button);
                });
                biPanels.forEach(function (panel) {
                    panel.classList.toggle('is-active', panel.getAttribute('data-bi-panel') === target);
                });
            });
        });

        filterButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                var target = button.getAttribute('data-filter');
                var visibleCount = 0;

                filterButtons.forEach(function (item) {
                    item.classList.toggle('is-active', item === button);
                });

                taskRows.forEach(function (row) {
                    var matches = target === 'all' || row.getAttribute('data-category') === target;
                    row.classList.toggle('is-hidden', !matches);
                    if (matches) visibleCount += 1;
                });

                if (emptyRow) {
                    emptyRow.hidden = visibleCount !== 0;
                }
            });
        });
    })();
</script>
@endpush

