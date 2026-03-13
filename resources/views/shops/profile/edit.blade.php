@extends('layouts.app')

@section('title', 'プロフィール編集')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
<style>
    /* プロフィール編集専用のスタイル */
    .form-section {
        margin-bottom: 30px;
    }
    .form-label {
        display: block;
        font-size: 0.75rem;
        color: var(--gold);
        text-transform: uppercase;
        letter-spacing: 0.1em;
        margin-bottom: 8px;
        font-weight: bold;
    }
    .form-input, .form-textarea, .form-select {
        width: 100%;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        color: #fff;
        padding: 12px 15px;
        font-size: 0.9rem;
        transition: all 0.3s;
    }
    .form-input:focus, .form-textarea:focus {
        outline: none;
        border-color: var(--gold);
        background: rgba(212, 175, 55, 0.05);
    }
    .input-hint {
        font-size: 0.7rem;
        color: #666;
        margin-top: 5px;
    }
    .checkbox-chip-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .checkbox-chip {
        position: relative;
        display: inline-flex;
        align-items: center;
    }
    .checkbox-chip input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .checkbox-chip span {
        display: inline-flex;
        align-items: center;
        min-height: 38px;
        padding: 8px 14px;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, 0.14);
        background: rgba(255, 255, 255, 0.04);
        color: #fff;
        font-size: 0.85rem;
    }
    .checkbox-chip input:checked + span {
        border-color: var(--gold);
        background: rgba(212, 175, 55, 0.18);
        color: #f8e7b0;
    }
</style>
@endpush

@push('scripts')
<script src="https://yubinbango.github.io/yubinbango/yubinbango.js" charset="UTF-8"></script>
@endpush

@section('content')
<div class="contents inner animate-fadeIn p-4 pb-24">
    {{-- ヘッダーエリア --}}
    <div class="flex justify-between items-center mb-8">
        <div class="title-area">
            <h2 class="serif-font text-2xl gold-gradient tracking-tight">Edit Profile</h2>
            <p class="text-[10px] text-gray-500 uppercase tracking-[0.2em] mt-1">Shop Information</p>
        </div>
        <a href="{{ route('shop.mypage.index') }}" class="text-gray-400 text-sm">キャンセル</a>
    </div>

    <form action="{{ route('shop.profile.store.update') }}" method="POST" class="h-adr">
        @csrf
        <span class="p-country-name" style="display:none;">Japan</span>
        <div class="space-y-6">

            {{-- 基本情報セクション --}}
            <div class="form-section glass-panel p-6 rounded-2xl">
                <h3 class="text-xs text-gray-500 mb-6 border-b border-white/5 pb-2 uppercase tracking-widest">Basic Information</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="form-label">店舗名</label>
                        <input type="text" name="shop_name" class="form-input" value="{{ old('shop_name', $shopData['shop_name']) }}" placeholder="店舗名を入力">
                    </div>

                    <div>
                        <label class="form-label">キャッチコピー（ひとこと）</label>
                        <input type="text" name="word" class="form-input" value="{{ old('word', $shopData['word']) }}" placeholder="例：最高級の夜を、あなたに。">
                        <p class="input-hint">一覧画面やマイページ上部に表示される短い紹介文です。</p>
                    </div>

                    <div>
                        <label class="form-label">業種</label>
                        <div class="checkbox-chip-grid">
                            @foreach(($masters['industries'] ?? []) as $industry)
                                <label class="checkbox-chip">
                                    <input
                                        type="checkbox"
                                        name="industry_ids[]"
                                        value="{{ $industry->id }}"
                                        {{ in_array((int) $industry->id, old('industry_ids', $shopData['industry_ids'] ?? []), true) ? 'checked' : '' }}
                                    >
                                    <span>{{ $industry->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- 詳細紹介セクション --}}
            <div class="form-section glass-panel p-6 rounded-2xl">
                <h3 class="text-xs text-gray-500 mb-6 border-b border-white/5 pb-2 uppercase tracking-widest">Detailed Intro</h3>
                
                <div>
                    <label class="form-label">お店の紹介文</label>
                    <textarea name="overview" rows="6" class="form-textarea" placeholder="お店のコンセプト、雰囲気、客層などを詳しく入力してください。">{{ old('overview', $shopData['overview']) }}</textarea>
                </div>
            </div>

            {{-- 所在地セクション --}}
            <div class="form-section glass-panel p-6 rounded-2xl">
                <h3 class="text-xs text-gray-500 mb-6 border-b border-white/5 pb-2 uppercase tracking-widest">Location</h3>

                <div class="mb-4">
                    <label class="form-label">郵便番号</label>
                    <input
                        type="text"
                        name="zip"
                        class="form-input p-postal-code"
                        value="{{ old('zip', $shopData['zip']) }}"
                        inputmode="numeric"
                        autocomplete="postal-code"
                        placeholder="例：1060032"
                    >
                    <p class="input-hint">郵便番号を入力すると、都道府県・市区町村が自動補完されます。</p>
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="form-label">都道府県</label>
                        <select name="pref" class="form-select p-region">
                            <option value="">選択してください</option>
                            @foreach ($prefOptions as $pref)
                                <option value="{{ $pref }}" {{ old('pref', $shopData['pref']) === $pref ? 'selected' : '' }}>{{ $pref }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">市区町村</label>
                        <input
                            type="text"
                            name="city"
                            class="form-input p-locality"
                            value="{{ old('city', $shopData['city']) }}"
                            autocomplete="address-level2"
                            placeholder="例：港区六本木"
                        >
                    </div>
                </div>

                <div>
                    <label class="form-label">以降の住所・ビル名</label>
                    <input
                        type="text"
                        name="addr1"
                        class="form-input p-street-address"
                        value="{{ old('addr1', $shopData['addr1']) }}"
                        autocomplete="address-line1"
                        placeholder="例：7-12-34 〇〇ビル 2F"
                    >
                </div>
            </div>

            {{-- 保存ボタン：既存のbtn-actionをスタイリッシュに活用 --}}
            <div class="pt-6">
                <button type="submit" class="btn-gold w-full py-4 shadow-2xl">
                    プロフィールを更新する
                </button>
            </div>

        </div>
    </form>
</div>
@endsection