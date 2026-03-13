@extends('layouts.app')

@section('title', '求人ステータス管理')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/recruitment.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
@endpush

@section('content')
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
                    <span class="status-badge {{ ($recruit['status'] ?? 'active') === 'active' ? 'status-active' : 'status-inactive' }}">
                        {{ ($recruit['status'] ?? 'active') === 'active' ? '求人公開中' : '公開停止中' }}
                    </span>
                    <h2 class="recruit-status-card-title">{{ $recruit['catch_copy'] ?: '求人情報を設定してください' }}</h2>
                    <p class="recruit-status-card-meta">時給: ¥{{ number_format($recruit['hourly_wage_regular'] ?? 0) }}〜</p>
                    @if(!empty($recruit['updated_at']))
                        <p class="recruit-status-card-meta">更新日: {{ $recruit['updated_at'] }}</p>
                    @endif
                </div>
            </div>

            {{-- お店からのひとこと --}}
            @if(!empty($recruit['message']))
            <div class="recruit-store-message">
                <div class="recruit-store-message-head">
                    <div class="recruit-store-message-icon" aria-hidden="true"><i class="fas fa-quote-left"></i></div>
                    <span class="recruit-store-message-label">お店からのひとこと</span>
                </div>
                <div class="recruit-store-message-inner">
                    <p class="recruit-store-message-text">{{ $recruit['message'] }}</p>
                    <div class="recruit-store-message-character" aria-hidden="true"><i class="fas fa-dove"></i></div>
                </div>
            </div>
            @endif

            <h3 class="recruit-block-title" style="margin-top:24px;"><i class="fas fa-coins"></i> 現在の募集条件</h3>
            <div class="recruit-job-types">
                <div class="recruit-type-card recruit-type-card-new is-main">
                    <div class="recruit-type-main">
                        <span class="recruit-type-label recruit-type-badge-new">本入店</span>
                        <div class="recruit-type-wage-row">
                            <span class="label">時給</span>
                            <span class="value">{{ number_format($recruit['hourly_wage_regular'] ?? 0) }}</span>
                            <span class="unit">円〜</span>
                        </div>
                    </div>
                    <div class="recruit-type-bonus-box">
                        <p class="bonus-label">勤務条件</p>
                        <p class="bonus-amount">{{ $recruit['working_days'] ?: '未設定' }}</p>
                        <p class="bonus-meta">（{{ $recruit['working_hours'] ?: '勤務時間未設定' }}）</p>
                        <p class="bonus-note">{{ $recruit['regular_holiday'] ?: '' }}</p>
                    </div>
                </div>
                @if(!empty($recruit['trial_hourly_wage']))
                    <div class="recruit-type-card recruit-type-card-new">
                        <div class="recruit-type-main">
                            <span class="recruit-type-label recruit-type-badge-new">体験入店</span>
                            <div class="recruit-type-wage-row">
                                <span class="label">時給</span>
                                <span class="value">{{ number_format($recruit['trial_hourly_wage']) }}</span>
                                <span class="unit">円〜</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="recruit-status-card-actions">
                <a href="{{ route('shop.recruits.edit') }}" class="recruit-btn recruit-btn-edit">
                    <i class="fas fa-pen"></i> 編集する
                </a>
                <a href="{{ $previewRoute }}" class="recruit-btn recruit-btn-preview">
                    <i class="fas fa-external-link-alt"></i> プレビュー
                </a>
                @if(!empty($publicPreviewRoute))
                    <a href="{{ $publicPreviewRoute }}" class="recruit-btn recruit-btn-preview">
                        <i class="fas fa-share-nodes"></i> 公開URL
                    </a>
                @endif
            </div>
        </article>
    </section>
</div>
@endsection
