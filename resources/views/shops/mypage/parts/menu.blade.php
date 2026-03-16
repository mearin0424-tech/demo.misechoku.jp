@php
    $current = $current ?? 'profile';
    $fullWidth = $fullWidth ?? false;
@endphp
<div class="cast-mypage-menu-section {{ $fullWidth ? 'cast-mypage-menu-section--full-width' : '' }}">
    <div class="mypage-section mypage-quick-actions cast-mypage-menu-buttons">
        <a href="{{ route('shop.recruits.status') }}" class="menu-btn job {{ $current === 'recruit' ? 'is-current' : '' }}">
            <div class="menu-btn-shine"></div>
            <div class="menu-btn-content">
                <div class="menu-btn-icon">
                    <i class="far fa-folder-open"></i>
                </div>
                <div class="menu-btn-text">
                    <p class="menu-btn-label">RECRUIT</p>
                    <p class="menu-btn-title">採用管理</p>
                </div>
            </div>
            <div class="menu-btn-arrow">
                <i class="fas fa-chevron-right"></i>
            </div>
        </a>
        <a href="{{ route('shop.mypage.payment.index') }}" class="menu-btn manage {{ $current === 'payment' ? 'is-current' : '' }}">
            <div class="menu-btn-shine"></div>
            <div class="menu-btn-content">
                <div class="menu-btn-icon">
                    <i class="far fa-credit-card"></i>
                </div>
                <div class="menu-btn-text">
                    <p class="menu-btn-label">PAYMENT</p>
                    <p class="menu-btn-title">請求管理</p>
                </div>
            </div>
            <div class="menu-btn-arrow">
                <i class="fas fa-chevron-right"></i>
            </div>
        </a>
        <a href="{{ route('shop.jobdescription') }}" class="menu-btn job {{ $current === 'jobdescription' ? 'is-current' : '' }}">
            <div class="menu-btn-shine"></div>
            <div class="menu-btn-content">
                <div class="menu-btn-icon">
                    <i class="far fa-file-alt"></i>
                </div>
                <div class="menu-btn-text">
                    <p class="menu-btn-label">JOB</p>
                    <p class="menu-btn-title">求人票</p>
                </div>
            </div>
            <div class="menu-btn-arrow">
                <i class="fas fa-chevron-right"></i>
            </div>
        </a>
    </div>
</div>
