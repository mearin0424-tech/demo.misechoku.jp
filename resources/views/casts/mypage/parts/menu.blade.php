@php
    $current = $current ?? 'profile';
@endphp
<nav class="cast-mypage-menu" aria-label="マイページメニュー">
    <ul class="cast-mypage-menu-list">
        <li>
            <a href="{{ route('cast.mypage.index') }}" class="cast-mypage-menu-item {{ $current === 'profile' ? 'is-current' : '' }}">
                <i class="fas fa-user" aria-hidden="true"></i>
                <span>プロフィール確認</span>
            </a>
        </li>
        <li>
            <a href="{{ route('cast.mypage.employment') }}" class="cast-mypage-menu-item {{ $current === 'employment' ? 'is-current' : '' }}">
                <i class="fas fa-briefcase" aria-hidden="true"></i>
                <span>採用状況</span>
            </a>
        </li>
        <li>
            <a href="{{ route('cast.mypage.payment') }}" class="cast-mypage-menu-item {{ $current === 'payment' ? 'is-current' : '' }}">
                <i class="fas fa-yen-sign" aria-hidden="true"></i>
                <span>請求・入金管理</span>
            </a>
        </li>
    </ul>
</nav>
