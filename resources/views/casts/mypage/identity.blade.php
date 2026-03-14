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
                            'approved'      => '承認済み',
                            'rejected'      => '不備・却下',
                        ][$status] ?? '未提出';
                        $labelAdmin = [
                            'not_submitted' => '提出待ち',
                            'pending'       => '未承認',
                            'approved'      => '承認済み',
                            'rejected'      => '差し戻し',
                        ][$status] ?? '提出待ち';
                        $latest = $latestIdentityDocument ?? null;
                    @endphp
                    <ul class="doc-list" style="margin-bottom:16px;">
                        <li class="doc-item">
                            <div class="doc-icon"><i class="fas fa-id-card"></i></div>
                            <div class="doc-info">
                                <span class="doc-name">本人確認書類</span>
                                <span class="doc-status {{ $status === 'approved' ? 'done' : 'pending' }}">
                                    {{ $labelCast }}（運営: {{ $labelAdmin }}）
                                </span>
                                @if($latest)
                                    <span class="text-xs" style="display:block; margin-top:6px; color:#C9B8B8;">
                                        種別: {{ $latest['type_label'] }}
                                        @if(!empty($latest['updated_at_label']))
                                            / 更新: {{ $latest['updated_at_label'] }}
                                        @endif
                                    </span>
                                @endif
                            </div>
                        </li>
                    </ul>

                    @if($latest && !empty($latest['ng_reason']))
                        <div class="management-summary-note" style="margin-bottom:16px; color:#ffb4b4;">
                            差し戻し理由: {{ $latest['ng_reason'] }}
                        </div>
                    @endif

                    <form id="cast-identity-form" class="management-bank-form" enctype="multipart/form-data">
                        @csrf
                        <div class="bank-form-row">
                            <label class="bank-label">書類種別</label>
                            <select name="type" class="bank-input" required>
                                <option value="driver_license">運転免許証</option>
                                <option value="passport">パスポート</option>
                                <option value="my_number">マイナンバーカード</option>
                            </select>
                        </div>
                        <div class="bank-form-row">
                            <label class="bank-label">表面（画像 or PDF）</label>
                            <input type="file" name="front_file" class="bank-input" accept=".pdf,image/*" required>
                        </div>
                        <div class="bank-form-row">
                            <label class="bank-label">裏面（任意）</label>
                            <input type="file" name="back_file" class="bank-input" accept=".pdf,image/*">
                        </div>
                        <div class="bank-form-row">
                            <label class="bank-label">有効期限（任意）</label>
                            <input type="date" name="expired_at" class="bank-input">
                        </div>
                        <div class="text-right" style="margin-top:12px;">
                            <button type="submit" class="btn-action manage">
                                <i class="fas fa-upload"></i> アップロード
                            </button>
                        </div>
                        <p id="cast-identity-message" class="management-summary-note" style="display:none; margin-top:8px;"></p>
                    </form>

                    @if(!empty($identityDocuments))
                        <div style="margin-top:24px;">
                            <h3 class="section-title" style="font-size:1rem;">提出履歴</h3>
                            <ul class="doc-list">
                                @foreach($identityDocuments as $document)
                                    <li class="doc-item">
                                        <div class="doc-icon"><i class="fas fa-file-alt"></i></div>
                                        <div class="doc-info">
                                            <span class="doc-name">{{ $document['type_label'] }}</span>
                                            <span class="doc-status {{ $document['status_key'] === 'approved' ? 'done' : 'pending' }}">
                                                {{ $document['status_label'] }}
                                            </span>
                                            <span class="text-xs" style="display:block; margin-top:6px; color:#C9B8B8;">
                                                更新日時: {{ $document['updated_at_label'] ?? '-' }}
                                            </span>
                                            @if(!empty($document['ng_reason']))
                                                <span class="text-xs" style="display:block; margin-top:4px; color:#ffb4b4;">
                                                    差し戻し理由: {{ $document['ng_reason'] }}
                                                </span>
                                            @endif
                                            <div class="text-xs" style="display:flex; gap:12px; margin-top:6px;">
                                                @if(!empty($document['front_url']))
                                                    <a href="{{ $document['front_url'] }}" target="_blank" rel="noopener">表面を確認</a>
                                                @endif
                                                @if(!empty($document['back_url']))
                                                    <a href="{{ $document['back_url'] }}" target="_blank" rel="noopener">裏面を確認</a>
                                                @endif
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
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
        }).then(function (r) {
            return r.json().then(function (json) {
                if (!r.ok) {
                    throw json;
                }
                return json;
            });
        })
        .then(function (res) {
            var msgEl = document.getElementById('cast-identity-message');
            if (!msgEl) return;
            msgEl.style.display = 'block';
            msgEl.textContent = res && res.message ? res.message : 'アップロードしました。';
            window.location.reload();
        }).catch(function (error) {
            var messages = error && error.errors ? Object.values(error.errors).flat() : [];
            alert(messages[0] || 'アップロードに失敗しました。時間をおいて再度お試しください。');
        });
    });
});
</script>
@endpush

