@extends('layouts.app')

@section('title', $title)
@section('body-class', $bodyClass)

@if ($role === 'shop')
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
<link rel="stylesheet" href="{{ asset('assets/css/register-shop-profile-crop.css') }}">
@endpush
@endif

@if ($role === 'cast')
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
<link rel="stylesheet" href="{{ asset('assets/css/register-cast-profile-crop.css') }}">
<style>
.register-skip-toggle {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 12px;
    margin-bottom: 14px;
    border: 1px dashed rgba(220, 181, 104, .35);
    border-radius: 10px;
    background: rgba(220, 181, 104, .05);
    color: #f8e9c8;
    font-size: 0.86rem;
    cursor: pointer;
}
.register-skip-toggle input { accent-color: #c5a059; }
.register-field-compact input { max-width: 280px; }
</style>
@endpush
@endif

@push('scripts')
    <script src="https://yubinbango.github.io/yubinbango/yubinbango.js" charset="UTF-8"></script>
    @if ($role === 'shop')
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
    <script src="{{ asset('assets/js/register-shop-profile-crop.js') }}"></script>
    @endif
    @if ($role === 'cast')
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
    <script src="{{ asset('assets/js/register-cast-profile-crop.js') }}"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // 「あとで登録する」トグル：チェック時に対象フィールドを非表示＋disabled で送信対象から外す
        document.querySelectorAll('[data-skip-section]').forEach(function (section) {
            var checkbox = section.querySelector('.register-skip-toggle input[type="checkbox"]');
            var target = section.querySelector('.register-skip-target');
            if (!checkbox || !target) return;
            var apply = function () {
                var skipped = checkbox.checked;
                target.hidden = skipped;
                target.querySelectorAll('input, select, textarea').forEach(function (el) {
                    el.disabled = skipped;
                });
            };
            checkbox.addEventListener('change', apply);
            apply();
        });

        // 本人確認書類のパターン切替（A: 顔写真付き / B: 顔写真なし＋住所確認）
        document.querySelectorAll('input[name="identity_category"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                document.querySelectorAll('[data-identity-pane]').forEach(function (pane) {
                    pane.hidden = pane.getAttribute('data-identity-pane') !== radio.value;
                });
            });
        });
    });
    </script>
    @endif
@endpush

