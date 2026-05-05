@extends('layouts.app')

@section('title', '求人ステータス管理')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/recruitment.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
@endpush

@section('content')
@php
    $horizontal = !empty($horizontalShopJobs);
@endphp
<div class="recruit-status-page recruit-detail-page animate-fadeIn">
    @if(session('message'))
        <p class="profile-edit-flash recruit-status-flash">{{ session('message') }}</p>
    @endif

    {{-- ========== 1. 採用ステータス（スマホ特化カードUI） ========== --}}
    <section class="recruit-status-mobile-shell recruit-status-section-casts">
        <header class="rsm-header">
            <a href="{{ route('shop.mypage.index') }}" class="rsm-icon-btn" aria-label="戻る">
                <i class="fas fa-chevron-left"></i>
            </a>
            <h2 class="rsm-title serif-font">Recruit Status</h2>
            <div class="rsm-header-actions">
                <button type="button" class="rsm-icon-btn" aria-label="完了">
                    <i class="fas fa-check-circle"></i>
                </button>
                <button type="button" class="rsm-icon-btn rsm-bell" aria-label="通知">
                    <i class="fas fa-bell"></i>
                    <span class="rsm-unread">3</span>
                </button>
                <button type="button" class="rsm-icon-btn" aria-label="メニュー">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </header>

        <div class="rsm-body">
            <div class="rsm-filters">
                <div class="rsm-filter-row">
                    <span class="rsm-filter-label">ステータス</span>
                    <div class="rsm-segment" id="status-tab-group">
                        <button type="button" class="is-active" data-filter-tab="active">進行中</button>
                        <button type="button" data-filter-tab="hired">採用</button>
                        <button type="button" data-filter-tab="rejected">不採用</button>
                    </div>
                </div>
                <div class="rsm-filter-row">
                    <span class="rsm-filter-label">求人種別</span>
                    <div class="rsm-segment" id="pattern-filter-group">
                        <button type="button" class="is-active" data-pattern-filter="all">すべて</button>
                        <button type="button" data-pattern-filter="P1">新規採用</button>
                        <button type="button" data-pattern-filter="P2">ヘルプ</button>
                    </div>
                </div>
                <div class="rsm-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="recruit-search-input" placeholder="名前で検索..." aria-label="名前で検索">
                </div>
            </div>

            <div class="rsm-card-list" id="recruit-status-card-list"></div>

            <template id="recruit-status-card-template">
                <article class="rsm-card">
                    <div class="rsm-delay-line" hidden></div>
                    <div class="rsm-top">
                        <div class="rsm-main">
                            <h3 class="rsm-name"></h3>
                            <div class="rsm-pattern"></div>
                            <div class="rsm-delay" hidden>
                                <i class="fas fa-exclamation-circle"></i>
                                <span class="rsm-delay-text"></span>
                            </div>
                        </div>
                        <div class="rsm-side">
                            <span class="rsm-status"></span>
                            <a class="rsm-talk-link" href="{{ route('shop.talk.index') }}" aria-label="トークルームへ">
                                <i class="fas fa-comment-dots"></i>
                            </a>
                        </div>
                    </div>
                    <div class="rsm-details">
                        <div class="rsm-detail-row">
                            <span>応募日</span>
                            <strong data-field="appliedAt"></strong>
                        </div>
                        <div class="rsm-detail-row">
                            <span>面談日</span>
                            <strong data-field="interviewDate"></strong>
                        </div>
                        <div class="rsm-detail-row" data-field-wrap="hiredDate" hidden>
                            <span>採用日</span>
                            <strong data-field="hiredDate"></strong>
                        </div>
                        <div class="rsm-reason-wrap" data-field-wrap="rejectionReason" hidden>
                            <span>不採用理由</span>
                            <p data-field="rejectionReason"></p>
                        </div>
                    </div>
                </article>
            </template>

            <div class="recruit-status-empty recruit-status-empty-small" id="recruit-status-empty-mobile" hidden>
                <p class="recruit-status-empty-text">該当するキャストがいません。</p>
            </div>
        </div>
    </section>

    {{-- ========== 2. 公開中の求人情報（編集・プレビュー） ========== --}}
    <section class="recruit-status-section recruit-status-section-job">
        <h2 class="recruit-status-section-title">
            <span class="recruit-status-section-icon"><i class="fas fa-briefcase"></i></span>
            公開中の求人情報
        </h2>
        <p class="recruit-status-section-desc">現在の求人内容の確認、編集、プレビューができます。</p>

        <article class="recruit-status-card">
            <div class="recruit-status-card-head">
                <div class="recruit-status-card-info">
                    @if($horizontal)
                        @php
                            $rReg = (int) ($recruit['regular_status'] ?? 0);
                            $rTrial = (int) ($recruit['trial_status'] ?? 0);
                            $rHelp = (int) ($recruit['help_status'] ?? 0);
                            $hourlyHead = (int) ($recruit['regular_hourly_wage'] ?? $recruit['hourly_wage_regular'] ?? 0);
                        @endphp
                        <span class="status-badge {{ $rReg === 1 ? 'status-active' : 'status-inactive' }}">
                            本入 {{ $rReg === 1 ? '公開' : '非公開' }} · 体験 {{ $rTrial === 1 ? '公開' : '非公開' }} · ヘルプ {{ $rHelp === 1 ? '公開' : '非公開' }}
                        </span>
                    @else
                        <span class="status-badge {{ ($recruit['status'] ?? 'active') === 'active' ? 'status-active' : 'status-inactive' }}">
                            {{ ($recruit['status'] ?? 'active') === 'active' ? '求人公開中' : '公開停止中' }}
                        </span>
                    @endif
                    <h3 class="recruit-status-card-title">{{ $recruit['catch_copy'] ?: '求人情報を設定してください' }}</h3>
                    <p class="recruit-status-card-meta">本入時給: ¥{{ number_format($horizontal ? $hourlyHead : ($recruit['hourly_wage_regular'] ?? 0)) }}〜</p>
                    @if(!empty($recruit['updated_at']))
                        <p class="recruit-status-card-meta">更新日: {{ $recruit['updated_at'] }}</p>
                    @endif
                </div>
                <div class="recruit-status-card-head-actions">
                    @if(!empty($shareUrl))
                    <button type="button" class="recruit-share-icon-btn" id="recruit-share-icon-open" aria-label="求人を共有">
                        <i class="fas fa-share-nodes"></i>
                    </button>
                    @endif
                    @if($horizontal)
                        <div style="display:flex;flex-direction:column;gap:8px;align-items:flex-end;">
                            @foreach ([1 => '本入', 2 => '体験', 3 => 'ヘルプ'] as $jt => $jtLabel)
                                @php
                                    $on = match ((int) $jt) {
                                        2 => $rTrial === 1,
                                        3 => $rHelp === 1,
                                        default => $rReg === 1,
                                    };
                                @endphp
                                <form action="{{ route('shop.recruits.toggle-status') }}" method="POST" style="margin:0;">
                                    @csrf
                                    <input type="hidden" name="job_type" value="{{ $jt }}">
                                    <button
                                        type="submit"
                                        class="toggle-btn {{ $on ? 'active' : '' }}"
                                        aria-label="{{ $jtLabel }}求人の公開を切り替え"
                                    >
                                        <span style="font-size:10px;margin-right:6px;min-width:2.5em;display:inline-block;text-align:left;">{{ $jtLabel }}</span>
                                        <div class="toggle-circle"></div>
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    @else
                    <form action="{{ route('shop.recruits.toggle-status') }}" method="POST">
                        @csrf
                        <button
                            type="submit"
                            class="toggle-btn {{ ($recruit['status'] ?? 'active') === 'active' ? 'active' : '' }}"
                            aria-label="公開のON/OFF"
                        >
                            <div class="toggle-circle"></div>
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            @if(!empty($recruit['message']))
            <div class="recruit-store-message">
                <div class="recruit-store-message-head">
                    <div class="recruit-store-message-icon" aria-hidden="true"><i class="fas fa-quote-left"></i></div>
                    <span class="recruit-store-message-label">店長からのメッセージ</span>
                </div>
                <div class="recruit-store-message-inner">
                    <p class="recruit-store-message-text">{{ $recruit['message'] }}</p>
                    <div class="recruit-store-message-character" aria-hidden="true"><i class="fas fa-dove"></i></div>
                </div>
            </div>
            @endif

            <h4 class="recruit-block-title" style="margin-top:24px;"><i class="fas fa-coins"></i> 現在の募集条件</h4>
            <div class="recruit-job-types">
                @if(empty($usesJobTypes) || !$usesJobTypes)
                {{-- 本入店（レガシー：job_type 未使用環境） --}}
                <div class="recruit-type-card recruit-type-card-new is-main">
                    <div class="recruit-type-main">
                        <span class="recruit-type-label recruit-type-badge-new">本入店</span>
                        <div class="recruit-type-wage-row">
                            <span class="label">時給</span>
                            <span class="value">{{ number_format($recruit['regular_hourly_wage'] ?? $recruit['hourly_wage_regular'] ?? 0) }}</span>
                            <span class="unit">円〜</span>
                        </div>
                    </div>
                    <div class="recruit-type-bonus-box">
                        <p class="bonus-label">勤務条件</p>
                        <p class="bonus-amount">{{ $recruit['working_days'] ?: '未設定' }}</p>
                        <p class="bonus-meta">（{{ $recruit['working_hours'] ?: '勤務時間未設定' }}）</p>
                        <p class="bonus-note">{{ $recruit['regular_holiday'] ?: '' }}</p>
                    </div>
                    <div class="recruit-type-actions">
                        <a href="{{ route('shop.recruits.edit') }}" class="recruit-btn recruit-btn-edit-sm">
                            <i class="fas fa-pen"></i> 編集
                        </a>
                    </div>
                </div>
                @endif

                @if($horizontal)
                <div class="recruit-type-card recruit-type-card-new is-main">
                    <div class="recruit-type-main">
                        <span class="recruit-type-label recruit-type-badge-new">本入店</span>
                        <div class="recruit-type-wage-row">
                            <span class="label">時給</span>
                            <span class="value">{{ number_format($recruit['regular_hourly_wage'] ?? $recruit['hourly_wage_regular'] ?? 0) }}</span>
                            <span class="unit">円〜</span>
                        </div>
                        <p class="recruit-type-status text-xs mt-1">
                            {{ (int) ($recruit['regular_status'] ?? 0) === 1 ? '公開中' : '非公開' }}
                        </p>
                    </div>
                    <div class="recruit-type-bonus-box">
                        <p class="bonus-label">勤務条件</p>
                        <p class="bonus-amount">{{ $recruit['working_days'] ?: '未設定' }}</p>
                        <p class="bonus-meta">（{{ $recruit['working_hours'] ?: '勤務時間未設定' }}）</p>
                        <p class="bonus-note">{{ $recruit['regular_holiday'] ?: '' }}</p>
                    </div>
                    <div class="recruit-type-actions">
                        <a href="{{ route('shop.recruits.edit') }}?type=fulltime" class="recruit-btn recruit-btn-edit-sm">
                            <i class="fas fa-pen"></i> 編集
                        </a>
                    </div>
                </div>
                @endif

                {{-- 体験入店 --}}
                <div class="recruit-type-card recruit-type-card-new {{ !empty($usesJobTypes) && $usesJobTypes && !$horizontal ? 'is-main' : '' }}">
                    <div class="recruit-type-main">
                        <span class="recruit-type-label recruit-type-badge-new">体験入店</span>
                        <div class="recruit-type-wage-row">
                            <span class="label">時給</span>
                            <span class="value">
                                @if(!empty($recruit['trial_hourly_wage']))
                                    {{ number_format($recruit['trial_hourly_wage']) }}
                                @else
                                    未設定
                                @endif
                            </span>
                            @if(!empty($recruit['trial_hourly_wage']))
                                <span class="unit">円〜</span>
                            @endif
                        </div>
                        @if(!empty($usesJobTypes) && $usesJobTypes && (int) ($recruit['regular_hourly_wage'] ?? $recruit['hourly_wage_regular'] ?? 0) > 0)
                            <p class="recruit-type-status text-xs mt-1" style="color:#a1a1aa;">
                                本入参考: ¥{{ number_format((int) ($recruit['regular_hourly_wage'] ?? $recruit['hourly_wage_regular'] ?? 0)) }}〜
                            </p>
                        @endif
                        <p class="recruit-type-status text-xs mt-1">
                            @if($horizontal)
                                {{ (int) ($recruit['trial_status'] ?? 0) === 1 ? '公開中' : '非公開' }}
                            @else
                                {{ !empty($recruit['trial_hourly_wage']) ? '公開中' : '非公開' }}
                            @endif
                        </p>
                    </div>
                    @if(empty($usesJobTypes) || !$usesJobTypes)
                    <div class="recruit-type-bonus-box">
                        <p class="bonus-label">勤務条件</p>
                        <p class="bonus-amount">{{ $recruit['working_days'] ?: '未設定' }}</p>
                        <p class="bonus-meta">（{{ $recruit['working_hours'] ?: '勤務時間未設定' }}）</p>
                    </div>
                    @endif
                    <div class="recruit-type-actions">
                        <a href="{{ route('shop.recruits.edit') }}{{ $horizontal ? '?type=trial' : ((empty($usesJobTypes) || !$usesJobTypes) ? '?type=trial' : '') }}" class="recruit-btn recruit-btn-edit-sm">
                            <i class="fas fa-pen"></i> 編集
                        </a>
                    </div>
                </div>

                {{-- ヘルプ --}}
                <div class="recruit-type-card recruit-type-card-new">
                    <div class="recruit-type-main">
                        <span class="recruit-type-label recruit-type-badge-new">ヘルプ</span>
                        <div class="recruit-type-wage-row">
                            <span class="label">時給</span>
                            <span class="value">
                                @if(!empty($recruit['help_hourly_wage']))
                                    {{ number_format($recruit['help_hourly_wage']) }}
                                @else
                                    未設定
                                @endif
                            </span>
                            @if(!empty($recruit['help_hourly_wage']))
                                <span class="unit">円〜</span>
                            @endif
                        </div>
                        <p class="recruit-type-status text-xs mt-1">
                            @if($horizontal)
                                {{ (int) ($recruit['help_status'] ?? 0) === 1 ? '公開中' : '非公開' }}
                            @else
                                {{ !empty($recruit['help_hourly_wage']) ? '公開中' : '非公開' }}
                            @endif
                        </p>
                    </div>
                    <div class="recruit-type-actions">
                        <a href="{{ route('shop.recruits.edit') }}?type=help" class="recruit-btn recruit-btn-edit-sm">
                            <i class="fas fa-pen"></i> 編集
                        </a>
                    </div>
                </div>
            </div>

            <div class="recruit-status-card-actions">
                <a href="{{ $previewRoute }}" class="recruit-btn recruit-btn-preview" target="_blank" rel="noopener">
                    <i class="fas fa-external-link-alt"></i> プレビュー
                </a>
            </div>
        </article>

        @if(!empty($shareUrl))
        {{-- 共有シート（スマホの共有機能風：リンクコピー・X・LINE・Instagram） --}}
        <div class="recruit-share-sheet" id="recruit-share-sheet" role="dialog" aria-modal="true" aria-label="求人を共有" aria-hidden="true"
             data-share-url="{{ e($shareUrl) }}"
             data-share-text="{{ e(($recruit['catch_copy'] ?? '求人情報') . ' ' . $shareUrl) }}">
            <div class="recruit-share-sheet-backdrop" id="recruit-share-sheet-backdrop"></div>
            <div class="recruit-share-sheet-panel">
                <p class="recruit-share-sheet-title">求人を共有</p>
                <div class="recruit-share-sheet-actions">
                    <button type="button" class="recruit-share-sheet-item" data-action="copy-link" aria-label="リンクをコピー">
                        <span class="recruit-share-sheet-item-icon"><i class="fas fa-link"></i></span>
                        <span class="recruit-share-sheet-item-label">リンクコピー</span>
                    </button>
                    <a href="https://twitter.com/intent/tweet?text={{ rawurlencode(($recruit['catch_copy'] ?? '求人情報') . ' ' . $shareUrl) }}" target="_blank" rel="noopener" class="recruit-share-sheet-item" data-action="x" aria-label="Xで共有">
                        <span class="recruit-share-sheet-item-icon"><i class="fab fa-x-twitter"></i></span>
                        <span class="recruit-share-sheet-item-label">X</span>
                    </a>
                    <a href="https://line.me/R/msg/text/?{{ rawurlencode(($recruit['catch_copy'] ?? '求人情報') . ' ' . $shareUrl) }}" target="_blank" rel="noopener" class="recruit-share-sheet-item" data-action="line" aria-label="LINEで共有">
                        <span class="recruit-share-sheet-item-icon"><i class="fab fa-line"></i></span>
                        <span class="recruit-share-sheet-item-label">LINE</span>
                    </a>
                    <button type="button" class="recruit-share-sheet-item" data-action="instagram" aria-label="Instagramで共有">
                        <span class="recruit-share-sheet-item-icon"><i class="fab fa-instagram"></i></span>
                        <span class="recruit-share-sheet-item-label">Instagram</span>
                    </button>
                </div>
                <button type="button" class="recruit-share-sheet-cancel" id="recruit-share-sheet-cancel">キャンセル</button>
            </div>
        </div>
        @endif
    </section>
