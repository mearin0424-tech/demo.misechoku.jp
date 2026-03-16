@extends('layouts.app')

@section('title', 'ミセチョク')

@section('content')
<div class="welcome-container" style="text-align: center; padding: 100px 20px;">
    <img src="{{ asset('assets/images/common/logo-yoko.png') }}" style="width: 250px; margin-bottom: 50px;">
    
    <h1 style="font-family: 'Shippori Mincho'; color: var(--color-gold); margin-bottom: 30px;">
        新しい出会いのカタチを、直感で。
    </h1>

    <div class="choice-box" style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;">
        <div class="choice-card" style="background: #2d0b0b; border: 1px solid #d4af37; padding: 30px; border-radius: 20px; width: 300px;">
            <i class="fas fa-glass-cheers" style="font-size: 3rem; color: #d4af37; margin-bottom: 20px;"></i>
            <h3>キャストの方</h3>
            <p style="font-size: 0.8rem; margin: 15px 0;">理想のお店と直接つながる</p>
            <a href="{{ route('login.demo') }}" class="btn-sidebar-primary" style="justify-content: center;">ログイン / 登録</a>
            <a href="{{ route('lp.cast') }}" style="display: block; margin-top: 15px; color: #d1c1c1; font-size: 0.8rem;">もっと詳しく</a>
        </div>

        <div class="choice-card" style="background: #2d0b0b; border: 1px solid #d4af37; padding: 30px; border-radius: 20px; width: 300px;">
            <i class="fas fa-store" style="font-size: 3rem; color: #d4af37; margin-bottom: 20px;"></i>
            <h3>店舗の方</h3>
            <p style="font-size: 0.8rem; margin: 15px 0;">最高のキャストを直感で探す</p>
            <a href="{{ route('login.demo') }}" class="btn-sidebar-primary" style="justify-content: center; background: #881337;">ログイン / 登録</a>
            <a href="{{ route('lp.shop') }}" style="display: block; margin-top: 15px; color: #d1c1c1; font-size: 0.8rem;">もっと詳しく</a>
        </div>
    </div>
</div>
@endsection