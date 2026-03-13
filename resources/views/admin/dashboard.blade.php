@extends('layouts.admin')

@section('title', 'ダッシュボード')
@section('admin_page_title', 'ダッシュボード')

@section('content')
    <div class="dashboard-page">
        @if(session('status'))
            <div class="dashboard-alert">
                {{ session('status') }}
            </div>
        @endif

        <section class="dashboard-section">
            <div class="dashboard-section-head">
                <h2 class="dashboard-section-title">プラットフォーム状況</h2>
                <div class="dashboard-updated-at">更新: 今日 10:45</div>
            </div>

            <div class="kpi-grid">
                @foreach ($kpis as $kpi)
                    <article class="kpi-card">
                        <div class="kpi-title">{{ $kpi['title'] }}</div>
                        <div class="kpi-main">
                            <span class="kpi-value">{{ $kpi['value'] }}</span>
                            <span class="kpi-unit">{{ $kpi['unit'] }}</span>
                        </div>
                        <div class="kpi-trend {{ $kpi['is_up'] ? 'is-up' : 'is-down' }}">
                            <i class="fas {{ $kpi['is_up'] ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i>
                            <span>{{ $kpi['trend'] }}</span>
                            <small>{{ $kpi['trend_label'] }}</small>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="dashboard-section">
            <div class="dashboard-section-head">
                <h2 class="dashboard-section-title">要対応タスク</h2>
            </div>

            <div class="task-summary-grid">
                <button type="button" class="task-summary-card is-active" data-filter="all">
                    <div class="task-summary-icon tone-neutral">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div class="task-summary-text">
                        <div class="task-summary-title">すべて表示</div>
                        <div class="task-summary-count">{{ count($tasks) }}件</div>
                    </div>
                </button>

                @foreach ($taskSummary as $summary)
                    <button type="button" class="task-summary-card" data-filter="{{ $summary['id'] }}">
                        <div class="task-summary-icon tone-{{ $summary['tone'] }}">
                            <i class="fas {{ $summary['icon'] }}"></i>
                        </div>
                        <div class="task-summary-text">
                            <div class="task-summary-title">{{ $summary['title'] }}</div>
                            <div class="task-summary-count">{{ $summary['count'] }}件</div>
                        </div>
                    </button>
                @endforeach
            </div>
        </section>

        <section class="dashboard-section">
            <div class="task-panel">
                <div class="task-panel-head">
                    <div class="task-filter-state">
                        <button type="button" class="task-filter-reset is-active" data-filter="all">すべて表示</button>
                        <div id="task-filter-indicator" class="task-filter-indicator" hidden></div>
                    </div>
                    <button type="button" class="task-sort-button">
                        <i class="fas fa-clock"></i>
                        <span>古い順</span>
                    </button>
                </div>

                <div class="task-table-wrap">
                    <table class="task-table">
                        <thead>
                            <tr>
                                <th>カテゴリ</th>
                                <th>対象</th>
                                <th>現在のステータス</th>
                                <th>金額</th>
                                <th>申請日時</th>
                                <th class="text-right">アクション</th>
                            </tr>
                        </thead>
                        <tbody id="task-table-body">
                            @foreach ($tasks as $task)
                                <tr class="task-row" data-category="{{ $task['cat_id'] }}">
                                    <td data-label="カテゴリ">
                                        <span class="task-category-badge tone-{{ $task['cat_id'] }}">
                                            {{ $task['category'] }}
                                        </span>
                                    </td>
                                    <td data-label="対象">
                                        <div class="task-target-name">{{ $task['target'] }}</div>
                                        <div class="task-target-meta">{{ $task['type'] }} ・ {{ $task['id'] }}</div>
                                    </td>
                                    <td data-label="ステータス">
                                        <div class="task-status {{ $task['urgency'] === 'critical' ? 'is-critical' : ($task['urgency'] === 'high' ? 'is-high' : '') }}">
                                            @if ($task['urgency'] === 'critical')
                                                <i class="fas fa-circle-exclamation"></i>
                                            @elseif ($task['urgency'] === 'high')
                                                <i class="fas fa-clock"></i>
                                            @endif
                                            <span>{{ $task['status'] }}</span>
                                        </div>
                                    </td>
                                    <td class="task-amount" data-label="金額">{{ $task['amount'] ?: '-' }}</td>
                                    <td class="task-date" data-label="申請日時">{{ $task['date'] }}</td>
                                    <td class="text-right" data-label="アクション">
                                        <button type="button" class="task-action-button {{ $task['urgency'] === 'critical' ? 'is-critical' : '' }}">
                                            <span>{{ $task['action'] }}</span>
                                            <i class="fas fa-arrow-right"></i>
                                        </button>
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
            width: 100%;
            max-width: 780px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 28px;
        }
        .dashboard-alert {
            padding: 12px 14px;
            border-radius: 14px;
            background: rgba(96, 165, 250, 0.08);
            border: 1px solid rgba(96, 165, 250, 0.24);
            color: #dbeafe;
            font-size: 0.9rem;
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
        .dashboard-section-title {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 700;
            color: #f3f4f6;
        }
        .dashboard-updated-at {
            padding: 8px 12px;
            border-radius: 10px;
            background: #1b1f26;
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: #aeb6c2;
            font-size: 0.78rem;
            font-weight: 600;
        }
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }
        .kpi-card {
            position: relative;
            overflow: hidden;
            padding: 20px;
            border-radius: 18px;
            background: linear-gradient(180deg, rgba(32, 36, 43, 0.98), rgba(24, 27, 33, 0.98));
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.22);
        }
        .kpi-card::after {
            content: "";
            position: absolute;
            top: -32px;
            right: -32px;
            width: 120px;
            height: 120px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(255,255,255,0.07) 0%, rgba(255,255,255,0) 70%);
        }
        .kpi-title {
            position: relative;
            z-index: 1;
            font-size: 0.88rem;
            font-weight: 700;
            color: #aeb6c2;
            margin-bottom: 12px;
        }
        .kpi-main {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: flex-end;
            gap: 6px;
            margin-bottom: 10px;
        }
        .kpi-value {
            font-size: 2rem;
            line-height: 1;
            font-weight: 800;
            letter-spacing: 0.02em;
        }
        .kpi-unit {
            font-size: 0.88rem;
            font-weight: 700;
            color: #aeb6c2;
            margin-bottom: 4px;
        }
        .kpi-trend {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.84rem;
            font-weight: 700;
        }
        .kpi-trend small {
            color: #8a92a0;
            font-size: 0.74rem;
            font-weight: 600;
        }
        .kpi-trend.is-up {
            color: #34d399;
        }
        .kpi-trend.is-down {
            color: #f87171;
        }
        .task-summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }
        .task-summary-card {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            padding: 16px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: #20242b;
            color: #f3f4f6;
            text-align: left;
            cursor: pointer;
            transition: transform 0.15s ease, border-color 0.15s ease, background 0.15s ease;
        }
        .task-summary-card:hover,
        .task-summary-card.is-active {
            transform: translateY(-1px);
            background: #262b33;
            border-color: rgba(255, 255, 255, 0.16);
        }
        .task-summary-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex: 0 0 auto;
        }
        .tone-neutral {
            background: rgba(255, 255, 255, 0.08);
            color: #d1d5db;
        }
        .tone-info {
            background: rgba(96, 165, 250, 0.12);
            color: #60a5fa;
        }
        .tone-purple {
            background: rgba(167, 139, 250, 0.12);
            color: #a78bfa;
        }
        .tone-success {
            background: rgba(52, 211, 153, 0.12);
            color: #34d399;
        }
        .tone-warning {
            background: rgba(251, 191, 36, 0.12);
            color: #fbbf24;
        }
        .tone-danger {
            background: rgba(248, 113, 113, 0.12);
            color: #f87171;
        }
        .task-summary-title {
            font-size: 0.8rem;
            font-weight: 700;
            color: #aeb6c2;
            margin-bottom: 4px;
        }
        .task-summary-count {
            font-size: 1.35rem;
            font-weight: 800;
            color: #f3f4f6;
        }
        .task-summary-text {
            min-width: 0;
        }
        .task-panel {
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: #20242b;
            overflow: hidden;
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.22);
        }
        .task-panel-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            padding: 16px 18px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(15, 17, 21, 0.4);
        }
        .task-filter-state {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .task-filter-reset,
        .task-sort-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 12px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: #171a20;
            color: #aeb6c2;
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
        }
        .task-filter-reset.is-active {
            background: #eef2f7;
            color: #111827;
            border-color: #eef2f7;
        }
        .task-filter-indicator {
            padding: 9px 12px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.08);
            color: #f3f4f6;
            font-size: 0.8rem;
            font-weight: 700;
        }
        .task-table-wrap {
            overflow-x: auto;
        }
        .task-table {
            width: 100%;
            min-width: 760px;
            border-collapse: collapse;
        }
        .task-table thead th {
            padding: 14px 18px;
            text-align: left;
            font-size: 0.74rem;
            font-weight: 700;
            color: #8a92a0;
            background: #171a20;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .task-table tbody td {
            padding: 18px;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            vertical-align: middle;
        }
        .task-row:hover {
            background: rgba(255, 255, 255, 0.02);
        }
        .task-category-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 700;
            border: 1px solid transparent;
        }
        .task-category-badge.tone-kyc {
            background: rgba(96, 165, 250, 0.12);
            color: #60a5fa;
            border-color: rgba(96, 165, 250, 0.22);
        }
        .task-category-badge.tone-doc {
            background: rgba(167, 139, 250, 0.12);
            color: #a78bfa;
            border-color: rgba(167, 139, 250, 0.22);
        }
        .task-category-badge.tone-deposit {
            background: rgba(52, 211, 153, 0.12);
            color: #34d399;
            border-color: rgba(52, 211, 153, 0.22);
        }
        .task-category-badge.tone-transfer {
            background: rgba(251, 191, 36, 0.12);
            color: #fbbf24;
            border-color: rgba(251, 191, 36, 0.22);
        }
        .task-category-badge.tone-error {
            background: rgba(248, 113, 113, 0.12);
            color: #f87171;
            border-color: rgba(248, 113, 113, 0.22);
        }
        .task-target-name {
            font-size: 0.92rem;
            font-weight: 700;
            color: #f3f4f6;
            margin-bottom: 4px;
        }
        .task-target-meta {
            font-size: 0.74rem;
            color: #8a92a0;
        }
        .task-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.84rem;
            font-weight: 700;
            color: #f3f4f6;
        }
        .task-status.is-high {
            color: #fbbf24;
        }
        .task-status.is-critical {
            color: #f87171;
        }
        .task-amount {
            font-size: 0.88rem;
            font-weight: 700;
            color: #f3f4f6;
        }
        .task-date {
            font-size: 0.78rem;
            font-weight: 600;
            color: #aeb6c2;
        }
        .text-right {
            text-align: right;
        }
        .task-action-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 13px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.14);
            background: #171a20;
            color: #d7dde7;
            font-size: 0.78rem;
            font-weight: 700;
            cursor: pointer;
        }
        .task-action-button:hover {
            background: #2a3038;
        }
        .task-action-button.is-critical {
            color: #f87171;
            border-color: rgba(248, 113, 113, 0.28);
            background: rgba(248, 113, 113, 0.08);
        }
        .task-empty {
            padding: 44px 20px;
            color: #aeb6c2;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            font-weight: 700;
        }
        .task-empty i {
            font-size: 1.9rem;
            color: #34d399;
            opacity: 0.65;
        }
        .task-row.is-hidden {
            display: none;
        }
        @media (max-width: 1023px) {
            .task-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .kpi-grid {
                grid-template-columns: repeat(1, minmax(0, 1fr));
            }
        }
        @media (max-width: 767px) {
            .dashboard-page {
                gap: 20px;
            }
            .dashboard-section-head {
                align-items: flex-start;
            }
            .dashboard-updated-at {
                width: 100%;
            }
            .kpi-card {
                padding: 16px;
                border-radius: 16px;
            }
            .kpi-title {
                margin-bottom: 10px;
            }
            .kpi-value {
                font-size: 1.7rem;
            }
            .task-summary-card {
                padding: 14px;
                border-radius: 14px;
            }
            .task-summary-grid {
                grid-template-columns: repeat(1, minmax(0, 1fr));
            }
            .task-panel-head {
                flex-direction: column;
                align-items: stretch;
                padding: 14px;
            }
            .task-filter-state {
                flex-direction: column;
                align-items: stretch;
            }
            .task-filter-reset,
            .task-sort-button,
            .task-filter-indicator {
                width: 100%;
                justify-content: center;
            }
            .task-table {
                min-width: 0;
            }
            .task-table-wrap {
                overflow: visible;
                padding: 12px;
            }
            .task-table thead {
                display: none;
            }
            .task-table tbody {
                display: block;
            }
            .task-table tbody tr {
                display: block;
                margin-bottom: 12px;
                border-radius: 16px;
                border: 1px solid rgba(255, 255, 255, 0.08);
                background: #171a20;
                overflow: hidden;
            }
            .task-table tbody tr:last-child {
                margin-bottom: 0;
            }
            .task-table tbody td {
                display: grid;
                grid-template-columns: 82px minmax(0, 1fr);
                gap: 10px;
                padding: 12px 14px;
            }
            .task-table tbody td::before {
                content: attr(data-label);
                font-size: 0.72rem;
                font-weight: 700;
                color: #8a92a0;
            }
            .task-table tbody td:first-child {
                border-top: 0;
            }
            .text-right {
                text-align: left;
            }
            .task-action-button {
                width: 100%;
                justify-content: center;
            }
            #task-empty-row td {
                display: block;
                padding: 0;
                border-top: 0;
            }
            #task-empty-row td::before {
                content: none;
            }
        }
        @media (max-width: 560px) {
            .dashboard-page {
                max-width: 100%;
            }
            .dashboard-section-title {
                font-size: 1.05rem;
            }
            .task-table-wrap {
                padding: 10px;
            }
            .task-table tbody td {
                grid-template-columns: 1fr;
                gap: 6px;
                padding: 11px 12px;
            }
            .task-table tbody td::before {
                font-size: 0.68rem;
            }
        }
    </style>
