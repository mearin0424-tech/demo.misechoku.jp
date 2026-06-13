{{--
    プライマリ CTA ボタン。アプリ共通の「solid mauve + 物理ボタン感」を 1 か所に集約。

    使い方:
        <x-ui.button icon="check">アップロード</x-ui.button>
        <x-ui.button variant="outline">キャンセル</x-ui.button>
        <x-ui.button variant="danger">削除</x-ui.button>
        <x-ui.button size="sm">保存</x-ui.button>
        <x-ui.button as="a" href="/route">詳細を見る</x-ui.button>

    Props:
        - variant : 'solid' (既定) | 'outline' | 'danger' | 'grad' (※後方互換のため残す。solid と同じ描画)
        - size    : 'sm' | 'md' (既定) | 'lg'
        - as      : 'button' (既定) | 'a'
        - href    : as='a' のときのみ
        - type    : button type (既定: 'button')
        - icon    : <x-ui.icon name="..."> のキー名
--}}
@props([
    'variant' => 'solid',
    'size'    => 'md',
    'as'      => 'button',
    'href'    => null,
    'type'    => 'button',
    'icon'    => null,
])
@php
    // サイズ
    $sizeClass = match($size) {
        'sm' => 'min-h-[36px] px-4 py-2 text-[13px]',
        'lg' => 'min-h-[52px] px-7 py-4 text-[15px]',
        default => 'min-h-[44px] px-6 py-3 text-[14px]',
    };

    // ベース（全 variant 共通）
    $base = 'inline-flex items-center justify-center gap-2 rounded-full font-bold'
          . ' transition-[filter,transform] duration-150'
          . ' active:scale-[0.97] focus-visible:outline-none';

    // 物理ボタン感のシャドウ（solid / danger / grad で共通）
    // 0 6px 14px の drop shadow + 上端 1px 白ハイライト + 下端 1px 黒陰影 で
    // グラデなしでも "光が上から当たって浮いている" 質感を作る
    $physicalShadow = 'shadow-[0_6px_14px_rgba(0,0,0,0.45),inset_0_1px_0_rgba(255,255,255,0.20),inset_0_-1px_0_rgba(0,0,0,0.18)]';
    $pressedShadow  = 'active:shadow-[0_2px_5px_rgba(0,0,0,0.45),inset_0_2px_4px_rgba(0,0,0,0.2)]';

    // バリアント別の色 / 影
    $tone = match($variant) {
        'outline' => 'bg-transparent border border-line-accent/45 text-accent-text hover:bg-accent/10 hover:border-accent/60',
        'danger'  => 'bg-[#dc2626] text-white hover:brightness-110 ' . $physicalShadow . ' ' . $pressedShadow,
        // 'grad' は後方互換（旧 gradient ボタン）。実装は solid と同じ。
        'grad'    => 'bg-accent text-on-accent hover:brightness-110 ' . $physicalShadow . ' ' . $pressedShadow,
        default   => 'bg-accent text-on-accent hover:brightness-110 ' . $physicalShadow . ' ' . $pressedShadow,
    };

    $tag = $as === 'a' ? 'a' : 'button';
@endphp
<{{ $tag }}
    @if($tag === 'a') href="{{ $href }}" @else type="{{ $type }}" @endif
    {{ $attributes->merge(['class' => $base.' '.$sizeClass.' '.$tone]) }}>
    @isset($icon)<x-ui.icon :name="$icon" class="text-base" />@endisset
    {{ $slot }}
</{{ $tag }}>
