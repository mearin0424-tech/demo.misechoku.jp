{{-- <x-ui.fab icon="plus" /> 押下は behaviors.js が 'fab' イベントを発火 --}}
@props(['icon' => 'plus'])
<button type="button" data-fab
    {{ $attributes->merge(['class' => 'fixed bottom-[90px] right-5 w-14 h-14 rounded-full flex items-center justify-center bg-accent text-on-accent shadow-fab-3d active:translate-y-1.5 transition-all z-30']) }}>
    <x-ui.icon :name="$icon" class="text-2xl" />
    {{ $slot }}
</button>
