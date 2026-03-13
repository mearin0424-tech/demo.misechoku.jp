@extends('layouts.app')

@section('title', 'マイページ - 請求・入金管理')
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
            <h1 class="mypage-page-title serif-font">請求・入金管理</h1>
            <div class="mypage-detail-box">
                <div class="mypage-section">
                    @if(session('status'))
                        <p class="management-summary-note">{{ session('status') }}</p>
                    @endif
                    @if(session('error'))
                        <p class="management-summary-note" style="color:#fca5a5;">{{ session('error') }}</p>
                    @endif
                    @if(empty($payments))
                        <p class="cast-mypage-placeholder">
                            請求履歴や入金状況を確認できます。<br>
                            まだ請求・入金の履歴がありません。
                        </p>
                    @else
                        <h2 class="mypage-actions-title">請求・入金履歴</h2>
                        <ul class="doc-list">
                            @foreach($payments as $row)
                                <li class="doc-item">
                                    <div class="doc-icon">
                                        <i class="fas fa-money-check-alt"></i>
                                    </div>
                                    <div class="doc-info">
                                        <span class="doc-name">{{ $row['title'] }}</span>
                                        <span class="doc-status {{ $row['status_class'] ?? '' }}">
                                            {{ $row['status_label'] }}
                                        </span>
                                        @if(!empty($row['date']))
                                            <span class="date-text numeric-font">{{ $row['date'] }}</span>
                                        @endif
                                        @if(!empty($row['amount']))
                                            <span class="date-text numeric-font">振込予定額: ¥{{ number_format($row['amount']) }}</span>
                                        @endif
                                    </div>
                                    @if(!empty($row['link']))
                                        <a href="{{ $row['link'] }}" class="doc-arrow">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div class="mypage-section">
                    <h2 class="mypage-actions-title">現在の入金ステータス</h2>
                    @php $flow = $depositFlow ?? ['cast' => '未申請','shop' => '未稼働','admin' => '未稼働']; @endphp
                    <table class="admin-table" style="margin-bottom:8px;">
                        <thead>
                            <tr>
                                <th>アクター</th>
                                <th>ステータス</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>キャスト</td><td>{{ $flow['cast'] }}</td></tr>
                            <tr><td>店舗</td><td>{{ $flow['shop'] }}</td></tr>
                            <tr><td>運営</td><td>{{ $flow['admin'] }}</td></tr>
                        </tbody>
                    </table>
                    <div class="text-right">
                        @if($canRequestDeposit ?? false)
                            <form method="POST" action="{{ route('cast.mypage.deposit.request') }}">
                                @csrf
                                <button type="submit" class="btn-action manage">
                                    ボーナス達成・入金を申請する
                                </button>
                            </form>
                        @elseif(($currentDeposit['status_code'] ?? null) === 6)
                            <form method="POST" action="{{ route('cast.mypage.deposit.confirm') }}">
                                @csrf
                                <button type="submit" class="btn-action manage">
                                    入金を確認しました
                                </button>
                            </form>
                        @elseif(!empty($requestDisabledReason))
                            <p class="text-xs" style="color:#C9B8B8;">{{ $requestDisabledReason }}</p>
                        @endif
                    </div>
                </div>

                <div class="mypage-section">
                    <h2 class="mypage-actions-title">キャストの振込先口座</h2>
                    <p class="text-xs" style="color:#C9B8B8; margin-bottom:8px;">
                        報酬をお受け取りいただくための銀行口座情報を登録してください。
                    </p>
                    <form id="cast-bank-form" class="management-bank-form">
                        @csrf
                        <div class="bank-form-row">
                            <label class="bank-label">金融機関名</label>
                            <input type="text" name="bank_name" class="bank-input" value="{{ $castBank['bank_name'] ?? '' }}" placeholder="〇〇銀行" required>
                        </div>
                        <div class="bank-form-row">
                            <label class="bank-label">支店名</label>
                            <input type="text" name="branch_name" class="bank-input" value="{{ $castBank['branch_name'] ?? '' }}" placeholder="△△支店">
                        </div>
                        <div class="bank-form-row">
                            <label class="bank-label">口座種別</label>
                            <select name="account_type" class="bank-input" required>
                                <option value="ordinary" {{ ($castBank['account_type'] ?? 'ordinary') === 'ordinary' ? 'selected' : '' }}>普通</option>
                                <option value="checking" {{ ($castBank['account_type'] ?? '') === 'checking' ? 'selected' : '' }}>当座</option>
                            </select>
                        </div>
                        <div class="bank-form-row">
                            <label class="bank-label">口座番号</label>
                            <input type="text" name="account_number" class="bank-input" value="{{ $castBank['account_number'] ?? '' }}" placeholder="1234567" required>
                        </div>
                        <div class="bank-form-row">
                            <label class="bank-label">口座名義（カナ）</label>
                            <input type="text" name="account_name" class="bank-input" value="{{ $castBank['account_name'] ?? '' }}" placeholder="ミセチョク ハナコ" required>
                        </div>
                        <div class="text-right mt-3">
                            <button type="submit" class="btn-action manage">
                                <i class="fas fa-save"></i> 口座情報を保存
                            </button>
                        </div>
                        <p id="cast-bank-message" class="management-summary-note" style="display:none;"></p>
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
    var form = document.getElementById('cast-bank-form');
    if (!form) return;
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var formData = new FormData(form);
        fetch('{{ route("cast.mypage.payment.bank.update") }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json'
            },
            body: formData
        }).then(function (r) { return r.json(); })
        .then(function (res) {
            var msgEl = document.getElementById('cast-bank-message');
            if (!msgEl) return;
            msgEl.style.display = 'block';
            msgEl.textContent = res && res.message ? res.message : '保存しました。';
        }).catch(function () {
            alert('保存に失敗しました。時間をおいて再度お試しください。');
        });
    });
});
</script>
@endpush