@section('content')
    <div class="register-page">
        <section class="register-hero">
            <div class="register-role-switch">
                <a href="{{ route('cast.register') }}" class="register-role {{ $role === 'cast' ? 'is-active' : '' }}">キャスト</a>
                <a href="{{ route('shop.register') }}" class="register-role {{ $role === 'shop' ? 'is-active' : '' }}">店舗</a>
                <a href="{{ route('login.demo') }}" class="register-role">運営</a>
            </div>
            <img src="{{ asset('assets/images/common/logo-yoko.png') }}" alt="ミセチョク" class="register-logo">
            <h1 class="register-title">{{ $heroTitle }}</h1>
        </section>

        <form method="POST" action="{{ $formAction }}" class="register-form h-adr" enctype="multipart/form-data">
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
                        <input type="date" name="birth_date" value="{{ old('birth_date') }}">
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
                </section>

                {{-- プロフィール詳細（編集画面と同等。あとでマイページでも登録可能） --}}
                <section class="register-card" data-skip-section="profile">
                    <div class="register-card-head">
                        <h2>プロフィール詳細</h2>
                    </div>

                    <label class="register-skip-toggle">
                        <input type="checkbox" name="profile_skip" value="1" @checked(old('profile_skip'))>
                        <span>あとで登録する（マイページから後で編集できます）</span>
                    </label>

                    <div class="register-skip-target" @if(old('profile_skip')) hidden @endif>
                    <label class="register-field">
                        <span>自己紹介</span>
                        <textarea name="intro" rows="4" placeholder="自己紹介">{{ old('intro') }}</textarea>
                    </label>

                    @if(!empty($masters['industries']) && $masters['industries']->isNotEmpty())
                    <div class="register-field">
                        <span>希望業種</span>
                        <div class="register-chip-grid">
                            @foreach($masters['industries'] as $industry)
                                <label class="register-chip">
                                    <input type="checkbox" name="industry_ids[]" value="{{ $industry->id }}" @checked(in_array((int)$industry->id, old('industry_ids', []), true))>
                                    <span>{{ $industry->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div class="metric-pair">
                        <div class="metric-field">
                            <label class="metric-field-label" for="reg-height">身長 <small>cm</small></label>
                            <div class="metric-input-wrap">
                                <input type="number" id="reg-height" name="height" value="{{ old('height') }}" inputmode="numeric" pattern="[0-9]*" min="130" max="200" placeholder="160">
                                <span class="metric-unit">cm</span>
                            </div>
                        </div>
                        <div class="metric-field">
                            <label class="metric-field-label" for="reg-weight">体重 <small>kg</small></label>
                            <div class="metric-input-wrap">
                                <input type="number" id="reg-weight" name="weight" value="{{ old('weight') }}" inputmode="numeric" pattern="[0-9]*" min="30" max="150" placeholder="48">
                                <span class="metric-unit">kg</span>
                            </div>
                        </div>
                    </div>
                    <div class="bwh-group">
                        <span class="metric-field-label">3サイズ <small>cm</small></span>
                        <div class="bwh-row">
                            <label class="bwh-field" aria-label="バスト">
                                <span class="bwh-letter">B</span>
                                <input type="number" name="bust" value="{{ old('bust') }}" inputmode="numeric" pattern="[0-9]*" min="50" max="120" placeholder="--">
                            </label>
                            <label class="bwh-field" aria-label="ウエスト">
                                <span class="bwh-letter">W</span>
                                <input type="number" name="waist" value="{{ old('waist') }}" inputmode="numeric" pattern="[0-9]*" min="40" max="120" placeholder="--">
                            </label>
                            <label class="bwh-field" aria-label="ヒップ">
                                <span class="bwh-letter">H</span>
                                <input type="number" name="hip" value="{{ old('hip') }}" inputmode="numeric" pattern="[0-9]*" min="50" max="120" placeholder="--">
                            </label>
                        </div>
                    </div>

                    @if(!empty($masters['looks']) && $masters['looks']->isNotEmpty())
                    <div class="register-field">
                        <span>ルックス</span>
                        <div class="register-chip-grid">
                            @foreach($masters['looks'] as $look)
                                <label class="register-chip">
                                    <input type="checkbox" name="look_tag_ids[]" value="{{ $look->id }}" @checked(in_array((int)$look->id, old('look_tag_ids', []), true))>
                                    <span>{{ $look->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if(!empty($masters['personalities']) && $masters['personalities']->isNotEmpty())
                    <div class="register-field">
                        <span>性格・内面</span>
                        <div class="register-chip-grid">
                            @foreach($masters['personalities'] as $personality)
                                <label class="register-chip">
                                    <input type="checkbox" name="personality_tag_ids[]" value="{{ $personality->id }}" @checked(in_array((int)$personality->id, old('personality_tag_ids', []), true))>
                                    <span>{{ $personality->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div class="register-grid register-grid-two">
                        <label class="register-field">
                            <span>希望シフト</span>
                            <select name="work_where">
                                <option value="">選択してください</option>
                                <option value="週1回出勤" @selected(old('work_where', old('shift_hope')) === '週1回出勤')>週1回出勤</option>
                                <option value="週2回出勤" @selected(old('work_where', old('shift_hope')) === '週2回出勤')>週2回出勤</option>
                                <option value="週3回以上" @selected(old('work_where', old('shift_hope')) === '週3回以上')>週3回以上</option>
                            </select>
                        </label>
                        <label class="register-field">
                            <span>勤務時間帯</span>
                            <select name="work_time">
                                <option value="">未選択</option>
                                <option value="morning" @selected(old('work_time') === 'morning')>朝〜昼</option>
                                <option value="day_night" @selected(old('work_time') === 'day_night')>夜</option>
                            </select>
                        </label>
                    </div>

                    <label class="register-field register-field-compact">
                        <span>現職業</span>
                        <input type="text" name="profession" value="{{ old('profession', old('current_job')) }}" maxlength="40" placeholder="例：会社員 / 学生 / フリーター">
                    </label>

                    <div class="register-field">
                        <span>ナイトワーク経験</span>
                        <div class="register-radio-row">
                            <label class="register-radio"><input type="radio" name="exp" value="none" @checked(old('exp', old('night_work_exp', '')) === 'none')> 無し</label>
                            <label class="register-radio"><input type="radio" name="exp" value="yes" @checked(old('exp', old('night_work_exp')) === 'yes')> 有り</label>
                        </div>
                    </div>
                    </div>{{-- /.register-skip-target --}}
                </section>

                {{-- プロフィール画像（必須 1枚） --}}
                <section class="register-card">
                    <div class="register-card-head">
                        <h2>プロフィール画像</h2>
                    </div>
                    <label class="register-field">
                        <span>メイン画像 <em>必須</em></span>
                        <input type="file" id="cast-register-profile-image" name="profile_image" accept="image/jpeg,image/png,image/gif,image/webp" required>
                        <small class="register-field-hint">顔が分かる画像を1枚選び、次の画面で<strong>縦長（3:4）</strong>の範囲を調整してから登録してください。（JPEG / PNG / GIF / WebP、最大2MB）</small>
                    </label>
                </section>

                {{-- 本人確認書類（任意・あとでマイページで登録可） --}}
                <section class="register-card" data-skip-section="identity">
                    <div class="register-card-head">
                        <h2>本人確認書類</h2>
                    </div>

                    <p class="register-field-hint" style="margin: 0 0 12px;">
                        本人確認書類はサービスご利用前に必ず必要です。今すぐ提出するか、登録後にマイページから提出するかを選べます。
                    </p>

                    <label class="register-skip-toggle">
                        <input type="hidden" name="identity_skip" value="0">
                        <input type="checkbox" name="identity_skip" value="1" @checked(old('identity_skip', '1') == '1')>
                        <span>あとで登録する（マイページから後でアップロードできます）</span>
                    </label>

                    <div class="register-skip-target" @if(old('identity_skip', '1') == '1') hidden @endif>
                        <div class="register-field">
                            <span>提出パターン</span>
                            <div class="register-radio-row">
                                <label class="register-radio">
                                    <input type="radio" name="identity_category" value="photo_id" @checked(old('identity_category', 'photo_id') === 'photo_id')>
                                    パターンA：顔写真付き身分証 1枚
                                </label>
                                <label class="register-radio">
                                    <input type="radio" name="identity_category" value="non_photo_id" @checked(old('identity_category') === 'non_photo_id')>
                                    パターンB：顔写真なし身分証 ＋ 住所確認書類
                                </label>
                            </div>
                        </div>

                        <div data-identity-pane="photo_id" @if(old('identity_category', 'photo_id') !== 'photo_id') hidden @endif>
                            <label class="register-field">
                                <span>書類種別</span>
                                <select name="identity_type">
                                    <option value="driver_license" @selected(old('identity_type') === 'driver_license')>運転免許証</option>
                                    <option value="passport" @selected(old('identity_type') === 'passport')>パスポート</option>
                                    <option value="mynumber_card" @selected(old('identity_type') === 'mynumber_card')>マイナンバーカード</option>
                                    <option value="residence_card" @selected(old('identity_type') === 'residence_card')>在留カード</option>
                                </select>
                            </label>

                            <div class="register-grid register-grid-two">
                                <label class="register-field">
                                    <span>表面（画像 / PDF）</span>
                                    <input type="file" name="identity_front_file" accept=".pdf,image/*">
                                </label>
                                <label class="register-field">
                                    <span>裏面（任意）</span>
                                    <input type="file" name="identity_back_file" accept=".pdf,image/*">
                                </label>
                            </div>

                            <label class="register-field">
                                <span>有効期限（任意）</span>
                                <input type="date" name="identity_expired_at" value="{{ old('identity_expired_at') }}">
                            </label>
                        </div>

                        <div data-identity-pane="non_photo_id" @if(old('identity_category') !== 'non_photo_id') hidden @endif>
                            <p class="register-field-hint" style="margin: 0 0 8px;">
                                <strong>① 顔写真なし身分証</strong> と <strong>② 住所確認書類</strong> の両方をアップロードしてください。
                            </p>

                            <label class="register-field">
                                <span>① 書類種別（顔写真なし身分証）</span>
                                <select name="identity_type">
                                    <option value="health_insurance" @selected(old('identity_type') === 'health_insurance')>健康保険証</option>
                                    <option value="pension_book" @selected(old('identity_type') === 'pension_book')>年金手帳</option>
                                </select>
                            </label>

                            <div class="register-grid register-grid-two">
                                <label class="register-field">
                                    <span>① 表面（画像 / PDF）</span>
                                    <input type="file" name="identity_front_file" accept=".pdf,image/*">
                                </label>
                                <label class="register-field">
                                    <span>① 裏面（任意）</span>
                                    <input type="file" name="identity_back_file" accept=".pdf,image/*">
                                </label>
                            </div>

                            <label class="register-field">
                                <span>② 書類種別（住所確認書類）</span>
                                <select name="identity_address_type">
                                    <option value="residence_certificate" @selected(old('identity_address_type') === 'residence_certificate')>住民票</option>
                                    <option value="utility_bill" @selected(old('identity_address_type') === 'utility_bill')>公共料金領収書</option>
                                </select>
                            </label>

                            <label class="register-field">
                                <span>② 表面（画像 / PDF）</span>
                                <input type="file" name="identity_address_front_file" accept=".pdf,image/*">
                            </label>
                        </div>
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

                    <label class="register-field">
                        <span>担当者名 <em>必須</em></span>
                        <input type="text" name="contact_name" value="{{ old('contact_name') }}" placeholder="例：田中 一郎">
                    </label>

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

                    <div class="register-grid register-grid-two">
                    <label class="register-field">
                        <span>番地・丁目 <em>必須</em></span>
                        <input
                            type="text"
                            name="addr"
                            value="{{ old('addr') }}"
                            class="p-street-address"
                            autocomplete="address-line1"
                            placeholder="例：7-12-34"
                        >
                    </label>
                    <label class="register-field">
                        <span>建物名・部屋番号</span>
                        <input
                            type="text"
                            name="building"
                            value="{{ old('building') }}"
                            autocomplete="address-line2"
                            placeholder="例：ミセチョクビル 5F"
                        >
                    </label>
                    </div>

                    <label class="register-field">
                        <span>キャッチコピー（ひとこと）</span>
                        <input type="text" name="word" value="{{ old('word') }}" placeholder="例：最高級の夜を、あなたに。">
                        <small class="register-field-hint">一覧やプロフィールに表示される短い紹介文です。</small>
                    </label>
                    <label class="register-field">
                        <span>お店の紹介文</span>
                        <textarea name="overview" rows="5" placeholder="お店のコンセプト、雰囲気、客層などを入力">{{ old('overview') }}</textarea>
                    </label>
                    @if(!empty($masters['industries']) && $masters['industries']->isNotEmpty())
                    <div class="register-field">
                        <span>業種</span>
                        <div class="register-chip-grid">
                            @foreach($masters['industries'] as $industry)
                                <label class="register-chip">
                                    <input type="checkbox" name="industry_ids[]" value="{{ $industry->id }}" @checked(in_array((int)$industry->id, old('industry_ids', []), true))>
                                    <span>{{ $industry->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div class="register-field">
                        <span>ご利用プラン <em>必須</em></span>
                        <div class="register-plan-grid">
                            <label class="register-plan-option">
                                <input type="radio" name="plan" value="basic" @checked(old('plan', 'basic') === 'basic')>
                                <span>
                                    <strong>Basic</strong>
                                </span>
                            </label>
                            <label class="register-plan-option is-disabled">
                                <input type="radio" name="plan" value="premium" disabled>
                                <span>
                                    <strong>Premium</strong>
                                    <small class="register-plan-coming">実装中（近日公開）</small>
                                </span>
                            </label>
                        </div>
                    </div>
                </section>

                {{-- 店舗プロフィール画像（必須 1枚） --}}
                <section class="register-card">
                    <div class="register-card-head">
                        <h2>店舗プロフィール画像</h2>
                    </div>
                    <label class="register-field">
                        <span>メイン画像 <em>必須</em></span>
                        <input type="file" id="shop-register-profile-image" name="shop_profile_image" accept="image/jpeg,image/png,image/gif,image/webp" required>
                        <small class="register-field-hint">店舗の雰囲気が伝わる画像を1枚選び、次の画面で<strong>横長（16:9）</strong>の範囲を調整してから登録してください。（JPEG / PNG / GIF / WebP、最大2MB）</small>
                    </label>
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

    @if ($role === 'cast')
    <div id="register-cast-crop-modal" class="register-cast-crop-overlay" role="dialog" aria-modal="true" aria-labelledby="register-cast-crop-title" style="display:none;">
        <div class="register-cast-crop-inner">
            <div>
                <h3 id="register-cast-crop-title">プロフィール画像の調整</h3>
                <p class="register-cast-crop-guide">表示枠は縦長（3:4）です。ズームや位置を調整し、「この画像で続ける」を押すと登録フォームに反映されます。</p>
            </div>
            <div class="register-cast-crop-frame">
                <img id="register-cast-crop-preview" src="" alt="">
            </div>
            <div class="register-cast-crop-actions">
                <button type="button" class="register-cast-crop-btn-secondary" id="register-cast-crop-cancel">別の画像を選ぶ</button>
                <button type="button" class="register-cast-crop-btn-primary" id="register-cast-crop-confirm">この画像で続ける</button>
            </div>
        </div>
    </div>
    @endif

    @if ($role === 'shop')
    <div id="register-shop-crop-modal" class="register-shop-crop-overlay" role="dialog" aria-modal="true" aria-labelledby="register-shop-crop-title" style="display:none;">
        <div class="register-shop-crop-inner">
            <div>
                <h3 id="register-shop-crop-title">店舗メイン画像の調整</h3>
                <p class="register-shop-crop-guide">表示枠は横長（16:9）です。ズームや位置を調整し、「この画像で続ける」を押すと登録フォームに反映されます。</p>
            </div>
            <div class="register-shop-crop-frame">
                <img id="register-shop-crop-preview" src="" alt="">
            </div>
            <div class="register-shop-crop-actions">
                <button type="button" class="register-shop-crop-btn-secondary" id="register-shop-crop-cancel">別の画像を選ぶ</button>
                <button type="button" class="register-shop-crop-btn-primary" id="register-shop-crop-confirm">この画像で続ける</button>
            </div>
        </div>
    </div>
    @endif

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

        .register-grid-three {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .register-chip-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .register-chip {
            position: relative;
            display: inline-flex;
            align-items: center;
        }

        .register-chip input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .register-chip span {
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

        .register-chip input:checked + span {
            border-color: rgba(229, 193, 88, 0.5);
            background: rgba(212, 175, 55, 0.18);
            color: #f8e7b0;
        }

        .register-three-size {
            display: flex;
            gap: 8px;
        }

        .register-three-size input {
            flex: 1;
            min-width: 0;
        }

        .register-radio-row {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }

        .register-radio {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            color: #f8f1e1;
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
        .register-field select,
        .register-field textarea {
            width: 100%;
            padding: 12px 14px;
            border-radius: 14px;
            border: 1px solid rgba(229, 193, 88, 0.18);
            background: rgba(255, 255, 255, 0.04);
            color: #fff;
            font-size: 16px; /* iOSズーム回避 */
            min-height: 46px;
            line-height: 1.4;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .register-field input[type="date"] {
            min-height: 46px;
            color-scheme: dark;
            font-variant-numeric: tabular-nums;
        }

        .register-field input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(79%) sepia(29%) saturate(566%) hue-rotate(5deg) brightness(91%) contrast(89%);
            cursor: pointer;
        }

        .register-field textarea {
            min-height: 100px;
            resize: vertical;
        }

        .register-field input::placeholder,
        .register-field textarea::placeholder {
            color: rgba(214, 198, 198, 0.48);
        }

        .register-field input:focus,
        .register-field select:focus,
        .register-field textarea:focus {
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

        .register-plan-option.is-disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .register-plan-coming {
            display: block;
            margin-top: 4px;
            font-size: 0.72rem;
            color: #c9b8b8;
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
