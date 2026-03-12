@extends('layouts.app')

@section('title', '本人確認')
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
            <h1 class="mypage-page-title serif-font">本人確認</h1>

            <div class="mypage-detail-box">
                <div class="mypage-section">
                    <h2 class="section-title section-title-gold">本人確認書類の提出状況</h2>
                    <p class="text-xs" style="color:#C9B8B8; margin-bottom:8px;">
                        パスポート / 運転免許証 / マイナンバーカードのいずれかをアップロードしてください。
                    </p>
                    @php
                        $status = $identityStatus ?? 'not_submitted';
                        $labelCast = [
                            'not_submitted' => '未提出',
                            'pending'       => '提出済み（未承認）',
                            'approved'      => '提出済み',
                        ][$status] ?? '未提出';
                        $labelAdmin = [
                            'not_submitted' => '提出待ち',
                            'pending'       => '未承認',
                            'approved'      => '承認済み',
                        ][$status] ?? '提出待ち';
                    @endphp
                    <ul class="doc-list" style="margin-bottom:16px;">
                        <li class="doc-item">
                            <div class="doc-icon"><i class="fas fa-id-card"></i></div>
                            <div class="doc-info">
                                <span class="doc-name">本人確認書類</span>
                                <span class="doc-status {{ $status === 'approved' ? 'done' : 'pending' }}">
                                    {{ $labelCast }}（運営: {{ $labelAdmin }}）
                                </span>
                            </div>
                        </li>
                    </ul>

                    <form id="cast-identity-form" class="management-bank-form">
                        @csrf
                        <div class="bank-form-row">
                            <label class="bank-label">本人確認書類（画像 or PDF）</label>
                            <input type="file" name="file" class="bank-input" accept=".pdf,image/*" required>
                        </div>
                        <div class="text-right" style="margin-top:12px;">
                            <button type="submit" class="btn-action manage">
                                <i class="fas fa-upload"></i> アップロード
                            </button>
                        </div>
                        <p id="cast-identity-message" class="management-summary-note" style="display:none; margin-top:8px;"></p>
                    </form>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('cast-identity-form');
    if (!form) return;
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var formData = new FormData(form);
        fetch('{{ route("cast.mypage.identity.upload") }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json'
            },
            body: formData
        }).then(function (r) { return r.json(); })
        .then(function (res) {
            var msgEl = document.getElementById('cast-identity-message');
            if (!msgEl) return;
            msgEl.style.display = 'block';
            msgEl.textContent = res && res.message ? res.message : 'アップロードしました。';
        }).catch(function () {
            alert('アップロードに失敗しました。時間をおいて再度お試しください。');
        });
    });
});
</script>
@endpush

