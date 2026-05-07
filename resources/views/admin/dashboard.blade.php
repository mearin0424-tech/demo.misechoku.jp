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
                    $allValues[] = (float) ($row[$item['key']] ?? 0);
                }
            }
            // データ未投入や全件0の状態でもゼロ除算しないよう最小値1を保証
            $rawMax = !empty($allValues) ? max($allValues) : 0;
            $max = $rawMax > 0 ? $rawMax * 1.1 : 1;
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
            ['key' => 'cast', 'label' => 'キャスト', 'color' => '#2563eb'],
            ['key' => 'shop', 'label' => '店舗', 'color' => '#7c3aed'],
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
                                            <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="3" fill="#ffffff" stroke="{{ $series['color'] }}" stroke-width="1.5"></circle>
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
                                            <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="3" fill="#ffffff" stroke="#60A5FA" stroke-width="1.5"></circle>
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
                                            <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="3" fill="#ffffff" stroke="#34D399" stroke-width="1.5"></circle>
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

