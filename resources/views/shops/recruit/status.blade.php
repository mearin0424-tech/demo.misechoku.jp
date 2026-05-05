@extends('layouts.app')

@section('title', '求人ステータス管理')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/recruitment.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
@endpush

@section('content')
<div class="recruit-status-page recruit-detail-page animate-fadeIn">
    @if(session('message'))
        <p class="profile-edit-flash recruit-status-flash">{{ session('message') }}</p>
    @endif

    {{-- ========== 1. 採用ステータス（スマホ特化カードUI） ========== --}}
    <section class="recruit-status-mobile-shell recruit-status-section-casts">
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
</div>

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
