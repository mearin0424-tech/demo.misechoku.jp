@extends('layouts.app')

@section('title', 'マイページ - 採用状況')
@section('body-class', 'page-cast-mypage')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
@endpush

@section('content')
<div class="content-wrapper animate-fadeIn">
    <div class="cast-mypage-sub-page">
        <section class="mypage-area">
            <a href="{{ route('cast.mypage.index') }}" class="cast-mypage-back-link">
                <i class="fas fa-chevron-left"></i> マイページへ戻る
            </a>
            <h1 class="mypage-page-title serif-font">採用状況</h1>
            <div class="mypage-detail-box">
                <div class="mypage-section">
                    @php
                        $employmentCollection = collect($employments ?? []);
                        $hiredCount = $employmentCollection->where('status_label', '採用')->count();
                        $pendingCount = $employmentCollection->whereIn('status_label', ['やり取り中', '面談日調整中', '面談日決定'])->count();
                        $rejectedCount = $employmentCollection->where('status_label', '不採用')->count();
                    @endphp
                    <div class="mypage-status-overview">
                        <div class="mypage-status-metric">
                            <span class="mypage-status-metric-label">採用</span>
                            <strong class="mypage-status-metric-value">{{ $hiredCount }}</strong>
                        </div>
                        <div class="mypage-status-metric">
                            <span class="mypage-status-metric-label">選考中</span>
                            <strong class="mypage-status-metric-value">{{ $pendingCount }}</strong>
                        </div>
                        <div class="mypage-status-metric">
                            <span class="mypage-status-metric-label">不採用</span>
                            <strong class="mypage-status-metric-value">{{ $rejectedCount }}</strong>
                        </div>
                    </div>

                    @if(empty($employments))
                        <p class="cast-mypage-placeholder">
                            応募した店舗の採用状況を確認できます。<br>
                            まだ応募履歴がありません。
                        </p>
                    @else
                        <h2 class="mypage-actions-title">応募中・採用中の店舗</h2>
                        <ul class="mypage-status-card-list">
                            @foreach($employments as $item)
                                <li class="mypage-status-card">
                                    <div class="mypage-status-card-icon">
                                        <i class="fas fa-store"></i>
                                    </div>
                                    <div class="mypage-status-card-body">
                                        <div class="mypage-status-card-head">
                                            <span class="mypage-status-card-name">{{ $item['shop_name'] }}</span>
                                            <span class="doc-status {{ $item['status_class'] ?? '' }}">
                                                {{ $item['status_label'] }}
                                            </span>
                                        </div>
                                        @if(!empty($item['applied_at']))
                                            <span class="mypage-status-card-date numeric-font">更新日: {{ $item['applied_at'] }}</span>
                                        @endif
                                    </div>
                                    <a href="{{ $item['link'] ?? '#' }}" class="mypage-status-card-link">
                                        <span class="mypage-status-card-link-text">トークを見る</span>
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
