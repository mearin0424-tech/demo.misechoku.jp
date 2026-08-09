@php
    $current = $current ?? 'profile';
    $fullWidth = $fullWidth ?? false;
    $menuData = $menuData ?? [];
    $recruitStatus = $menuData['recruit_status'] ?? '未設定';
    $hiredCount = (int) ($menuData['hired_count'] ?? 0);
    $paymentPendingCount = (int) ($menuData['payment_pending_count'] ?? 0);
@endphp
<div class="cast-mypage-menu-section {{ $fullWidth ? 'cast-mypage-menu-section--full-width' : '' }}">
    <div class="mypage-section mypage-quick-actions cast-mypage-menu-buttons">
        <a href="{{ route('shop.recruits.edit') }}" class="menu-btn job {{ $current === 'jobdescription' ? 'is-current' : '' }}">
            <div class="menu-btn-shine"></div>
            <div class="menu-btn-content">
                <div class="menu-btn-icon">
                    <i class="far fa-file-alt"></i>
                </div>
                <div class="menu-btn-text">
                    <p class="menu-btn-title">求人票</p>
                    <p class="menu-btn-meta">掲載ステータス: {{ $recruitStatus }}</p>
                </div>
            </div>
            <div class="menu-btn-arrow">
                <i class="fas fa-chevron-right"></i>
            </div>
        </a>
        <a href="{{ route('shop.mypage.management') }}" class="menu-btn job {{ $current === 'management' ? 'is-current' : '' }}">
            <div class="menu-btn-shine"></div>
            <div class="menu-btn-content">
                <div class="menu-btn-icon">
                    <i class="fas fa-users-cog"></i>
                </div>
                <div class="menu-btn-text">
                    <p class="menu-btn-title">採用・入金管理</p>
                    <p class="menu-btn-meta">採用数: {{ number_format($hiredCount) }}件 / 入金待ち: {{ number_format($paymentPendingCount) }}件</p>
                </div>
            </div>
            <div class="menu-btn-arrow">
                <i class="fas fa-chevron-right"></i>
            </div>
        </a>
        <a href="{{ route('shop.mypage.staff.index') }}" class="menu-btn job {{ $current === 'staff' ? 'is-current' : '' }}">
            <div class="menu-btn-shine"></div>
            <div class="menu-btn-content">
                <div class="menu-btn-icon">
                    <i class="fas fa-id-badge"></i>
                </div>
                <div class="menu-btn-text">
                    <p class="menu-btn-title">スタッフ管理</p>
                    <p class="menu-btn-meta">1店舗で複数アカウントが使えます</p>
                </div>
            </div>
            <div class="menu-btn-arrow">
                <i class="fas fa-chevron-right"></i>
            </div>
        </a>
    </div>
</div>
