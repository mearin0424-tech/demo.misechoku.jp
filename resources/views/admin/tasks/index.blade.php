@extends('layouts.admin')

@section('title', '請求・振込タスク管理')

@section('content')
    @php
        // タスクのカテゴリ別件数（フィルタチップ用）
        $taskList = $tasks ?? [];
        $catCounts = [
            'all' => count($taskList),
            'invoice' => 0,
            'deposit' => 0,
            'transfer' => 0,
            'error' => 0,
        ];
        foreach ($taskList as $t) {
            $cat = $t['cat_id'] ?? null;
            if (isset($catCounts[$cat])) {
                $catCounts[$cat]++;
            }
        }
        $filterChips = [
            ['key' => 'all', 'label' => 'すべて'],
            ['key' => 'invoice', 'label' => '請求書発行'],
            ['key' => 'deposit', 'label' => '入金照合'],
            ['key' => 'transfer', 'label' => '振込実行'],
            ['key' => 'error', 'label' => 'エラー'],
        ];
    @endphp

    <div class="admin-page">
        @include('admin.parts.operation-nav', ['active' => 'tasks'])

        <h1 class="admin-title">請求・振込タスク</h1>
        <p class="admin-description">
            ステータスに応じて、運営が今対応すべき請求・振込タスクだけを一覧化しています。実作業は各行の「詳細へ」から完了できます。
        </p>

        @if(session('status'))
            <div class="admin-alert admin-alert-success">{{ session('status') }}</div>
        @endif
        @if(session('error'))
            <div class="admin-alert admin-alert-error">{{ session('error') }}</div>
        @endif

        {{-- フィルタチップ --}}
        <div class="admin-page-toolbar">
            <div class="admin-page-toolbar-filters" data-task-filters>
                @foreach ($filterChips as $chip)
                    <button type="button"
                        class="admin-filter-chip {{ $chip['key'] === 'all' ? 'is-active' : '' }}"
                        data-task-filter="{{ $chip['key'] }}">
                        <span>{{ $chip['label'] }}</span>
                        <strong>{{ $catCounts[$chip['key']] ?? 0 }}</strong>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="task-card-list">
            @forelse($taskList as $task)
                @php
                    $urgency = $task['urgency'] ?? 'normal';
                @endphp
                <section class="task-card urgency-{{ $urgency }}" data-task-cat="{{ $task['cat_id'] ?? '' }}">
                    <div class="task-card-head">
                        <div>
                            <div class="task-card-id">#{{ $task['id'] }} / {{ $task['task_actor_label'] ?? '運営' }}対応</div>
                            <h2 class="task-card-title">{{ $task['task_title'] }}</h2>
                        </div>
                        <span class="task-card-chip">{{ $task['status_label'] }}</span>
                    </div>

                    <div class="task-card-grid">
                        <div class="task-card-item">
                            <div class="task-card-label">店舗 / キャスト</div>
                            <div class="task-card-value">{{ $task['shop_name'] }} / {{ $task['cast_name'] }}</div>
                        </div>
                        <div class="task-card-item">
                            <div class="task-card-label">請求額</div>
                            <div class="task-card-value">¥{{ number_format($task['invoice_amount']) }}</div>
                        </div>
                        <div class="task-card-item">
                            <div class="task-card-label">期限 / 発生日</div>
                            <div class="task-card-value">{{ $task['task_due_date'] ?: '—' }}</div>
                        </div>
                        <div class="task-card-item">
                            <div class="task-card-label">次アクション</div>
                            <div class="task-card-value">{{ $task['next_action'] }}</div>
                        </div>
                    </div>

                    @if(!empty($task['task_summary']))
                        <div class="task-card-text">{{ $task['task_summary'] }}</div>
                    @endif

                    @if(!empty($task['review_average']) || !empty($task['task_review_summary']))
                        <div class="task-card-text">
                            レビュー
                            @if(!empty($task['review_average']))
                                / 総合 {{ number_format((float) $task['review_average'], 1) }}
                            @endif
                            @if(!empty($task['task_review_summary']))
                                <br>{{ $task['task_review_summary'] }}
                            @endif
                        </div>
                    @endif

                    <div class="task-card-actions">
                        <a href="{{ $task['task_url'] ?? (route('admin.deposits.index') . '#deposit-' . $task['id']) }}" class="btn-action manage">
                            詳細へ <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </section>
            @empty
                <div class="admin-panel">
                    <p class="admin-note">現在対応が必要なタスクはありません。</p>
                </div>
            @endforelse
            <div class="admin-panel" id="task-empty-filter" hidden>
                <p class="admin-note">このカテゴリに該当するタスクはありません。</p>
            </div>
        </div>
    </div>
@endsection

@push('admin-scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var chips = document.querySelectorAll('[data-task-filter]');
    var cards = document.querySelectorAll('.task-card[data-task-cat]');
    var emptyHint = document.getElementById('task-empty-filter');

    chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            var target = chip.getAttribute('data-task-filter');
            var visible = 0;
            chips.forEach(function (c) { c.classList.toggle('is-active', c === chip); });
            cards.forEach(function (card) {
                var match = target === 'all' || card.getAttribute('data-task-cat') === target;
                card.style.display = match ? '' : 'none';
                if (match) visible++;
            });
            if (emptyHint) emptyHint.hidden = visible !== 0 || cards.length === 0;
        });
    });
});
</script>
@endpush
