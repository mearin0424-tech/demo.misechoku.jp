{{-- <x-ui.icon name="home" class="text-xl" />  意味名→Phosphor を一元管理（DESIGN.md §5 と同期） --}}
@props(['name'])
@php
    $map = [
        'home'     => 'ph-fill ph-house',
        'swipe'    => 'ph-fill ph-cards',
        'search'   => 'ph-bold ph-magnifying-glass',
        'likes'    => 'ph-fill ph-heart',
        'talk'     => 'ph-fill ph-chat-teardrop-text',
        'mypage'   => 'ph-fill ph-user',
        'back'     => 'ph-bold ph-caret-left',
        'forward'  => 'ph-bold ph-caret-right',
        'share'    => 'ph-bold ph-share-network',
        'like'     => 'ph-fill ph-heart',
        'nope'     => 'ph-bold ph-x',
        'super'    => 'ph-fill ph-star',
        'settings' => 'ph-bold ph-gear-six',
        'close'    => 'ph-bold ph-x',
        'plus'     => 'ph-bold ph-plus',
        'check'    => 'ph-bold ph-check',
        'list'     => 'ph-bold ph-list',
        'bell'     => 'ph-bold ph-bell',
        'task'     => 'ph-fill ph-check-circle',
        'edit'     => 'ph-bold ph-pencil-simple',
        'staff'    => 'ph-fill ph-users-three',
    ];
    // 未定義の名前は ? を出してミスを可視化（無音で消えない）
    $icon = $map[$name] ?? 'ph-bold ph-question';
@endphp
<i {{ $attributes->merge(['class' => $icon]) }} aria-hidden="true"></i>
