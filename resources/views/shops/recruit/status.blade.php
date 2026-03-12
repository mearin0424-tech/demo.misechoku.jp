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
    $mainKey = 'regular';
@endphp
<div class="recruit-status-page recruit-detail-page animate-fadeIn">
    <header class="recruit-status-header">
        <a href="{{ route('shop.mypage.index') }}" class="recruit-status-back">
            <i class="fas fa-chevron-left"></i> マイページへ
        </a>
        <div class="recruit-status-title-block">
            <h1 class="recruit-status-title serif-font">Recruit Status</h1>
            <p class="recruit-status-sub">求人情報の確認・公開管理（プレビュー風）</p>
        </div>
    </header>

    <section class="recruit-status-list">
        <article class="recruit-status-card">
            <div class="recruit-status-card-head">
                <div class="recruit-status-card-info">
                    <span class="status-badge status-active">求人公開中</span>
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
                <div class="recruit-store-message-head">
                    <div class="recruit-store-message-icon" aria-hidden="true"><i class="fas fa-quote-left"></i></div>
                    <span class="recruit-store-message-label">お店からのひとこと</span>
                </div>
                <div class="recruit-store-message-inner">
                    <p class="recruit-store-message-text">{{ $detail['store_message'] }}</p>
                    <div class="recruit-store-message-character" aria-hidden="true"><i class="fas fa-dove"></i></div>
                </div>
            </div>
            @endif

            {{-- 採用形態・ボーナス（求人票と同じメリハリ） --}}
            <h3 class="recruit-block-title" style="margin-top:24px;"><i class="fas fa-coins"></i> 採用形態・ボーナス</h3>
            <div class="recruit-job-types">
                @foreach(['regular' => ['recruit-type-regular', true], 'trial' => ['recruit-type-trial', false], 'help' => ['recruit-type-help', false]] as $key => $arr)
                    @if(!empty($jobTypes[$key]))
                    @php
                        $type = $jobTypes[$key];
                        list($typeClass, $isMain) = $arr;
                    @endphp
                    <div class="recruit-type-card {{ $typeClass }} {{ $isMain ? 'recruit-type-card-new is-main' : 'recruit-type-card-new' }}">
                        <div class="recruit-type-main">
                            <span class="recruit-type-label recruit-type-badge-new">{{ $type['label'] }}</span>
                            <div class="recruit-type-wage-row">
                                <span class="label">時給</span>
                                <span class="value">{{ number_format($type['hourly_wage']) }}</span>
                                <span class="unit">円〜</span>
                            </div>
                        </div>
                        <div class="recruit-type-bonus-box">
                            <p class="bonus-label">ノルマ達成ボーナス報酬</p>
                            <p class="bonus-amount">{{ $type['work_reward'] ?? '—' }}</p>
                            <p class="bonus-meta">（1日の勤務時間：{{ $type['daily_hours'] ?? '—' }}h）</p>
                            <p class="bonus-note">{{ $type['notes'] ?? '' }}</p>
                        </div>
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
                badge.innerText = '求人公開中';
                badge.className = 'status-badge status-active';
            } else {
                badge.innerText = '公開停止中';
                badge.className = 'status-badge status-inactive';
            }
        }
    }
</script>
@endpush
