@php
    $vHasTrial = !empty($rv['trial_hourly_wage']);
    $vHasHelp = !empty($rv['help_hourly_wage']);
    $vRegularWage = (int) ($rv['hourly_wage_regular'] ?? 0);
    $vSalaryTags = collect($rv['store_features']['報酬'] ?? [])->values();
    $vOtherTags = collect($rv['store_features'] ?? [])->except('報酬')->flatten()->filter()->unique()->values();
    $vPillTags = $vSalaryTags->merge($vOtherTags)->unique()->values();
@endphp

@if(!empty($rv['catch_copy']))
    <p class="recruit-ref-catch">{{ $rv['catch_copy'] }}</p>
@endif

@if($vk === 'trial')
    <div class="recruit-ref-pay-highlight">
        <span class="label">体験入店時給</span>
        <div class="line">
            @if($vHasTrial)
                <span class="yen">¥</span><span class="num">{{ number_format((int) $rv['trial_hourly_wage']) }}</span><span class="tilde">〜</span>
            @else
                <span style="font-size:0.9rem;color:#71717a;font-weight:700;">体験入店求人で入力してください</span>
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
    <div class="recruit-ref-pay-highlight">
        <span class="label">ヘルプ時給</span>
        <div class="line">
            @if($vHasHelp)
                <span class="yen">¥</span><span class="num">{{ number_format((int) $rv['help_hourly_wage']) }}</span><span class="tilde">〜</span>
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
