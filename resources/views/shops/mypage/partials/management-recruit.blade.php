{{-- 採用パネル：rsm-card 一式 --}}
<div class="management-pane" data-pane="recruit" id="management-pane-recruit">
    @if(session('message'))
        <p class="profile-edit-flash recruit-status-flash">{{ session('message') }}</p>
    @endif

    <div class="recruit-status-page recruit-detail-page">
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
                        <div class="rsm-top">
                            <div class="rsm-main">
                                <div class="rsm-main-inner">
                                    <div class="rsm-avatar" aria-hidden="true">
                                        <img class="rsm-avatar-img" alt="" data-field="avatarImg" width="44" height="44" hidden>
                                        <span class="rsm-avatar-fallback" data-field="avatarFallback"><i class="fas fa-user"></i></span>
                                    </div>
                                    <div class="rsm-main-text">
                                        <h3 class="rsm-name"></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="rsm-side">
                                <a class="rsm-talk-link" href="{{ route('shop.talk.index') }}" aria-label="トークルームへ">
                                    <i class="fas fa-comment-dots"></i>
                                </a>
                            </div>
                        </div>
                        <div class="rsm-details">
                            <div class="rsm-meta-row">
                                <div class="rsm-meta-item">
                                    <span class="rsm-meta-key">種別</span>
                                    <span class="rsm-meta-val" data-field="jobKind"></span>
                                </div>
                                <div class="rsm-meta-item">
                                    <span class="rsm-meta-key">ステータス</span>
                                    <span class="rsm-meta-val" data-field="statusDisplay"></span>
                                </div>
                            </div>
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
                            <div class="rsm-applied-block" data-field-wrap="appliedSummary" hidden>
                                <div class="rsm-applied-title">応募時の求人条件</div>
                                <ul class="rsm-applied-list" data-field="appliedList"></ul>
                            </div>
                            <div class="rsm-applied-block" data-field-wrap="confirmedSummary" hidden>
                                <div class="rsm-applied-title">確定条件（採用時）</div>
                                <ul class="rsm-applied-list" data-field="confirmedList"></ul>
                            </div>
                            <div class="rsm-hired-wage-block" data-field-wrap="hiredWage" hidden>
                                <form method="post" class="rsm-hired-wage-form" data-field="hiredWageForm">
                                    <input type="hidden" name="_token" value="" data-field="csrfToken" autocomplete="off">
                                    <input type="hidden" name="application_id" value="" data-field="applicationId">
                                    <span class="rsm-hired-wage-label">採用時給（確定）</span>
                                    <div class="rsm-hired-wage-row">
                                        <input type="text" name="hired_regular_hourly_wage" class="rsm-hired-wage-input" data-field="hiredWageInput" inputmode="numeric" placeholder="例: 5000" autocomplete="off">
                                        <button type="submit" class="rsm-hired-wage-submit">保存</button>
                                    </div>
                                </form>
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
</div>

@push('scripts')
<script>
window.recruitStatusCsrf = @json(csrf_token());
window.recruitStatusHiredWageUrl = @json(route('shop.recruits.application-hired-wage'));
(function() {
    var data = @json($applications ?? []);

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
            var talkNode = node.querySelector('.rsm-talk-link');

            node.querySelector('.rsm-name').textContent = item.cast_name || 'キャスト';
            var jobKindEl = node.querySelector('[data-field="jobKind"]');
            var statusDisplayEl = node.querySelector('[data-field="statusDisplay"]');
            if (jobKindEl) jobKindEl.textContent = item.job_kind_label || '本入店';
            if (statusDisplayEl) {
                statusDisplayEl.textContent = item.status_display_label || '';
                statusDisplayEl.classList.remove('rsm-meta-status-hired', 'rsm-meta-status-rejected', 'rsm-meta-status-overdue');
                var st = item.status;
                if (st === 4 || st === 6) {
                    statusDisplayEl.classList.add('rsm-meta-status-hired');
                } else if (st === 5 || st === 7) {
                    statusDisplayEl.classList.add('rsm-meta-status-rejected');
                } else if (item.is_decision_overdue) {
                    statusDisplayEl.classList.add('rsm-meta-status-overdue');
                }
            }

            var avatarImg = node.querySelector('[data-field="avatarImg"]');
            var avatarFb = node.querySelector('[data-field="avatarFallback"]');
            if (item.cast_avatar_url) {
                avatarImg.hidden = false;
                if (avatarFb) avatarFb.hidden = true;
                avatarImg.src = item.cast_avatar_url;
                avatarImg.onload = function() { if (avatarFb) avatarFb.hidden = true; };
                avatarImg.onerror = function() {
                    avatarImg.hidden = true;
                    if (avatarFb) avatarFb.hidden = false;
                };
            } else {
                avatarImg.removeAttribute('src');
                avatarImg.hidden = true;
                if (avatarFb) avatarFb.hidden = false;
            }

            if (talkNode && item.cast_id) {
                talkNode.href = '{{ route('shop.talk.room', ['id' => '__CAST_ID__']) }}'.replace('__CAST_ID__', item.cast_id);
            }

            node.querySelector('[data-field="appliedAt"]').textContent = item.created_at || '未設定';
            var interview = node.querySelector('[data-field="interviewDate"]');
            if (item.result_date) {
                interview.textContent = item.result_date;
                interview.classList.add('is-gold');
                if (item.is_decision_overdue) {
                    interview.classList.add('is-overdue');
                }
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
            var isRejected = item.status === 5 || item.status === 7;
            if (isRejected && item.rejection_reason) {
                reasonWrap.hidden = false;
                node.querySelector('[data-field="rejectionReason"]').textContent = item.rejection_reason;
            }

            var appliedWrap = node.querySelector('[data-field-wrap="appliedSummary"]');
            var appliedList = node.querySelector('[data-field="appliedList"]');
            if (item.applied_summary_lines && item.applied_summary_lines.length) {
                appliedWrap.hidden = false;
                appliedList.innerHTML = '';
                item.applied_summary_lines.forEach(function(line) {
                    var li = document.createElement('li');
                    li.textContent = line;
                    appliedList.appendChild(li);
                });
            } else {
                appliedWrap.hidden = true;
                appliedList.innerHTML = '';
            }

            var confirmedWrap = node.querySelector('[data-field-wrap="confirmedSummary"]');
            var confirmedList = node.querySelector('[data-field="confirmedList"]');
            if (item.confirmed_summary_lines && item.confirmed_summary_lines.length) {
                confirmedWrap.hidden = false;
                confirmedList.innerHTML = '';
                item.confirmed_summary_lines.forEach(function(line) {
                    var li = document.createElement('li');
                    li.textContent = line;
                    confirmedList.appendChild(li);
                });
            } else {
                confirmedWrap.hidden = true;
                confirmedList.innerHTML = '';
            }

            var wageWrap = node.querySelector('[data-field-wrap="hiredWage"]');
            var wageForm = node.querySelector('[data-field="hiredWageForm"]');
            if (item.can_edit_hired_wage) {
                wageWrap.hidden = false;
                wageForm.action = window.recruitStatusHiredWageUrl || '';
                node.querySelector('[data-field="csrfToken"]').value = window.recruitStatusCsrf || '';
                node.querySelector('[data-field="applicationId"]').value = item.id;
                node.querySelector('[data-field="hiredWageInput"]').value = item.hired_regular_hourly_wage_input || '';
            } else {
                wageWrap.hidden = true;
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
