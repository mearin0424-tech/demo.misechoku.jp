{{-- ### demo function and data for test ###
     Mock LINE login form. Only exposed when config('demo.enabled') is on. --}}
@extends('layouts.app-v2')

@section('title', 'デモ用 モック LINE ログイン')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-[440px] rounded-panel border border-line-accent/40 bg-gradient-to-br from-surface-from to-base shadow-card-3d p-6 space-y-5">
        <div class="text-center space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-accent/10 border border-line-accent/40 text-accent-text text-[12px] font-bold">
                <i class="fa-solid fa-flask-vial"></i> デモ環境限定
            </div>
            <h1 class="text-[18px] font-bold text-text-main">モック LINE ログイン</h1>
            <p class="text-[12px] text-text-sub leading-relaxed">
                本番の LINE OAuth をスキップし、任意の「モック LINE ユーザー ID」で認証をシミュレートします。<br>
                先に登録 → 設定 > 通知設定 > <strong>LINE と連携（モック）</strong> で連携した ID を入力してください。
            </p>
        </div>

        @if ($errors->any())
            <div class="rounded-panel border border-red-500/40 bg-red-500/10 text-red-200 text-[13px] p-3 space-y-1">
                @foreach ($errors->all() as $err)
                    <div>{{ $err }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.line.mock.post') }}" class="space-y-4">
            @csrf

            <label class="block space-y-1.5">
                <span class="text-[12px] font-bold text-text-sub">ロール</span>
                <select name="role"
                        class="w-full h-11 rounded-panel bg-accent/10 border border-line-accent/40 text-text-main px-3">
                    <option value="cast" @selected(($defaultRole ?? 'cast') === 'cast')>キャストで入る</option>
                    <option value="shop" @selected(($defaultRole ?? 'cast') === 'shop')>店舗で入る</option>
                </select>
            </label>

            <label class="block space-y-1.5">
                <span class="text-[12px] font-bold text-text-sub">モック LINE ユーザー ID</span>
                <input name="user_id" type="text" required
                       value="{{ $defaultLineId ?? '' }}"
                       class="w-full h-11 rounded-panel bg-accent/10 border border-line-accent/40 text-text-main px-3 font-mono text-[13px]">
                <span class="text-[11px] text-text-sub">
                    連携時に表示された ID を入力（`mock:` プレフィックスは自動付与）
                </span>
            </label>

            <button type="submit" class="btn-primary-cta btn-primary-cta--full btn-primary-cta--pill">
                <i class="fa-brands fa-line"></i> モック LINE でログイン
            </button>
        </form>

        <div class="pt-2 border-t border-line/60 text-center">
            <a href="{{ route('login.demo') }}" class="text-[12px] text-text-sub underline">← デモログインへ戻る</a>
        </div>
    </div>
</div>
@endsection
