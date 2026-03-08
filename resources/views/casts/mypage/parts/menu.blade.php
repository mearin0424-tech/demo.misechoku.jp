@php
    $current = $current ?? 'profile';
    $fullWidth = $fullWidth ?? false;
@endphp
<div class="cast-mypage-menu-section {{ $fullWidth ? 'cast-mypage-menu-section--full-width' : '' }}">
    <div class="mypage-section mypage-quick-actions">
        <h2 class="mypage-actions-title">メニュー</h2>
        <a href="{{ route('cast.mypage.index') }}" class="btn-action-card profile {{ $current === 'profile' ? 'is-current' : '' }}">
            <span class="btn-action-icon-wrap"><i class="fas fa-user"></i></span>
            <span class="btn-action-body">
                <span class="btn-action-label">PROFILE</span>
                <span class="btn-action-text">プロフィール確認</span>
            </span>
            <i class="fas fa-chevron-right btn-action-arrow"></i>
        </a>
        <a href="{{ route('cast.mypage.employment') }}" class="btn-action-card job {{ $current === 'employment' ? 'is-current' : '' }}">
            <span class="btn-action-icon-wrap"><i class="fas fa-briefcase"></i></span>
            <span class="btn-action-body">
                <span class="btn-action-label">EMPLOYMENT</span>
                <span class="btn-action-text">採用状況</span>
            </span>
            <i class="fas fa-chevron-right btn-action-arrow"></i>
        </a>
        <a href="{{ route('cast.mypage.payment') }}" class="btn-action-card manage {{ $current === 'payment' ? 'is-current' : '' }}">
            <span class="btn-action-icon-wrap"><i class="fas fa-file-invoice-dollar"></i></span>
            <span class="btn-action-body">
                <span class="btn-action-label">PAYMENT</span>
                <span class="btn-action-text">請求・入金管理</span>
            </span>
            <i class="fas fa-chevron-right btn-action-arrow"></i>
        </a>
        <a href="https://mearin0424-tech.github.io/personality-test/personality-test.html" class="btn-action-card personality-test" target="_blank" rel="noopener noreferrer">
            <span class="btn-action-icon-wrap"><i class="fas fa-clipboard-list"></i></span>
            <span class="btn-action-body">
                <span class="btn-action-label">PERSONALITY</span>
                <span class="btn-action-text">接客タイプ診断</span>
            </span>
            <i class="fas fa-chevron-right btn-action-arrow"></i>
        </a>
    </div>
</div>
