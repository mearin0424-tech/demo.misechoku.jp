@extends('layouts.admin')

@section('title', '通知・タスク仕様')

@section('content')
<div class="admin-page">
    @include('admin.parts.page-title', ['eyebrow' => 'NOTIFICATIONS & TASKS', 'title' => '通知・タスク仕様'])

    <p class="admin-description">
        運営から送る <strong>通知</strong>、<strong>リマインダー通知</strong>、および運営／キャスト／店舗が扱う <strong>未済タスク</strong> の仕様を確認・変更できます。<br>
        トリガー条件・タスクの解消条件は仕様として固定（変更不可）で、表示文言・ON/OFF・リマインドのタイミングのみ変更できます。
    </p>

    @if(session('status'))
        <div class="admin-alert admin-alert-success">{{ session('status') }}</div>
    @endif

    {{-- タブ切り替え --}}
    <div class="spec-tabs" role="tablist">
        <a href="{{ route('admin.notification-spec.index', ['tab' => 'notifications']) }}"
           class="spec-tab {{ $tab === 'notifications' ? 'is-active' : '' }}" role="tab"
           aria-selected="{{ $tab === 'notifications' ? 'true' : 'false' }}">
            <i class="fas fa-bell"></i> 通知
        </a>
        <a href="{{ route('admin.notification-spec.index', ['tab' => 'reminders']) }}"
           class="spec-tab {{ $tab === 'reminders' ? 'is-active' : '' }}" role="tab"
           aria-selected="{{ $tab === 'reminders' ? 'true' : 'false' }}">
            <i class="fas fa-clock-rotate-left"></i> リマインダー通知
        </a>
        <a href="{{ route('admin.notification-spec.index', ['tab' => 'tasks']) }}"
           class="spec-tab {{ $tab === 'tasks' ? 'is-active' : '' }}" role="tab"
           aria-selected="{{ $tab === 'tasks' ? 'true' : 'false' }}">
            <i class="fas fa-list-check"></i> 未済タスク
        </a>
    </div>

    {{-- 通知タブ --}}
    @if($tab === 'notifications')
        <p class="admin-note u-mb-12">
            トリガー条件は仕様として固定です。<strong>ON/OFF</strong>、<strong>タイトル</strong>、<strong>本文</strong> のみ編集できます。本文中の <code>{token}</code> は送信時に動的な値（店舗名・キャスト名・金額など）に置換されます。
        </p>
        @foreach($notificationsByGroup as $groupLabel => $items)
            <section class="admin-panel spec-panel">
                <h2 class="admin-panel-title">{{ $groupLabel }}</h2>
                <div class="spec-card-grid">
                    @foreach($items as $item)
                        <article class="spec-card" id="{{ $item['key'] }}">
                            <header class="spec-card__head">
                                <div>
                                    <h3 class="spec-card__title">{{ $item['label'] }}</h3>
                                    <p class="spec-card__condition">
                                        <span class="spec-card__condition-label">トリガー条件</span>
                                        {{ $item['condition'] }}
                                    </p>
                                </div>
                                <code class="spec-card__key">{{ $item['key'] }}</code>
                            </header>
                            <form method="POST" action="{{ route('admin.notification-spec.notifications.update', $item['key']) }}" class="spec-card__form">
                                @csrf @method('PUT')
                                <label class="spec-toggle">
                                    <input type="checkbox" name="enabled" value="1" @if($item['current_enabled']) checked @endif>
                                    <span class="spec-toggle__track" aria-hidden="true">
                                        <span class="spec-toggle__thumb"></span>
                                    </span>
                                    <span class="spec-toggle__label">この通知を送信する</span>
                                </label>

                                <label class="spec-field">
                                    <span class="spec-field__label">通知タイトル</span>
                                    <input type="text" name="title" value="{{ $item['current_title'] }}" maxlength="255">
                                </label>

                                <label class="spec-field">
                                    <span class="spec-field__label">本文</span>
                                    <textarea name="body" rows="3" maxlength="5000">{{ $item['current_body'] }}</textarea>
                                </label>

                                <div class="spec-card__actions">
                                    <button type="submit" class="btn-action manage">
                                        <i class="fas fa-floppy-disk"></i> 保存
                                    </button>
                                </div>
                            </form>
                        </article>
                    @endforeach
                </div>
            </section>
        @endforeach
    @endif

    {{-- リマインダータブ --}}
    @if($tab === 'reminders')
        <p class="admin-note u-mb-12">
            トリガー条件は仕様として固定です。<strong>発火タイミング（数値）</strong>と <strong>本文</strong> を変更できます。
        </p>
        @foreach($remindersByGroup as $groupLabel => $items)
            <section class="admin-panel spec-panel">
                <h2 class="admin-panel-title">{{ $groupLabel }}</h2>
                <div class="spec-card-grid">
                    @foreach($items as $item)
                        <article class="spec-card" id="{{ $item['key'] }}">
                            <header class="spec-card__head">
                                <div>
                                    <h3 class="spec-card__title">{{ $item['label'] }}</h3>
                                    <p class="spec-card__condition">
                                        <span class="spec-card__condition-label">トリガー条件</span>
                                        {{ $item['condition'] }}
                                    </p>
                                </div>
                                <code class="spec-card__key">{{ $item['key'] }}</code>
                            </header>
                            <form method="POST" action="{{ route('admin.notification-spec.reminders.update', $item['key']) }}" class="spec-card__form">
                                @csrf @method('PUT')
                                <label class="spec-field spec-field--inline">
                                    <span class="spec-field__label">発火タイミング</span>
                                    <span class="spec-field__inline">
                                        <input type="number" name="offset" value="{{ $item['current_offset'] }}" min="0" max="9999" class="spec-input-num" required>
                                        <span class="spec-field__suffix">{{ $unitLabel($item['unit']) }}</span>
                                        <small class="spec-field__hint">（デフォルト：{{ $item['default_offset'] }}{{ $unitLabel($item['unit']) }}）</small>
                                    </span>
                                </label>

                                <label class="spec-field">
                                    <span class="spec-field__label">通知タイトル</span>
                                    <input type="text" name="title" value="{{ $item['current_title'] }}" maxlength="255">
                                </label>

                                <label class="spec-field">
                                    <span class="spec-field__label">本文</span>
                                    <textarea name="body" rows="3" maxlength="5000">{{ $item['current_body'] }}</textarea>
                                </label>

                                <div class="spec-card__actions">
                                    <button type="submit" class="btn-action manage">
                                        <i class="fas fa-floppy-disk"></i> 保存
                                    </button>
                                </div>
                            </form>
                        </article>
                    @endforeach
                </div>
            </section>
        @endforeach
    @endif

    {{-- タスクタブ --}}
    @if($tab === 'tasks')
        <p class="admin-note u-mb-12">
            タスクが <strong>発生する条件</strong> と <strong>解消する条件</strong> は仕様として固定（変更不可）です。各タスクの <strong>表示タイトル・説明文</strong> のみ変更できます。
        </p>
        @foreach($tasksByActor as $actorLabel => $items)
            <section class="admin-panel spec-panel">
                <h2 class="admin-panel-title">
                    <i class="fas fa-user-cog"></i> {{ $actorLabel }} 向けタスク
                </h2>
                <div class="spec-card-grid">
                    @foreach($items as $item)
                        <article class="spec-card spec-card--task" id="{{ $item['key'] }}">
                            <header class="spec-card__head">
                                <div>
                                    <h3 class="spec-card__title">{{ $item['label'] }}</h3>
                                </div>
                                <code class="spec-card__key">{{ $item['key'] }}</code>
                            </header>

                            <div class="spec-readonly">
                                <div class="spec-readonly__row">
                                    <span class="spec-readonly__label"><i class="fas fa-circle-exclamation"></i> 発生条件</span>
                                    <span class="spec-readonly__value">{{ $item['condition'] }}</span>
                                </div>
                                <div class="spec-readonly__row">
                                    <span class="spec-readonly__label"><i class="fas fa-circle-check"></i> 解消条件</span>
                                    <span class="spec-readonly__value">{{ $item['resolution'] }}</span>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('admin.notification-spec.tasks.update', $item['key']) }}" class="spec-card__form">
                                @csrf @method('PUT')
                                <label class="spec-field">
                                    <span class="spec-field__label">表示タイトル</span>
                                    <input type="text" name="title" value="{{ $item['current_title'] }}" maxlength="255">
                                </label>

                                <label class="spec-field">
                                    <span class="spec-field__label">説明文</span>
                                    <textarea name="body" rows="3" maxlength="5000">{{ $item['current_body'] }}</textarea>
                                </label>

                                <div class="spec-card__actions">
                                    <button type="submit" class="btn-action manage">
                                        <i class="fas fa-floppy-disk"></i> 保存
                                    </button>
                                </div>
                            </form>
                        </article>
                    @endforeach
                </div>
            </section>
        @endforeach
    @endif
</div>
@endsection
