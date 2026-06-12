@php
    $vHasTrial = !empty($rv['trial_hourly_wage']);
    $vHasHelp = !empty($rv['help_hourly_wage']);
    $vRegularWage = (int) ($rv['hourly_wage_regular'] ?? 0);
    $vNoruma = (int) ($rv['noruma_reward'] ?? 0);
    $vBonusDays = trim((string) ($rv['bonus_total_working_days'] ?? $rv['bonus_working_days'] ?? ''));
    $vBonusHours = trim((string) ($rv['bonus_total_working_hours'] ?? $rv['bonus_working_hours'] ?? ''));
    $vBonusExtra = trim((string) ($rv['bonus_other_conditions'] ?? $rv['bonus_condition'] ?? ''));
    $vBonusCondParts = array_filter([
        $vBonusDays !== '' ? '累計勤務日数: ' . $vBonusDays . '日以上' : null,
        $vBonusHours !== '' ? '累計勤務時間: ' . $vBonusHours . '時間以上' : null,
        $vBonusExtra !== '' ? $vBonusExtra : null,
    ]);
    $vBonusConditionsText = implode('、', $vBonusCondParts);
    $vShowBonus = $vNoruma > 0 || $vBonusConditionsText !== '';
    $vStoreFeatures = $rv['store_features'] ?? [];
    $vHasFeatureMatrix = false;
    foreach ($matrixLabels as $key => $_lbl) {
        if (!empty($vStoreFeatures[$key]) && count((array) $vStoreFeatures[$key]) > 0) {
            $vHasFeatureMatrix = true;
            break;
        }
    }
    $vMessageBody = trim((string) ($rv['message'] ?? ''));
    $vJobSupplement = trim((string) ($rv['job_content'] ?? ''));
    $vSalaryNotes = trim((string) ($rv['salary_text'] ?? ''));
    $vHelpNotes = trim((string) ($rv['help_job_content'] ?? ''));
@endphp

<section id="section-message-{{ $vk }}">
    <h2 class="recruit-ref-h2"><i class="fas fa-comment-dots"></i> 店長からのメッセージ</h2>
    <div class="recruit-ref-msg">{{ $vMessageBody !== '' ? $vMessageBody : '店長からのメッセージは求人編集から入力できます。' }}</div>

    @if(!empty($shareUrlResolved ?? null))
        <div class="recruit-ref-share-row">
            <button type="button" class="recruit-ref-share-btn recruit-ref-share-btn--gold js-recruit-native-share">
                <i class="fas fa-share-alt"></i> 共有
            </button>
            <a href="{{ $xShareUrl }}" target="_blank" rel="noopener noreferrer" class="recruit-ref-share-btn recruit-ref-share-btn--muted">
                <span style="font-weight:900;">𝕏</span>
            </a>
            <a href="{{ $lineShareUrl }}" target="_blank" rel="noopener noreferrer" class="recruit-ref-share-btn recruit-ref-share-btn--line">
                LINE
            </a>
        </div>
    @endif
</section>

