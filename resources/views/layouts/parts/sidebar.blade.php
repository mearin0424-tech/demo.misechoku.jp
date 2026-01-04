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
                            <li><a href="#"><i class="fas fa-envelope"></i> メールアドレス変更</a></li>
                            <li><a href="#"><i class="fas fa-key"></i> パスワード変更</a></li>
                            <li><a href="#" class="text-danger"><i class="fas fa-user-slash"></i> 退会手続き</a></li>
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
                <li><a href="{{ url('/support/column') }}"><i class="fas fa-lightbulb"></i> お役立ちコラム</a></li>
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
            </ul>
        </div>
    </div>

    <div class="sidebar-footer">
        <button class="btn-logout" onclick="if(confirm('ログアウトしますか？')) location.href='{{ url('/logout') }}'">
            <i class="fas fa-sign-out-alt"></i> <span>ログアウト</span>
        </button>
    </div>
</aside>

<style>
#side-menu {
    background: #1a0505;
    color: #fff;
}
.sidebar-header { 
    padding: 20px; 
    border-bottom: 1px solid rgba(255,255,255,0.05); 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
}
.btn-sidebar-close { background: none; border: none; color: #fff; font-size: 1.2rem; cursor: pointer; }
.sidebar-logo { height: 30px; }
.sidebar-content { flex: 1; overflow-y: auto; padding: 20px; }
.sidebar-section { margin-bottom: 30px; }

.menu-label-header { 
    font-size: 10px; 
    color: #d4af37; 
    margin-bottom: 15px; 
    letter-spacing: 2px; 
    font-weight: bold;
    opacity: 0.6;
}

.sidebar-sub-menu { list-style: none; padding: 0; margin: 0; }
.sidebar-sub-menu li { margin-bottom: 15px; }
.sidebar-sub-menu a, .menu-summary { 
    color: #d1c1c1; 
    text-decoration: none; 
    font-size: 0.95rem; 
    display: flex; 
    align-items: center; 
    gap: 12px; 
    cursor: pointer;
}
.sidebar-sub-menu a i, .menu-summary i:first-child { 
    color: #d4af37; 
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

.sidebar-footer { padding: 20px; border-top: 1px solid rgba(255,255,255,0.05); }
.btn-logout {
    width: 100%; padding: 12px; background: transparent;
    border: 1px solid #b91c1c; color: #b91c1c;
    border-radius: 8px; cursor: pointer; font-weight: bold;
    transition: 0.3s;
}
.btn-logout:hover {
    background: #b91c1c;
    color: #fff;
}
</style>