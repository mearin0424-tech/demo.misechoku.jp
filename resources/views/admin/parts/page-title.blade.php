{{-- 管理画面ページヘッダ：英字エンブレム + 和文セリフ・タイトル
     使い方: @include('admin.parts.page-title', ['eyebrow' => 'INVOICES', 'title' => '請求書発行'])
--}}
<div class="admin-page-header">
    @if(!empty($eyebrow))
        <span class="admin-page-eyebrow">{{ $eyebrow }}</span>
    @endif
    @if(!empty($title))
        <h1 class="admin-title">{{ $title }}</h1>
    @endif
</div>
