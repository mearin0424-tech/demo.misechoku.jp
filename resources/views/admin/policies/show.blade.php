@extends('layouts.admin')

@php
    use App\Support\MarkdownRenderer;
@endphp

@section('title', $document->title . ' - 規約管理')
@section('admin_page_title', $document->title)

@push('admin-styles')
    <style>
        .policy-md-preview { font-size: 0.9rem; line-height: 1.85; color: var(--admin-text); }
        .policy-md-preview > *:first-child { margin-top: 0; }
        .policy-md-preview > *:last-child { margin-bottom: 0; }
        .policy-md-preview p { margin: 0 0 0.75em; }
        .policy-md-preview ul, .policy-md-preview ol { margin: 0 0 0.75em; padding-left: 1.25em; }
        .policy-md-preview li { margin: 0.25em 0; }
        .policy-md-preview h1, .policy-md-preview h2, .policy-md-preview h3 { margin: 0.85em 0 0.45em; font-size: 1rem; color: var(--admin-gold); }
        .policy-md-preview a { color: var(--admin-blue); }
        .policy-md-preview strong { color: #f5e6e6; }
        .policy-md-preview code { font-size: 0.85em; background: rgba(0,0,0,0.25); padding: 0.1em 0.35em; border-radius: 4px; }
    </style>
@endpush

@section('content')
    <div class="admin-page">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
            <div>
                <h1 class="admin-title" style="margin-bottom:4px;">{{ $document->title }}</h1>
                <p class="admin-description" style="margin:0;">
                    キー: <code>{{ $document->key }}</code>
                    @unless($document->isAbout())
                        　章数: {{ $document->chapters->count() }}章
                    @endunless
                </p>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <a href="{{ route('admin.dashboard') }}" class="btn-action manage" style="background:transparent;border-color:var(--admin-line);color:var(--admin-text);">
                    <i class="fas fa-arrow-left"></i> ダッシュボード
                </a>
                @unless($document->isAbout())
                    <a href="{{ route('admin.policies.edit', ['key' => $document->key]) }}#policy-chapters-panel" class="btn-action manage" style="background:transparent;border-color:rgba(230,208,128,0.35);color:var(--admin-gold);">
                        <i class="fas fa-plus"></i> 章を追加
                    </a>
                @endunless
                <a href="{{ route('admin.policies.edit', ['key' => $document->key]) }}" class="btn-action manage">
                    <i class="fas fa-pen"></i> 編集する
                </a>
            </div>
        </div>

        @if(session('status'))
            <div class="admin-alert">{{ session('status') }}</div>
        @endif

        {{-- 更新履歴はページ先頭 --}}
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
                            @foreach($document->revisions as $rev)
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
                    <div class="policy-md-preview">
                        {!! MarkdownRenderer::toHtml($document->lead_body) !!}
                    </div>
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
                            @if($value !== '')
                                <div class="policy-md-preview">
                                    {!! MarkdownRenderer::toHtml($value) !!}
                                </div>
                            @else
                                <div style="font-size:.88rem;color:var(--admin-muted);">-</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @unless($document->isAbout())
            @forelse($document->chapters as $chapter)
                <div class="admin-panel" style="border-style:dashed;border-color:rgba(230,208,128,0.15);">
                    <h2 class="admin-panel-title" style="margin-bottom:10px;">
                        <span style="color:var(--admin-gold);font-size:.78rem;letter-spacing:.06em;">第{{ $loop->iteration }}章</span>
                        　{{ $chapter->title }}
                    </h2>
                    <div class="policy-md-preview">
                        {!! MarkdownRenderer::toHtml($chapter->body) !!}
                    </div>
                </div>
            @empty
                <div class="admin-panel">
                    <p class="admin-note">章がまだ登録されていません。「章を追加」または「編集する」から章を追加してください。</p>
                </div>
            @endforelse
        @endunless
    </div>
@endsection
