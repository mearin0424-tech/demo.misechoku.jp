@php
    $vHasTrial = !empty($rv['trial_hourly_wage']);
    $vHasHelp = !empty($rv['help_hourly_wage']);
    $vRegularWage = (int) ($rv['hourly_wage_regular'] ?? 0);
    $vWorkStyleTags = collect($rv['store_features']['働き方・給与'] ?? [])->values();
    $vOtherTags = collect($rv['store_features'] ?? [])->except('働き方・給与')->flatten()->filter()->unique()->values();
    $vPillTags = $vWorkStyleTags->merge($vOtherTags)->unique()->values();
@endphp

@if($vk === 'trial')
    @php
        $trialMinV = (int) ($rv['trial_hourly_wage'] ?? 0);
        $trialMaxV = isset($rv['trial_hourly_wage_max']) && $rv['trial_hourly_wage_max'] !== null && (int) $rv['trial_hourly_wage_max'] > 0
            ? (int) $rv['trial_hourly_wage_max'] : null;
    @endphp
    <div class="recruit-ref-pay-highlight">
        <span class="label">新規入店時給</span>
        <div class="line">
            @if($vHasTrial)
                <span class="yen">¥</span><span class="num">{{ number_format($trialMinV) }}</span>
                @if($trialMaxV !== null && $trialMaxV > $trialMinV)
                    <span class="tilde">〜</span><span class="yen">¥</span><span class="num">{{ number_format($trialMaxV) }}</span>
                @else
                    <span class="tilde">〜</span>
                @endif
            @else
                <span style="font-size:0.9rem;color:#71717a;font-weight:700;">新規入店求人で入力してください</span>
            @endif
        </div>
    </div>
    @if($vRegularWage > 0)
        <p class="recruit-ref-pay-ref" style="margin:10px 0 0;font-size:11px;line-height:1.5;color:#a1a1aa;font-weight:600;">
            本入時給（参考・体験後は面談で調整）:
            <span style="color:#d4af37;font-weight:800;">¥{{ number_format($vRegularWage) }}〜</span>
        </p>
    @endif
@else
    @php
        $helpMinV = (int) ($rv['help_hourly_wage'] ?? 0);
        $helpMaxV = isset($rv['help_hourly_wage_max']) && $rv['help_hourly_wage_max'] !== null && (int) $rv['help_hourly_wage_max'] > 0
            ? (int) $rv['help_hourly_wage_max'] : null;
    @endphp
    <div class="recruit-ref-pay-highlight">
        <span class="label">ヘルプ時給</span>
        <div class="line">
            @if($vHasHelp)
                <span class="yen">¥</span><span class="num">{{ number_format($helpMinV) }}</span>
                @if($helpMaxV !== null && $helpMaxV > $helpMinV)
                    <span class="tilde">〜</span><span class="yen">¥</span><span class="num">{{ number_format($helpMaxV) }}</span>
                @else
                    <span class="tilde">〜</span>
                @endif
            @else
                <span style="font-size:0.9rem;color:#71717a;font-weight:700;">ヘルプ求人で入力してください</span>
            @endif
        </div>
    </div>
@endif

<div class="recruit-ref-tags" aria-label="特徴タグ">
    @foreach($vPillTags as $i => $tag)
        @php $ts = (string) $tag; $t = strpos($ts, '#') === 0 ? $ts : '#' . $ts; @endphp
        <span class="{{ $i < 2 ? 'gold' : 'dim' }}">{{ $t }}</span>
    @endforeach
</div>
