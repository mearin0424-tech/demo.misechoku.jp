{{-- 採用→入金 を一気通貫で見せるケースカード --}}
@php
    $stages = $case['stages'] ?? [];
    $progressIndex = (int) ($case['progress_index'] ?? 0);
    $isCompleted = (bool) ($case['is_completed'] ?? false);
    $isActionable = !empty($case['actionable']);
    $deposit = $case['deposit'] ?? null;

    // ---- 今誰のボールか（キャスト視点）----
    //   0: あなた（ボーナス申請）   1: 店舗（承認）   2: 運営（請求書）
    //   3: 店舗（入金）             4: 運営（振込）   5: あなた（受領確認） 6: 完了
    // ラベルは「要対応 / 待ち（誰） / 完了」の3状態で明確に表記する
    $actor = match (true) {
        $isCompleted          => ['cls' => 'case-actor--done',  'icon' => 'fa-circle-check',    'label' => '完了'],
        $progressIndex === 0  => ['cls' => 'case-actor--you',   'icon' => 'fa-bolt',            'label' => '要対応'],
        $progressIndex === 1  => ['cls' => 'case-actor--shop',  'icon' => 'fa-hourglass-half',  'label' => '待ち（店舗）'],
        $progressIndex === 2  => ['cls' => 'case-actor--admin', 'icon' => 'fa-hourglass-half',  'label' => '待ち（運営）'],
        $progressIndex === 3  => ['cls' => 'case-actor--shop',  'icon' => 'fa-hourglass-half',  'label' => '待ち（店舗）'],
        $progressIndex === 4  => ['cls' => 'case-actor--admin', 'icon' => 'fa-hourglass-half',  'label' => '待ち（運営）'],
        $progressIndex === 5  => ['cls' => 'case-actor--you',   'icon' => 'fa-bolt',            'label' => '要対応'],
        default               => ['cls' => 'case-actor--admin', 'icon' => 'fa-circle-question', 'label' => '確認中'],
    };
    $caseState = $isCompleted ? 'done' : ($isActionable ? 'action' : 'waiting');

    // ---- 現在ステージの説明 + 次に起こること ----
    $currentStage = $stages[$progressIndex] ?? null;
    $nextStage    = $stages[$progressIndex + 1] ?? null;
    $nowNote = match ($progressIndex) {
        0 => '採用が確定しました。ボーナス申請を行うと入金フローが始まります。',
        1 => '申請内容を店舗が確認しています。承認されると運営が請求書を発行します。',
        2 => '運営が店舗宛の請求書を準備しています。',
        3 => '店舗が請求書のお支払いを進めています。入金が確認されると振込準備に入ります。',
        4 => '店舗の入金を確認しました。運営からあなたの口座へ振込を実行します。',
        5 => 'あなたの口座へ振込済みです。通帳・アプリで着金を確認して「入金を確認しました」を押してください。',
        default => null,
    };

    // ---- 停滞警告（5日以上更新なし・進行中のみ）----
    $stallDays = null;
    if (!$isCompleted && !empty($deposit['updated_at_label'])) {
        try {
            $lastUpdated = \Carbon\Carbon::parse($deposit['updated_at_label']);
            $days = $lastUpdated->diffInDays(now());
            if ($days >= 5) $stallDays = $days;
        } catch (\Throwable $e) { /* パース不可なら非表示 */ }
    }
@endphp
<article class="case-card {{ $isActionable ? 'is-actionable' : '' }} {{ $isCompleted ? 'is-completed' : '' }}"
         data-case-state="{{ $caseState }}">
    <span class="case-actor {{ $actor['cls'] }}">
        <i class="fas {{ $actor['icon'] }}" aria-hidden="true"></i>{{ $actor['label'] }}
        @if($stallDays !== null)
            <span class="case-stall"><i class="fas fa-triangle-exclamation"></i>{{ $stallDays }}日停滞</span>
        @endif
    </span>
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

    {{-- 現在ステージの説明 + 次に起こること --}}
    @if(!$isCompleted && $nowNote !== null)
        <div class="case-now {{ $isActionable ? 'case-now--action' : '' }}">
            <i class="fas {{ $isActionable ? 'fa-bolt' : 'fa-circle-info' }} case-now__icon" aria-hidden="true"></i>
            <span class="case-now__body">
                @if($currentStage)
                    <span class="case-now__stage">{{ $currentStage['label'] }}</span>
                @endif
                {{ $nowNote }}
                @if($nextStage)
                    <span class="case-now__next"><i class="fas fa-arrow-right"></i>次のステップ: {{ $nextStage['label'] }}（{{ $nextStage['desc'] ?? '' }}）</span>
                @endif
            </span>
        </div>
    @elseif($isCompleted)
        <div class="case-now case-now--done">
            <i class="fas fa-circle-check case-now__icon" aria-hidden="true"></i>
            <span class="case-now__body">全てのステップが完了しました。お疲れさまでした！</span>
        </div>
    @endif

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

    {{-- アクション行：要対応時の主ボタンのみ（待ち/完了テキストは右上バッジに集約済み） --}}
    @if($isActionable)
        <div class="case-card__action-row">
            <button type="button" class="case-card__action-btn"
                    data-case-action="{{ $case['actionable'] }}"
                    data-application-id="{{ $case['application_id'] }}">
                <i class="fas {{ $case['actionable'] === 'request' ? 'fa-paper-plane' : 'fa-check-circle' }}"></i>
                {{ $case['actionable_label'] }}
            </button>
        </div>
    @endif

    {{-- トーク導線：全幅の明確なボタン --}}
    @if(!empty($case['talk_link']))
        <a href="{{ $case['talk_link'] }}" class="case-card__talk-open">
            <i class="fas fa-comment-dots"></i> トークを開く
            <i class="fas fa-chevron-right case-card__talk-open-chev" aria-hidden="true"></i>
        </a>
    @endif
</article>
