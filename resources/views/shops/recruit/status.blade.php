@extends('layouts.app')

@section('title', '求人ステータス管理')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/recruitment.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
@endpush

@section('content')
<div class="recruit-status-page recruit-detail-page animate-fadeIn">
    <header class="recruit-status-header">
        <a href="{{ route('shop.mypage.index') }}" class="recruit-status-back">
            <i class="fas fa-chevron-left"></i> マイページへ
        </a>
        <div class="recruit-status-title-block">
            <h1 class="recruit-status-title serif-font">Recruit Status</h1>
            <p class="recruit-status-sub">採用ステータス・公開求人・共有の管理</p>
        </div>
    </header>

    @if(session('message'))
        <p class="profile-edit-flash recruit-status-flash">{{ session('message') }}</p>
    @endif

    {{-- ========== 1. 採用ステータス（マッチしているキャスト一覧） ========== --}}
    <section class="recruit-status-section recruit-status-section-casts">
        <h2 class="recruit-status-section-title">
            <span class="recruit-status-section-icon"><i class="fas fa-user-check"></i></span>
            採用ステータス
        </h2>
        <p class="recruit-status-section-desc">マッチしているキャストの一覧。面談日の設定と採用ステータスを確認・更新できます。</p>

        @if(count($applications ?? []) > 0)
            <div class="recruit-status-table-wrap">
                <table class="recruit-status-table">
                    <thead>
                        <tr>
                            <th>キャスト</th>
                            <th>面談日</th>
                            <th>採用ステータス</th>
                            <th>応募日</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($applications as $app)
                        <tr>
                            <td>
                                <span class="recruit-status-cast-name">{{ $app['cast_name'] }}</span>
                                <span class="recruit-status-cast-id">{{ $app['cast_id'] }}</span>
                            </td>
                            <td>
                                @if($app['result_date'])
                                    <span class="recruit-status-date">{{ $app['result_date'] }}</span>
                                @else
                                    <span class="recruit-status-date-none">未設定</span>
                                @endif
                            </td>
                            <td>
                                <span class="recruit-status-badge recruit-status-badge-{{ $app['status'] }}">
                                    {{ $app['status_label'] }}
                                </span>
                            </td>
                            <td><span class="recruit-status-meta">{{ $app['created_at'] ?? '—' }}</span></td>
                            <td>
                                <a href="#" class="recruit-status-link" aria-label="面談日・ステータスを設定">
                                    <i class="fas fa-cog"></i> 設定
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="recruit-status-empty">
                <i class="fas fa-inbox recruit-status-empty-icon"></i>
                <p class="recruit-status-empty-text">まだマッチしているキャストはいません。</p>
                <p class="recruit-status-empty-sub">求人を公開すると、応募があったキャストがここに表示されます。</p>
            </div>
        @endif
    </section>

    {{-- ========== 2. 公開中の求人情報（編集・プレビュー） ========== --}}
    <section class="recruit-status-section recruit-status-section-job">
        <h2 class="recruit-status-section-title">
            <span class="recruit-status-section-icon"><i class="fas fa-briefcase"></i></span>
            公開中の求人情報
        </h2>
        <p class="recruit-status-section-desc">現在の求人内容の確認、編集、プレビューができます。</p>

        <article class="recruit-status-card">
            <div class="recruit-status-card-head">
                <div class="recruit-status-card-info">
                    <span class="status-badge {{ ($recruit['status'] ?? 'active') === 'active' ? 'status-active' : 'status-inactive' }}">
                        {{ ($recruit['status'] ?? 'active') === 'active' ? '求人公開中' : '公開停止中' }}
                    </span>
                    <h3 class="recruit-status-card-title">{{ $recruit['catch_copy'] ?: '求人情報を設定してください' }}</h3>
                    <p class="recruit-status-card-meta">時給: ¥{{ number_format($recruit['hourly_wage_regular'] ?? 0) }}〜</p>
                    @if(!empty($recruit['updated_at']))
                        <p class="recruit-status-card-meta">更新日: {{ $recruit['updated_at'] }}</p>
                    @endif
                </div>
                <form action="{{ route('shop.recruits.toggle-status') }}" method="POST">
                    @csrf
                    <button
                        type="submit"
                        class="toggle-btn {{ ($recruit['status'] ?? 'active') === 'active' ? 'active' : '' }}"
                        aria-label="公開のON/OFF"
                    >
                        <div class="toggle-circle"></div>
                    </button>
                </form>
            </div>

            @if(!empty($recruit['message']))
            <div class="recruit-store-message">
                <div class="recruit-store-message-head">
                    <div class="recruit-store-message-icon" aria-hidden="true"><i class="fas fa-quote-left"></i></div>
                    <span class="recruit-store-message-label">お店からのひとこと</span>
                </div>
                <div class="recruit-store-message-inner">
                    <p class="recruit-store-message-text">{{ $recruit['message'] }}</p>
                    <div class="recruit-store-message-character" aria-hidden="true"><i class="fas fa-dove"></i></div>
                </div>
            </div>
            @endif

            <h4 class="recruit-block-title" style="margin-top:24px;"><i class="fas fa-coins"></i> 現在の募集条件</h4>
            <div class="recruit-job-types">
                <div class="recruit-type-card recruit-type-card-new is-main">
                    <div class="recruit-type-main">
                        <span class="recruit-type-label recruit-type-badge-new">本入店</span>
                        <div class="recruit-type-wage-row">
                            <span class="label">時給</span>
                            <span class="value">{{ number_format($recruit['hourly_wage_regular'] ?? 0) }}</span>
                            <span class="unit">円〜</span>
                        </div>
                    </div>
                    <div class="recruit-type-bonus-box">
                        <p class="bonus-label">勤務条件</p>
                        <p class="bonus-amount">{{ $recruit['working_days'] ?: '未設定' }}</p>
                        <p class="bonus-meta">（{{ $recruit['working_hours'] ?: '勤務時間未設定' }}）</p>
                        <p class="bonus-note">{{ $recruit['regular_holiday'] ?: '' }}</p>
                    </div>
                </div>
                @if(!empty($recruit['trial_hourly_wage']))
                    <div class="recruit-type-card recruit-type-card-new">
                        <div class="recruit-type-main">
                            <span class="recruit-type-label recruit-type-badge-new">体験入店</span>
                            <div class="recruit-type-wage-row">
                                <span class="label">時給</span>
                                <span class="value">{{ number_format($recruit['trial_hourly_wage']) }}</span>
                                <span class="unit">円〜</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="recruit-status-card-actions">
                <a href="{{ route('shop.recruits.edit') }}" class="recruit-btn recruit-btn-edit">
                    <i class="fas fa-pen"></i> 編集する
                </a>
                <a href="{{ $previewRoute }}" class="recruit-btn recruit-btn-preview" target="_blank" rel="noopener">
                    <i class="fas fa-external-link-alt"></i> プレビュー
                </a>
            </div>
        </article>
    </section>

    {{-- ========== 3. 求人情報の共有 ========== --}}
    <section class="recruit-status-section recruit-status-section-share">
        <h2 class="recruit-status-section-title">
            <span class="recruit-status-section-icon"><i class="fas fa-share-nodes"></i></span>
            求人情報の共有
        </h2>
        <p class="recruit-status-section-desc">この求人ページのURLをコピーしたり、SNSで共有できます。</p>

        @if(!empty($shareUrl))
            <div class="recruit-share-box">
                <label class="recruit-share-label">共有用URL</label>
                <div class="recruit-share-input-row">
                    <input type="text" class="recruit-share-input" id="recruit-share-url" readonly value="{{ $shareUrl }}" aria-label="共有用URL">
                    <button type="button" class="recruit-share-copy-btn" id="recruit-share-copy" aria-label="URLをコピー">
                        <i class="fas fa-copy"></i> コピー
                    </button>
                </div>
                <div class="recruit-share-actions">
                    <a href="{{ $shareUrl }}" target="_blank" rel="noopener" class="recruit-share-btn recruit-share-btn-open">
                        <i class="fas fa-external-link-alt"></i> 新しいタブで開く
                    </a>
                    <a href="https://twitter.com/intent/tweet?text={{ rawurlencode(($recruit['catch_copy'] ?? '求人情報') . ' ' . $shareUrl) }}" target="_blank" rel="noopener" class="recruit-share-btn recruit-share-btn-x" aria-label="Xで共有">
                        <i class="fab fa-x-twitter"></i> X
                    </a>
                    <button type="button" class="recruit-share-btn recruit-share-btn-instagram" id="recruit-share-instagram" aria-label="Instagramで共有（テキストをコピーしてInstagramを開く）" data-share-text="{{ e(($recruit['catch_copy'] ?? '求人情報') . ' ' . $shareUrl) }}">
                        <i class="fab fa-instagram"></i> Instagram
                    </button>
                    <a href="https://line.me/R/msg/text/?{{ rawurlencode(($recruit['catch_copy'] ?? '求人情報') . ' ' . $shareUrl) }}" target="_blank" rel="noopener" class="recruit-share-btn recruit-share-btn-line" aria-label="LINEで共有">
                        <i class="fab fa-line"></i> LINE
                    </a>
                </div>
            </div>
        @else
            <div class="recruit-status-empty recruit-status-empty-small">
                <p class="recruit-status-empty-text">求人を保存すると共有用URLが発行されます。</p>
            </div>
        @endif
    </section>
