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
                    @if(empty($employments))
                        <p class="cast-mypage-placeholder">
                            応募した店舗の採用状況を確認できます。<br>
                            まだ応募履歴がありません。
                        </p>
                    @else
                        <h2 class="mypage-actions-title">応募中・採用中の店舗</h2>
                        <ul class="doc-list">
                            @foreach($employments as $item)
                                <li class="doc-item">
                                    <div class="doc-icon">
                                        <i class="fas fa-store"></i>
                                    </div>
                                    <div class="doc-info">
                                        <span class="doc-name">{{ $item['shop_name'] }}</span>
                                        <span class="doc-status {{ $item['status_class'] ?? '' }}">
                                            {{ $item['status_label'] }}
                                        </span>
                                        @if(!empty($item['applied_at']))
                                            <span class="date-text numeric-font">{{ $item['applied_at'] }}</span>
                                        @endif
                                    </div>
                                    <a href="{{ $item['link'] ?? '#' }}" class="doc-arrow">
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