<section id="requirements-{{ $vk }}">
    <h2 class="recruit-ref-h2-lg">
        <span class="bar" aria-hidden="true"></span>
        募集要項
        <span class="recruit-ref-subtle recruit-req-sub-label">{{ $vk === 'trial' ? '（新規入店）' : '（ヘルプ）' }}</span>
    </h2>

    <div class="recruit-req-block">
        @if($vShowBonus)
            <div class="recruit-ref-bonus-card" aria-labelledby="recruit-bonus-title-{{ $vk }}">
                <div id="recruit-bonus-title-{{ $vk }}" class="recruit-ref-bonus-card__head">
                    <i class="fas fa-gift" aria-hidden="true"></i>
                    <span>入店ボーナス</span>
                </div>
                <div class="recruit-ref-bonus-card__amount">
                    @if($vNoruma > 0)
                        <span class="num">{{ number_format($vNoruma) }}</span>
                        <span class="suffix">円支給</span>
                    @else
                        <span class="num" style="font-size:1rem;">条件のみ設定されています</span>
                    @endif
                </div>
                @if($vBonusConditionsText !== '')
                    <div class="recruit-ref-bonus-card__cond"><strong>条件:</strong> {{ $vBonusConditionsText }}</div>
                @endif
            </div>
        @endif

        <div class="recruit-ref-inforow"><span class="k">給与</span><span class="v">
            @if($vk === 'trial')
                @if($vHasTrial)
                    <span style="color:#a78bfa;font-weight:800;">新規入店: {{ number_format((int) $rv['trial_hourly_wage']) }}円〜</span>
                    @if($vRegularWage > 0)
                        <br><span style="color:#a1a1aa;font-size:0.8125rem;font-weight:600;">本入（参考）: {{ number_format($vRegularWage) }}円〜 ※正式条件は体験後に面談</span>
                    @endif
                @else
                    —
                @endif
            @else
                @if($vHasHelp)
                    <span style="color:#a78bfa;font-weight:800;">{{ number_format((int) $rv['help_hourly_wage']) }}円〜</span>
                @else
                    —
                @endif
            @endif
        </span></div>
        <div class="recruit-ref-inforow"><span class="k">給与備考</span><span class="v" style="white-space:pre-wrap;color:#d4d4d8;">{{ $vSalaryNotes !== '' ? $vSalaryNotes : '—' }}</span></div>
        @if($vk === 'help' && $vHelpNotes !== '')
            <div class="recruit-ref-inforow"><span class="k">ヘルプの内容</span><span class="v" style="white-space:pre-wrap;color:#d4d4d8;">{{ $vHelpNotes }}</span></div>
        @endif
        <div class="recruit-ref-inforow"><span class="k">勤務時間</span><span class="v">{{ $rv['working_hours'] ?: '—' }}</span></div>
        <div class="recruit-ref-inforow"><span class="k">勤務日・シフト</span><span class="v">{{ $rv['working_days'] ?: '—' }}</span></div>
        <div class="recruit-ref-inforow"><span class="k">応募資格</span><span class="v">{{ $rv['qualification'] ?? '—' }}</span></div>
        <div class="recruit-ref-inforow"><span class="k">控除</span><span class="v">10.21%（源泉所得税）</span></div>

        @if($vHasFeatureMatrix)
            <div class="recruit-ref-tag-matrix">
                <p>特徴・アピールタグ</p>
                @foreach($matrixLabels as $key => $label)
                    @php $tags = $vStoreFeatures[$key] ?? []; @endphp
                    @if(!empty($tags))
                        <div class="recruit-ref-tag-matrix-row">
                            <span class="cat">{{ $label }}</span>
                            <div class="recruit-ref-tag-matrix-pills">
                                @foreach((array) $tags as $t)
                                    <span>{{ $t }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</section>

@if($vJobSupplement !== '')
<section id="section-job-supplement-{{ $vk }}" class="recruit-ref-job-supplement">
    <h2 class="recruit-ref-h2"><i class="fas fa-briefcase"></i> お仕事内容について補足</h2>
    <div class="recruit-ref-msg recruit-ref-msg--pre">{!! nl2br(e($vJobSupplement)) !!}</div>
</section>
@endif

@if(!empty($forCast))
<div class="recruit-footer-cta">
    @php
        $applyTalkJobKind = $vk === 'help' ? 'help' : 'trial';
        $applyTalkTopic = $vk === 'help' ? 'help' : 'new_hire';
        $talkShopId = $shop['id'] ?? $shop['shop_id'] ?? $rv['id'] ?? $rv['shop_id'] ?? null;
    @endphp
    @if(!empty($talkShopId))
        <a href="{{ route('cast.talk.room', ['id' => $talkShopId, 'job_kind' => $applyTalkJobKind, 'talk_topic' => $applyTalkTopic]) }}" class="recruit-cta-btn"><i class="fas fa-paper-plane"></i> 応募する</a>
        <a href="{{ route('cast.talk.room', ['id' => $talkShopId, 'talk_topic' => 'other']) }}" class="recruit-cta-btn" style="margin-top:8px; opacity:.88;"><i class="fas fa-comment-dots"></i> 質問・相談</a>
    @endif
</div>
@endif
