@extends('layouts.admin')

@section('title', '問合せ詳細 #' . ($inquiry['id'] ?? ''))
@section('admin_page_title', '問合せ詳細')

@section('content')
    @php
        $statusBadge = match ($inquiry['status_tone']) {
            'pending' => 'is-danger',
            'in_progress' => 'is-warning',
            'resolved' => 'is-success',
            'closed' => 'is-inactive',
            default => '',
        };
    @endphp

    @php
        $actor = match ($inquiry['status_tone']) {
            'pending', 'in_progress' => ['cls' => 'is-admin', 'icon' => 'fa-bell', 'label' => '運営対応'],
            'resolved', 'closed' => ['cls' => 'is-done', 'icon' => 'fa-circle-check', 'label' => '完了'],
            default => ['cls' => 'is-admin-soft', 'icon' => 'fa-circle-question', 'label' => '—'],
        };
    @endphp

    <div class="admin-page">
        <div class="u-flex-between">
            @include('admin.parts.page-title', ['eyebrow' => 'INQUIRY DETAIL', 'title' => '問合せ #' . $inquiry['id']])
            <a href="{{ route('admin.inquiries.index') }}" class="btn-action btn-action-secondary">
                <i class="fas fa-arrow-left"></i> 一覧へ戻る
            </a>
        </div>

        <section class="admin-panel">
            <div class="u-flex-between u-mb-12">
                <h2 class="admin-panel-title u-mb-0">{{ $inquiry['subject'] ?: '（件名なし）' }}</h2>
                <div class="u-flex u-gap-8">
                    <span class="actor-pill {{ $actor['cls'] }}">
                        <i class="fas {{ $actor['icon'] }}"></i> {{ $actor['label'] }}
                    </span>
                    <span class="admin-status-badge {{ $statusBadge }}">{{ $inquiry['status'] }}</span>
                </div>
            </div>

            <div class="inquiry-detail-meta">
                <div class="inquiry-detail-meta-item">
                    <div class="inquiry-detail-meta-label">区分</div>
                    <div class="inquiry-detail-meta-value">{{ $inquiry['from_type'] ?: '—' }}</div>
                </div>
                <div class="inquiry-detail-meta-item">
                    <div class="inquiry-detail-meta-label">名前</div>
                    <div class="inquiry-detail-meta-value">{{ $inquiry['from_name'] ?: '—' }}</div>
                </div>
                <div class="inquiry-detail-meta-item">
                    <div class="inquiry-detail-meta-label">メールアドレス</div>
                    <div class="inquiry-detail-meta-value">
                        @if(!empty($inquiry['from_email']))
                            <a href="mailto:{{ $inquiry['from_email'] }}">{{ $inquiry['from_email'] }}</a>
                        @else
                            —
                        @endif
                    </div>
                </div>
                <div class="inquiry-detail-meta-item">
                    <div class="inquiry-detail-meta-label">受付日時</div>
                    <div class="inquiry-detail-meta-value">{{ $inquiry['created_at']->format('Y-m-d H:i') }}</div>
                </div>
                @if(!empty($inquiry['updated_at']))
                    <div class="inquiry-detail-meta-item">
                        <div class="inquiry-detail-meta-label">最終更新</div>
                        <div class="inquiry-detail-meta-value">{{ $inquiry['updated_at']->format('Y-m-d H:i') }}</div>
                    </div>
                @endif
            </div>

            <h3 class="admin-panel-title">本文</h3>
            <div class="inquiry-detail-body">{{ $inquiry['body'] ?: '本文はありません。' }}</div>
        </section>

        <section class="admin-panel">
            <h2 class="admin-panel-title">対応アクション</h2>
            <p class="admin-note u-mb-12">
                ステータス更新機能は今後追加予定です。当面は登録メールアドレス宛にメールでご返信ください。
            </p>
            @if(!empty($inquiry['from_email']))
                <a href="mailto:{{ $inquiry['from_email'] }}?subject=Re: {{ rawurlencode($inquiry['subject'] ?: 'お問合せ') }}"
                   class="btn-action manage">
                    <i class="fas fa-envelope"></i> メールで返信
                </a>
            @else
                <p class="admin-note">メールアドレス未登録の問い合わせのため、メール返信できません。</p>
            @endif
        </section>
    </div>
@endsection
