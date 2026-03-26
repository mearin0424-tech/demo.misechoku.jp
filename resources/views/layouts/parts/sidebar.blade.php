@php
    $isCast = Request::is('cast*');
    $isShop = Request::is('shop*');
    $typePath = $isCast ? 'cast' : 'shop';
@endphp

<aside id="side-menu">
    <div class="sidebar-header">
        <a href="{{ $isCast ? route('cast.home') : route('shop.home') }}">
            <img src="{{ asset('assets/images/common/logo-yoko.png') }}" alt="ミセチョク" class="sidebar-logo">
        </a>
        <button class="btn-sidebar-close" onclick="document.getElementById('side-menu').classList.remove('open'); document.getElementById('menu-overlay').classList.remove('show');">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="sidebar-content">
        {{-- PWA: アプリをインストール（beforeinstallprompt 時のみ表示） --}}
        <div class="sidebar-section" id="pwa-install-section" style="display: none;">
            <div class="menu-label-header">APP</div>
            <button type="button" id="pwa-install-btn" class="pwa-install-btn">
                <i class="fas fa-download"></i> アプリをインストール
            </button>
        </div>

        {{-- SETTING セクション --}}
        <div class="sidebar-section">
            <div class="menu-label-header">SETTING</div>
            <ul class="sidebar-sub-menu">
                <li>
                    <details class="sidebar-details">
                        <summary class="menu-summary">
                            <span><i class="fas fa-cog"></i> アカウント設定</span>
                            <i class="fas fa-chevron-down arrow"></i>
                        </summary>
                        <ul class="sidebar-nested-menu">
                            <li><a href="{{ url('/setting/account/email') }}"><i class="fas fa-envelope"></i> メールアドレス変更</a></li>
                            <li><a href="{{ url('/setting/account/password') }}"><i class="fas fa-key"></i> パスワード変更</a></li>
                            @if($isCast)
                                <li><a href="{{ route('cast.mypage.identity') }}"><i class="fas fa-id-card"></i> 本人確認</a></li>
                            @endif
                            <li><a href="{{ url('/setting/account/withdraw') }}" class="text-danger"><i class="fas fa-user-slash"></i> 退会手続き</a></li>
                        </ul>
                    </details>
                </li>
                @if(!$isCast)
                    <li><a href="{{ url('/subscription') }}"><i class="fas fa-crown"></i> プラン設定</a></li>
                @endif
                <li><a href="{{ url('/setting/notification') }}"><i class="fas fa-bell"></i> 通知設定</a></li>
            </ul>
        </div>

        {{-- SUPPORT セクション --}}
        <div class="sidebar-section">
            <div class="menu-label-header">SUPPORT</div>
            <ul class="sidebar-sub-menu">
                <li><a href="{{ url("/$typePath/feature") }}"><i class="fas fa-star"></i> サービスの特徴</a></li>
                <li><a href="{{ url("/$typePath/htu") }}"><i class="fas fa-book"></i> ご利用ガイド</a></li>
                <li><a href="{{ url("/$typePath/faq") }}"><i class="fas fa-question-circle"></i> よくある質問（FAQ）</a></li>
                <li><a href="{{ url("/{$typePath}/column") }}"><i class="fas fa-lightbulb"></i> お役立ちコラム</a></li>
                <li><a href="{{ url("/{$typePath}/notices") }}"><i class="fas fa-bullhorn"></i> お知らせ</a></li>
                <li><a href="{{ url('/support/form') }}"><i class="fas fa-paper-plane"></i> 問い合わせ窓口</a></li>
            </ul>
        </div>

        {{-- OFFICIAL セクション --}}
        <div class="sidebar-section">
            <div class="menu-label-header">OFFICIAL</div>
            <ul class="sidebar-sub-menu">
                <li><a href="{{ url('/about') }}"><i class="fas fa-building"></i> 運営協会</a></li>
                <li><a href="{{ url('/terms') }}"><i class="fas fa-file-contract"></i> 利用規約</a></li>
                <li><a href="{{ url('/privacy') }}"><i class="fas fa-shield-alt"></i> プライバシーポリシー</a></li>
                <li><a href="{{ route('pages.official.privacy') }}"><i class="fas fa-lock"></i> 安全な個人情報の取り扱いについて</a></li>
            </ul>
        </div>
    </div>

    <div class="sidebar-footer">
        <button class="btn-logout" onclick="if(confirm('ログアウトしますか？')) location.href='{{ route('auth.logout') }}'">
            <i class="fas fa-sign-out-alt"></i> <span>ログアウト</span>
        </button>
    </div>
</aside>

<style>
.sidebar-header { 
    padding: 20px; 
    border-bottom: 1px solid rgba(230,208,128,0.16); 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
}
.btn-sidebar-close { background: none; border: none; color: #F5E6E6; font-size: 1.2rem; cursor: pointer; border-radius: 999px; padding: 4px; transition: background 0.2s ease, color 0.2s ease; }
.btn-sidebar-close:hover { background: rgba(230,208,128,0.15); color: #FDF0B2; }
.sidebar-logo { height: 30px; }
.sidebar-content { flex: 1; overflow-y: auto; padding: 20px; }
.sidebar-section { margin-bottom: 30px; }

.menu-label-header { 
    font-size: 10px; 
    color: rgba(230,208,128,0.7); 
    margin-bottom: 15px; 
    letter-spacing: 2px; 
    font-weight: 600;
    opacity: 0.7;
}

.sidebar-sub-menu { list-style: none; padding: 0; margin: 0; }
.sidebar-sub-menu li { margin-bottom: 15px; }
.sidebar-sub-menu a, .menu-summary { 
    color: #D6C6C6; 
    text-decoration: none; 
    font-size: 0.95rem; 
    display: flex; 
    align-items: center; 
    gap: 12px; 
    cursor: pointer;
}
.sidebar-sub-menu a {
    padding: 8px 10px;
    border-radius: 12px;
    transition: background 0.2s ease, color 0.2s ease, transform 0.12s ease, box-shadow 0.12s ease;
}
.sidebar-sub-menu a:hover {
    background: rgba(230,208,128,0.12);
    color: #F5E6E6;
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(0,0,0,0.45);
}
.sidebar-sub-menu a i, .menu-summary i:first-child { 
    color: #E6D080; 
    width: 20px; 
    text-align: center; 
}

.menu-summary {
    list-style: none;
    justify-content: space-between;
    width: 100%;
}
.menu-summary::-webkit-details-marker { display: none; }

.sidebar-details[open] .arrow {
    transform: rotate(180deg);
}
.arrow {
    font-size: 0.8rem;
    transition: transform 0.3s;
    opacity: 0.5;
}

.sidebar-footer { padding: 20px; border-top: 1px solid rgba(230,208,128,0.16); }
.btn-logout {
    width: 100%; padding: 12px; background: rgba(32, 7, 10, 0.9);
    border: 1px solid rgba(248, 113, 113, 0.7); color: #fecaca;
    border-radius: 999px; cursor: pointer; font-weight: 600;
    letter-spacing: 0.12em;
    font-size: 0.8rem;
    text-transform: uppercase;
    transition: background 0.25s ease, border-color 0.25s ease, transform 0.12s ease, box-shadow 0.12s ease;
}
.btn-logout:hover {
    background: rgba(185, 28, 28, 0.9);
    color: #fff;
    border-color: rgba(252, 165, 165, 0.9);
    box-shadow: 0 6px 18px rgba(0,0,0,0.6);
    transform: translateY(-1px);
}

.pwa-install-btn {
    width: 100%; padding: 12px 16px; background: radial-gradient(circle at 0% 0%, rgba(253,240,178,0.25), rgba(18,4,5,0.98));
    border: 1px solid rgba(229,193,88,0.7); color: #FDF0B2; border-radius: 999px;
    cursor: pointer; font-size: 0.95rem; font-weight: bold; display: flex; align-items: center; justify-content: center; gap: 10px;
    transition: 0.3s;
}
.pwa-install-btn:hover { background: rgba(212,175,55,0.15); color: #e5c84a; }
</style>