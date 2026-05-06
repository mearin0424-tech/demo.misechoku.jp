@extends('layouts.app')

@section('title', '許可証の管理')
@section('body-class', 'page-shop-mypage shop-mypage-v2 page-shop-documents-manage')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/shop-license-documents.css') }}?v=20260505">
@endpush

@section('content')
@php
    $s = $document['status'] ?? 'not_submitted';
    $record = $document['record'] ?? null;
    $canRequestReview = !empty($record['can_request_review']);
    $canWithdrawReview = !empty($record['can_withdraw_review']);
    $isBusiness = ($document['key'] ?? '') === 'business';
@endphp

<div class="mypage-page contents inner animate-fadeIn shop-mypage-v2">
    <section class="mypage-area">
        <h1 class="mypage-shop-name serif-font shop-mypage-store-title">{{ $document['name'] ?? '許可証' }}</h1>

        @if(session('message'))
            <p class="profile-edit-flash" style="margin-bottom:16px;">{{ session('message') }}</p>
        @endif

        @if($errors->any())
            <div class="profile-edit-errors" style="margin-bottom:16px;">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="shop-mypage-section document-section">
            <div class="shop-mypage-license-card">
                <div class="shop-mypage-license-card-body">
                    <p class="document-upload-name">{{ $document['name'] ?? '許可証' }}</p>
                    <div class="document-status-row">
                        <span class="document-status-chip is-{{ str_replace('_', '-', $s) }}">
                            {{ $document['status_label'] ?? '未提出' }}
                        </span>
                        @if(!empty($record['expiring_soon']))
                            <span class="document-expiring-soon-chip">{{ $record['expiration_notice_label'] ?? '更新期限半年以内' }}</span>
                        @endif
                    </div>
                    <p class="document-upload-meta">
                        最終更新: {{ $record['updated_at_label'] ?? '—' }}
                    </p>
                    @if($s === 'rejected' && !empty($record['ng_reason']))
                        <p class="license-upload-modal__ng">差し戻し理由: {{ $record['ng_reason'] }}</p>
                    @endif
                </div>
            </div>
        </div>

        @if(!empty($record['file_url']))
            <p style="margin: 12px 0 20px;">
                <a href="{{ $record['file_url'] }}" target="_blank" rel="noopener noreferrer" class="is-muted">現在のファイルを確認する</a>
            </p>
        @endif

        @if($canRequestReview)
            <form method="post" action="{{ route('shop.mypage.documents.upload') }}" enctype="multipart/form-data" style="margin-bottom:14px;">
                @csrf
                <input type="hidden" name="type" value="{{ $document['key'] }}">
                <label for="license-file" class="license-upload-modal__expired-label">ファイルを選択（PDF/JPG/PNG, 8MBまで）</label>
                <input id="license-file" type="file" name="file" required accept=".pdf,.png,.jpg,.jpeg,image/*,application/pdf">

                @if($isBusiness)
                    <div style="margin-top:10px;">
                        <label class="license-upload-modal__expired-label" for="expired-at-upload">営業許可証の有効期限</label>
                        <input id="expired-at-upload" type="date" name="expired_at" value="{{ $record['expired_at'] ?? '' }}" min="{{ now()->format('Y-m-d') }}">
                    </div>
                @endif
                <div class="shop-doc-onboarding-actions" style="margin-top:12px;">
                    <button type="submit" class="is-primary">ファイルをアップロード</button>
                </div>
            </form>

            <form method="post" action="{{ route('shop.mypage.documents.request-review') }}" style="margin-bottom:14px;">
                @csrf
                <input type="hidden" name="type" value="{{ $document['key'] }}">
                @if($isBusiness)
                    <label class="license-upload-modal__expired-label" for="expired-at-request">営業許可証の有効期限（提出時）</label>
                    <input id="expired-at-request" type="date" name="expired_at" value="{{ $record['expired_at'] ?? '' }}" min="{{ now()->format('Y-m-d') }}" required>
                @endif
                <div class="shop-doc-onboarding-actions" style="margin-top:12px;">
                    <button type="submit" class="is-primary">提出する</button>
                </div>
            </form>
        @endif

        @if($canWithdrawReview)
            <form method="post" action="{{ route('shop.mypage.documents.withdraw-review') }}" onsubmit="return confirm('提出を取り下げます。よろしいですか？');">
                @csrf
                <input type="hidden" name="type" value="{{ $document['key'] }}">
                <div class="shop-doc-onboarding-actions">
                    <button type="submit" class="is-secondary">提出取り下げ</button>
                </div>
            </form>
        @endif

        <div class="shop-doc-onboarding-actions">
            <a href="{{ route('shop.mypage.index') }}" class="is-muted">マイページへ戻る</a>
            <a href="{{ route('shop.mypage.documents.onboarding') }}" class="is-muted">提出一覧へ戻る</a>
        </div>
    </section>
</div>
@endsection
