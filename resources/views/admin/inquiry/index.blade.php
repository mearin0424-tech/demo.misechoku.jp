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
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>区分</th>
                        <th>名前</th>
                        <th>件名</th>
                        <th>ステータス</th>
                        <th>受付日時</th>
                        <th class="text-right">操作</th>
                    </tr>
                </thead>
                <tbody id="inquiry-table-body">
                    @forelse($inquiries as $inquiry)
                        @php $inqActor = $resolveActor($inquiry['status_tone']); @endphp
                        <tr data-inquiry-row
                            data-status-tone="{{ $inquiry['status_tone'] }}"
                            data-keyword="{{ strtolower($inquiry['from_name'] . ' ' . $inquiry['subject'] . ' ' . $inquiry['from_type']) }}">
                            <td>{{ $inquiry['id'] }}</td>
                            <td>
                                @if (!empty($inquiry['from_type']))
                                    <span class="admin-status-badge">{{ $inquiry['from_type'] }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $inquiry['from_name'] ?: '—' }}</td>
                            <td>{{ $inquiry['subject'] ?: '（件名なし）' }}</td>
                            <td>
                                <span class="actor-pill {{ $inqActor['cls'] }}">
                                    <i class="fas {{ $inqActor['icon'] }}"></i> {{ $inqActor['label'] }}
                                </span>
                                <div class="u-mt-4">
                                    <span class="admin-status-badge {{ $statusBadge($inquiry['status_tone']) }}">
                                        {{ $inquiry['status'] }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-muted text-sm">{{ $inquiry['created_at']->format('Y-m-d H:i') }}</td>
                            <td class="text-right">
                                <a href="{{ route('admin.inquiries.show', $inquiry['id']) }}" class="btn-action btn-action-secondary">
                                    詳細 <i class="fas fa-arrow-right"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">問い合わせはありません。</td>
                        </tr>
                    @endforelse
                    <tr id="inquiry-empty-row" hidden>
                        <td colspan="7" class="text-center text-muted">条件に一致する問合せはありません。</td>
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

    function apply() {
        var visible = 0;
        rows.forEach(function (row) {
            var tone = row.getAttribute('data-status-tone') || '';
            var kw = row.getAttribute('data-keyword') || '';
            var matchFilter = currentFilter === 'all' || tone === currentFilter;
            var matchKw = currentKeyword === '' || kw.indexOf(currentKeyword) !== -1;
            var show = matchFilter && matchKw;
            row.hidden = !show;
            if (show) visible++;
        });
        if (emptyRow) emptyRow.hidden = visible !== 0 || rows.length === 0;
    }

    chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            currentFilter = chip.getAttribute('data-inquiry-filter');
            chips.forEach(function (c) { c.classList.toggle('is-active', c === chip); });
            apply();
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            currentKeyword = (searchInput.value || '').toLowerCase().trim();
            apply();
        });
    }
});
</script>
@endpush
