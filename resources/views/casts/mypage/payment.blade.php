@extends('layouts.app')

@section('title', 'マイページ - 請求・入金管理')
@section('body-class', 'page-cast-mypage')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
<style>
    .input-hint {
        margin-top: 6px;
        font-size: 0.72rem;
        line-height: 1.6;
        color: #9f8d8d;
    }
    .deposit-precheck {
        display: grid;
        gap: 14px;
    }
    .deposit-precheck-card {
        padding: 18px;
        border-radius: 18px;
        border: 1px solid rgba(255,255,255,0.08);
        background: rgba(255,255,255,0.03);
    }
    .deposit-precheck-title {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 10px;
        font-weight: 700;
    }
    .deposit-precheck-meta,
    .deposit-precheck-note {
        font-size: 0.82rem;
        line-height: 1.7;
        color: #cdbcbc;
    }
    .deposit-precheck-note {
        margin-top: 10px;
    }
    .deposit-checklist {
        display: grid;
        gap: 10px;
        margin-top: 12px;
    }
    .deposit-check-row {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        font-size: 0.9rem;
        color: #f7eded;
    }
    .deposit-review-grid {
        display: grid;
        gap: 12px;
        margin-top: 12px;
    }
    .deposit-review-score {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .deposit-review-score select,
    .deposit-review-grid textarea {
        width: 100%;
        border-radius: 12px;
        border: 1px solid rgba(255,255,255,0.08);
        background: rgba(14, 7, 8, 0.9);
        color: #fff;
        padding: 12px 14px;
    }
    .deposit-review-score select {
        max-width: 120px;
    }
</style>
@endpush

@section('content')
<div class="content-wrapper animate-fadeIn">
    <div class="cast-mypage-sub-page">
        <section class="mypage-area">
            @php
                $flow = $depositFlow ?? ['cast' => '未申請','shop' => '未稼働','admin' => '未稼働'];
                $currentStatusLabel = $currentDeposit['status_label'] ?? (($canRequestDeposit ?? false) ? '入金申請が可能です' : '未申請');
            @endphp
            <a href="{{ route('cast.mypage.index') }}" class="cast-mypage-back-link">
                <i class="fas fa-chevron-left"></i> マイページへ戻る
            </a>
            <h1 class="mypage-page-title serif-font">請求・入金管理</h1>
            <div class="mypage-detail-box">
                <div class="mypage-section">
                    <div class="mypage-payment-hero">
                        <span class="mypage-payment-hero-label">現在の状況</span>
                        <strong class="mypage-payment-hero-value">{{ $currentStatusLabel }}</strong>
                        <p class="mypage-payment-hero-note">
                            {{ $requestDisabledReason ?? '採用後の入金申請から、運営確認・振込完了までをここで管理できます。' }}
                        </p>
                    </div>

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
                        <ul class="mypage-status-card-list">
                            @foreach($payments as $row)
                                <li class="mypage-status-card">
                                    <div class="mypage-status-card-icon">
                                        <i class="fas fa-money-check-alt"></i>
                                    </div>
                                    <div class="mypage-status-card-body">
                                        <div class="mypage-status-card-head">
                                            <span class="mypage-status-card-name">{{ $row['title'] }}</span>
                                            <span class="doc-status {{ $row['status_class'] ?? '' }}">
                                                {{ $row['status_label'] }}
                                            </span>
                                        </div>
                                        @if(!empty($row['date']))
                                            <span class="mypage-status-card-date numeric-font">{{ $row['date'] }}</span>
                                        @endif
                                        @if(!empty($row['amount']))
                                            <span class="mypage-status-card-meta numeric-font">振込予定額: ¥{{ number_format($row['amount']) }}</span>
                                        @endif
                                    </div>
                                    @if(!empty($row['link']))
                                        <a href="{{ $row['link'] }}" class="mypage-status-card-link">
                                            <span class="mypage-status-card-link-text">詳細</span>
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                @if(!empty($requestTarget))
                    <div class="mypage-section">
                        <h2 class="mypage-actions-title">ボーナス金申請前の確認</h2>
                        <div class="deposit-precheck">
                            <div class="deposit-precheck-card">
                                <div class="deposit-precheck-title">
                                    <span>{{ $requestTarget['shop_name'] ?? '対象案件' }}</span>
                                    <span class="doc-status status-pending">採用済み案件</span>
                                </div>
                                <div class="deposit-precheck-meta">
                                    ボーナス金額: ¥{{ number_format((int) ($requestTarget['bonus_amount'] ?? 0)) }}
                                </div>
                                <div class="deposit-precheck-note">
                                    {!! nl2br(e($requestTarget['bonus_condition'] ?: '求人情報に登録された条件を満たしているか確認してください。')) !!}
                                </div>
                            </div>

                            @if($canRequestDeposit ?? false)
                                <form method="POST" action="{{ route('cast.mypage.deposit.request') }}" class="deposit-precheck-card">
                                    @csrf
                                    <div class="deposit-precheck-title">
                                        <span>確認・レビュー投稿・入金依頼</span>
                                        @if(!empty($requestTarget['review_exists']))
                                            <span class="doc-status status-paid">レビュー投稿済み</span>
                                        @else
                                            <span class="doc-status status-pending">レビュー未投稿</span>
                                        @endif
                                    </div>
                                    <div class="deposit-checklist">
                                        <label class="deposit-check-row">
                                            <input type="checkbox" name="confirm_bonus_condition" value="1">
                                            <span>求人情報に登録されたボーナス達成条件を確認し、申請内容に相違がないことを確認しました。</span>
                                        </label>
                                    </div>

                                    @if(!empty($requestTarget['review_exists']))
                                        <p class="deposit-precheck-note">
                                            {{ $requestTarget['review_posted_at'] ?? '' }} にレビュー投稿済みです。<br>
                                            {{ $requestTarget['review_comment'] ?? '' }}
                                        </p>
                                    @else
                                        <div class="deposit-review-grid">
                                            @foreach(($requestTarget['review_contents'] ?? []) as $content)
                                                <label class="deposit-review-score">
                                                    <span>{{ $content['name'] }}</span>
                                                    <select name="review_scores[{{ $content['id'] }}]">
                                                        <option value="">選択</option>
                                                        @for($score = 5; $score >= 1; $score--)
                                                            <option value="{{ $score }}">{{ $score }} / 5</option>
                                                        @endfor
                                                    </select>
                                                </label>
                                            @endforeach
                                            <textarea name="review_comment" rows="4" placeholder="働いてみた感想、雰囲気、条件の印象などを入力してください。"></textarea>
                                        </div>
                                    @endif

                                    <div class="text-right mt-3">
                                        <button type="submit" class="btn-action manage">
                                            {{ !empty($requestTarget['review_exists']) ? '入金依頼を送信する' : 'レビュー投稿と入金依頼を送信する' }}
                                        </button>
                                    </div>
                                </form>
                            @elseif(!empty($requestDisabledReason))
                                <div class="deposit-precheck-card">
                                    <p class="deposit-precheck-note">{{ $requestDisabledReason }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="mypage-section">
                    <h2 class="mypage-actions-title">現在の入金ステータス</h2>
                    <div class="mypage-flow-grid">
                        <div class="mypage-flow-card">
                            <span class="mypage-flow-card-label">キャスト</span>
                            <strong class="mypage-flow-card-value">{{ $flow['cast'] }}</strong>
                        </div>
                        <div class="mypage-flow-card">
                            <span class="mypage-flow-card-label">店舗</span>
                            <strong class="mypage-flow-card-value">{{ $flow['shop'] }}</strong>
                        </div>
                        <div class="mypage-flow-card">
                            <span class="mypage-flow-card-label">運営</span>
                            <strong class="mypage-flow-card-value">{{ $flow['admin'] }}</strong>
                        </div>
                    </div>
                    <div class="text-right">
                        @if(($currentDeposit['status_code'] ?? null) === 6)
                            <form method="POST" action="{{ route('cast.mypage.deposit.confirm') }}">
                                @csrf
                                <button type="submit" class="btn-action manage">
                                    入金を確認しました
                                </button>
                            </form>
                        @elseif(empty($requestTarget) && !empty($requestDisabledReason))
                            <p class="text-xs" style="color:#C9B8B8;">{{ $requestDisabledReason }}</p>
                        @endif
                    </div>
                </div>

                <div class="mypage-section">
                    <h2 class="mypage-actions-title">キャストの振込先口座</h2>
                    <p class="text-xs" style="color:#C9B8B8; margin-bottom:8px;">
                        報酬をお受け取りいただくための銀行口座情報を登録してください。
                    </p>
                    <form id="cast-bank-form" class="management-bank-form" data-bank-autocomplete>
                        @csrf
                        <div class="bank-form-row">
                            <label class="bank-label">金融機関名</label>
                            <input type="text" name="bank_name" class="bank-input" value="{{ $castBank['bank_name'] ?? '' }}" placeholder="〇〇銀行" autocomplete="off" list="cast-bank-suggestions" data-bank-name-input required>
                            <input type="hidden" name="bank_code" value="{{ $castBank['bank_code'] ?? '' }}" data-bank-code-input>
                            <datalist id="cast-bank-suggestions" data-bank-list></datalist>
                            <p class="input-hint">金融機関名を入力すると候補が表示されます。候補から選ぶと支店候補も検索しやすくなります。</p>
                        </div>
                        <div class="bank-form-row">
                            <label class="bank-label">支店名</label>
                            <input type="text" name="branch_name" class="bank-input" value="{{ $castBank['branch_name'] ?? '' }}" placeholder="△△支店" autocomplete="off" list="cast-branch-suggestions" data-branch-name-input>
                            <input type="hidden" name="branch_code" value="{{ $castBank['branch_code'] ?? '' }}" data-branch-code-input>
                            <datalist id="cast-branch-suggestions" data-branch-list></datalist>
                            <p class="input-hint">支店名は、金融機関を候補から選択したあとに候補表示されます。</p>
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
                            <input type="text" name="account_number" class="bank-input" value="{{ $castBank['account_number'] ?? '' }}" placeholder="1234567" inputmode="numeric" maxlength="7" pattern="[0-9]*" data-account-number-input required>
                            <p class="input-hint">口座番号は7桁の数字で入力してください。</p>
                        </div>
                        <div class="bank-form-row">
                            <label class="bank-label">口座名義（カナ）</label>
                            <input type="text" name="account_name" class="bank-input" value="{{ $castBank['account_name'] ?? '' }}" placeholder="ミセチョクハナコ" data-account-name-input required>
                            <p class="input-hint">ひらがな・半角ｶﾅで入力しても、自動で口座名義向けの形式に整えます。</p>
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
