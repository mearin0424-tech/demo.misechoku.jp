@extends('layouts.admin')

@section('title', '請求・振込タスク管理')

@push('admin-styles')
<style>
    .task-card-list {
        display: grid;
        gap: 16px;
    }
    .task-card {
        padding: 18px;
        border-radius: 18px;
        border: 0;
        background: var(--admin-card);
        box-shadow: var(--admin-shadow-lg);
    }
    .task-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 12px;
    }
    .task-card-id {
        font-size: 0.78rem;
        color: var(--admin-muted);
    }
    .task-card-title {
        margin: 4px 0 0;
        font-size: 1rem;
        font-weight: 800;
        color: var(--admin-text);
    }
    .task-card-chip {
        display: inline-flex;
        align-items: center;
        padding: 5px 10px;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.1);
        color: #1d4ed8;
        font-size: 0.74rem;
        font-weight: 700;
    }
    .task-card-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 10px;
        margin-bottom: 12px;
    }
    .task-card-item {
        padding: 12px 14px;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.85);
        border: 0;
        box-shadow: var(--admin-shadow);
    }
    .task-card-label {
        font-size: 0.72rem;
        color: var(--admin-muted);
        margin-bottom: 6px;
    }
    .task-card-value {
        font-size: 0.88rem;
        font-weight: 700;
        line-height: 1.6;
        color: var(--admin-text);
    }
    .task-card-text {
        margin-top: 10px;
        font-size: 0.83rem;
        line-height: 1.7;
        color: var(--admin-sub);
        white-space: pre-wrap;
    }
    .task-card-actions {
        margin-top: 14px;
        display: flex;
        justify-content: flex-end;
    }
</style>
@endpush

@section('content')
    <div class="admin-page">
        <h1 class="admin-title">請求・振込タスク管理</h1>
        <p class="admin-description">
            ステータスに応じて、運営が今対応すべき請求・振込タスクだけを一覧化しています。<br>
            実作業は各行の詳細導線から `入金・振込管理` 画面で完了できます。
        </p>

        @if(session('status'))
            <div class="admin-alert">
                {{ session('status') }}
            </div>
        @endif

        @if(session('error'))
            <div class="admin-alert" style="background: rgba(194, 65, 60, 0.1); border: 0; color: #7f1d1d;">
                {{ session('error') }}
            </div>
        @endif

        <div class="task-card-list">
            @forelse($tasks as $task)
                <section class="task-card">
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
                            詳細へ
                        </a>
                    </div>
                </section>
            @empty
                <div class="admin-panel">
                    <p class="admin-note">現在対応が必要なタスクはありません。</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection

