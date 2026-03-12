@extends('layouts.app')

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
            <form method="POST" action="{{ route('bk.bank.store') }}" class="admin-bank-form">
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

    <style>
        .admin-page {
            padding: 24px 0;
        }
        .admin-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 12px;
            color: #e5e7eb;
        }
        .admin-alert {
            background: rgba(55, 65, 81, 0.6);
            border: 1px solid rgba(156, 163, 175, 0.9);
            color: #e5e7eb;
            padding: 8px 12px;
            border-radius: 6px;
            margin-bottom: 14px;
            font-size: 0.85rem;
        }
        .admin-description {
            font-size: 0.85rem;
            color: #9ca3af;
            margin-bottom: 12px;
        }
        .admin-panel {
            padding: 12px 14px;
            border-radius: 8px;
            background: rgba(17, 24, 39, 0.9);
            border: 1px solid rgba(55, 65, 81, 0.9);
        }
        .admin-panel-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: #e5e7eb;
            margin-bottom: 8px;
        }
        .admin-form-row {
            margin-bottom: 10px;
        }
        .admin-label {
            display: block;
            font-size: 0.8rem;
            color: #d1d5db;
            margin-bottom: 4px;
        }
        .admin-input {
            width: 100%;
            background: #111827;
            border: 1px solid #374151;
            border-radius: 4px;
            padding: 6px 8px;
            font-size: 0.85rem;
            color: #e5e7eb;
        }
        .admin-input:focus {
            outline: none;
            border-color: #60a5fa;
        }
        .admin-form-actions {
            margin-top: 12px;
            text-align: right;
        }
    </style>
@endsection

