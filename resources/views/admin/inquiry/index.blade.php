@extends('layouts.admin')

@section('title', '問合せ管理')

@section('content')
    @php
        $statusBadge = function (string $tone): string {
            return match ($tone) {
                'pending' => 'is-danger',
                'in_progress' => 'is-warning',
                'resolved' => 'is-success',
                'closed' => 'is-inactive',
                default => '',
            };
        };
        // アクター判定: 未対応・対応中=運営対応 / 対応済・クローズ=完了
        $resolveActor = function (string $tone): array {
            return match ($tone) {
                'pending', 'in_progress' => ['cls' => 'is-admin', 'icon' => 'fa-bell', 'label' => '運営対応'],
                'resolved', 'closed' => ['cls' => 'is-done', 'icon' => 'fa-circle-check', 'label' => '完了'],
                default => ['cls' => 'is-admin-soft', 'icon' => 'fa-circle-question', 'label' => '—'],
            };
        };
        $filterChips = [
            ['key' => 'all', 'label' => 'すべて'],
            ['key' => 'pending', 'label' => '未対応'],
            ['key' => 'in_progress', 'label' => '対応中'],
            ['key' => 'resolved', 'label' => '対応済'],
            ['key' => 'closed', 'label' => 'クローズ'],
        ];
    @endphp

    @php
        // 古い未対応の集計（3日以上 = warning, 7日以上 = critical）
        $oldPendingCount = 0;
        $criticalPendingCount = 0;
        foreach ($inquiries as $inq) {
            if (in_array($inq['status_tone'], ['pending', 'in_progress'], true)) {
                $days = (int) $inq['created_at']->diffInDays(now());
                if ($days >= 7) {
                    $criticalPendingCount++;
                } elseif ($days >= 3) {
                    $oldPendingCount++;
                }
            }
        }
    @endphp

    <div class="admin-page">
        <div class="u-flex-between">
            @include('admin.parts.page-title', [
                'eyebrow' => 'INQUIRIES',
                'title' => '問合せ管理',
                'info' => '
                    <p>ミセチョク運営への<strong>問い合わせ内容</strong>を一覧で確認・対応する画面です。</p>
                    <p>未対応のものから優先的に処理してください。返信は登録メールアドレス宛にメールで行います。</p>
                ',
            ])
            @include('admin.parts.operation-achievement', ['operationAchievementRoute' => 'admin.inquiries.index'])
        </div>

        {{-- KPI: クリックでフィルタと連動 --}}
        <section class="dashboard-kpi-grid inquiry-kpi-grid" data-inquiry-kpis>
            <button type="button"
                class="dashboard-kpi-card dashboard-kpi-card--link {{ ($statusCounts['pending'] ?? 0) > 0 ? 'is-attention' : '' }}"
                data-jump-filter="pending">
                <div class="dashboard-kpi-head">
                    <div class="dashboard-kpi-title">未対応</div>
                    <i class="fas fa-circle-exclamation"></i>
                </div>
                <div class="dashboard-kpi-main">
                    <span class="dashboard-kpi-value">{{ number_format($statusCounts['pending'] ?? 0) }}</span>
                    <span class="dashboard-kpi-unit">件</span>
                </div>
                <div class="dashboard-kpi-trend">優先対応</div>
            </button>
            <button type="button"
                class="dashboard-kpi-card dashboard-kpi-card--link"
                data-jump-filter="in_progress">
                <div class="dashboard-kpi-head">
                    <div class="dashboard-kpi-title">対応中</div>
                    <i class="fas fa-pen-to-square"></i>
                </div>
                <div class="dashboard-kpi-main">
                    <span class="dashboard-kpi-value">{{ number_format($statusCounts['in_progress'] ?? 0) }}</span>
                    <span class="dashboard-kpi-unit">件</span>
                </div>
                <div class="dashboard-kpi-trend">作業中</div>
            </button>
            <button type="button"
                class="dashboard-kpi-card dashboard-kpi-card--link {{ $criticalPendingCount > 0 ? 'is-critical' : ($oldPendingCount > 0 ? 'is-attention' : '') }}"
                data-jump-old="1">
                <div class="dashboard-kpi-head">
                    <div class="dashboard-kpi-title">3日以上 未着手</div>
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
                <div class="dashboard-kpi-main">
                    <span class="dashboard-kpi-value">{{ number_format($oldPendingCount + $criticalPendingCount) }}</span>
                    <span class="dashboard-kpi-unit">件</span>
                </div>
                <div class="dashboard-kpi-trend is-down">SLA リスク</div>
            </button>
            <article class="dashboard-kpi-card">
                <div class="dashboard-kpi-head">
                    <div class="dashboard-kpi-title">対応済（累計）</div>
                    <i class="fas fa-circle-check"></i>
                </div>
                <div class="dashboard-kpi-main">
                    <span class="dashboard-kpi-value">{{ number_format(($statusCounts['resolved'] ?? 0) + ($statusCounts['closed'] ?? 0)) }}</span>
                    <span class="dashboard-kpi-unit">件</span>
                </div>
            </article>
        </section>

        {{-- フィルタ＋検索 --}}
        <div class="admin-page-toolbar">
            <div class="admin-page-toolbar-row">
                <div class="admin-page-toolbar-search">
                    <i class="fas fa-magnifying-glass"></i>
                    <input type="text" id="inquiry-search" placeholder="名前・件名・区分で検索" autocomplete="off">
                </div>
            </div>
            <div class="admin-page-toolbar-filters" data-inquiry-filters>
                @foreach ($filterChips as $chip)
                    <button type="button"
                        class="admin-filter-chip {{ $chip['key'] === 'all' ? 'is-active' : '' }}"
                        data-inquiry-filter="{{ $chip['key'] }}">
                        <span>{{ $chip['label'] }}</span>
                        <strong>{{ $statusCounts[$chip['key']] ?? 0 }}</strong>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="table-wrapper">
            <table class="admin-table admin-table--stack inquiry-table">
                <thead>
                    <tr>
                        <th>送信者（区分 / ID）</th>
                        <th>件名</th>
                        <th>ステータス</th>
                        <th>受付日時</th>
                        <th>経過</th>
                        <th class="text-right">操作</th>
                    </tr>
                </thead>
                <tbody id="inquiry-table-body">
                    @forelse($inquiries as $inquiry)
                        @php
                            $inqActor = $resolveActor($inquiry['status_tone']);
                            $isOpen = in_array($inquiry['status_tone'], ['pending', 'in_progress'], true);
                            $daysElapsed = (int) $inquiry['created_at']->diffInDays(now());
                            $hoursElapsed = (int) $inquiry['created_at']->diffInHours(now());
                            $ageBucket = !$isOpen ? 'closed' : ($daysElapsed >= 7 ? 'critical' : ($daysElapsed >= 3 ? 'warning' : 'normal'));
                            $ageLabel = $hoursElapsed < 24 ? ($hoursElapsed . '時間') : ($daysElapsed . '日');
                            $detailUrl = route('admin.inquiries.show', $inquiry['id']);
                        @endphp
                        <tr data-inquiry-row
                            data-status-tone="{{ $inquiry['status_tone'] }}"
                            data-age-bucket="{{ $ageBucket }}"
                            data-href="{{ $detailUrl }}"
                            data-keyword="{{ strtolower($inquiry['from_name'] . ' ' . $inquiry['subject'] . ' ' . $inquiry['from_type']) }}"
                            class="inquiry-row inquiry-row--{{ $ageBucket }}">
                            <td>
                                @if (!empty($inquiry['from_type']))
                                    <span class="admin-status-badge">{{ $inquiry['from_type'] }}</span>
                                @endif
                                {{ $inquiry['from_name'] ?: '—' }}
                                <span class="text-muted text-sm">#{{ $inquiry['id'] }}</span>
                            </td>
                            <td data-label="件名">{{ $inquiry['subject'] ?: '（件名なし）' }}</td>
                            <td data-label="ステータス">
                                <span class="actor-pill {{ $inqActor['cls'] }}">
                                    <i class="fas {{ $inqActor['icon'] }}"></i> {{ $inqActor['label'] }}
                                </span>
                                <div class="u-mt-4">
                                    <span class="admin-status-badge {{ $statusBadge($inquiry['status_tone']) }}">
                                        {{ $inquiry['status'] }}
                                    </span>
                                </div>
                            </td>
                            <td data-label="受付日時" class="text-muted text-sm">{{ $inquiry['created_at']->format('Y-m-d H:i') }}</td>
                            <td data-label="経過" class="inquiry-age inquiry-age--{{ $ageBucket }}">
                                @if($isOpen)
                                    <i class="fas {{ $ageBucket === 'critical' ? 'fa-fire' : ($ageBucket === 'warning' ? 'fa-clock' : 'fa-hourglass-half') }}"></i>
                                    {{ $ageLabel }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-right stack-actions">
                                <a href="{{ $detailUrl }}" class="btn-action btn-action-secondary">
                                    詳細 <i class="fas fa-arrow-right"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">問い合わせはありません。</td>
                        </tr>
                    @endforelse
                    <tr id="inquiry-empty-row" hidden>
                        <td colspan="6" class="text-center text-muted">条件に一致する問合せはありません。</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('admin-scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var searchInput = document.getElementById('inquiry-search');
    var chips = document.querySelectorAll('[data-inquiry-filter]');
    var rows = document.querySelectorAll('[data-inquiry-row]');
    var emptyRow = document.getElementById('inquiry-empty-row');
    var currentFilter = 'all';
    var currentKeyword = '';
    var ageOnly = false;     // 古い未対応(3日以上)のみ

    function apply() {
        var visible = 0;
        rows.forEach(function (row) {
            var tone = row.getAttribute('data-status-tone') || '';
            var bucket = row.getAttribute('data-age-bucket') || 'normal';
            var kw = row.getAttribute('data-keyword') || '';
            var matchFilter = currentFilter === 'all' || tone === currentFilter;
            var matchAge = !ageOnly || bucket === 'warning' || bucket === 'critical';
            var matchKw = currentKeyword === '' || kw.indexOf(currentKeyword) !== -1;
            var show = matchFilter && matchAge && matchKw;
            row.hidden = !show;
            if (show) visible++;
        });
        if (emptyRow) emptyRow.hidden = visible !== 0 || rows.length === 0;
    }

    function setFilter(key) {
        ageOnly = false;
        currentFilter = key;
        chips.forEach(function (c) { c.classList.toggle('is-active', c.getAttribute('data-inquiry-filter') === key); });
        apply();
    }

    chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            setFilter(chip.getAttribute('data-inquiry-filter'));
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            currentKeyword = (searchInput.value || '').toLowerCase().trim();
            apply();
        });
    }

    // ====== KPI 連動 ======
    document.querySelectorAll('[data-inquiry-kpis] [data-jump-filter]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            setFilter(btn.getAttribute('data-jump-filter') || 'all');
        });
    });
    var oldKpi = document.querySelector('[data-inquiry-kpis] [data-jump-old]');
    if (oldKpi) {
        oldKpi.addEventListener('click', function () {
            currentFilter = 'all';
            ageOnly = true;
            chips.forEach(function (c) { c.classList.toggle('is-active', c.getAttribute('data-inquiry-filter') === 'all'); });
            apply();
        });
    }

    // ====== 行全体をクリック可能に（コントロール内クリックは除外） ======
    rows.forEach(function (row) {
        row.addEventListener('click', function (e) {
            if (e.target.closest('a, button, input, textarea, select')) return;
            var href = row.getAttribute('data-href');
            if (href) location.href = href;
        });
    });
});
</script>
@endpush
