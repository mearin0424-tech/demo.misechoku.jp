@extends('layouts.app')

@section('title', 'マイページ - 請求・入金管理')
@section('body-class', 'page-cast-mypage')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
@endpush

@section('content')
<div class="content-wrapper animate-fadeIn">
    <div class="cast-mypage-sub-page">
        @include('casts.mypage.parts.menu', ['current' => 'payment'])

        <section class="mypage-area">
            <h1 class="mypage-page-title serif-font">請求・入金管理</h1>
            <div class="mypage-detail-box">
                <div class="mypage-section">
                    <p class="cast-mypage-placeholder">
                        請求履歴や入金状況を確認できます。<br>
                        （準備中）
                    </p>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