</div>

@if(!empty($shareUrl))
@push('scripts')
<script>
(function() {
    var openBtn = document.getElementById('recruit-share-icon-open');
    var sheet = document.getElementById('recruit-share-sheet');
    var backdrop = document.getElementById('recruit-share-sheet-backdrop');
    var cancelBtn = document.getElementById('recruit-share-sheet-cancel');
    if (!sheet) return;

    function openSheet() {
        sheet.classList.add('is-open');
        sheet.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }
    function closeSheet() {
        sheet.classList.remove('is-open');
        sheet.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    if (openBtn) openBtn.addEventListener('click', openSheet);
    if (backdrop) backdrop.addEventListener('click', closeSheet);
    if (cancelBtn) cancelBtn.addEventListener('click', closeSheet);

    var shareUrl = sheet.getAttribute('data-share-url') || '';
    var shareText = sheet.getAttribute('data-share-text') || shareUrl;

    sheet.querySelectorAll('.recruit-share-sheet-item').forEach(function(el) {
        var action = el.getAttribute('data-action');
        if (action === 'copy-link') {
            el.addEventListener('click', function(e) {
                e.preventDefault();
                try {
                    navigator.clipboard.writeText(shareUrl);
                    el.querySelector('.recruit-share-sheet-item-label').textContent = 'コピーしました';
                    setTimeout(function() {
                        el.querySelector('.recruit-share-sheet-item-label').textContent = 'リンクコピー';
                        closeSheet();
                    }, 600);
                } catch (err) {
                    document.execCommand('copy');
                    closeSheet();
                }
            });
        } else if (action === 'instagram') {
            el.addEventListener('click', function(e) {
                e.preventDefault();
                try {
                    navigator.clipboard.writeText(shareText);
                } catch (err) {}
                window.open('https://www.instagram.com/', '_blank', 'noopener');
                closeSheet();
            });
        } else if (action === 'x' || action === 'line') {
            el.addEventListener('click', function() { closeSheet(); });
        }
    });
})();
</script>
@endpush
@endif

@push('scripts')
<script>
(function() {
    var data = @json($applications ?? []);
    var statusDef = {
        1: { label: 'やり取り中', className: 'is-status-1' },
        2: { label: '日程調整中', className: 'is-status-2' },
        3: { label: '面談日決定', className: 'is-status-3' },
        4: { label: '採用', className: 'is-status-4' },
        5: { label: '不採用', className: 'is-status-5' },
        6: { label: '本採用', className: 'is-status-6' },
        7: { label: '体験後不採用', className: 'is-status-7' }
    };

    var listEl = document.getElementById('recruit-status-card-list');
    var emptyEl = document.getElementById('recruit-status-empty-mobile');
    var template = document.getElementById('recruit-status-card-template');
    var statusTabGroup = document.getElementById('status-tab-group');
    var patternFilterGroup = document.getElementById('pattern-filter-group');
    var searchInput = document.getElementById('recruit-search-input');
    if (!listEl || !template || !statusTabGroup || !patternFilterGroup || !searchInput) return;

    var state = {
        tab: 'active',
        pattern: 'all',
        keyword: ''
    };

    function inStatusTab(item, tab) {
        if (tab === 'active') return [1, 2, 3].indexOf(item.status) !== -1;
        if (tab === 'hired') return [4, 6].indexOf(item.status) !== -1;
        if (tab === 'rejected') return [5, 7].indexOf(item.status) !== -1;
        return true;
    }

    function getStatusLabel(item) {
        if (item.status === 4) {
            return item.pattern === 'P2' ? 'ヘルプ採用' : '体験採用';
        }
        return item.status_label || (statusDef[item.status] ? statusDef[item.status].label : '未設定');
    }

    function render() {
        var rows = data.filter(function(item) {
            var hitTab = inStatusTab(item, state.tab);
            var hitPattern = state.pattern === 'all' || item.pattern === state.pattern;
            var hitKeyword = state.keyword === '' || (item.cast_name || '').indexOf(state.keyword) !== -1;
            return hitTab && hitPattern && hitKeyword;
        });

        listEl.innerHTML = '';
        emptyEl.hidden = rows.length !== 0;
        if (rows.length === 0) return;

        rows.forEach(function(item) {
            var node = template.content.firstElementChild.cloneNode(true);
            var statusNode = node.querySelector('.rsm-status');
            var patternNode = node.querySelector('.rsm-pattern');
            var talkNode = node.querySelector('.rsm-talk-link');

            node.querySelector('.rsm-name').textContent = item.cast_name || 'キャスト';
            patternNode.textContent = item.pattern_label || '新規採用';
            patternNode.classList.add(item.pattern === 'P2' ? 'is-p2' : 'is-p1');

            statusNode.textContent = getStatusLabel(item);
            statusNode.classList.add((statusDef[item.status] || { className: 'is-status-1' }).className);

            if (talkNode && item.cast_id) {
                talkNode.href = '{{ route('shop.talk.room', ['id' => '__CAST_ID__']) }}'.replace('__CAST_ID__', item.cast_id);
            }

            var delayed = !!item.is_delayed;
            var delayLine = node.querySelector('.rsm-delay-line');
            var delayWrap = node.querySelector('.rsm-delay');
            if (delayed) {
                node.classList.add('is-delayed');
                delayLine.hidden = false;
                delayWrap.hidden = false;
                node.querySelector('.rsm-delay-text').textContent = item.delay_message || '期限超過';
            }

            node.querySelector('[data-field="appliedAt"]').textContent = item.created_at || '未設定';
            var interview = node.querySelector('[data-field="interviewDate"]');
            if (item.result_date) {
                interview.textContent = item.result_date;
                interview.classList.add('is-gold');
            } else {
                interview.textContent = '未定';
                interview.classList.add('is-muted');
            }

            var hiredWrap = node.querySelector('[data-field-wrap="hiredDate"]');
            var hired = node.querySelector('[data-field="hiredDate"]');
            if (item.real_start_date) {
                hiredWrap.hidden = false;
                hired.textContent = item.real_start_date;
                hired.classList.add('is-hired');
            }

            var reasonWrap = node.querySelector('[data-field-wrap="rejectionReason"]');
            if (item.rejection_reason) {
                reasonWrap.hidden = false;
                node.querySelector('[data-field="rejectionReason"]').textContent = item.rejection_reason;
            }

            listEl.appendChild(node);
        });
    }

    statusTabGroup.addEventListener('click', function(e) {
        var btn = e.target.closest('button[data-filter-tab]');
        if (!btn) return;
        state.tab = btn.getAttribute('data-filter-tab');
        statusTabGroup.querySelectorAll('button').forEach(function(el) { el.classList.remove('is-active'); });
        btn.classList.add('is-active');
        render();
    });

    patternFilterGroup.addEventListener('click', function(e) {
        var btn = e.target.closest('button[data-pattern-filter]');
        if (!btn) return;
        state.pattern = btn.getAttribute('data-pattern-filter');
        patternFilterGroup.querySelectorAll('button').forEach(function(el) { el.classList.remove('is-active', 'is-p1-active', 'is-p2-active'); });
        btn.classList.add('is-active');
        if (state.pattern === 'P1') btn.classList.add('is-p1-active');
        if (state.pattern === 'P2') btn.classList.add('is-p2-active');
        render();
    });

    searchInput.addEventListener('input', function() {
        state.keyword = (searchInput.value || '').trim();
        render();
    });

    render();
})();
</script>
@endpush
@endsection