</div>

@if(!empty($shareUrl))
@push('scripts')
<script>
(function() {
    var copyBtn = document.getElementById('recruit-share-copy');
    var input = document.getElementById('recruit-share-url');
    if (copyBtn && input) {
        copyBtn.addEventListener('click', function() {
            input.select();
            input.setSelectionRange(0, 99999);
            try {
                navigator.clipboard.writeText(input.value);
                copyBtn.innerHTML = '<i class="fas fa-check"></i> コピーしました';
                copyBtn.classList.add('is-copied');
                setTimeout(function() {
                    copyBtn.innerHTML = '<i class="fas fa-copy"></i> コピー';
                    copyBtn.classList.remove('is-copied');
                }, 2000);
            } catch (e) {
                document.execCommand('copy');
                copyBtn.innerHTML = '<i class="fas fa-check"></i> コピーしました';
                setTimeout(function() { copyBtn.innerHTML = '<i class="fas fa-copy"></i> コピー'; }, 2000);
            }
        });
    }

    var igBtn = document.getElementById('recruit-share-instagram');
    if (igBtn) {
        igBtn.addEventListener('click', function() {
            var text = igBtn.getAttribute('data-share-text') || '';
            var url = input ? input.value : '';
            if (!text && url) text = url;
            if (!text) return;
            try {
                navigator.clipboard.writeText(text);
                igBtn.innerHTML = '<i class="fas fa-check"></i> コピーしました';
                igBtn.classList.add('is-copied');
                setTimeout(function() {
                    igBtn.innerHTML = '<i class="fab fa-instagram"></i> Instagram';
                    igBtn.classList.remove('is-copied');
                }, 2500);
            } catch (e) {
                document.execCommand('copy');
                igBtn.innerHTML = '<i class="fas fa-check"></i> コピーしました';
                setTimeout(function() { igBtn.innerHTML = '<i class="fab fa-instagram"></i> Instagram'; }, 2500);
            }
            window.open('https://www.instagram.com/', '_blank', 'noopener');
        });
    }
})();
</script>
@endpush
@endif
@endsection
