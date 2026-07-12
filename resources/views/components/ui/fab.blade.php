{{--
    フローティングアクションボタン（FAB）。
    画面右下に常時浮かぶ円形 CTA。x-ui.button と同じ「物理ボタン感」を適用。

    使い方:
        <x-ui.fab icon="plus" />
        <x-ui.fab icon="search" aria-label="詳細検索" />

    押下挙動は behaviors.js が data-fab を見て 'fab' イベントを発火する。

    Props:
        - icon : <x-ui.icon name="..."> のキー名（既定 'plus'）
--}}
@props(['icon' => 'plus'])
<button type="button" data-fab
    {{ $attributes->merge(['class' =>
        'fixed bottom-[90px] right-5 w-14 h-14 rounded-full flex items-center justify-center'
        . ' bg-accent text-on-accent z-30'
        . ' shadow-[0_8px_18px_rgba(0,0,0,0.5),inset_0_1px_0_rgba(255,255,255,0.22),inset_0_-1px_0_rgba(0,0,0,0.18)]'
        . ' transition-transform duration-150'
        . ' active:scale-95 active:shadow-[0_2px_6px_rgba(0,0,0,0.45),inset_0_2px_4px_rgba(0,0,0,0.2)]'
    ]) }}>
    <x-ui.icon :name="$icon" class="text-2xl" />
    {{ $slot }}
</button>
