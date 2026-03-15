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
    .deposit-review-card {
        padding: 14px;
        border-radius: 14px;
        border: 1px solid rgba(255,255,255,0.08);
        background: rgba(255,255,255,0.02);
    }
    .deposit-review-label {
        display: block;
        margin-bottom: 8px;
        font-size: 0.92rem;
        font-weight: 700;
        color: #fff8ea;
    }
    .deposit-review-score {
        display: grid;
        gap: 8px;
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
    .bank-registration-card {
        margin-top: 14px;
        padding: 18px;
        border-radius: 22px;
        border: 1px solid rgba(212, 175, 55, 0.14);
        background: linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.02));
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.05);
    }
    .bank-registration-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 8px;
    }
    .bank-registration-title {
        margin: 0;
        font-size: 1rem;
        color: #fff8ea;
        font-weight: 700;
    }
    .bank-registration-copy {
        margin: 8px 0 0;
        font-size: 0.84rem;
        line-height: 1.8;
        color: #cdbcbc;
    }
    .bank-registration-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
        margin-top: 16px;
    }
    .bank-registration-note {
        margin-top: 14px;
        padding: 12px 14px;
        border-radius: 16px;
        background: rgba(255,255,255,0.04);
        color: #d7c8c8;
        font-size: 0.78rem;
        line-height: 1.7;
    }
    .bank-status-message {
        display: none;
        margin-top: 14px;
        padding: 12px 14px;
        border-radius: 14px;
        font-size: 0.83rem;
        line-height: 1.7;
    }
    .bank-status-message.is-success {
        display: block;
        background: rgba(34, 197, 94, 0.14);
        border: 1px solid rgba(34, 197, 94, 0.22);
        color: #dcfce7;
    }
    .bank-status-message.is-error {
        display: block;
        background: rgba(248, 113, 113, 0.12);
        border: 1px solid rgba(248, 113, 113, 0.24);
        color: #fee2e2;
    }
    @media (max-width: 640px) {
        .bank-registration-grid {
            grid-template-columns: 1fr;
        }
    }

    /* --- 口座セクション（未登録／登録済＋モーダル導線） --- */
    .payment-bank-section {
        padding-top: 1rem;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
    }
    .payment-bank-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 1rem;
    }
    .payment-bank-label {
        font-size: 0.68rem;
        letter-spacing: 0.12em;
        color: #a89b9b;
        margin: 0 0 4px;
    }
    .payment-bank-title {
        margin: 0;
        font-size: 1rem;
        color: var(--color-text-header, #fff);
    }
    .payment-bank-change-btn {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--mypage-gold, #cda766);
        padding: 6px 12px;
        border: 1px solid rgba(205, 167, 102, 0.3);
        border-radius: 9999px;
        background: transparent;
        cursor: pointer;
        white-space: nowrap;
        transition: background 0.2s, color 0.2s;
    }
    .payment-bank-change-btn:hover {
        background: rgba(205, 167, 102, 0.1);
    }
    .payment-bank-unregistered {
        background: rgba(26, 17, 17, 0.95);
        border: 1px dashed rgba(255, 255, 255, 0.12);
        border-radius: 1rem;
        padding: 2rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    .payment-bank-unregistered-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #2a1d1d;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        color: #a89b9b;
        font-size: 1.25rem;
    }
    .payment-bank-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        margin-bottom: 1rem;
    }
    .payment-bank-badge-warning {
        background: #3a2828;
        color: var(--mypage-gold, #cda766);
    }
    .payment-bank-unregistered-text {
        font-size: 0.875rem;
        line-height: 1.7;
        color: #d4c8c8;
        margin: 0 0 1.5rem;
    }
    .payment-bank-register-btn {
        width: 100%;
        padding: 14px 24px;
        border-radius: 12px;
        font-weight: 700;
        color: #110a0a;
        background: linear-gradient(to right, #cda766, #b38f4a);
        box-shadow: 0 4px 15px rgba(205, 167, 102, 0.3);
        border: none;
        cursor: pointer;
        transition: opacity 0.2s, transform 0.1s;
    }
    .payment-bank-register-btn:hover {
        opacity: 0.9;
    }
    .payment-bank-register-btn:active {
        transform: scale(0.98);
    }
    .payment-bank-registered {
        background: linear-gradient(135deg, rgba(35, 24, 24, 0.98), rgba(26, 17, 17, 0.98));
        border: 1px solid rgba(138, 110, 61, 0.4);
        border-radius: 1rem;
        padding: 1.25rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
    }
    .payment-bank-data-rows {
        display: flex;
        flex-direction: column;
        gap: 0;
    }
    .payment-bank-data-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }
    .payment-bank-data-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    .payment-bank-data-label {
        font-size: 0.75rem;
        color: #a89b9b;
    }
    .payment-bank-data-value {
        font-size: 0.875rem;
        font-weight: 500;
        color: #fff;
    }

    /* --- 口座登録モーダル --- */
    .payment-bank-modal {
        position: fixed;
        inset: 0;
        z-index: 50;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        align-items: center;
        background: rgba(0, 0, 0, 0.8);
        backdrop-filter: blur(4px);
        padding: 0;
    }
    .payment-bank-modal[hidden] {
        display: none;
    }
    @media (min-width: 640px) {
        .payment-bank-modal {
            justify-content: center;
        }
    }
    .payment-bank-modal-backdrop {
        position: absolute;
        inset: 0;
        cursor: pointer;
    }
    .payment-bank-modal-panel {
        position: relative;
        width: 100%;
        max-width: 28rem;
        max-height: 90vh;
        background: #160d0d;
        border-top-left-radius: 1.5rem;
        border-top-right-radius: 1.5rem;
        border: 1px solid rgba(255, 255, 255, 0.08);
        display: flex;
        flex-direction: column;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }
    @media (min-width: 640px) {
        .payment-bank-modal-panel {
            border-radius: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
    }
    .payment-bank-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        background: #1a1111;
    }
    .payment-bank-modal-title {
        margin: 0;
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--mypage-gold, #cda766);
        letter-spacing: 0.05em;
    }
    .payment-bank-modal-close {
        width: 2.5rem;
        height: 2.5rem;
        border: none;
        background: transparent;
        color: #a89b9b;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.2s, background 0.2s;
    }
    .payment-bank-modal-close:hover {
        color: #fff;
        background: #2a1d1d;
    }
    .payment-bank-modal-body {
        overflow-y: auto;
        padding: 1.5rem;
    }
    .payment-bank-modal-note {
        font-size: 0.75rem;
        line-height: 1.7;
        color: #a89b9b;
        margin: 0 0 1.5rem;
        padding: 1rem;
        background: #231818;
        border-radius: 0.75rem;
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .payment-bank-modal-grid {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }
    .payment-bank-modal-grid .bank-form-row {
        margin: 0;
    }
    .payment-bank-modal-grid .bank-label {
        display: block;
        font-size: 0.75rem;
        font-weight: 500;
        color: #a89b9b;
        margin-bottom: 6px;
        margin-left: 4px;
    }
    .payment-bank-modal-grid .bank-input {
        width: 100%;
        padding: 14px 1rem;
        border-radius: 0.75rem;
        border: 1px solid rgba(255, 255, 255, 0.08);
        background: #231818;
        color: #fff;
        font-size: 0.875rem;
    }
    .payment-bank-modal-grid .bank-input:focus {
        border-color: var(--mypage-gold, #cda766);
        outline: none;
        box-shadow: 0 0 0 1px var(--mypage-gold, #cda766);
    }
    .payment-bank-modal-footer {
        display: flex;
        gap: 0.75rem;
        padding: 1.25rem 1.5rem;
        background: #1a1111;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
    }
    .payment-bank-modal-btn {
        flex: 1;
        padding: 14px 1rem;
        border-radius: 0.75rem;
        font-size: 0.875rem;
        font-weight: 700;
        cursor: pointer;
        transition: opacity 0.2s, transform 0.1s;
    }
    .payment-bank-modal-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    .payment-bank-modal-btn-cancel {
        background: transparent;
        border: 1px solid rgba(255, 255, 255, 0.08);
        color: #d4c8c8;
    }
    .payment-bank-modal-btn-cancel:hover:not(:disabled) {
        background: #2a1d1d;
    }
    .payment-bank-modal-btn-submit {
        background: linear-gradient(to right, #cda766, #b38f4a);
        color: #110a0a;
        border: none;
        box-shadow: 0 4px 15px rgba(205, 167, 102, 0.2);
    }
    .payment-bank-modal-btn-submit:hover:not(:disabled) {
        opacity: 0.9;
    }
    .payment-bank-modal-btn:active:not(:disabled) {
        transform: scale(0.98);
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
                                            <input type="checkbox" name="confirm_bonus_condition" value="1" {{ old('confirm_bonus_condition') ? 'checked' : '' }}>
                                            <span>求人情報に登録されたボーナス達成条件を確認し、申請内容に相違がないことを確認しました。</span>
                                        </label>
                                    </div>

                                    @if(!empty($requestTarget['review_exists']))
                                        <div class="deposit-precheck-note">
                                            @if(!empty($requestTarget['review_posted_at']))
                                                <div>投稿日時: {{ $requestTarget['review_posted_at'] }}</div>
                                            @endif
                                            @if(!empty($requestTarget['review_average']))
                                                <div style="margin-top:4px;">総合評価: {{ number_format((float) $requestTarget['review_average'], 1) }} / 5</div>
                                            @endif
                                        </div>
                                        @if(!empty($requestTarget['review_details']))
                                            <div class="deposit-review-grid">
                                                @foreach($requestTarget['review_details'] as $detail)
                                                    <div class="deposit-review-card">
                                                        <span class="deposit-review-label">{{ $detail['name'] }}</span>
                                                        <strong>{{ number_format((float) $detail['score'], 1) }} / 5</strong>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                        @if(!empty($requestTarget['review_comment']))
                                            <div class="deposit-precheck-note">{!! nl2br(e($requestTarget['review_comment'])) !!}</div>
                                        @endif
                                    @else
                                        <div class="deposit-precheck-note">
                                            勤務完了後、お店の雰囲気や働きやすさをレビューしてください。設問は運営のレビュー設問マスタに基づいて表示されます。
                                        </div>
                                        <div class="deposit-review-grid">
                                            @foreach(($requestTarget['review_contents'] ?? []) as $content)
                                                <div class="deposit-review-card">
                                                    <label class="deposit-review-score">
                                                        <span class="deposit-review-label">{{ $content['name'] }}</span>
                                                        <select name="review_scores[{{ $content['id'] }}]" required>
                                                            <option value="">評価を選択してください</option>
                                                            @for($score = 5; $score >= 1; $score--)
                                                                <option value="{{ $score }}" {{ (string) old('review_scores.' . $content['id']) === (string) $score ? 'selected' : '' }}>{{ $score }} / 5</option>
                                                            @endfor
                                                        </select>
                                                    </label>
                                                </div>
                                            @endforeach
                                            <div class="deposit-review-card">
                                                <label class="deposit-review-label" for="review-comment">レビューコメント</label>
                                                <textarea id="review-comment" name="review_comment" rows="4" placeholder="働いてみた感想、雰囲気、条件の印象などを入力してください。">{{ old('review_comment') }}</textarea>
                                                <p class="input-hint">接客のしやすさ、スタッフ対応、給与条件の納得感などを書くと他のキャストの参考になります。</p>
                                            </div>
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

                <div class="mypage-section payment-bank-section">
                    <div class="payment-bank-header">
                        <div>
                            <p class="payment-bank-label">キャストの振込先口座</p>
                            <h2 class="mypage-actions-title payment-bank-title">報酬の振込先口座情報</h2>
                        </div>
                        @if(!empty($castBank['exists']))
                            <button type="button" class="payment-bank-change-btn" data-open-bank-modal aria-label="口座情報を変更">
                                変更する
                            </button>
                        @endif
                    </div>

                    @if(empty($castBank['exists']))
                        {{-- 未登録: 口座情報登録モーダルへの導線のみ --}}
                        <div class="payment-bank-unregistered">
                            <div class="payment-bank-unregistered-icon">
                                <i class="fas fa-university" aria-hidden="true"></i>
                            </div>
                            <span class="payment-bank-badge payment-bank-badge-warning">
                                <i class="fas fa-exclamation-circle" aria-hidden="true"></i> 未登録
                            </span>
                            <p class="payment-bank-unregistered-text">
                                報酬を受け取るための口座が登録されていません。<br>
                                申請を行う前に、口座情報の登録をお願いします。
                            </p>
                            <button type="button" class="payment-bank-register-btn" data-open-bank-modal>
                                口座情報を登録する
                            </button>
                        </div>
                    @else
                        {{-- 登録済: 口座情報表示＋変更でモーダル --}}
                        <div class="payment-bank-registered">
                            <div class="payment-bank-data-rows">
                                <div class="payment-bank-data-row">
                                    <span class="payment-bank-data-label">金融機関</span>
                                    <span class="payment-bank-data-value">{{ $castBank['bank_name'] ?? '' }}</span>
                                </div>
                                <div class="payment-bank-data-row">
                                    <span class="payment-bank-data-label">支店名</span>
                                    <span class="payment-bank-data-value">{{ $castBank['branch_name'] ?? '' }}</span>
                                </div>
                                <div class="payment-bank-data-row">
                                    <span class="payment-bank-data-label">口座種別</span>
                                    <span class="payment-bank-data-value">{{ $castBank['account_type_label'] ?? '普通' }}</span>
                                </div>
                                <div class="payment-bank-data-row">
                                    <span class="payment-bank-data-label">口座番号</span>
                                    @php
                                        $anum = $castBank['account_number'] ?? '';
                                        $masked = strlen($anum) > 4 ? str_repeat('*', strlen($anum) - 4) . substr($anum, -4) : $anum;
                                    @endphp
                                    <span class="payment-bank-data-value">{{ $masked }}</span>
                                </div>
                                <div class="payment-bank-data-row">
                                    <span class="payment-bank-data-label">口座名義</span>
                                    <span class="payment-bank-data-value">{{ $castBank['account_name'] ?? $castBank['account_holder_name'] ?? '' }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>
</div>

{{-- 口座情報登録モーダル（未登録時はここに遷移、登録済は「変更する」で表示） --}}
<div id="cast-bank-modal" class="payment-bank-modal" role="dialog" aria-labelledby="cast-bank-modal-title" aria-modal="true" hidden>
    <div class="payment-bank-modal-backdrop" data-close-bank-modal></div>
    <div class="payment-bank-modal-panel">
        <div class="payment-bank-modal-header">
            <h3 id="cast-bank-modal-title" class="payment-bank-modal-title">振込先口座の登録</h3>
            <button type="button" class="payment-bank-modal-close" data-close-bank-modal aria-label="閉じる">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="payment-bank-modal-body">
            <p class="payment-bank-modal-note">
                金融機関と支店は正式名称で入力してください。<br>
                口座名義カナは、銀行側に登録している表記に合わせると照合がスムーズです。
            </p>
            <form id="cast-bank-modal-form" class="management-bank-form" data-bank-autocomplete>
                @csrf
                <div class="payment-bank-modal-grid">
                    <div class="bank-form-row">
                        <label class="bank-label" for="cast-bank-modal-bank">金融機関名</label>
                        <input id="cast-bank-modal-bank" type="text" name="bank_name" class="bank-input" value="{{ $castBank['bank_name'] ?? '' }}" placeholder="例: みずほ銀行" autocomplete="off" list="cast-bank-modal-suggestions" data-bank-name-input required>
                        <input type="hidden" name="bank_code" value="{{ $castBank['bank_code'] ?? '' }}" data-bank-code-input>
                        <datalist id="cast-bank-modal-suggestions" data-bank-list></datalist>
                    </div>
                    <div class="bank-form-row">
                        <label class="bank-label" for="cast-bank-modal-branch">支店名</label>
                        <input id="cast-bank-modal-branch" type="text" name="branch_name" class="bank-input" value="{{ $castBank['branch_name'] ?? '' }}" placeholder="例: 渋谷支店" autocomplete="off" list="cast-bank-modal-branch-suggestions" data-branch-name-input required>
                        <input type="hidden" name="branch_code" value="{{ $castBank['branch_code'] ?? '' }}" data-branch-code-input>
                        <datalist id="cast-bank-modal-branch-suggestions" data-branch-list></datalist>
                    </div>
                    <div class="bank-form-row">
                        <label class="bank-label" for="cast-bank-modal-type">口座種別</label>
                        <select id="cast-bank-modal-type" name="account_type" class="bank-input" required>
                            <option value="ordinary" {{ ($castBank['account_type'] ?? 'ordinary') === 'ordinary' ? 'selected' : '' }}>普通</option>
                            <option value="current" {{ ($castBank['account_type'] ?? '') === 'current' ? 'selected' : '' }}>当座</option>
                        </select>
                    </div>
                    <div class="bank-form-row">
                        <label class="bank-label" for="cast-bank-modal-number">口座番号 (7桁)</label>
                        <input id="cast-bank-modal-number" type="text" name="account_number" class="bank-input" value="{{ $castBank['account_number'] ?? '' }}" placeholder="1234567" inputmode="numeric" maxlength="8" pattern="[0-9]*" data-account-number-input required>
                    </div>
                    <div class="bank-form-row">
                        <label class="bank-label" for="cast-bank-modal-name">口座名義 (全角カナ)</label>
                        <input id="cast-bank-modal-name" type="text" name="account_name" class="bank-input" value="{{ $castBank['account_name'] ?? $castBank['account_holder_name'] ?? '' }}" placeholder="例: ヤマダ タロウ" required>
                    </div>
                </div>
            </form>
        </div>
        <div class="payment-bank-modal-footer">
            <button type="button" class="payment-bank-modal-btn payment-bank-modal-btn-cancel" data-close-bank-modal>
                キャンセル
            </button>
            <button type="submit" form="cast-bank-modal-form" class="payment-bank-modal-btn payment-bank-modal-btn-submit" id="cast-bank-modal-submit">
                登録・保存する
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('cast-bank-modal');
    var form = document.getElementById('cast-bank-modal-form');
    if (!modal || !form) return;

    function openModal() {
        modal.removeAttribute('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeModal() {
        modal.setAttribute('hidden', '');
        document.body.style.overflow = '';
    }

    document.querySelectorAll('[data-open-bank-modal]').forEach(function (btn) {
        btn.addEventListener('click', openModal);
    });
    document.querySelectorAll('[data-close-bank-modal]').forEach(function (el) {
        el.addEventListener('click', closeModal);
    });
    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var formData = new FormData(form);
        var submitBtn = document.getElementById('cast-bank-modal-submit');
        if (submitBtn) {
            submitBtn.disabled = true;
        }
        fetch('{{ route("cast.mypage.payment.bank.update") }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(function (r) {
            return r.json().then(function (body) {
                return { ok: r.ok, body: body };
            });
        })
        .then(function (res) {
            if (submitBtn) submitBtn.disabled = false;
            if (res.ok) {
                closeModal();
                window.location.reload();
                return;
            }
            var errors = res.body && res.body.errors ? Object.values(res.body.errors).flat() : [];
            var msg = errors.length ? errors.join(' ') : (res.body && res.body.message ? res.body.message : '保存に失敗しました。');
            alert(msg);
        })
        .catch(function () {
            if (submitBtn) submitBtn.disabled = false;
            alert('保存に失敗しました。時間をおいて再度お試しください。');
        });
    });
});
</script>
@endpush
