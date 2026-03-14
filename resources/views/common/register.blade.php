@extends('layouts.app')

@section('title', $title)
@section('body-class', $bodyClass)
@section('guide_message', $guideMessage)

@push('scripts')
    <script src="https://yubinbango.github.io/yubinbango/yubinbango.js" charset="UTF-8"></script>
@endpush

@section('content')
    <div class="register-page">
        <section class="register-hero">
            <div class="register-role-switch">
                <a href="{{ route('cast.register') }}" class="register-role {{ $role === 'cast' ? 'is-active' : '' }}">キャスト</a>
                <a href="{{ route('shop.register') }}" class="register-role {{ $role === 'shop' ? 'is-active' : '' }}">店舗</a>
                <a href="{{ route('admin.login') }}" class="register-role">運営</a>
            </div>
            <img src="{{ asset('assets/images/common/logo-yoko.png') }}" alt="ミセチョク" class="register-logo">
            <h1 class="register-title">{{ $heroTitle }}</h1>
        </section>

        <form method="POST" action="{{ $formAction }}" class="register-form h-adr">
            @csrf
            <span class="p-country-name" style="display:none;">Japan</span>

            @if (session('success'))
                <div class="register-alert register-alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="register-alert register-alert-error">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @if ($role === 'cast')
                <section class="register-card">
                    <div class="register-card-head">
                        <h2>基本情報</h2>
                    </div>

                    <div class="register-grid register-grid-two">
                        <label class="register-field">
                            <span>ニックネーム <em>必須</em></span>
                            <input type="text" name="nickname" value="{{ old('nickname') }}" placeholder="例：ゆな">
                        </label>

                        <label class="register-field">
                            <span>氏名 <em>必須</em></span>
                            <input type="text" name="name" value="{{ old('name') }}" placeholder="例：山田 花子">
                        </label>
                    </div>

                    <div class="register-field">
                        <span>生年月日 <em>必須</em></span>
                        <div class="register-grid register-grid-birth">
                            <input type="number" name="birth_year" value="{{ old('birth_year') }}" min="1950" max="{{ date('Y') }}" placeholder="1998">
                            <input type="number" name="birth_month" value="{{ old('birth_month') }}" min="1" max="12" placeholder="6">
                            <input type="number" name="birth_day" value="{{ old('birth_day') }}" min="1" max="31" placeholder="18">
                        </div>
                    </div>

                    <label class="register-field">
                        <span>郵便番号 <em>必須</em></span>
                        <input
                            type="text"
                            name="zip"
                            value="{{ old('zip') }}"
                            class="p-postal-code"
                            data-postal-code
                            maxlength="8"
                            pattern="[0-9-]*"
                            inputmode="numeric"
                            autocomplete="postal-code"
                            placeholder="例：160-0021"
                        >
                        <small class="register-field-hint">住所を自動入力します。</small>
                    </label>

                    <div class="register-grid register-grid-two">
                        <label class="register-field">
                            <span>都道府県 <em>必須</em></span>
                            <select name="pref" class="p-region">
                                <option value="">選択してください</option>
                                @foreach ($prefOptions as $pref)
                                    <option value="{{ $pref }}" @selected(old('pref') === $pref)>{{ $pref }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="register-field">
                            <span>市区町村 <em>必須</em></span>
                            <input
                                type="text"
                                name="city"
                                value="{{ old('city') }}"
                                class="p-locality"
                                autocomplete="address-level2"
                                placeholder="例：新宿区歌舞伎町"
                            >
                        </label>
                    </div>

                    <label class="register-field">
                        <span>町名・番地</span>
                        <input
                            type="text"
                            name="addr1"
                            value="{{ old('addr1') }}"
                            class="p-street-address"
                            autocomplete="address-line1"
                            placeholder="例：1-2-3"
                        >
                    </label>

                    <div class="register-grid register-grid-two">
                        <label class="register-field">
                            <span>ナイトワーク経験 <em>必須</em></span>
                            <select name="experience">
                                <option value="">選択してください</option>
                                <option value="beginner" @selected(old('experience') === 'beginner')>未経験</option>
                                <option value="experienced" @selected(old('experience') === 'experienced')>経験あり</option>
                            </select>
                        </label>

                        <label class="register-field">
                            <span>希望シフト <em>必須</em></span>
                            <select name="shift_style">
                                <option value="">選択してください</option>
                                <option value="once" @selected(old('shift_style') === 'once')>週1回程度</option>
                                <option value="twice" @selected(old('shift_style') === 'twice')>週2回程度</option>
                                <option value="flex" @selected(old('shift_style') === 'flex')>柔軟に相談したい</option>
                            </select>
                        </label>
                    </div>
                </section>
            @else
                <section class="register-card">
                    <div class="register-card-head">
                        <h2>店舗情報</h2>
                    </div>

                    <div class="register-grid register-grid-two">
                        <label class="register-field">
                            <span>運営会社名 <em>必須</em></span>
                            <input type="text" name="company_name" value="{{ old('company_name') }}" placeholder="例：株式会社ミセチョク">
                        </label>

                        <label class="register-field">
                            <span>店舗名 <em>必須</em></span>
                            <input type="text" name="shop_name" value="{{ old('shop_name') }}" placeholder="例：CLUB LUMIERE">
                        </label>
                    </div>

                    <div class="register-grid register-grid-two">
                        <label class="register-field">
                            <span>担当者名 <em>必須</em></span>
                            <input type="text" name="contact_name" value="{{ old('contact_name') }}" placeholder="例：田中 一郎">
                        </label>

                        <label class="register-field">
                            <span>業態 <em>必須</em></span>
                            <select name="business_type">
                                <option value="">選択してください</option>
                                <option value="club" @selected(old('business_type') === 'club')>クラブ</option>
                                <option value="lounge" @selected(old('business_type') === 'lounge')>ラウンジ</option>
                                <option value="girls-bar" @selected(old('business_type') === 'girls-bar')>ガールズバー</option>
                                <option value="other" @selected(old('business_type') === 'other')>その他</option>
                            </select>
                        </label>
                    </div>

                    <label class="register-field">
                        <span>郵便番号 <em>必須</em></span>
                        <input
                            type="text"
                            name="zip"
                            value="{{ old('zip') }}"
                            class="p-postal-code"
                            data-postal-code
                            maxlength="8"
                            pattern="[0-9-]*"
                            inputmode="numeric"
                            autocomplete="postal-code"
                            placeholder="例：106-0032"
                        >
                        <small class="register-field-hint">住所を自動入力します。</small>
                    </label>

                    <div class="register-grid register-grid-two">
                        <label class="register-field">
                            <span>都道府県 <em>必須</em></span>
                            <select name="pref" class="p-region">
                                <option value="">選択してください</option>
                                @foreach ($prefOptions as $pref)
                                    <option value="{{ $pref }}" @selected(old('pref') === $pref)>{{ $pref }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="register-field">
                            <span>市区町村 <em>必須</em></span>
                            <input
                                type="text"
                                name="city"
                                value="{{ old('city') }}"
                                class="p-locality"
                                autocomplete="address-level2"
                                placeholder="例：港区六本木"
                            >
                        </label>
                    </div>

                    <label class="register-field">
                        <span>住所・ビル名 <em>必須</em></span>
                        <input
                            type="text"
                            name="address"
                            value="{{ old('address') }}"
                            class="p-street-address"
                            autocomplete="address-line1"
                            placeholder="例：7-12-34 ミセチョクビル 5F"
                        >
                    </label>

                    <div class="register-field">
                        <span>ご利用プラン <em>必須</em></span>
                        <div class="register-plan-grid">
                            <label class="register-plan-option">
                                <input type="radio" name="plan" value="basic" @checked(old('plan', 'basic') === 'basic')>
                                <span>
                                    <strong>Basic</strong>
                                </span>
                            </label>
                            <label class="register-plan-option">
                                <input type="radio" name="plan" value="premium" @checked(old('plan') === 'premium')>
                                <span>
                                    <strong>Premium</strong>
                                </span>
                            </label>
                        </div>
                    </div>
                </section>
            @endif

            <section class="register-card">
                <div class="register-card-head">
                    <h2>アカウント情報</h2>
                </div>

                <div class="register-grid register-grid-two">
                    <label class="register-field">
                        <span>メールアドレス <em>必須</em></span>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="example@misechoku.jp">
                    </label>

                    <label class="register-field">
                        <span>電話番号 <em>必須</em></span>
                        <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="09012345678">
                    </label>
                </div>

                <div class="register-grid register-grid-two">
                    <label class="register-field">
                        <span>パスワード <em>必須</em></span>
                        <input type="password" name="password" placeholder="8文字以上で入力">
                    </label>

                    <label class="register-field">
                        <span>パスワード確認 <em>必須</em></span>
                        <input type="password" name="password_confirmation" placeholder="もう一度入力">
                    </label>
                </div>
            </section>

            <section class="register-card register-card-compact">
                <label class="register-check">
                    <input type="checkbox" name="terms" value="1" @checked(old('terms'))>
                    <span>
                        <a href="{{ route('pages.official.terms') }}">利用規約</a> と
                        <a href="{{ route('pages.official.privacy') }}">プライバシーポリシー</a> に同意します。
                    </span>
                </label>
            </section>

            <div class="register-actions">
                <button type="submit" class="register-submit">{{ $submitLabel }}</button>
                <a href="{{ $loginUrl }}" class="register-secondary">ログインへ戻る</a>
                <a href="{{ $loginUrl }}" class="register-login-link">デモログインへ戻る</a>
            </div>
        </form>
    </div>

    <style>
        body.page-auth-register #bottom-nav,
        body.page-auth-register #side-menu,
        body.page-auth-register .header-right {
            display: none !important;
        }

        body.page-auth-register main {
            padding-bottom: 40px;
        }

        body.page-auth-register.page-auth-register-cast {
            background:
                radial-gradient(circle at top left, rgba(229, 193, 88, 0.18), transparent 32%),
                linear-gradient(180deg, #120405 0%, #190509 45%, #110406 100%);
        }

        body.page-auth-register.page-auth-register-shop {
            background:
                radial-gradient(circle at top right, rgba(229, 193, 88, 0.18), transparent 32%),
                linear-gradient(180deg, #11060a 0%, #1a0a11 45%, #120406 100%);
        }

        body.page-auth-register #bg-layer {
            background:
                radial-gradient(circle at 15% 10%, rgba(229, 193, 88, 0.08), transparent 22%),
                radial-gradient(circle at 82% 16%, rgba(255, 255, 255, 0.04), transparent 18%),
                radial-gradient(circle at 50% 100%, rgba(122, 24, 44, 0.22), transparent 30%);
        }

        .register-page {
            width: min(100%, 760px);
            margin: 0 auto;
            padding: 28px 0 0;
        }

        .register-hero,
        .register-card {
            border: 1px solid rgba(229, 193, 88, 0.18);
            border-radius: 28px;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.05), rgba(255, 255, 255, 0.02)),
                linear-gradient(135deg, rgba(38, 13, 18, 0.96), rgba(18, 6, 10, 0.98));
            box-shadow:
                0 22px 60px rgba(0, 0, 0, 0.36),
                inset 0 1px 0 rgba(255, 255, 255, 0.08);
        }

        .register-hero {
            padding: 32px 28px 26px;
            text-align: center;
        }

        .register-role-switch {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 20px;
        }

        .register-role {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            border: 1px solid #3d1a1f;
            border-radius: 14px;
            background: rgba(18, 4, 5, 0.58);
            color: #a89090;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .register-role.is-active {
            border-color: rgba(230, 208, 128, 0.42);
            color: #e6d080;
            background: rgba(35, 15, 18, 0.95);
        }

        .register-logo {
            width: 210px;
            max-width: 70%;
            margin-bottom: 14px;
            filter: drop-shadow(0 10px 24px rgba(0, 0, 0, 0.35));
        }

        .register-title {
            margin: 0;
            font-size: clamp(1.5rem, 4vw, 1.9rem);
            line-height: 1.4;
            color: #fff4d6;
        }

        .register-form {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-top: 18px;
        }

        .register-alert {
            padding: 14px 16px;
            border-radius: 18px;
            font-size: 0.84rem;
            line-height: 1.75;
        }

        .register-alert-success {
            border: 1px solid rgba(134, 239, 172, 0.28);
            background: rgba(20, 83, 45, 0.42);
            color: #dcfce7;
        }

        .register-alert-error {
            border: 1px solid rgba(255, 177, 177, 0.3);
            background: rgba(122, 24, 44, 0.42);
            color: #fff1f2;
        }

        .register-card {
            padding: 24px 22px;
        }

        .register-card-compact {
            padding: 18px 22px;
        }

        .register-card-head {
            margin-bottom: 18px;
        }

        .register-card-head h2 {
            margin: 0;
            font-size: 1.05rem;
            color: #fff4d6;
        }

        .register-grid {
            display: grid;
            gap: 14px;
        }

        .register-grid-two {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            margin-bottom: 14px;
        }

        .register-grid-birth {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .register-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
            color: #f8f1e1;
            font-size: 0.84rem;
        }

        .register-field span {
            color: #f8f1e1;
        }

        .register-field em {
            font-style: normal;
            color: #f6d98b;
            font-size: 0.73rem;
            margin-left: 6px;
        }

        .register-field-hint {
            color: rgba(218, 199, 199, 0.7);
            font-size: 0.73rem;
            line-height: 1.6;
        }

        .register-field input,
        .register-field select {
            width: 100%;
            padding: 13px 14px;
            border-radius: 16px;
            border: 1px solid rgba(229, 193, 88, 0.16);
            background: rgba(255, 255, 255, 0.04);
            color: #fff;
            font-size: 0.94rem;
        }

        .register-field input::placeholder {
            color: rgba(214, 198, 198, 0.48);
        }

        .register-field input:focus,
        .register-field select:focus {
            outline: none;
            border-color: rgba(253, 240, 178, 0.72);
            box-shadow: 0 0 0 3px rgba(229, 193, 88, 0.12);
        }

        .register-plan-grid {
            display: grid;
            gap: 12px;
        }

        .register-plan-option {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            padding: 14px 16px;
            border-radius: 18px;
            border: 1px solid rgba(229, 193, 88, 0.16);
            background: rgba(255, 255, 255, 0.03);
            cursor: pointer;
        }

        .register-plan-option input {
            width: auto;
            margin-top: 3px;
            accent-color: #d4af37;
        }

        .register-plan-option strong {
            display: block;
            color: #fff5da;
        }

        .register-check {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            color: #f6ead0;
            font-size: 0.84rem;
            line-height: 1.8;
        }

        .register-check input {
            width: auto;
            margin-top: 4px;
            accent-color: #d4af37;
        }

        .register-check a,
        .register-login-link {
            color: #f6d98b;
            text-decoration: none;
        }

        .register-actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 4px;
        }

        .register-submit,
        .register-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-height: 54px;
            padding: 14px 18px;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 700;
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        }

        .register-submit {
            border: none;
            background: linear-gradient(135deg, #f4df9c, #c99722);
            color: #2a1208;
            box-shadow: 0 18px 36px rgba(0, 0, 0, 0.28);
            cursor: pointer;
        }

        .register-secondary {
            border: 1px solid rgba(229, 193, 88, 0.26);
            background: rgba(255, 255, 255, 0.04);
            color: #fff4d6;
        }

        .register-submit:hover,
        .register-secondary:hover {
            transform: translateY(-1px);
        }

        .register-login-link {
            text-align: center;
            font-size: 0.84rem;
            color: rgba(246, 217, 139, 0.9);
        }

        @media (max-width: 640px) {
            .register-page {
                padding-top: 18px;
            }

            .register-hero,
            .register-card {
                border-radius: 24px;
            }

            .register-hero {
                padding: 28px 20px 24px;
            }

            .register-card {
                padding: 20px 18px;
            }

            .register-grid-two,
            .register-grid-birth {
                grid-template-columns: 1fr;
            }

            .register-role-switch {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection
