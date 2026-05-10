@extends('layouts.admin')

@section('title', '本人確認・書類審査')

@push('admin-styles')
<style>
.verification-profile {
    font-size: 0.82rem;
    line-height: 1.7;
    color: #f1e6c4;
    min-width: 220px;
}
.verification-profile-label {
    display: inline-block;
    min-width: 5em;
    margin-right: 6px;
    color: #c9b8b8;
    font-size: 0.72rem;
}
</style>
@endpush

@section('content')
    @php
        $castDocs = $castDocuments ?? [];
        $shopDocs = $shopDocuments ?? [];
        $countByStatus = function (array $docs, string $key): int {
            $n = 0;
            foreach ($docs as $d) {
                if (($d['status_key'] ?? '') === $key) $n++;
            }
            return $n;
        };
        $shopExpiredCount = 0;
        foreach ($shopDocs as $d) {
            if (($d['expiry_filter_key'] ?? '') === 'expired') $shopExpiredCount++;
        }
        $defaultTab = request('focus') === 'shop' ? 'shop' : 'cast';

        // アクター判定: pending=運営対応 / rejected=ユーザー再提出待ち / approved=完了
        $resolveActor = function (string $statusKey, string $userType): array {
            return match ($statusKey) {
                'pending' => ['cls' => 'is-admin', 'icon' => 'fa-bell', 'label' => '運営対応'],
                'rejected' => $userType === 'cast'
                    ? ['cls' => 'is-cast', 'icon' => 'fa-user', 'label' => 'キャスト再提出待ち']
                    : ['cls' => 'is-shop', 'icon' => 'fa-store', 'label' => '店舗再提出待ち'],
                'approved' => ['cls' => 'is-done', 'icon' => 'fa-circle-check', 'label' => '完了'],
                default => ['cls' => 'is-admin-soft', 'icon' => 'fa-circle-question', 'label' => '—'],
            };
        };
    @endphp

    <div class="admin-page">
        <div class="u-flex-between">
            @include('admin.parts.page-title', ['eyebrow' => 'KYC & DOCUMENTS', 'title' => '本人確認・書類審査'])
            @include('admin.parts.operation-achievement', ['operationAchievementRoute' => 'admin.verification.index'])
        </div>

        @if(session('status'))
            <div class="admin-alert admin-alert-success">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="admin-alert admin-alert-error">{{ $errors->first() }}</div>
        @endif

        {{-- サマリー：未処理件数を強調表示 --}}
        <section class="dashboard-kpi-grid">
            <article class="dashboard-kpi-card">
                <div class="dashboard-kpi-head">
                    <div class="dashboard-kpi-title">本人確認 未処理</div>
                    <i class="fas fa-id-card"></i>
                </div>
                <div class="dashboard-kpi-main">
                    <span class="dashboard-kpi-value">{{ $summary['cast_pending'] ?? 0 }}</span>
                    <span class="dashboard-kpi-unit">件</span>
                </div>
            </article>
            <article class="dashboard-kpi-card">
                <div class="dashboard-kpi-head">
                    <div class="dashboard-kpi-title">店舗書類 未処理</div>
                    <i class="fas fa-folder-open"></i>
                </div>
                <div class="dashboard-kpi-main">
                    <span class="dashboard-kpi-value">{{ $summary['shop_pending'] ?? 0 }}</span>
                    <span class="dashboard-kpi-unit">件</span>
                </div>
            </article>
            <article class="dashboard-kpi-card">
                <div class="dashboard-kpi-head">
                    <div class="dashboard-kpi-title">店舗書類 期限切れ</div>
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
                <div class="dashboard-kpi-main">
                    <span class="dashboard-kpi-value">{{ $shopExpiredCount }}</span>
                    <span class="dashboard-kpi-unit">件</span>
                </div>
            </article>
        </section>

        {{-- タブ切替（キャスト / 店舗） --}}
        <div class="admin-tabs" role="tablist">
            <button type="button" class="admin-tab {{ $defaultTab === 'cast' ? 'is-active' : '' }}" data-verif-tab="cast" role="tab">
                <i class="fas fa-user"></i>
                <span>キャスト本人確認</span>
                <span class="admin-tab-badge {{ ($summary['cast_pending'] ?? 0) > 0 ? 'is-alert' : '' }}">{{ count($castDocs) }}</span>
            </button>
            <button type="button" class="admin-tab {{ $defaultTab === 'shop' ? 'is-active' : '' }}" data-verif-tab="shop" role="tab">
                <i class="fas fa-store"></i>
                <span>店舗提出書類</span>
                <span class="admin-tab-badge {{ ($summary['shop_pending'] ?? 0) > 0 ? 'is-alert' : '' }}">{{ count($shopDocs) }}</span>
            </button>
        </div>

        {{-- ===========================
             キャストタブパネル
             =========================== --}}
        <div class="admin-tab-panel {{ $defaultTab === 'cast' ? 'is-active' : '' }}" data-verif-panel="cast" id="cast-section">
            <div class="admin-page-toolbar">
                <div class="admin-page-toolbar-row">
                    <div class="admin-page-toolbar-search">
                        <i class="fas fa-magnifying-glass"></i>
                        <input type="text" class="verification-filter" data-target-table="cast-verification-table" data-filter-key="keyword" placeholder="名前・ID・書類種別で検索" value="{{ request('cast_keyword', '') }}">
                    </div>
                </div>
                <div class="admin-page-toolbar-filters">
                    <button type="button" class="admin-filter-chip {{ request('cast_status', 'pending') === 'pending' ? 'is-active' : '' }}"
                        data-verif-quickfilter="pending" data-target-table="cast-verification-table">
                        <span>未承認</span>
                        <strong>{{ $countByStatus($castDocs, 'pending') }}</strong>
                    </button>
                    <button type="button" class="admin-filter-chip {{ request('cast_status') === 'rejected' ? 'is-active' : '' }}"
                        data-verif-quickfilter="rejected" data-target-table="cast-verification-table">
                        <span>不備・却下</span>
                        <strong>{{ $countByStatus($castDocs, 'rejected') }}</strong>
                    </button>
                    <button type="button" class="admin-filter-chip {{ request('cast_status') === 'approved' ? 'is-active' : '' }}"
                        data-verif-quickfilter="approved" data-target-table="cast-verification-table">
                        <span>承認済み</span>
                        <strong>{{ $countByStatus($castDocs, 'approved') }}</strong>
                    </button>
                    <button type="button" class="admin-filter-chip {{ request('cast_status') === 'all' ? 'is-active' : '' }}"
                        data-verif-quickfilter="all" data-target-table="cast-verification-table">
                        <span>すべて</span>
                        <strong>{{ count($castDocs) }}</strong>
                    </button>
                </div>
            </div>

            <div class="table-wrapper u-mt-12">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>キャスト</th>
                            <th>登録情報（書類との照合用）</th>
                            <th>書類種別</th>
                            <th>ステータス</th>
                            <th>提出物</th>
                            <th>理由</th>
                            <th>更新日時</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody id="cast-verification-table">
                        @forelse($castDocs as $document)
                            @php $castActor = $resolveActor($document['status_key'], 'cast'); @endphp
                            <tr data-status="{{ $document['status_key'] }}" data-sort-rank="{{ $document['sort_rank'] }}" data-updated-at="{{ $document['updated_at_sort'] }}" data-keyword="{{ strtolower(($document['target_name'] ?? '').' '.($document['target_id'] ?? '').' '.($document['real_name'] ?? '').' '.($document['type_label'] ?? '')) }}">
                                <td>
                                    <strong>{{ $document['target_name'] ?: '—' }}</strong>
                                    <div class="text-xs text-muted">{{ $document['target_id'] }}</div>
                                    @if(!empty($document['email']))
                                        <div class="text-xs text-muted">{{ $document['email'] }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="verification-profile">
                                        @if(!empty($document['real_name']))
                                            <div><span class="verification-profile-label">氏名</span>{{ $document['real_name'] }}</div>
                                        @endif
                                        @if(!empty($document['birthday']))
                                            <div><span class="verification-profile-label">生年月日</span>{{ $document['birthday'] }}</div>
                                        @endif
                                        @if(!empty($document['tel']))
                                            <div><span class="verification-profile-label">電話</span>{{ $document['tel'] }}</div>
                                        @endif
                                        @if(!empty($document['zip']) || !empty($document['address']))
                                            <div>
                                                <span class="verification-profile-label">住所</span>
                                                @if(!empty($document['zip']))〒{{ $document['zip'] }} @endif
                                                {{ $document['address'] }}
                                            </div>
                                        @endif
                                        @if(empty($document['real_name']) && empty($document['birthday']) && empty($document['tel']) && empty($document['address']))
                                            <span class="text-xs text-muted">プロフィール詳細未入力</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if(!empty($document['category_label']) && $document['category_label'] !== '—')
                                        <div class="text-xs text-muted">{{ $document['category_label'] }}</div>
                                    @endif
                                    {{ $document['type_label'] }}
                                    @if(!empty($document['expired_at_label']))
                                        <div class="text-xs text-muted">有効期限: {{ $document['expired_at_label'] }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="actor-pill {{ $castActor['cls'] }}">
                                        <i class="fas {{ $castActor['icon'] }}"></i> {{ $castActor['label'] }}
                                    </span>
                                    <div class="u-mt-4">
                                        <span class="verification-status verification-status-{{ $document['status_key'] }}">{{ $document['status_label'] }}</span>
                                    </div>
                                    @if(!empty($document['approved_at_label']))
                                        <div class="text-xs text-muted u-mt-4">承認日時: {{ $document['approved_at_label'] }}</div>
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
                                        <span class="text-xs text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-sm text-muted">{{ $document['updated_at_label'] ?: '-' }}</td>
                                <td style="min-width:200px;">
                                    @if($document['status_code'] !== 2)
                                        <form method="POST" action="{{ route('admin.verification.cast.approve', ['document' => $document['id']]) }}" style="display:inline-block; margin-bottom:6px;">
                                            @csrf
                                            <button type="submit" class="btn-action manage">承認</button>
                                        </form>
                                        <button type="button"
                                            class="btn-action btn-action-secondary verification-reject-trigger"
                                            data-reject-action="{{ route('admin.verification.cast.reject', ['document' => $document['id']]) }}"
                                            data-reject-title="キャスト本人確認書類を却下"
                                            data-reject-subject="{{ $document['target_name'] }} / {{ $document['type_label'] }}"
                                            data-template-group="document_reject_cast">
                                            却下
                                        </button>
                                    @else
                                        <span class="text-xs text-muted">承認済み</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">提出された本人確認書類はありません。</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ===========================
             店舗タブパネル
             =========================== --}}
        <div class="admin-tab-panel {{ $defaultTab === 'shop' ? 'is-active' : '' }}" data-verif-panel="shop" id="shop-section">
            <div class="admin-page-toolbar">
                <div class="admin-page-toolbar-row">
                    <div class="admin-page-toolbar-search">
                        <i class="fas fa-magnifying-glass"></i>
                        <input type="text" class="verification-filter" data-target-table="shop-verification-table" data-filter-key="keyword" placeholder="店舗名・ID・書類種別で検索" value="{{ request('shop_keyword', '') }}">
                    </div>
                </div>
                <div class="admin-page-toolbar-filters">
                    <button type="button" class="admin-filter-chip {{ request('shop_status', 'pending') === 'pending' ? 'is-active' : '' }}"
                        data-verif-quickfilter="pending" data-target-table="shop-verification-table">
                        <span>未承認</span>
                        <strong>{{ $countByStatus($shopDocs, 'pending') }}</strong>
                    </button>
                    <button type="button" class="admin-filter-chip {{ request('shop_status') === 'rejected' ? 'is-active' : '' }}"
                        data-verif-quickfilter="rejected" data-target-table="shop-verification-table">
                        <span>不備・却下</span>
                        <strong>{{ $countByStatus($shopDocs, 'rejected') }}</strong>
                    </button>
                    <button type="button" class="admin-filter-chip {{ request('shop_status') === 'approved' ? 'is-active' : '' }}"
                        data-verif-quickfilter="approved" data-target-table="shop-verification-table">
                        <span>承認済み</span>
                        <strong>{{ $countByStatus($shopDocs, 'approved') }}</strong>
                    </button>
                    <button type="button" class="admin-filter-chip {{ request('shop_status') === 'all' ? 'is-active' : '' }}"
                        data-verif-quickfilter="all" data-target-table="shop-verification-table">
                        <span>すべて</span>
                        <strong>{{ count($shopDocs) }}</strong>
                    </button>
                    <button type="button" class="admin-filter-chip {{ $shopExpiredCount > 0 ? '' : '' }}"
                        data-verif-expiry-filter="expired" data-target-table="shop-verification-table">
                        <i class="fas fa-triangle-exclamation"></i>
                        <span>期限切れのみ</span>
                        <strong>{{ $shopExpiredCount }}</strong>
                    </button>
                </div>
            </div>

            <div class="table-wrapper u-mt-12">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>店舗</th>
                            <th>登録情報（書類との照合用）</th>
                            <th>書類</th>
                            <th>ステータス</th>
                            <th>提出物</th>
                            <th>理由</th>
                            <th>更新日時</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody id="shop-verification-table">
                        @forelse($shopDocs as $document)
                            @php $shopActor = $resolveActor($document['status_key'], 'shop'); @endphp
                            <tr data-status="{{ $document['status_key'] }}" data-expiry="{{ $document['expiry_filter_key'] ?? 'none' }}" data-sort-rank="{{ $document['sort_rank'] }}" data-updated-at="{{ $document['updated_at_sort'] }}" data-keyword="{{ strtolower(($document['target_name'] ?? '').' '.($document['target_id'] ?? '').' '.($document['type_label'] ?? '')) }}">
                                <td>
                                    <strong>{{ $document['target_name'] ?: '—' }}</strong>
                                    <div class="text-xs text-muted">{{ $document['target_id'] }}</div>
                                    @if(!empty($document['email']))
                                        <div class="text-xs text-muted">{{ $document['email'] }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="verification-profile">
                                        @if(!empty($document['shop_name']))
                                            <div><span class="verification-profile-label">店舗名</span>{{ $document['shop_name'] }}</div>
                                        @endif
                                        @if(!empty($document['tel']))
                                            <div><span class="verification-profile-label">電話</span>{{ $document['tel'] }}</div>
                                        @endif
                                        @if(!empty($document['zip']) || !empty($document['address']))
                                            <div>
                                                <span class="verification-profile-label">住所</span>
                                                @if(!empty($document['zip']))〒{{ $document['zip'] }} @endif
                                                {{ $document['address'] }}
                                            </div>
                                        @endif
                                        @if(empty($document['shop_name']) && empty($document['tel']) && empty($document['address']))
                                            <span class="text-xs text-muted">プロフィール未入力</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    {{ $document['type_label'] }}
                                    @if(!empty($document['expired_at_label']))
                                        <div class="text-xs {{ ($document['expiry_filter_key'] ?? '') === 'expired' ? '' : 'text-muted' }}">
                                            @if(($document['expiry_filter_key'] ?? '') === 'expired')
                                                <span class="admin-status-badge is-danger">期限切れ</span>
                                            @endif
                                            有効期限: {{ $document['expired_at_label'] }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="actor-pill {{ $shopActor['cls'] }}">
                                        <i class="fas {{ $shopActor['icon'] }}"></i> {{ $shopActor['label'] }}
                                    </span>
                                    <div class="u-mt-4">
                                        <span class="verification-status verification-status-{{ $document['status_key'] }}">{{ $document['status_label'] }}</span>
                                    </div>
                                    @if(!empty($document['approved_at_label']))
                                        <div class="text-xs text-muted u-mt-4">承認日時: {{ $document['approved_at_label'] }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($document['file_url']))
                                        <a href="{{ $document['file_url'] }}" target="_blank" rel="noopener">ファイル確認</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($document['ng_reason']))
                                        <div class="verification-comment">{{ $document['ng_reason'] }}</div>
                                    @else
                                        <span class="text-xs text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-sm text-muted">{{ $document['updated_at_label'] ?: '-' }}</td>
                                <td style="min-width:200px;">
                                    @if($document['status_code'] !== 2)
                                        <form method="POST" action="{{ route('admin.verification.shopdoc.approve', ['document' => $document['id']]) }}" style="display:inline-block; margin-bottom:6px;">
                                            @csrf
                                            <button type="submit" class="btn-action manage">承認</button>
                                        </form>
                                        <button type="button"
                                            class="btn-action btn-action-secondary verification-reject-trigger"
                                            data-reject-action="{{ route('admin.verification.shopdoc.reject', ['document' => $document['id']]) }}"
                                            data-reject-title="店舗提出書類を却下"
                                            data-reject-subject="{{ $document['target_name'] }} / {{ $document['type_label'] }}"
                                            data-template-group="document_reject_shop">
                                            却下
                                        </button>
                                    @else
                                        <span class="text-xs text-muted">承認済み</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">提出された店舗書類はありません。</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- 却下モーダル --}}
    <div id="verification-reject-modal" class="verification-modal-overlay" style="display:none;">
        <div class="verification-modal" role="dialog" aria-modal="true" aria-labelledby="verification-reject-title">
            <div class="verification-modal-head">
                <h3 id="verification-reject-title">書類を却下</h3>
                <button type="button" class="verification-modal-close" aria-label="閉じる" onclick="closeRejectModal()">&times;</button>
            </div>
            <div class="verification-modal-body">
                <p id="verification-reject-subject" class="text-sm text-muted u-mb-12"></p>
                <div>
                    <div class="text-xs text-muted u-mb-8">テンプレート</div>
                    <div id="verification-reject-template-list" class="verification-template-list"></div>
                </div>
                <form id="verification-reject-form" method="POST">
                    @csrf
                    <input type="hidden" name="reject_action" id="verification-reject-action-input" value="{{ old('reject_action', '') }}">
                    <textarea name="ng_reason" id="verification-reject-reason" class="form-control" rows="5" placeholder="差し戻し理由を入力">{{ old('ng_reason') }}</textarea>
                    <div class="verification-modal-actions">
                        <button type="button" class="btn-action btn-action-secondary" onclick="closeRejectModal()">キャンセル</button>
                        <button type="submit" class="btn-action manage">却下する</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('admin-scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ============== タブ切替 ==============
    var tabs = document.querySelectorAll('[data-verif-tab]');
    var panels = document.querySelectorAll('[data-verif-panel]');
    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            var key = tab.getAttribute('data-verif-tab');
            tabs.forEach(function (t) { t.classList.toggle('is-active', t === tab); });
            panels.forEach(function (p) { p.classList.toggle('is-active', p.getAttribute('data-verif-panel') === key); });
        });
    });

    // ============== 却下モーダル ==============
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
        if (rejectReason) setTimeout(function () { rejectReason.focus(); }, 0);
    };
    window.closeRejectModal = function () {
        if (!rejectModal) return;
        rejectModal.style.display = 'none';
    };
    document.querySelectorAll('.verification-reject-trigger').forEach(function (button) {
        button.addEventListener('click', function () {
            openRejectModal(button.dataset.rejectAction, button.dataset.rejectTitle, button.dataset.rejectSubject, button.dataset.templateGroup);
        });
    });
    if (rejectModal) {
        rejectModal.addEventListener('click', function (e) {
            if (e.target === rejectModal) closeRejectModal();
        });
    }

    // ============== フィルタリング ==============
    var filterState = {
        'cast-verification-table': { status: '{{ request('cast_status', 'pending') }}', keyword: '{{ request('cast_keyword', '') }}', expiry: 'all' },
        'shop-verification-table': { status: '{{ request('shop_status', 'pending') }}', keyword: '{{ request('shop_keyword', '') }}', expiry: 'all' },
    };

    function applyFilters(tableId) {
        var tbody = document.getElementById(tableId);
        if (!tbody) return;
        var state = filterState[tableId] || {};
        var rows = tbody.querySelectorAll('tr[data-status]');
        rows.forEach(function (row) {
            var matchStatus = !state.status || state.status === 'all' || row.dataset.status === state.status;
            var matchKw = !state.keyword || (row.dataset.keyword || '').indexOf(state.keyword) !== -1;
            var matchExpiry = !state.expiry || state.expiry === 'all' || row.dataset.expiry === state.expiry;
            row.hidden = !(matchStatus && matchKw && matchExpiry);
        });
    }

    document.querySelectorAll('[data-verif-quickfilter]').forEach(function (chip) {
        chip.addEventListener('click', function () {
            var status = chip.getAttribute('data-verif-quickfilter');
            var tableId = chip.getAttribute('data-target-table');
            // 同じテーブルの同じ種類のチップから is-active を外す
            document.querySelectorAll('[data-target-table="' + tableId + '"][data-verif-quickfilter]').forEach(function (c) {
                c.classList.toggle('is-active', c === chip);
            });
            filterState[tableId].status = status;
            applyFilters(tableId);
        });
    });
    document.querySelectorAll('[data-verif-expiry-filter]').forEach(function (chip) {
        chip.addEventListener('click', function () {
            var tableId = chip.getAttribute('data-target-table');
            var willEnable = !chip.classList.contains('is-active');
            chip.classList.toggle('is-active', willEnable);
            filterState[tableId].expiry = willEnable ? chip.getAttribute('data-verif-expiry-filter') : 'all';
            applyFilters(tableId);
        });
    });
    document.querySelectorAll('.verification-filter[data-filter-key="keyword"]').forEach(function (input) {
        input.addEventListener('input', function () {
            var tableId = input.getAttribute('data-target-table');
            filterState[tableId].keyword = (input.value || '').toLowerCase().trim();
            applyFilters(tableId);
        });
    });

    // 初期適用
    applyFilters('cast-verification-table');
    applyFilters('shop-verification-table');

    // ============== 並び順（未承認→却下→承認 / 同階層は更新日時降順）==============
    function sortRows(tableId) {
        var tbody = document.getElementById(tableId);
        if (!tbody) return;
        var rows = Array.from(tbody.querySelectorAll('tr[data-status]'));
        rows.sort(function (a, b) {
            var rankA = Number(a.dataset.sortRank || 999);
            var rankB = Number(b.dataset.sortRank || 999);
            if (rankA !== rankB) return rankA - rankB;
            return Number(b.dataset.updatedAt || 0) - Number(a.dataset.updatedAt || 0);
        });
        rows.forEach(function (row) { tbody.appendChild(row); });
    }
    sortRows('cast-verification-table');
    sortRows('shop-verification-table');

    // ============== 旧 reject_action のリプレイ ==============
    var oldRejectAction = @json(old('reject_action'));
    if (oldRejectAction) {
        var matched = null;
        document.querySelectorAll('.verification-reject-trigger').forEach(function (button) {
            if (!matched && button.dataset.rejectAction === oldRejectAction) matched = button;
        });
        openRejectModal(
            oldRejectAction,
            matched ? matched.dataset.rejectTitle : '書類を却下',
            matched ? matched.dataset.rejectSubject : '',
            matched ? matched.dataset.templateGroup : ''
        );
    }
});
</script>
@endpush