@endsection

@push('admin-scripts')
<script>
    (function () {
        var filterButtons = document.querySelectorAll('[data-filter]');
        var rows = document.querySelectorAll('.task-row');
        var emptyRow = document.getElementById('task-empty-row');
        var indicator = document.getElementById('task-filter-indicator');
        var titles = {
            all: 'すべて表示',
            kyc: '本人確認待ち',
            doc: '書類審査待ち',
            deposit: '店舗入金確認',
            transfer: 'キャスト振込',
            error: '振込エラー'
        };

        function applyFilter(target) {
            var visibleCount = 0;

            filterButtons.forEach(function (button) {
                button.classList.toggle('is-active', button.getAttribute('data-filter') === target);
            });

            rows.forEach(function (row) {
                var matches = target === 'all' || row.getAttribute('data-category') === target;
                row.classList.toggle('is-hidden', !matches);
                if (matches) visibleCount += 1;
            });

            if (emptyRow) {
                emptyRow.hidden = visibleCount !== 0;
            }

            if (!indicator) return;

            if (target === 'all') {
                indicator.hidden = true;
                indicator.textContent = '';
                return;
            }

            indicator.hidden = false;
            indicator.textContent = (titles[target] || target) + ' で絞り込み中';
        }

        filterButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                applyFilter(button.getAttribute('data-filter'));
            });
        });
    })();
</script>
@endpush

