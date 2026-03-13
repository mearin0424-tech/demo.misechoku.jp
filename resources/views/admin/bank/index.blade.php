@extends('layouts.admin')

@section('title', '運営口座情報設定')

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
            <form method="POST" action="{{ route('admin.bank.store') }}" class="admin-bank-form">
                @csrf
                <div class="admin-form-row">
                    <label class="admin-label">金融機関名</label>
                    <input type="text" name="bank_name" class="admin-input" value="{{ old('bank_name', $bank['bank_name']) }}" required>
                </div>
                <div class="admin-form-row">
                    <label class="admin-label">支店名</label>
                    <input type="text" name="branch_name" class="admin-input" value="{{ old('branch_name', $bank['branch_name']) }}">
                </div>
                <div class="admin-form-row">
                    <label class="admin-label">口座種別</label>
                    <select name="account_type" class="admin-input" required>
                        <option value="ordinary" {{ old('account_type', $bank['account_type']) === 'ordinary' ? 'selected' : '' }}>普通</option>
                        <option value="checking" {{ old('account_type', $bank['account_type']) === 'checking' ? 'selected' : '' }}>当座</option>
                    </select>
                </div>
                <div class="admin-form-row">
                    <label class="admin-label">口座番号</label>
                    <input type="text" name="account_number" class="admin-input" value="{{ old('account_number', $bank['account_number']) }}" required>
                </div>
                <div class="admin-form-row">
                    <label class="admin-label">口座名義（カナ）</label>
                    <input type="text" name="account_name" class="admin-input" value="{{ old('account_name', $bank['account_name']) }}" required>
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

