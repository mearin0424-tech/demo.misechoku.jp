@extends('layouts.admin')

@section('title', '運営口座情報設定')

@section('content')
    <div class="admin-page">
        @include('admin.parts.page-title', [
            'eyebrow' => 'BANK ACCOUNT',
            'title' => '運営口座情報設定',
            'info' => '
                <p>お店側に発行する<strong>請求書に記載される、運営の振込先口座情報</strong>を登録します。</p>
            ',
        ])

        @if(session('status'))
            <div class="admin-alert">
                {{ session('status') }}
            </div>
        @endif

        {{-- 現在の登録内容サマリ --}}
        @if($bank && !empty($bank->bank_name))
            <section class="admin-panel bank-current-card">
                <div class="u-flex-between u-mb-12">
                    <h2 class="admin-panel-title u-mb-0">現在の登録内容</h2>
                    <span class="admin-status-badge is-success"><i class="fas fa-circle-check"></i> 登録済み</span>
                </div>
                <div class="bank-current-grid">
                    <div class="bank-current-item">
                        <span class="bank-current-item__label">金融機関</span>
                        <span class="bank-current-item__value">{{ $bank->bank_name }}（{{ $bank->bank_code ?: '—' }}）</span>
                    </div>
                    <div class="bank-current-item">
                        <span class="bank-current-item__label">支店</span>
                        <span class="bank-current-item__value">{{ $bank->branch_name ?: '—' }}（{{ $bank->branch_code ?: '—' }}）</span>
                    </div>
                    <div class="bank-current-item">
                        <span class="bank-current-item__label">口座種別・番号</span>
                        <span class="bank-current-item__value">
                            {{ in_array($bank->account_type ?? '', ['current', 'checking'], true) ? '当座' : '普通' }}
                            / <code>{{ $bank->account_number ?: '—' }}</code>
                        </span>
                    </div>
                    <div class="bank-current-item">
                        <span class="bank-current-item__label">口座名義</span>
                        <span class="bank-current-item__value">{{ $bank->account_name ?: '—' }}</span>
                    </div>
                </div>
                <p class="admin-note u-mt-12">
                    請求書PDFのプレビューは
                    <a href="{{ route('admin.invoices.template-settings') }}">テンプレート設定</a> から確認できます。
                </p>
            </section>
        @else
            <div class="admin-alert admin-alert-warning">
                <i class="fas fa-triangle-exclamation"></i>
                <strong>運営口座が未登録です。</strong>登録するまで請求書を発行できません。
            </div>
        @endif

        <div class="admin-panel">
            <h2 class="admin-panel-title">口座情報の{{ $bank && !empty($bank->bank_name) ? '更新' : '登録' }}</h2>
            <form method="POST" action="{{ route('admin.bank.store') }}" class="admin-bank-form" data-bank-autocomplete>
                @csrf
                @include('partials.bank-account-form-fields', [
                    'variant' => 'admin',
                    'bankValues' => $bank,
                ])
                <div class="admin-form-actions">
                    <button type="submit" class="btn-action manage">
                        <i class="fas fa-save"></i> 口座情報を保存
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
