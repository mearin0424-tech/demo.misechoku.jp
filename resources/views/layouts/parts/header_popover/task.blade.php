@php
    /** @var array<int, array<string,mixed>> $todoList */
    $todoList   = $todoList   ?? [];
    /** @var array{high: array<int, array<string,mixed>>, normal: array<int, array<string,mixed>>} $taskGroups */
    $taskGroups = $taskGroups ?? ['high' => [], 'normal' => []];
    $highCount   = count($taskGroups['high']   ?? []);
    $normalCount = count($taskGroups['normal'] ?? []);
    $total       = $highCount + $normalCount;
@endphp
<div id="header-task-popup"
     class="header-popup task-popup stop-propagation"
     style="display:none;"
     data-total="{{ $total }}"
     data-high="{{ $highCount }}">

    {{-- ========== ヘッダー ========== --}}
    <header class="task-popup__head">
        <div class="task-popup__head-title">
            <i class="fas fa-list-check task-popup__head-icon" aria-hidden="true"></i>
            <span class="task-popup__head-label">やることリスト</span>
            {{-- タイトル横のカウントは廃止（ヘッダーアイコン上のバッジで十分） --}}
        </div>
        <button type="button"
                class="task-popup__close"
                onclick="togglePopup('header-task-popup')"
                aria-label="閉じる">
            <i class="fas fa-times" aria-hidden="true"></i>
        </button>
    </header>

    {{-- ========== 概要バー ========== --}}
    @if($total > 0)
        <div class="task-popup__summary">
            @if($highCount > 0)
                <span class="task-popup__summary-badge task-popup__summary-badge--high">
                    <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                    緊急 {{ $highCount }}件
                </span>
            @endif
            @if($normalCount > 0)
                <span class="task-popup__summary-badge task-popup__summary-badge--normal">
                    <i class="fas fa-circle-info" aria-hidden="true"></i>
                    通常 {{ $normalCount }}件
                </span>
            @endif
        </div>
    @endif

    {{-- ========== 本体 ========== --}}
    <div class="task-popup__body">
        @if($total === 0)
            <div class="task-popup__empty">
                <span class="task-popup__empty-illust" aria-hidden="true">
                    <i class="fas fa-circle-check"></i>
                </span>
                <p class="task-popup__empty-title">すべて完了しています</p>
                <p class="task-popup__empty-desc">現在対応が必要なタスクはありません。</p>
            </div>
        @else
            @foreach(['high', 'normal'] as $bucket)
                @php
                    $items = $taskGroups[$bucket] ?? [];
                @endphp
                @if(count($items) > 0)
                    <section class="task-popup__section task-popup__section--{{ $bucket }}">
                        <h5 class="task-popup__section-label">
                            @if($bucket === 'high')
                                <i class="fas fa-fire" aria-hidden="true"></i>
                                今すぐ対応
                            @else
                                <i class="fas fa-clipboard-list" aria-hidden="true"></i>
                                対応推奨
                            @endif
                        </h5>
                        @foreach($items as $todo)
                            @php
                                $url  = $todo['url'] ?? null;
                                $text = $todo['text'] ?? '';
                                $icon = $todo['icon'] ?? 'fa-circle-info';
                                $cat  = $todo['category_label'] ?? '';
                                $urg  = $todo['urgency'] ?? 'normal';
                                $tag  = $url ? 'a' : 'div';
                            @endphp
                            <{{ $tag }}
                                @if($url) href="{{ $url }}" @endif
                                class="task-popup__item task-popup__item--{{ $urg }}">
                                <span class="task-popup__item-icon" aria-hidden="true">
                                    <i class="fas {{ $icon }}"></i>
                                </span>
                                <span class="task-popup__item-body">
                                    <span class="task-popup__item-cat">{{ $cat }}</span>
                                    <span class="task-popup__item-text">{{ $text }}</span>
                                </span>
                                @if($url)
                                    <i class="fas fa-chevron-right task-popup__item-chev" aria-hidden="true"></i>
                                @endif
                            </{{ $tag }}>
                        @endforeach
                    </section>
                @endif
            @endforeach
        @endif
    </div>
</div>
