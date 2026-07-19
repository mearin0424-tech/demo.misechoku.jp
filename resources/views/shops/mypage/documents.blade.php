@extends('layouts.app-v2')

@section('title', '許可証の提出・管理')
@section('body-class', 'page-shop-documents')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/shop-license-documents.css') }}?v=20260719-light">
@endpush

@section('content')
@php
    $docList = collect($documents ?? []);
    $docTotal = $docList->count();
    $docApproved = $docList->where('status', 'approved')->count();
    $docPending = $docList->where('status', 'pending')->count();
    $docRejected = $docList->where('status', 'rejected')->count();
    $docNotSubmitted = $docTotal - $docApproved - $docPending - $docRejected;
@endphp
<div class="license-page">
    {{-- タイトルはヘッダー中央、説明はオコジョガイド（character_guide_settings）に集約 --}}

    {{-- 提出状況サマリー：提出できているか・何を提出したかを一目で --}}
    <section class="license-summary" aria-label="提出状況サマリー">
        <div class="license-summary__counts">
            <span class="license-summary__count is-approved"><i class="fas fa-circle-check"></i>承認 {{ $docApproved }}</span>
            <span class="license-summary__count is-pending"><i class="fas fa-hourglass-half"></i>審査中 {{ $docPending }}</span>
            @if($docRejected > 0)
                <span class="license-summary__count is-rejected"><i class="fas fa-circle-exclamation"></i>差戻し {{ $docRejected }}</span>
            @endif
            <span class="license-summary__count is-none"><i class="fas fa-minus"></i>未提出 {{ $docNotSubmitted }}</span>
        </div>
        <ul class="license-summary__list">
            @foreach($docList as $doc)
                @php
                    $st = $doc['status'] ?? 'not_submitted';
                    $rec = $doc['record'] ?? [];
                    $fileName = $rec['file_name'] ?? '';
                    $updated = $rec['updated_at_label'] ?? '';
                @endphp
                <li class="license-summary__row is-{{ str_replace('_', '-', $st) }}">
                    <span class="license-summary__row-icon" aria-hidden="true">
                        <i class="fas {{ $st === 'approved' ? 'fa-circle-check' : ($st === 'pending' ? 'fa-hourglass-half' : ($st === 'rejected' ? 'fa-circle-exclamation' : 'fa-minus')) }}"></i>
                    </span>
                    <span class="license-summary__row-body">
                        <span class="license-summary__row-name">{{ $doc['display_name'] ?? ($doc['name'] ?? '許可証') }}</span>
                        <span class="license-summary__row-detail">
                            @if($fileName !== '')
                                提出ファイル: <strong>{{ $fileName }}</strong>@if($updated !== '')（{{ $updated }}）@endif
                            @elseif($st === 'not_submitted')
                                まだ提出されていません
                            @else
                                アップロード済みファイル
                            @endif
                        </span>
                    </span>
                    <span class="license-summary__row-status">{{ $doc['status_label'] ?? '未提出' }}</span>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- 各書類の提出・差し替え（既存アコーディオン部品を流用） --}}
    @include('shops.mypage.partials.shop-license-documents', ['documents' => $documents ?? []])

    <p class="license-page__back">
        <a href="{{ route('shop.mypage.index') }}"><i class="fas fa-arrow-left"></i> マイページへ戻る</a>
    </p>
</div>
@endsection

@push('styles')
<style>
/* ===== 許可証ページ（ライトモード）レイアウト ===== */
.license-page { padding: 16px 0 32px; }
.license-page__head { margin-bottom: 14px; }
.license-page__title {
    display: flex; align-items: center; gap: 8px;
    margin: 0 0 6px;
    font-size: 1.15rem; font-weight: 800; color: #241f33;
}
.license-page__title i { color: #7c3aed; }
.license-page__desc { margin: 0; font-size: 0.82rem; line-height: 1.7; color: #5f5876; }
.license-page__back { margin: 18px 0 0; font-size: 0.82rem; }
.license-page__back a { color: #6d28d9; text-decoration: none; font-weight: 700; }

/* 提出状況サマリー */
.license-summary {
    background: #ffffff;
    border: 1px solid rgba(124, 58, 237, 0.20);
    border-radius: 14px;
    padding: 14px;
    margin-bottom: 16px;
    box-shadow: 0 6px 18px rgba(76, 29, 149, 0.08);
}
.license-summary__counts { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
.license-summary__count {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 10px; border-radius: 999px;
    font-size: 0.74rem; font-weight: 800;
    background: #f5f2fb; color: #6d6685; border: 1px solid rgba(95, 88, 118, 0.20);
}
.license-summary__count.is-approved { background: rgba(5,150,105,0.08); color: #059669; border-color: rgba(5,150,105,0.30); }
.license-summary__count.is-pending  { background: rgba(180,83,9,0.08); color: #b45309; border-color: rgba(180,83,9,0.30); }
.license-summary__count.is-rejected { background: rgba(220,38,38,0.06); color: #dc2626; border-color: rgba(220,38,38,0.30); }
.license-summary__list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; }
.license-summary__row {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 4px;
}
.license-summary__row + .license-summary__row { border-top: 1px solid rgba(124, 58, 237, 0.10); }
.license-summary__row-icon {
    flex: 0 0 auto; width: 30px; height: 30px; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 0.9rem;
    background: #f5f2fb; color: #8b84a1;
}
.license-summary__row.is-approved .license-summary__row-icon { background: rgba(5,150,105,0.10); color: #059669; }
.license-summary__row.is-pending  .license-summary__row-icon { background: rgba(180,83,9,0.10); color: #b45309; }
.license-summary__row.is-rejected .license-summary__row-icon { background: rgba(220,38,38,0.08); color: #dc2626; }
.license-summary__row-body { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 2px; }
.license-summary__row-name { font-size: 0.88rem; font-weight: 800; color: #241f33; }
.license-summary__row-detail { font-size: 0.74rem; color: #6d6685; overflow-wrap: anywhere; }
.license-summary__row-detail strong { color: #2d2742; }
.license-summary__row-status { flex: 0 0 auto; font-size: 0.74rem; font-weight: 800; color: #6d6685; white-space: nowrap; }
.license-summary__row.is-approved .license-summary__row-status { color: #059669; }
.license-summary__row.is-pending  .license-summary__row-status { color: #b45309; }
.license-summary__row.is-rejected .license-summary__row-status { color: #dc2626; }

/* ===== アコーディオン部品のダーク面をライトへ上書き（partial の後読みで勝つ） ===== */
.license-accordion { background: #ffffff; }
.license-accordion__body { background: #faf8fd; }
.license-accordion__file-row,
.license-accordion__dropzone { background: #ffffff; }
.license-accordion__withdraw-zone { background: #f5f2fb; }
.license-accordion__status--not-submitted {
    background: #f5f2fb;
    color: #6d6685;
}
.license-accordion__optional { background: rgba(95, 88, 118, 0.10); }
.license-accordion__btn--secondary { background: #ffffff; }
.license-accordion__btn--secondary:hover { background: rgba(124, 58, 237, 0.06); }
.license-accordion__input { color-scheme: light; }
.license-withdraw-modal__panel { background: #ffffff; }
</style>
@endpush
