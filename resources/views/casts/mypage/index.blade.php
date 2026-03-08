@extends('layouts.app')

@section('title', 'マイページ')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
@endpush

@section('content')
<div class="mypage-page contents inner animate-fadeIn">
    <section class="mypage-area">
        <h1 class="mypage-shop-name serif-font gold-gradient">マイページ</h1>

        <div class="mypage-detail-box">
            {{-- プロフィール --}}
            <div class="mypage-section profile-info-section">
                <div class="section-title-row">
                    <h2 class="section-title">プロフィール</h2>
                    <a href="{{ route('cast.profile.edit') }}" class="btn-outline-gold">プロフィール確認・編集</a>
                </div>
                <p class="shop-access-text text-sm opacity-80">
                    ニックネーム、スペック、その他情報を編集できます。
                </p>
            </div>

            {{-- 書類管理 --}}
            <div class="mypage-section document-section">
                <h2 class="section-title section-title-gold">書類管理</h2>
                <ul class="doc-list">
                    @foreach($documents as $doc)
                    <li class="doc-item">
                        <div class="doc-icon"><i class="fas fa-file-alt"></i></div>
                        <div class="doc-info">
                            <span class="doc-name">{{ $doc['name'] }}</span>
                            <span class="doc-status {{ $doc['status'] == 'submitted' ? 'done' : 'pending' }}">
                                {{ $doc['status'] == 'submitted' ? '提出済' : '未提出' }}
                            </span>
                        </div>
                        <i class="fas fa-chevron-right doc-arrow"></i>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>
</div>
@endsection
