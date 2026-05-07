{{-- 管理画面ページヘッダ：英字エンブレム + 和文セリフ・タイトル
     使い方:
        @include('admin.parts.page-title', ['eyebrow' => 'INVOICES', 'title' => '請求書発行'])

     説明文を載せたい場合は info を渡す（改行は <br> で。HTML可）：
        @include('admin.parts.page-title', [
            'eyebrow' => 'CASTS',
            'title'   => 'キャスト管理',
            'info'    => '行をタップすると詳細画面に移動します。<br>停止操作・運用実績は詳細画面から確認できます。',
        ])
--}}
@php
    $infoId = 'admin-info-' . substr(md5(($eyebrow ?? '') . '|' . ($title ?? '') . '|' . microtime(true)), 0, 8);
@endphp
<div class="admin-page-header">
    @if(!empty($eyebrow))
        <span class="admin-page-eyebrow">{{ $eyebrow }}</span>
    @endif
    <div class="admin-title-row">
        @if(!empty($title))
            <h1 class="admin-title">{{ $title }}</h1>
        @endif
        @if(!empty($info ?? null))
            <div class="admin-info-wrap">
                <button type="button"
                        class="admin-info-btn"
                        aria-controls="{{ $infoId }}"
                        aria-expanded="false"
                        title="この画面でできること">
                    <i class="fas fa-circle-info" aria-hidden="true"></i>
                    <span class="u-visually-hidden">この画面でできること</span>
                </button>
                <div class="admin-info-popover" id="{{ $infoId }}" role="region" aria-label="この画面でできること" hidden>
                    <div class="admin-info-popover__head">
                        <i class="fas fa-circle-info" aria-hidden="true"></i>
                        <strong>この画面でできること</strong>
                        <button type="button" class="admin-info-popover__close" aria-label="閉じる">
                            <i class="fas fa-xmark" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="admin-info-popover__body">{!! $info !!}</div>
                </div>
            </div>
        @endif
    </div>
</div>
