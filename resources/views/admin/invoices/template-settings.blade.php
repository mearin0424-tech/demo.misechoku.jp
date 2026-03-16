@extends('layouts.admin')

@section('title', '請求書テンプレート設定')

@section('content')
    <div class="admin-page">
        <h1 class="admin-title">請求書テンプレート設定</h1>
        <p class="admin-description">
            請求書に表示する発行元名・メールアドレス・ロゴ・備考文を設定できます。設定は帳票テンプレートのプレビューおよび発行される請求書に反映されます。
        </p>

        @if(session('status'))
            <div class="admin-alert">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="admin-alert" style="background: rgba(248, 113, 113, 0.12); border-color: rgba(248, 113, 113, 0.3); color: #fee2e2;">
                <ul style="margin: 0; padding-left: 1.2em;">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="admin-panel">
            <form method="POST" action="{{ route('admin.invoices.template-settings.update') }}">
                @csrf
                <div class="admin-form-row">
                    <label class="admin-label" for="issuer_name">発行元名</label>
                    <input type="text" id="issuer_name" name="issuer_name" class="admin-input" value="{{ old('issuer_name', $issuer_name) }}" placeholder="ミセチョク運営事務局" maxlength="255">
                    <p class="admin-note" style="margin-top: 6px;">請求書ヘッダーに表示する発行元の名称です。</p>
                </div>
                <div class="admin-form-row">
                    <label class="admin-label" for="issuer_email">発行元メールアドレス</label>
                    <input type="email" id="issuer_email" name="issuer_email" class="admin-input" value="{{ old('issuer_email', $issuer_email) }}" placeholder="support@misechoku.jp" maxlength="255">
                    <p class="admin-note" style="margin-top: 6px;">請求書に記載する問い合わせ用メールアドレスです。</p>
                </div>
                <div class="admin-form-row">
                    <label class="admin-label" for="logo_url">ロゴURL</label>
                    <input type="url" id="logo_url" name="logo_url" class="admin-input" value="{{ old('logo_url', $logo_url) }}" placeholder="https://example.com/logo.png" maxlength="500">
                    <p class="admin-note" style="margin-top: 6px;">請求書の上部に表示するロゴ画像のURL。未入力の場合は表示しません。</p>
                </div>
                <div class="admin-form-row">
                    <label class="admin-label" for="footer_text">備考（フッター文言）</label>
                    <textarea id="footer_text" name="footer_text" class="admin-input" rows="5" maxlength="2000" placeholder="店舗からのご入金確認後、運営にてキャストへの振込を行います。&#10;上記支払期限までに、下記お振込先へお振り込みください。">{{ old('footer_text', $footer_text) }}</textarea>
                    <p class="admin-note" style="margin-top: 6px;">請求書の「備考」欄に表示する文言。未入力の場合は既定の説明文を表示します。</p>
                </div>
                <div class="admin-form-actions">
                    <button type="submit" class="btn-action manage">
                        <i class="fas fa-save"></i> 設定を保存
                    </button>
                    <a href="{{ route('admin.invoices.index') }}" class="btn-action manage" style="background:#374151;">請求書発行画面へ戻る</a>
                </div>
            </form>
        </section>

        <section class="admin-panel">
            <h2 class="admin-panel-title">プレビュー</h2>
            <p class="admin-note" style="margin-bottom: 12px;">設定を保存したあと、下記から帳票テンプレートを開いて見た目を確認できます。</p>
            <a href="{{ route('admin.deposits.invoice-template.download') }}" class="btn-action manage" target="_blank" rel="noopener">
                <i class="fas fa-file-pdf"></i> 帳票テンプレートをプレビュー
            </a>
        </section>
    </div>
@endsection
