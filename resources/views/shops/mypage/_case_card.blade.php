{{-- 採用→入金 を一気通貫で見せるケースカード（店舗視点） --}}
@php
    $stages = $case['stages'] ?? [];
    $progressIndex = (int) ($case['progress_index'] ?? 0);
    $isCompleted = (bool) ($case['is_completed'] ?? false);
    $isActionable = !empty($case['actionable']);
    $deposit = $case['deposit'] ?? null;

    $pillClass = match ($case['status_tone'] ?? '') {
        'action'   => 'is-action',
        'progress' => 'is-progress',
        'done'     => 'is-done',
        default    => 'is-progress',
    };
    $pillIcon = match ($case['status_tone'] ?? '') {
        'action'   => 'fa-bolt',
        'done'     => 'fa-check-circle',
        default    => 'fa-clock',
    };
    $actionIcon = match ($case['actionable'] ?? '') {
        'approve' => 'fa-check-circle',
        'pay'     => 'fa-yen-sign',
        default   => 'fa-bolt',
    };
@endphp
<article class="case-card {{ $isActionable ? 'is-actionable' : '' }} {{ $isCompleted ? 'is-completed' : '' }}">
    <header class="case-card__head">
        @if(!empty($case['cast_avatar_url']))
            <img src="{{ $case['cast_avatar_url'] }}" alt="" class="case-card__avatar">
        @else
            <div class="case-card__icon">
                <i class="fas {{ $isCompleted ? 'fa-check' : 'fa-user' }}"></i>
            </div>
        @endif
        <div class="case-card__main">
            <h3 class="case-card__shop-name">{{ $case['cast_name'] }}</h3>
            <div class="case-card__meta">
                @if(!empty($case['job_kind_label']))
                    <span><i class="fas fa-briefcase" style="color:#dcb568;"></i> {{ $case['job_kind_label'] }}</span>
                @endif
                @if(!empty($case['hired_at']))
                    <span><i class="fas fa-calendar-check" style="color:#dcb568;"></i> 採用 {{ $case['hired_at'] }}</span>
                @endif
                @if(!empty($case['hired_hourly_wage_display']))
                    <span>採用時給 <strong>{{ $case['hired_hourly_wage_display'] }}円</strong></span>
                @endif
            </div>
        </div>
        <span class="case-card__pill {{ $pillClass }}">
            <i class="fas {{ $pillIcon }}"></i>
            {{ $case['status_label'] ?? '' }}
        </span>
    </header>

    {{-- パイプライン：採用 → ボーナス申請 → 店舗承認 → 請求書発行 → 店舗入金 → 振込実行 → 受領完了 --}}
    <ol class="case-pipeline" aria-label="採用から入金完了までの進捗">
        @foreach($stages as $idx => $stage)
            @php
                $state = match (true) {
                    $idx < $progressIndex => 'is-done',
                    $idx === $progressIndex && !$isCompleted => 'is-current',
                    $idx <= $progressIndex && $isCompleted => 'is-done',
                    default => '',
                };
            @endphp
            <li class="case-pipeline__step {{ $state }}">
                <span class="case-pipeline__bullet" aria-hidden="true">
                    @if($state === 'is-done')
                        <i class="fas fa-check"></i>
                    @else
                        {{ $idx + 1 }}
                    @endif
                </span>
                <span class="case-pipeline__label">{{ $stage['label'] }}</span>
            </li>
        @endforeach
    </ol>

    {{-- 数値ハイライト --}}
    @if($deposit)
        <div class="case-card__highlights">
            @if(!empty($deposit['invoice_amount']))
                <div class="case-card__highlight">
                    <i class="fas fa-file-invoice-dollar"></i>請求金額
                    <strong>¥{{ number_format((int) $deposit['invoice_amount']) }}</strong>
                </div>
            @elseif(!empty($deposit['bonus_amount']))
                <div class="case-card__highlight">
                    <i class="fas fa-gift"></i>ボーナス
                    <strong>¥{{ number_format((int) $deposit['bonus_amount']) }}</strong>
                </div>
            @endif
            @if(!empty($deposit['invoice_due_date']))
                <div class="case-card__highlight">
                    <i class="fas fa-calendar-day"></i>支払期限
                    <strong>{{ $deposit['invoice_due_date'] }}</strong>
                </div>
            @elseif(!empty($deposit['invoice_issued_at']))
                <div class="case-card__highlight">
                    <i class="fas fa-file-invoice"></i>請求書発行
                    <strong>{{ $deposit['invoice_issued_at'] }}</strong>
                </div>
            @elseif(!empty($deposit['shop_payment_confirmed_at']))
                <div class="case-card__highlight">
                    <i class="fas fa-check"></i>入金確認
                    <strong>{{ $deposit['shop_payment_confirmed_at'] }}</strong>
                </div>
            @elseif(!empty($deposit['updated_at_label']))
                <div class="case-card__highlight">
                    <i class="fas fa-clock"></i>最終更新
                    <strong>{{ $deposit['updated_at_label'] }}</strong>
                </div>
            @endif
        </div>
    @endif

    {{-- 請求書ダウンロード（発行後はいつでも参照可能） --}}
    @if($deposit && !empty($deposit['invoice_pdf_url']))
        <div class="case-card__invoice-link-row">
            <a href="{{ $deposit['invoice_pdf_url'] }}" target="_blank" rel="noopener" class="case-card__invoice-link">
                <i class="fas fa-file-pdf"></i> 請求書を確認（PDF）
            </a>
        </div>
    @endif

    {{-- アクション行 --}}
    <div class="case-card__action-row">
        @if(!empty($case['talk_link']))
            <a href="{{ $case['talk_link'] }}" class="case-card__view-talk">
                <i class="fas fa-comment-dots"></i> トークを見る
            </a>
        @endif
        @if($isActionable)
            <button type="button" class="case-card__action-btn"
                    data-case-action="{{ $case['actionable'] }}"
                    data-application-id="{{ $case['application_id'] }}"
                    data-deposit-id="{{ $deposit['id'] ?? '' }}">
                <i class="fas {{ $actionIcon }}"></i>
                {{ $case['actionable_label'] }}
            </button>
        @elseif(!empty($case['waiting_on']))
            <span class="case-card__waiting">
                <i class="fas fa-hourglass-half"></i> {{ $case['waiting_on'] }}
            </span>
        @elseif($isCompleted)
            <span class="case-card__waiting" style="color:#6ee7b7;">
                <i class="fas fa-check-double"></i> 振込完了
            </span>
        @endif
    </div>
</article>
