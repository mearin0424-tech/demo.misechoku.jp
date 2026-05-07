@extends('layouts.admin')

@section('title', '運営ログイン')

@section('content')
    <div class="admin-page admin-auth-shell">
        <div class="admin-role-switch">
            <a href="{{ route('login.demo') }}" class="admin-role-link">キャスト</a>
            <a href="{{ route('login.demo') }}" class="admin-role-link">店舗</a>
            <a href="{{ route('login.demo') }}" class="admin-role-link is-active">運営</a>
        </div>

        <div class="admin-panel">
            <div style="text-align:center;">
                @include('admin.parts.page-title', ['eyebrow' => 'ADMIN LOGIN', 'title' => '運営ログイン'])
            </div>
            <p class="admin-description" style="text-align:center;">運営用ログインです。</p>

            @if ($errors->any())
                <div class="admin-alert admin-alert-error">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('status'))
                <div class="admin-alert admin-alert-success">
                    {{ session('status') }}
                </div>
            @endif

            <p class="admin-description" style="text-align:center;">ログインは共通画面で行います。</p>
            <a href="{{ route('login.demo') }}" class="btn-action manage" style="width:100%;">
                <i class="fas fa-right-to-bracket"></i> 共通ログイン画面へ
            </a>
        </div>
    </div>
@endsection
