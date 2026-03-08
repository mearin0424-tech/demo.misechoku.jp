@extends('layouts.app')

@section('title', '求人ステータス管理')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/recruitment.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
@endpush

@section('content')
@php
    $detail = $recruitDetail ?? null;
    $jobTypes = $detail['job_types'] ?? [];
@endphp
<div class="recruit-status-page contents animate-fadeIn">
    <header class="recruit-status-header">
        <a href="{{ route('shop.mypage.index') }}" class="recruit-status-back">
            <i class="fas fa-chevron-left"></i> マイページへ
        </a>
        <div class="recruit-status-title-block">
            <h1 class="recruit-status-title serif-font">Recruit Status</h1>
            <p class="recruit-status-sub">求人情報の確認・公開管理</p>
        </div>
    </header>

    <section class="recruit-status-summary">
        <p class="recruit-status-summary-label">公開中の求人</p>
        <p class="recruit-status-summary-value">{{ count($recruits) }} <span class="unit">件</span></p>
    </section>

    <section class="recruit-status-list">
        <article class="recruit-status-card">
            <div class="recruit-status-card-head">
                <div class="recruit-status-card-info">
                    <span class="status-badge status-active">ON AIR</span>
                    <h2 class="recruit-status-card-title">レギュラーキャスト募集</h2>
                    <p class="recruit-status-card-meta">時給: ¥5,000〜</p>
                </div>
                <div class="toggle-btn active" onclick="toggleStatus(this)" aria-label="公開のON/OFF">
                    <div class="toggle-circle"></div>
                </div>
            </div>

            {{-- お店からのひとこと --}}
            @if($detail && !empty($detail['store_message']))
            <div class="recruit-store-message">
                <span class="recruit-store-message-label">お店からのひとこと</span>
                <div class="recruit-store-message-inner">
                    <p class="recruit-store-message-text">{{ $detail['store_message'] }}</p>
                    <div class="recruit-store-message-character" aria-hidden="true">
                        <i class="fas fa-dove"></i>
                    </div>
                </div>
            </div>
            @endif

            {{-- 本入店・体験入店・ヘルプ採用 カード --}}
            <div class="recruit-job-types">
                @foreach(['regular' => 'recruit-type-regular', 'trial' => 'recruit-type-trial', 'help' => 'recruit-type-help'] as $key => $typeClass)
                    @if(!empty($jobTypes[$key]))
                    @php $type = $jobTypes[$key]; @endphp
                    <div class="recruit-type-card {{ $typeClass }}">
                        <span class="recruit-type-label">{{ $type['label'] }}</span>
                        <p class="recruit-type-wage">時給 {{ number_format($type['hourly_wage']) }}円〜</p>
                        <p class="recruit-type-bonus">{{ $type['bonus'] }}</p>
                        <p class="recruit-type-reward">{{ $type['work_reward'] }}</p>
                        <p class="recruit-type-daily">（1日の勤務時間: {{ $type['daily_hours'] }}時間）</p>
                        <p class="recruit-type-notes">{{ $type['notes'] }}</p>
                    </div>
                    @endif
                @endforeach
            </div>

            <div class="recruit-status-card-actions">
                <a href="{{ route('shop.recruits.edit') }}" class="recruit-btn recruit-btn-edit">
                    <i class="fas fa-pen"></i> 編集する
                </a>
                <a href="{{ route('shop.recruits.show', ['id' => 1]) }}" class="recruit-btn recruit-btn-preview">
                    <i class="fas fa-external-link-alt"></i> プレビュー
                </a>
            </div>
        </article>
    </section>
</div>
@endsection

@push('scripts')
<script>
    function toggleStatus(el) {
        el.classList.toggle('active');
        const card = el.closest('.recruit-status-card');
        const badge = card ? card.querySelector('.status-badge') : null;
        if (badge) {
            if (el.classList.contains('active')) {
                badge.innerText = 'ON AIR';
                badge.className = 'status-badge status-active';
            } else {
                badge.innerText = 'PAUSED';
                badge.className = 'status-badge status-inactive';
            }
        }
    }
</script>
@endpush
