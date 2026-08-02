{{-- 24h 以内の面談確定案件があれば in-app バナーで表示。
     InjectHeaderBadges middleware が $upcomingInterviews を全ページに共有。
     ページ側で個別 hide したい場合は body に .no-interview-banner を付ける。 --}}
@php $interviews = $upcomingInterviews ?? []; @endphp

@if(!empty($interviews))
<div class="interview-reminder-banner-wrap">
    @foreach($interviews as $itv)
        <a href="{{ $itv['talk_url'] }}" class="interview-reminder-banner" aria-label="面談リマインダー：{{ $itv['at_label'] }} {{ $itv['partner_name'] }}">
            <span class="interview-reminder-banner__icon" aria-hidden="true">
                <i class="fas fa-calendar-check"></i>
            </span>
            <span class="interview-reminder-banner__body">
                <span class="interview-reminder-banner__title">
                    @if($itv['hours_until'] <= 1)
                        まもなく面談
                    @elseif($itv['hours_until'] <= 3)
                        {{ $itv['hours_until'] }}時間後に面談
                    @else
                        本日〜明日の面談
                    @endif
                </span>
                <span class="interview-reminder-banner__meta">
                    {{ $itv['at_label'] }}・{{ $itv['partner_name'] }}
                </span>
            </span>
            <span class="interview-reminder-banner__arrow" aria-hidden="true">
                <i class="fas fa-arrow-right"></i>
            </span>
        </a>
    @endforeach
</div>

<style>
.interview-reminder-banner-wrap {
    display: flex; flex-direction: column; gap: 6px;
    padding: 8px 12px 0;
}
.interview-reminder-banner {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 14px;
    background: linear-gradient(90deg, rgba(180, 83, 9, 0.14), rgba(180, 83, 9, 0.06));
    border: 1px solid rgba(180, 83, 9, 0.35);
    border-radius: 12px;
    color: inherit; text-decoration: none;
    box-shadow: 0 4px 12px rgba(180, 83, 9, 0.14);
    transition: transform 0.12s ease, box-shadow 0.15s ease;
}
.interview-reminder-banner:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(180, 83, 9, 0.20);
}
.interview-reminder-banner:active { transform: scale(0.99); }
.interview-reminder-banner__icon {
    flex: 0 0 auto;
    width: 36px; height: 36px;
    border-radius: 50%;
    background: rgba(180, 83, 9, 0.24);
    color: #b45309;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 0.95rem;
}
.interview-reminder-banner__body { flex: 1 1 auto; min-width: 0; }
.interview-reminder-banner__title {
    display: block;
    font-size: 0.86rem; font-weight: 800;
    color: #b45309;
    line-height: 1.3;
}
body.theme-light .interview-reminder-banner__title { color: #b45309; }
body:not(.theme-light) .interview-reminder-banner__title { color: #fbbf24; }
.interview-reminder-banner__meta {
    display: block;
    font-size: 0.74rem;
    color: rgba(120, 53, 15, 0.85);
    line-height: 1.4;
    margin-top: 2px;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
body:not(.theme-light) .interview-reminder-banner__meta { color: rgba(252, 211, 77, 0.9); }
.interview-reminder-banner__arrow {
    flex: 0 0 auto; color: #b45309; font-size: 0.85rem;
}
body:not(.theme-light) .interview-reminder-banner__arrow { color: #fbbf24; }
</style>
@endif
