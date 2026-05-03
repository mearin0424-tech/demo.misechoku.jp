@extends('layouts.admin')

@section('title', $document->title . ' - 規約管理')
@section('admin_page_title', $document->title)

@section('content')
    <div class="admin-page">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
            <div>
                <h1 class="admin-title" style="margin-bottom:4px;">{{ $document->title }}</h1>
                <p class="admin-description" style="margin:0;">
                    キー: <code>{{ $document->key }}</code>　章数: {{ $document->chapters->count() }}章
                </p>
            </div>
            <div style="display:flex;gap:8px;">
                <a href="{{ route('admin.policies.index') }}" class="btn-action manage" style="background:transparent;border-color:var(--admin-line);color:var(--admin-text);">
                    <i class="fas fa-arrow-left"></i> 一覧へ
                </a>
                <a href="{{ route('admin.policies.edit', ['key' => $document->key]) }}" class="btn-action manage">
                    <i class="fas fa-pen"></i> 編集する
                </a>
            </div>
        </div>

        @if(session('status'))
            <div class="admin-alert">{{ session('status') }}</div>
        @endif

        <div class="admin-panel">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:8px;">
                <h2 class="admin-panel-title" style="margin:0;">編集ステータス</h2>
                @if($document->is_locked)
                    <span class="admin-badge" style="background:rgba(248,113,113,.18);color:#fda4af;">
                        <i class="fas fa-lock" style="margin-right:4px;"></i>ロック中（閲覧のみ）
                    </span>
                @else
                    <span class="admin-badge" style="background:rgba(52,211,153,.18);color:#86efac;">
                        <i class="fas fa-lock-open" style="margin-right:4px;"></i>編集可能
                    </span>
                @endif
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;font-size:.84rem;color:var(--admin-sub);">
                <div>
                    <div style="font-size:.7rem;color:var(--admin-muted);margin-bottom:4px;">最終更新者</div>
                    <div>{{ $document->updated_by_name ?: '-' }}</div>
                </div>
                <div>
                    <div style="font-size:.7rem;color:var(--admin-muted);margin-bottom:4px;">最終更新日時</div>
                    <div>{{ optional($document->content_updated_at)->format('Y-m-d H:i') ?: '未更新' }}</div>
                </div>
            </div>
        </div>

        @if($document->lead_title || $document->lead_body)
            <div class="admin-panel">
                @if($document->lead_title)
                    <h2 class="admin-panel-title">{{ $document->lead_title }}</h2>
                @endif
                @if($document->lead_body)
                    <div style="white-space:pre-wrap;line-height:1.85;color:var(--admin-text);font-size:.92rem;">{{ $document->lead_body }}</div>
                @endif
            </div>
        @endif

        @if($document->isAbout() && is_array($document->meta) && count($document->meta) > 0)
            <div class="admin-panel">
                <h2 class="admin-panel-title">OVERVIEW / 協会概要</h2>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:12px;">
                    @foreach($metaSchema as $row)
                        @php
                            $entry = $document->meta[$row['key']] ?? null;
                            $value = is_array($entry) ? ($entry['value'] ?? '') : '';
                            $label = is_array($entry) ? ($entry['label'] ?? $row['label']) : $row['label'];
                        @endphp
                        <div style="border-bottom:1px solid var(--admin-line-soft);padding-bottom:8px;">
                            <div style="font-size:.72rem;color:var(--admin-muted);margin-bottom:4px;letter-spacing:.06em;">{{ $label }}</div>
                            <div style="font-size:.92rem;color:var(--admin-text);white-space:pre-wrap;">{{ $value !== '' ? $value : '-' }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @forelse($document->chapters as $chapter)
            <div class="admin-panel">
                <h2 class="admin-panel-title">
                    <span style="color:var(--admin-gold);font-size:.78rem;letter-spacing:.06em;">第{{ $loop->iteration }}章</span>
                    　{{ $chapter->title }}
                </h2>
                <div style="white-space:pre-wrap;line-height:1.9;color:var(--admin-text);font-size:.9rem;">{{ $chapter->body }}</div>
            </div>
        @empty
            <div class="admin-panel">
                <p class="admin-note">章がまだ登録されていません。「編集する」から章タイトルと本文を追加してください。</p>
            </div>
        @endforelse

        <div class="admin-panel">
            <h2 class="admin-panel-title">更新履歴</h2>
            @if($document->revisions->isEmpty())
                <p class="admin-note">まだ更新履歴はありません。</p>
            @else
                <div class="table-wrapper" style="border:none;box-shadow:none;background:transparent;">
                    <table class="admin-table" style="min-width:540px;">
                        <thead>
                            <tr>
                                <th>日時</th>
                                <th>操作</th>
                                <th>更新者</th>
                                <th>メモ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($document->revisions->take(20) as $rev)
                                <tr>
                                    <td>{{ optional($rev->created_at)->format('Y-m-d H:i') }}</td>
                                    <td>{{ $rev->action_label }}</td>
                                    <td>{{ $rev->updated_by_name ?: '-' }}</td>
                                    <td style="white-space:normal;">{{ $rev->summary ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
