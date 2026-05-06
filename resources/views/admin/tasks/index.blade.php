@extends('layouts.admin')

@section('title', '請求・振込タスク管理')

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
            <div class="admin-alert admin-alert-error">
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

