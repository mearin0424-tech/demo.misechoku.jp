{{-- 採用→入金 を一気通貫で見せるケースカード --}}
@php
    $stages = $case['stages'] ?? [];
    $progressIndex = (int) ($case['progress_index'] ?? 0);
    $isCompleted = (bool) ($case['is_completed'] ?? false);
    $isActionable = !empty($case['actionable']);
    $deposit = $case['deposit'] ?? null;
@endphp
<article class="case-card {{ $isActionable ? 'is-actionable' : '' }} {{ $isCompleted ? 'is-completed' : '' }}">
    <header class="case-card__head">
        <div class="case-card__icon">
            <i class="fas {{ $isCompleted ? 'fa-check' : 'fa-store' }}"></i>
        </div>
        <div class="case-card__main">
            <h3 class="case-card__shop-name">{{ $case['shop_name'] }}</h3>
            <div class="case-card__meta">
                @if(!empty($case['hired_at']))
                    <span><i class="fas fa-calendar-check"></i> {{ $case['hired_at'] }}</span>
                @endif
                @if(!empty($case['hired_hourly_wage_display']))
                    <span>時給 <strong>{{ $case['hired_hourly_wage_display'] }}円</strong></span>
                @else
                    <span class="case-card__meta-muted"><i class="fas fa-clock"></i> 時給設定待ち</span>
                @endif
            </div>
        </div>
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

    {{-- 数値ハイライト：入金フローが始まっていれば表示 --}}
    @if($deposit)
        <div class="case-card__highlights">
            @if(!empty($deposit['cast_transfer_amount']))
                <div class="case-card__highlight">
                    <i class="fas fa-yen-sign"></i>振込額
                    <strong>¥{{ number_format((int) $deposit['cast_transfer_amount']) }}</strong>
                </div>
            @elseif(!empty($deposit['bonus_amount']))
                <div class="case-card__highlight">
                    <i class="fas fa-gift"></i>ボーナス
                    <strong>¥{{ number_format((int) $deposit['bonus_amount']) }}</strong>
                </div>
            @endif
            @if(!empty($deposit['cast_transferred_at']))
                <div class="case-card__highlight">
                    <i class="fas fa-paper-plane"></i>振込日
                    <strong>{{ $deposit['cast_transferred_at'] }}</strong>
                </div>
            @elseif(!empty($deposit['shop_payment_confirmed_at']))
                <div class="case-card__highlight">
                    <i class="fas fa-check"></i>店舗入金確認
                    <strong>{{ $deposit['shop_payment_confirmed_at'] }}</strong>
                </div>
            @elseif(!empty($deposit['invoice_issued_at']))
                <div class="case-card__highlight">
                    <i class="fas fa-file-invoice"></i>請求書発行
                    <strong>{{ $deposit['invoice_issued_at'] }}</strong>
                </div>
            @elseif(!empty($deposit['updated_at_label']))
                <div class="case-card__highlight">
                    <i class="fas fa-clock"></i>最終更新
                    <strong>{{ $deposit['updated_at_label'] }}</strong>
                </div>
            @endif
        </div>
    @endif

    {{-- アクション行 --}}
    <div class="case-card__action-row">
        @if(!empty($case['talk_link']))
            <a href="{{ $case['talk_link'] }}" class="case-card__view-talk">
                <i class="fas fa-comment-dots"></i> トークを見る
            </a>
        @endif
        @if(!empty($case['shop_id']))
            <a href="{{ route('cast.mypage.reviews', ['shop_id' => $case['shop_id']]) }}" class="case-card__view-talk">
                <i class="fas fa-star"></i> 投稿したレビューを見る
            </a>
        @endif
        @if($isActionable)
            <button type="button" class="case-card__action-btn"
                    data-case-action="{{ $case['actionable'] }}"
                    data-application-id="{{ $case['application_id'] }}">
                <i class="fas {{ $case['actionable'] === 'request' ? 'fa-paper-plane' : 'fa-check-circle' }}"></i>
                {{ $case['actionable_label'] }}
            </button>
        @elseif(!empty($case['waiting_on']))
            <span class="case-card__waiting">
                <i class="fas fa-hourglass-half"></i> {{ $case['waiting_on'] }}
            </span>
        @elseif($isCompleted)
            <span class="case-card__waiting case-card__waiting--done">
                <i class="fas fa-check-double"></i> 振込完了
            </span>
        @endif
    </div>
</article>
