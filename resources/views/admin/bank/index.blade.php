@extends('layouts.admin')

@section('title', '運営口座情報設定')

@push('admin-scripts')
<script>
    (function () {
        function normalizeAccountNumber(value) {
            return String(value || '').replace(/\D+/g, '').slice(0, 7);
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-account-number-input]').forEach(function (input) {
                var syncNumber = function () {
                    input.value = normalizeAccountNumber(input.value);
                };

                input.addEventListener('input', syncNumber);
                input.addEventListener('blur', syncNumber);
                syncNumber();
            });
        });
    })();
</script>
@endpush

@section('content')
    <div class="admin-page">
        <h1 class="admin-title">運営口座情報設定</h1>

        @if(session('status'))
            <div class="admin-alert">
                {{ session('status') }}
            </div>
        @endif

        <p class="admin-description">
            お店側に発行する請求書に記載される、運営の振込先口座情報を登録します。
        </p>

        <div class="admin-panel">
            <h2 class="admin-panel-title">口座情報</h2>
            <form method="POST" action="{{ route('admin.bank.store') }}" class="admin-bank-form" data-bank-autocomplete>
                @csrf
                <div class="admin-form-row">
                    <label class="admin-label">金融機関名</label>
                    <input type="text" name="bank_name" class="admin-input" value="{{ old('bank_name', $bank['bank_name']) }}" autocomplete="off" list="admin-bank-suggestions" data-bank-name-input required>
                    <input type="hidden" name="bank_code" value="{{ old('bank_code', $bank['bank_code']) }}" data-bank-code-input>
                    <datalist id="admin-bank-suggestions" data-bank-list></datalist>
                </div>
                <div class="admin-form-row">
                    <label class="admin-label">支店名</label>
                    <input type="text" name="branch_name" class="admin-input" value="{{ old('branch_name', $bank['branch_name']) }}" autocomplete="off" list="admin-branch-suggestions" data-branch-name-input required>
                    <input type="hidden" name="branch_code" value="{{ old('branch_code', $bank['branch_code']) }}" data-branch-code-input>
                    <datalist id="admin-branch-suggestions" data-branch-list></datalist>
                </div>
                <div class="admin-form-row">
                    <label class="admin-label">口座種別</label>
                    <select name="account_type" class="admin-input" required>
                        <option value="ordinary" {{ old('account_type', $bank['account_type']) === 'ordinary' ? 'selected' : '' }}>普通</option>
                        <option value="current" {{ old('account_type', $bank['account_type']) === 'current' ? 'selected' : '' }}>当座</option>
                    </select>
                </div>
                <div class="admin-form-row">
                    <label class="admin-label">口座番号</label>
                    <input type="text" name="account_number" class="admin-input" value="{{ old('account_number', $bank['account_number']) }}" inputmode="numeric" maxlength="8" pattern="[0-9]*" data-account-number-input required>
                    <small style="display:block; margin-top:6px; color:#7c8ba3;">口座番号は7桁または8桁の数字で入力してください。</small>
                </div>
                <div class="admin-form-row">
                    <label class="admin-label">口座名義（カナ）</label>
                    <input type="text" name="account_name" class="admin-input" value="{{ old('account_name', $bank['account_name']) }}" placeholder="ヤマダタロウ" required>
                    <small style="display:block; margin-top:6px; color:#7c8ba3;">銀行側の登録カナ表記に合わせて入力してください。</small>
                </div>

                <div class="admin-form-actions">
                    <button type="submit" class="btn-action manage">
                        <i class="fas fa-save"></i> 口座情報を保存
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

