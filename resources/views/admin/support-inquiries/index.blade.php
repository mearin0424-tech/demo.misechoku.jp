@extends('layouts.admin')

@section('title', 'サポート問い合わせ')

@section('content')
<div class="admin-page">
    @include('admin.parts.page-title', [
        'eyebrow' => 'SUPPORT',
        'title' => 'サポート問い合わせ管理',
        'info' => '
            <p>ユーザーから届いたサポート問い合わせを確認・対応します。</p>
            <p>「<strong>新着</strong>」をクリックすると未対応のみ絞り込めます。</p>
        ',
    ])

    @if(session('status'))
        <div class="admin-alert admin-alert-success">{{ session('status') }}</div>
    @endif

    {{-- KPI（クリックでフィルタ） --}}
    <section class="dashboard-kpi-grid content-kpi-grid">
        <a href="{{ route('admin.support-inquiries.index') }}"
           class="dashboard-kpi-card dashboard-kpi-card--link {{ $status === '' ? 'is-active' : '' }}"
           aria-pressed="{{ $status === '' ? 'true' : 'false' }}">
            <div class="dashboard-kpi-head">
                <div class="dashboard-kpi-title">合計</div>
                <i class="fas fa-envelope"></i>
            </div>
            <div class="dashboard-kpi-main">
                <span class="dashboard-kpi-value">{{ number_format($counts['all']) }}</span>
                <span class="dashboard-kpi-unit">件</span>
            </div>
        </a>
        <a href="{{ route('admin.support-inquiries.index', ['status' => 'new']) }}"
           class="dashboard-kpi-card dashboard-kpi-card--link {{ $status === 'new' ? 'is-active' : '' }}"
           aria-pressed="{{ $status === 'new' ? 'true' : 'false' }}">
            <div class="dashboard-kpi-head">
                <div class="dashboard-kpi-title">新着</div>
                <i class="fas fa-bell"></i>
            </div>
            <div class="dashboard-kpi-main">
                <span class="dashboard-kpi-value">{{ number_format($counts['new']) }}</span>
                <span class="dashboard-kpi-unit">件</span>
            </div>
            <div class="dashboard-kpi-trend is-up">要対応</div>
        </a>
        <a href="{{ route('admin.support-inquiries.index', ['status' => 'in_progress']) }}"
           class="dashboard-kpi-card dashboard-kpi-card--link {{ $status === 'in_progress' ? 'is-active' : '' }}"
           aria-pressed="{{ $status === 'in_progress' ? 'true' : 'false' }}">
            <div class="dashboard-kpi-head">
                <div class="dashboard-kpi-title">対応中</div>
                <i class="fas fa-comments"></i>
            </div>
            <div class="dashboard-kpi-main">
                <span class="dashboard-kpi-value">{{ number_format($counts['in_progress']) }}</span>
                <span class="dashboard-kpi-unit">件</span>
            </div>
        </a>
    </section>

    <div class="table-wrapper" style="margin-top: 16px;">
        <table class="admin-table admin-table--stack">
            <thead>
                <tr>
                    <th>送信者（ID）</th>
                    <th>受付日時</th>
                    <th>カテゴリ</th>
                    <th>本文（抜粋）</th>
                    <th>ステータス</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($inquiries as $inquiry)
                    @php
                        $statusBadge = match ($inquiry->status) {
                            \App\Models\SupportInquiry::STATUS_NEW => 'is-warning',
                            \App\Models\SupportInquiry::STATUS_IN_PROGRESS => 'is-info',
                            \App\Models\SupportInquiry::STATUS_RESOLVED => 'is-success',
                            default => 'is-inactive',
                        };
                        $senderBadge = match ($inquiry->sender_type) {
                            \App\Models\SupportInquiry::SENDER_CAST => '👤 キャスト',
                            \App\Models\SupportInquiry::SENDER_SHOP => '🏪 店舗',
                            default => '👻 ゲスト',
                        };
                    @endphp
                    <tr>
                        <td>
                            <div>{{ $senderBadge }} <span class="admin-table-sub">#{{ $inquiry->id }}</span></div>
                            @if($inquiry->sender_id)
                                <div class="admin-table-sub">{{ $inquiry->sender_id }}</div>
                            @endif
                        </td>
                        <td data-label="受付日時">{{ optional($inquiry->created_at)->format('Y-m-d H:i') }}</td>
                        <td data-label="カテゴリ">{{ $inquiry->categoryLabel() }}</td>
                        <td data-label="本文（抜粋）" class="admin-table-cell-clip">{{ \Illuminate\Support\Str::limit($inquiry->body, 60) }}</td>
                        <td data-label="ステータス">
                            <span class="badge {{ $statusBadge }}">{{ $inquiry->statusLabel() }}</span>
                        </td>
                        <td class="stack-actions">
                            <a href="{{ route('admin.support-inquiries.show', $inquiry->id) }}" class="btn-action">
                                <i class="fas fa-eye"></i> 詳細
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="admin-table-empty">
                            <i class="fas fa-inbox"></i> 該当する問い合わせはありません。
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($inquiries->hasPages())
        <div class="admin-pagination">
            {{ $inquiries->links() }}
        </div>
    @endif
</div>
@endsection
