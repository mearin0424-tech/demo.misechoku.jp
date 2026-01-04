@extends('layouts.app')

@section('title', 'マイページ')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
@endpush

@section('content')
<div class="contents inner">
    <section class="mypage-area">
        <h1 class="mypage-shop-name">{{ $shopData['shop_name'] }}</h1>

        <div class="shop-header-top">
            <div class="shop-icon-wrapper">
                <img src="{{ $subImages[0] ?? asset('assets/images/common/no-image.png') }}" class="shop-icon-main" id="main-icon-display">
                <button class="btn-add-icon" onclick="document.getElementById('gallery-upload').click()"><i class="fas fa-plus"></i></button>
            </div>
            <div class="shop-word-bubble" onclick="openWordEdit()">
                <p id="display-word">{{ $shopData['word'] }}</p>
                <button class="btn-word-edit" style="position:absolute; right:10px; bottom:5px; background:none; border:none; color:var(--color-gold);"><i class="fas fa-pen"></i></button>
            </div>
        </div>

        <div class="review-area-top" style="padding: 0 15px; margin-bottom: 15px;">
            <a href="{{ route('pages.support.column') }}" class="shop-review-link" style="color:var(--color-gold); text-decoration:none; font-size:0.9rem;">
                <span class="stars"><i class="fas fa-star"></i> {{ $shopData['review_avg'] }}</span>
                <span class="count" style="color:#888;">({{ $shopData['review_count'] }}件)</span>
                <i class="fas fa-chevron-right" style="margin-left:5px;"></i>
            </a>
        </div>

        <div class="detail-box" style="padding: 0 15px;">
            <div class="profile-info-section">
                <div class="section-title-row" style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #333; padding-bottom:5px; margin-bottom:15px;">
                    <h3 style="font-size:1rem; color:#aaa; margin:0;">プロフィール情報</h3>
                    <button class="btn-outline-gold" onclick="openProfileEdit()" style="padding:4px 12px; font-size:0.75rem;">編集</button>
                </div>
                <p class="shop-access-text" style="font-size:0.85rem; color:#888; margin-bottom:10px;">
                    <i class="fas fa-map-marker-alt"></i> {{ $shopData['pref'] }}{{ $shopData['city'] }}{{ $shopData['addr1'] }}
                </p>
                <div class="shop-overview-text" id="display-overview" style="font-size:0.9rem; line-height:1.6; color:#d1c1c1;">
                    {!! nl2br(e($shopData['overview'])) !!}
                </div>
            </div>

            <div class="mypage-actions" style="margin-top:25px;">
                <a href="{{ route('shop.recruits.status') }}" class="btn-action job">
                    <i class="fas fa-edit"></i> 求人情報の確認・編集
                </a>
                <a href="{{ route('shop.mypage.payment.index') }}" class="btn-action manage">
                    <i class="fas fa-tasks"></i> 採用・請求管理
                </a>
            </div>

            {{-- 書類管理 --}}
            <div class="document-section" style="margin-top:35px;">
                <h3 style="font-size:1rem; color:#aaa; border-bottom:1px solid #333; padding-bottom:5px; margin-bottom:15px;">書類管理</h3>
                <ul class="doc-list" style="list-style:none; padding:0;">
                    @foreach($documents as $doc)
                    <li class="doc-item">
                        <div class="doc-icon" style="font-size:1.2rem; color:#666; margin-right:15px;"><i class="fas fa-file-alt"></i></div>
                        <div class="doc-info" style="flex:1;">
                            <span class="doc-name" style="display:block; font-weight:bold; font-size:0.9rem;">{{ $doc['name'] }}</span>
                            <span class="doc-status {{ $doc['status'] == 'submitted' ? 'done' : 'pending' }}">
                                {{ $doc['status'] == 'submitted' ? '提出済' : '未提出' }}
                            </span>
                        </div>
                        <i class="fas fa-chevron-right" style="color:#444;"></i>
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- ギャラリー --}}
            <div class="gallery-edit-section" style="margin-top:35px;">
                <div class="section-guide" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                    <h3 style="font-size:1rem; color:#aaa; margin:0;">ギャラリー編集</h3>
                    <div class="guide-mini" style="font-size:0.65rem; color:#777;">
                        <i class="fas fa-info-circle"></i> ドラッグで並び替え
                    </div>
                </div>
                <ul class="gallery-sortable" id="gallery-list">
                    @for($i=0; $i<6; $i++)
                        @if(isset($subImages[$i]))
                            <li class="gallery-item filled" draggable="true">
                                <img src="{{ $subImages[$i] }}">
                                @if($i === 0)<span class="main-badge" style="position:absolute; top:0; left:0; background:var(--color-accent); color:#fff; font-size:0.6rem; padding:2px 5px;">MAIN</span>@endif
                                <button class="btn-delete" style="position:absolute; top:2px; right:2px; background:rgba(0,0,0,0.6); color:#fff; border:none; border-radius:50%; width:18px; height:18px; font-size:10px;">×</button>
                            </li>
                        @else
                            <li class="gallery-item placeholder" onclick="document.getElementById('gallery-upload').click()">
                                <i class="fas fa-plus"></i>
                            </li>
                        @endif
                    @endfor
                </ul>
            </div>
        </div>
    </section>
</div>

{{-- 編集モーダル類 --}}
<div id="modal-word" class="modal-profile-custom" style="justify-content:center; align-items:center; padding:20px;">
    <div style="background:var(--color-card); border:1px solid var(--color-border); width:100%; max-width:400px; border-radius:15px; padding:20px;">
        <h3 style="margin-top:0; color:var(--color-gold);">ひとこと編集</h3>
        <textarea id="word-input" rows="3" style="width:100%; background:#111; color:#fff; border:1px solid #444; border-radius:8px; padding:10px; margin:15px 0;"></textarea>
        <div style="display:flex; gap:10px;">
            <button class="btn-action manage" onclick="closeWordEdit()" style="margin:0;">キャンセル</button>
            <button class="btn-action job" onclick="saveWord()" style="margin:0; flex:1;">保存</button>
        </div>
    </div>
</div>

<input type="file" id="gallery-upload" style="display:none;" accept="image/*">
@endsection

@push('scripts')
<script>
    function openWordEdit() {
        document.getElementById('modal-word').style.display = 'flex';
        document.getElementById('word-input').value = document.getElementById('display-word').innerText;
    }
    function closeWordEdit() { document.getElementById('modal-word').style.display = 'none'; }
    
    function openProfileEdit() { alert('プロフィール編集画面へ遷移、またはモーダルを表示'); }

    // 保存処理などの非同期通信は Vite 管理下の JS (chat-handler.js 等) へ移行を推奨
</script>
@endpush