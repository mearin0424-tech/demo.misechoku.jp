@extends('layouts.admin')

@section('title', '運営口座情報設定')

@section('content')
    <div class="admin-page">
        @include('admin.parts.page-title', ['eyebrow' => 'BANK ACCOUNT', 'title' => '運営口座情報設定'])

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
