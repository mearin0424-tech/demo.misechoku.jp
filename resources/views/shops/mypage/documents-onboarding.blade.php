@extends('layouts.app')

@section('title', '許可証の提出')
@section('body-class', 'page-shop-mypage shop-mypage-v2 page-shop-documents-onboarding')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/shop-license-documents.css') }}?v=20260505">
@endpush

@section('content')
<div class="mypage-page contents inner animate-fadeIn shop-mypage-v2">
    <section class="mypage-area">
        <h1 class="mypage-shop-name serif-font shop-mypage-store-title">許可証の提出</h1>
        <p class="shop-doc-onboarding-intro">
            ご登録ありがとうございます。求人をサイト上で公開するには、<strong>営業許可証</strong>と<strong>風営許可証</strong>の両方について、ファイルの提出と運営による承認が必要です。下の一覧からそれぞれアップロードしてください（後からマイページでも変更できます）。
        </p>

        @if(session('message'))
            <p class="profile-edit-flash" style="margin-bottom:16px;">{{ session('message') }}</p>
        @endif

        @include('shops.mypage.partials.shop-license-documents', ['documents' => $documents ?? []])

        <div class="shop-doc-onboarding-actions">
            <a href="{{ route('shop.recruits.edit') }}" class="is-primary">求人票の登録へ進む</a>
            <a href="{{ route('shop.home') }}" class="is-secondary">あとで（ホームへ）</a>
            <a href="{{ route('shop.mypage.index') }}" class="is-muted">マイページへ</a>
        </div>
    </section>
</div>
@endsection
