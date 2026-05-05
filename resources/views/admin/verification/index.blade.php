@extends('layouts.admin')

@section('title', '本人確認・書類審査')

@section('content')
    <div class="admin-page">
        <h1 class="admin-title">本人確認・書類審査</h1>

        @if(session('status'))
            <div class="admin-alert">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="admin-alert" style="background:#fff1f2; color:#b91c1c;">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="admin-panel">
            <h2 class="admin-panel-title">サマリー</h2>
            <div style="display:flex; gap:16px; flex-wrap:wrap;">
                <div class="admin-card">
                    <div class="text-xs text-gray-400">本人確認の未処理</div>
                    <div style="font-size:1.5rem; font-weight:700;">{{ $summary['cast_pending'] ?? 0 }}件</div>
                </div>
                <div class="admin-card">
                    <div class="text-xs text-gray-400">店舗書類の未処理</div>
                    <div style="font-size:1.5rem; font-weight:700;">{{ $summary['shop_pending'] ?? 0 }}件</div>
                </div>
                <div class="admin-card">
                    <div class="text-xs text-gray-400">審査完了（実績・累計）</div>
                    <div style="font-size:1.5rem; font-weight:700;">{{ number_format((int) (($adminOperationAchievements ?? [])['admin.verification.index'] ?? 0)) }}件</div>
                    <p class="text-xs text-gray-400" style="margin:8px 0 0; line-height:1.5;">承認・却下まで完了した本人確認・書類の合計です。</p>
                </div>
            </div>
        </section>

        <section class="admin-panel">
            <h2 class="admin-panel-title">キャスト本人確認</h2>
            <p class="text-xs text-gray-400" style="margin-bottom:12px;">並び順: 未承認 → 不備・却下 → 承認済み / 同一ステータス内は更新日時の新しい順</p>
            <div class="verification-filters">
                <label>
                    <span>ステータス</span>
                    <select class="verification-filter" data-target-table="cast-verification-table" data-filter-key="status">
                        <option value="all" {{ request('cast_status') === 'all' ? 'selected' : '' }}>すべて</option>
                        <option value="pending" {{ request('cast_status', 'pending') === 'pending' ? 'selected' : '' }}>未承認</option>
                        <option value="rejected" {{ request('cast_status') === 'rejected' ? 'selected' : '' }}>不備・却下</option>
                        <option value="approved" {{ request('cast_status') === 'approved' ? 'selected' : '' }}>承認済み</option>
                    </select>
                </label>
                <label>
                    <span>検索</span>
                    <input type="text" class="verification-filter" data-target-table="cast-verification-table" data-filter-key="keyword" placeholder="名前 / ID / 書類種別" value="{{ request('cast_keyword', '') }}">
                </label>
                <div class="verification-filter-count">表示件数: <strong data-count-for="cast-verification-table">{{ count($castDocuments) }}</strong></div>
                <div class="verification-filter-links">
                    <a href="{{ route('admin.verification.index', ['cast_status' => 'pending', 'focus' => 'cast']) }}">未承認だけ表示</a>
                    <a href="{{ route('admin.verification.index', ['cast_status' => 'all', 'focus' => 'cast']) }}">すべて表示</a>
                </div>
            </div>
            <table class="admin-table" id="cast-section">
                <thead>
                    <tr>
                        <th>キャスト</th>
                        <th>書類種別</th>
                        <th>ステータス</th>
                        <th>提出物</th>
                        <th>理由</th>
                        <th>更新日時</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody id="cast-verification-table">
                    @forelse($castDocuments as $document)
                        <tr data-status="{{ $document['status_key'] }}" data-sort-rank="{{ $document['sort_rank'] }}" data-updated-at="{{ $document['updated_at_sort'] }}" data-keyword="{{ strtolower($document['target_name'].' '.$document['target_id'].' '.$document['type_label']) }}">
                            <td>{{ $document['target_name'] }}<br><span class="text-xs text-gray-400">{{ $document['target_id'] }}</span></td>
                            <td>
                                {{ $document['type_label'] }}
                                @if(!empty($document['expired_at_label']))
                                    <div class="text-xs text-gray-400">有効期限: {{ $document['expired_at_label'] }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="verification-status verification-status-{{ $document['status_key'] }}">{{ $document['status_label'] }}</span>
                                @if(!empty($document['approved_at_label']))
                                    <div class="text-xs text-gray-400">承認日時: {{ $document['approved_at_label'] }}</div>
                                @endif
                            </td>
                            <td>
                                @if(!empty($document['front_url']))
                                    <a href="{{ $document['front_url'] }}" target="_blank" rel="noopener">表面</a>
                                @endif
                                @if(!empty($document['back_url']))
                                    <br><a href="{{ $document['back_url'] }}" target="_blank" rel="noopener">裏面</a>
                                @endif
                            </td>
                            <td>
                                @if(!empty($document['ng_reason']))
                                    <div class="verification-comment">{{ $document['ng_reason'] }}</div>
                                @else
                                    <span class="text-xs text-gray-400">コメントなし</span>
                                @endif
                            </td>
                            <td>{{ $document['updated_at_label'] ?: '-' }}</td>
                            <td style="min-width:220px;">
                                @if($document['status_code'] !== 2)
                                    <form method="POST" action="{{ route('admin.verification.cast.approve', ['document' => $document['id']]) }}" style="display:inline-block; margin-bottom:8px;">
                                        @csrf
                                        <button type="submit" class="btn-action manage">承認する</button>
                                    </form>
                                    <button
                                        type="button"
                                        class="btn-action btn-action-secondary verification-reject-trigger"
                                        data-reject-action="{{ route('admin.verification.cast.reject', ['document' => $document['id']]) }}"
                                        data-reject-title="キャスト本人確認書類を却下"
                                        data-reject-subject="{{ $document['target_name'] }} / {{ $document['type_label'] }}"
                                        data-template-group="document_reject_cast"
                                        style="margin-top:8px;"
                                    >
                                        却下する
                                    </button>
                                @else
                                    <span class="text-xs text-gray-400">承認済み</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-gray-400">提出された本人確認書類はありません。</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <section class="admin-panel">
            <h2 class="admin-panel-title">店舗提出書類</h2>
            <p class="text-xs text-gray-400" style="margin-bottom:12px;">並び順: 未承認 → 不備・却下 → 承認済み / 同一ステータス内は更新日時の新しい順</p>
            <div class="verification-filters">
                <label>
                    <span>ステータス</span>
                    <select class="verification-filter" data-target-table="shop-verification-table" data-filter-key="status">
                        <option value="all" {{ request('shop_status') === 'all' ? 'selected' : '' }}>すべて</option>
                        <option value="pending" {{ request('shop_status', 'pending') === 'pending' ? 'selected' : '' }}>未承認</option>
                        <option value="rejected" {{ request('shop_status') === 'rejected' ? 'selected' : '' }}>不備・却下</option>
                        <option value="approved" {{ request('shop_status') === 'approved' ? 'selected' : '' }}>承認済み</option>
                    </select>
                </label>
                <label>
                    <span>検索</span>
                    <input type="text" class="verification-filter" data-target-table="shop-verification-table" data-filter-key="keyword" placeholder="店舗名 / ID / 書類種別" value="{{ request('shop_keyword', '') }}">
                </label>
                <label>
                    <span>有効期限</span>
                    <select class="verification-filter" data-target-table="shop-verification-table" data-filter-key="expiry">
                        <option value="all">すべて</option>
                        <option value="expired">期限切れ</option>
                        <option value="within_3_months">3か月以内</option>
                    </select>
                </label>
                <div class="verification-filter-count">表示件数: <strong data-count-for="shop-verification-table">{{ count($shopDocuments) }}</strong></div>
                <div class="verification-filter-links">
                    <a href="{{ route('admin.verification.index', ['shop_status' => 'pending', 'focus' => 'shop']) }}">未承認だけ表示</a>
                    <a href="{{ route('admin.verification.index', ['shop_status' => 'all', 'focus' => 'shop']) }}">すべて表示</a>
                </div>
            </div>
            <table class="admin-table" id="shop-section">
                <thead>
                    <tr>
                        <th>店舗</th>
                        <th>書類</th>
                        <th>ステータス</th>
                        <th>提出物</th>
                        <th>理由</th>
                        <th>更新日時</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody id="shop-verification-table">
                    @forelse($shopDocuments as $document)
                        <tr data-status="{{ $document['status_key'] }}" data-expiry="{{ $document['expiry_filter_key'] ?? 'none' }}" data-sort-rank="{{ $document['sort_rank'] }}" data-updated-at="{{ $document['updated_at_sort'] }}" data-keyword="{{ strtolower($document['target_name'].' '.$document['target_id'].' '.$document['type_label']) }}">
                            <td>{{ $document['target_name'] }}<br><span class="text-xs text-gray-400">{{ $document['target_id'] }}</span></td>
                            <td>
                                {{ $document['type_label'] }}
                                @if(!empty($document['expired_at_label']))
                                    <div class="text-xs text-gray-400">有効期限: {{ $document['expired_at_label'] }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="verification-status verification-status-{{ $document['status_key'] }}">{{ $document['status_label'] }}</span>
                                @if(!empty($document['approved_at_label']))
                                    <div class="text-xs text-gray-400">承認日時: {{ $document['approved_at_label'] }}</div>
                                @endif
                            </td>
                            <td>
                                @if(!empty($document['file_url']))
                                    <a href="{{ $document['file_url'] }}" target="_blank" rel="noopener">ファイルを確認</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if(!empty($document['ng_reason']))
                                    <div class="verification-comment">{{ $document['ng_reason'] }}</div>
                                @else
                                    <span class="text-xs text-gray-400">コメントなし</span>
                                @endif
                            </td>
                            <td>{{ $document['updated_at_label'] ?: '-' }}</td>
                            <td style="min-width:220px;">
                                @if($document['status_code'] !== 2)
                                    <form method="POST" action="{{ route('admin.verification.shopdoc.approve', ['document' => $document['id']]) }}" style="display:inline-block; margin-bottom:8px;">
                                        @csrf
                                        <button type="submit" class="btn-action manage">承認する</button>
                                    </form>
                                    <button
                                        type="button"
                                        class="btn-action btn-action-secondary verification-reject-trigger"
                                        data-reject-action="{{ route('admin.verification.shopdoc.reject', ['document' => $document['id']]) }}"
                                        data-reject-title="店舗提出書類を却下"
                                        data-reject-subject="{{ $document['target_name'] }} / {{ $document['type_label'] }}"
                                        data-template-group="document_reject_shop"
                                        style="margin-top:8px;"
                                    >
                                        却下する
                                    </button>
                                @else
                                    <span class="text-xs text-gray-400">承認済み</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-gray-400">提出された店舗書類はありません。</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </div>

    <div id="verification-reject-modal" class="verification-modal-overlay" style="display:none;">
        <div class="verification-modal" role="dialog" aria-modal="true" aria-labelledby="verification-reject-title">
            <div class="verification-modal-head">
                <h3 id="verification-reject-title">書類を却下</h3>
                <button type="button" class="verification-modal-close" aria-label="閉じる" onclick="closeRejectModal()">&times;</button>
            </div>
            <div class="verification-modal-body">
                <p id="verification-reject-subject" class="text-sm text-gray-500" style="margin-bottom:12px;"></p>
                <div>
                    <div class="text-xs text-gray-400" style="margin-bottom:8px;">テンプレート</div>
                    <div id="verification-reject-template-list" class="verification-template-list"></div>
                </div>
                <form id="verification-reject-form" method="POST">
                    @csrf
                    <input type="hidden" name="reject_action" id="verification-reject-action-input" value="{{ old('reject_action', '') }}">
                    <textarea
                        name="ng_reason"
                        id="verification-reject-reason"
                        class="form-control"
                        rows="5"
                        placeholder="差し戻し理由を入力"
                    >{{ old('ng_reason') }}</textarea>
                    <div class="verification-modal-actions">
                        <button type="button" class="btn-action btn-action-secondary" onclick="closeRejectModal()">キャンセル</button>
                        <button type="submit" class="btn-action manage">却下する</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .verification-filters {
        display: flex;
        gap: 12px;
        align-items: end;
        flex-wrap: wrap;
        margin-bottom: 16px;
    }
    .verification-filters label {
        display: flex;
        flex-direction: column;
        gap: 6px;
        min-width: 220px;
    }
    .verification-filters select,
    .verification-filters input {
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 10px 12px;
        background: #fff;
    }
    .verification-filter-count {
        color: #6b7280;
        padding-bottom: 10px;
    }
    .verification-filter-links {
        display: flex;
        gap: 12px;
        padding-bottom: 10px;
    }
    .verification-filter-links a {
        color: #8b5e00;
        text-decoration: none;
        font-size: 0.9rem;
    }
    .verification-comment {
        white-space: pre-wrap;
        line-height: 1.5;
    }
    .verification-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.55);
        z-index: 999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
    }
    .verification-modal {
        width: min(100%, 560px);
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.25);
        overflow: hidden;
    }
    .verification-modal-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid #e5e7eb;
    }
    .verification-modal-body {
        padding: 20px;
    }
    .verification-modal-close {
        border: 0;
        background: transparent;
        font-size: 1.5rem;
        line-height: 1;
        cursor: pointer;
        color: #6b7280;
    }
    .verification-modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 16px;
    }
    .verification-template-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 12px;
    }
    .verification-template-button {
        border: 1px solid #d1d5db;
        background: #f8fafc;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 0.85rem;
        cursor: pointer;
    }
    .verification-status {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.85rem;
        font-weight: 700;
    }
    .verification-status-pending {
        background: #fff7ed;
        color: #c2410c;
    }
    .verification-status-rejected {
        background: #fef2f2;
        color: #b91c1c;
    }
    .verification-status-approved {
        background: #ecfdf5;
        color: #047857;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var rejectTemplates = @json($rejectTemplates ?? []);
    var rejectModal = document.getElementById('verification-reject-modal');
    var rejectForm = document.getElementById('verification-reject-form');
    var rejectTitle = document.getElementById('verification-reject-title');
    var rejectSubject = document.getElementById('verification-reject-subject');
    var rejectReason = document.getElementById('verification-reject-reason');
    var rejectTemplateList = document.getElementById('verification-reject-template-list');

    function renderRejectTemplates(group) {
        if (!rejectTemplateList) return;
        rejectTemplateList.innerHTML = '';
        (rejectTemplates[group] || []).forEach(function (template) {
            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'verification-template-button';
            button.textContent = template.title || 'テンプレート';
            button.addEventListener('click', function () {
                if (rejectReason) {
                    rejectReason.value = template.body || '';
                    rejectReason.focus();
                }
            });
            rejectTemplateList.appendChild(button);
        });
    }

    window.openRejectModal = function (action, title, subject, templateGroup) {
        if (!rejectModal || !rejectForm) return;
        rejectForm.setAttribute('action', action || '');
        document.getElementById('verification-reject-action-input').value = action || '';
        if (rejectTitle) rejectTitle.textContent = title || '書類を却下';
        if (rejectSubject) rejectSubject.textContent = subject || '';
        renderRejectTemplates(templateGroup || '');
        rejectModal.style.display = 'flex';
        if (rejectReason) {
            setTimeout(function () { rejectReason.focus(); }, 0);
        }
    };

    window.closeRejectModal = function () {
        if (!rejectModal) return;
        rejectModal.style.display = 'none';
    };

    document.querySelectorAll('.verification-reject-trigger').forEach(function (button) {
        button.addEventListener('click', function () {
            openRejectModal(
                button.dataset.rejectAction,
                button.dataset.rejectTitle,
                button.dataset.rejectSubject,
                button.dataset.templateGroup
            );
        });
    });

    if (rejectModal) {
        rejectModal.addEventListener('click', function (event) {
            if (event.target === rejectModal) {
                closeRejectModal();
            }
        });
    }

    function sortRows(tableId) {
        var tbody = document.getElementById(tableId);
        if (!tbody) return;
        var rows = Array.from(tbody.querySelectorAll('tr[data-status]'));
        rows.sort(function (a, b) {
            var rankA = Number(a.dataset.sortRank || 999);
            var rankB = Number(b.dataset.sortRank || 999);
            if (rankA !== rankB) return rankA - rankB;
            var updatedA = Number(a.dataset.updatedAt || 0);
            var updatedB = Number(b.dataset.updatedAt || 0);
            return updatedB - updatedA;
        });
        rows.forEach(function (row) {
            tbody.appendChild(row);
        });
    }

    function applyFilters(tableId) {
        var tbody = document.getElementById(tableId);
        if (!tbody) return;

        var filters = document.querySelectorAll('.verification-filter[data-target-table="' + tableId + '"]');
        var values = { status: '', keyword: '', expiry: '' };
        filters.forEach(function (filter) {
            values[filter.dataset.filterKey] = (filter.value || '').toLowerCase().trim();
        });

        var rows = tbody.querySelectorAll('tr[data-status]');
        var visible = 0;
        rows.forEach(function (row) {
            var matchesStatus = !values.status || values.status === 'all' || row.dataset.status === values.status;
            var keyword = row.dataset.keyword || '';
            var matchesKeyword = !values.keyword || keyword.indexOf(values.keyword) !== -1;
            var matchesExpiry = !values.expiry || values.expiry === 'all' || row.dataset.expiry === values.expiry;
            var show = matchesStatus && matchesKeyword && matchesExpiry;
            row.hidden = !show;
            if (show) visible++;
        });

        var counter = document.querySelector('[data-count-for="' + tableId + '"]');
        if (counter) counter.textContent = String(visible);
    }

    document.querySelectorAll('.verification-filter').forEach(function (filter) {
        filter.addEventListener('input', function () {
            applyFilters(filter.dataset.targetTable);
        });
        filter.addEventListener('change', function () {
            applyFilters(filter.dataset.targetTable);
        });
    });

    sortRows('cast-verification-table');
    sortRows('shop-verification-table');
    applyFilters('cast-verification-table');
    applyFilters('shop-verification-table');

    var focus = @json(request('focus'));
    if (focus === 'cast') {
        var castSection = document.getElementById('cast-section');
        if (castSection) castSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    if (focus === 'shop') {
        var shopSection = document.getElementById('shop-section');
        if (shopSection) shopSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    var oldRejectAction = @json(old('reject_action'));
    if (oldRejectAction) {
        var matchedButton = null;
        document.querySelectorAll('.verification-reject-trigger').forEach(function (button) {
            if (!matchedButton && button.dataset.rejectAction === oldRejectAction) {
                matchedButton = button;
            }
        });
        openRejectModal(
            oldRejectAction,
            matchedButton ? matchedButton.dataset.rejectTitle : '書類を却下',
            matchedButton ? matchedButton.dataset.rejectSubject : '',
            matchedButton ? matchedButton.dataset.templateGroup : ''
        );
    }
});
</script>
@endpush

