@extends('layouts.admin')

@section('title', '問い合わせ #' . $inquiry->id)

@section('content')
<div class="admin-page">
    @include('admin.parts.page-title', [
        'eyebrow' => 'SUPPORT',
        'title' => '問い合わせ #' . $inquiry->id,
        'info' => '<p>ステータス更新・対応メモを残せます。返信は記録メール宛に直接行ってください。</p>',
    ])

    @if(session('status'))
        <div class="admin-alert admin-alert-success">{{ session('status') }}</div>
    @endif

    <div class="admin-detail-grid" style="display: grid; gap: 16px; grid-template-columns: 1fr; max-width: 760px;">

        {{-- 受信内容 --}}
        <section class="admin-panel">
            <h2 class="admin-panel-title">受信内容</h2>
            <dl class="admin-def-list">
                <dt>受付日時</dt>
                <dd>{{ optional($inquiry->created_at)->format('Y-m-d H:i:s') }}</dd>

                <dt>送信者</dt>
                <dd>
                    {{ \App\Models\SupportInquiry::SENDER_CAST === $inquiry->sender_type ? 'キャスト'
                        : (\App\Models\SupportInquiry::SENDER_SHOP === $inquiry->sender_type ? '店舗' : 'ゲスト') }}
                    @if($inquiry->sender_id)
                        <small>（{{ $inquiry->sender_id }}）</small>
                    @endif
                </dd>

                <dt>カテゴリ</dt>
                <dd>{{ $inquiry->categoryLabel() }}</dd>

                <dt>返信先</dt>
                <dd><a href="mailto:{{ $inquiry->email }}">{{ $inquiry->email }}</a></dd>

                <dt>UA</dt>
                <dd><small style="color: var(--admin-muted);">{{ $inquiry->user_agent ?? '-' }}</small></dd>

                <dt>IP</dt>
                <dd><small style="color: var(--admin-muted);">{{ $inquiry->ip_address ?? '-' }}</small></dd>
            </dl>

            <hr style="margin: 14px 0; border: 0; border-top: 1px solid var(--admin-line);">

            <h3 class="admin-panel-subtitle">本文</h3>
            <div class="admin-prose" style="white-space: pre-wrap; background: var(--admin-surface-alt); border-radius: 8px; padding: 12px 14px; font-size: 14px; line-height: 1.7;">{{ $inquiry->body }}</div>
        </section>

        {{-- ステータス更新 --}}
        <section class="admin-panel">
            <h2 class="admin-panel-title">対応ステータス</h2>
            <p style="margin-bottom: 10px;">
                現在: <span class="badge">{{ $inquiry->statusLabel() }}</span>
                @if($inquiry->responded_at)
                    <small style="margin-left: 8px; color: var(--admin-muted);">一次応対: {{ $inquiry->responded_at->format('Y-m-d H:i') }}</small>
                @endif
            </p>
            <form method="POST" action="{{ route('admin.support-inquiries.status', $inquiry->id) }}" style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                @csrf
                <select name="status" class="admin-input" required>
                    @foreach(\App\Models\SupportInquiry::STATUS_LABELS as $key => $label)
                        <option value="{{ $key }}" @selected($inquiry->status === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-action manage">
                    <i class="fas fa-check"></i> 更新
                </button>
            </form>
        </section>

        {{-- メモ --}}
        <section class="admin-panel">
            <h2 class="admin-panel-title">対応メモ</h2>
            <form method="POST" action="{{ route('admin.support-inquiries.note', $inquiry->id) }}">
                @csrf
                <textarea name="admin_note" rows="6" maxlength="4000" class="admin-input" placeholder="応対履歴・経緯などを記録してください">{{ old('admin_note', $inquiry->admin_note) }}</textarea>
                <div style="margin-top: 8px;">
                    <button type="submit" class="btn-action manage">
                        <i class="fas fa-save"></i> メモを保存
                    </button>
                </div>
            </form>
        </section>

        <div>
            <a href="{{ route('admin.support-inquiries.index') }}" class="btn-action">
                <i class="fas fa-arrow-left"></i> 一覧へ戻る
            </a>
        </div>
    </div>
</div>
@endsection
