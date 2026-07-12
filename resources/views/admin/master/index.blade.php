@extends('layouts.admin')

@section('title', 'マスタ設定管理')

@section('content')
    @php
        $editingRecord = $selectedCatalog['editing_record'] ?? null;
        $sortLabels = [
            'created_desc' => '登録日順',
            'name_asc' => 'あいうえお順',
        ];
        $hasDirectory = $selectedCatalog
            ? collect($selectedCatalog['fields'])->contains(fn ($field) => $field['input'] === 'directory')
            : false;
        $hasActive = $selectedCatalog
            ? (!empty($selectedCatalog['uses_del_flg']) || !empty($selectedCatalog['uses_is_active']))
            : false;
        $hasSortOrder = $selectedCatalog ? !empty($selectedCatalog['uses_sort_order']) : false;
    @endphp
    <div class="admin-page">
        @include('admin.parts.page-title', [
            'eyebrow' => 'MASTER',
            'title' => 'マスタコントロール',
            'info' => '
                <p>管理したいマスタを選択して、項目の<strong>追加・編集・削除・表示順の変更</strong>を行います。</p>
                <p>表示順の数値が小さいほど画面上で先頭に並びます。</p>
            ',
        ])

        @if (session('status'))
            <div class="admin-alert admin-alert-success">{{ session('status') }}</div>
        @endif

        @if (!empty($error))
            <div class="admin-alert admin-alert-error">{{ $error }}</div>
        @endif

        {{-- マスタ選択：カードグリッドは縦を圧迫しスマホで扱いにくいため、
             大きめのプルダウン1本に統一（選択で即遷移） --}}
        <section class="admin-card admin-card-wide">
            <div class="master-picker">
                <label class="master-picker__label" for="master-catalog-select">
                    <i class="fas fa-database" aria-hidden="true"></i> 管理するマスタ
                </label>
                <select
                    id="master-catalog-select"
                    class="master-picker__select"
                    onchange="if(this.value){window.location.href=this.value;}"
                >
                    <option value="">マスタを選択してください</option>
                    @foreach ($catalogs as $catalog)
                        <option
                            value="{{ route('admin.masters.index', ['catalog' => $catalog['key']]) }}"
                            @selected(($selectedCatalog['key'] ?? null) === $catalog['key'])
                        >
                            {{ $catalog['title'] }}（{{ number_format($catalog['count']) }}件）
                        </option>
                    @endforeach
                </select>
                @if ($selectedCatalog)
                    <p class="master-picker__desc">{{ $selectedCatalog['description'] }}</p>
                @endif
            </div>
        </section>

        @if ($selectedCatalog)
            <section class="admin-card admin-card-wide">
                <div class="admin-card-head">
                    <div>
                        <h2>{{ $selectedCatalog['title'] }}</h2>
                        <p>{{ $selectedCatalog['description'] }}</p>
                    </div>
                </div>

                <div class="admin-master-layout">
                    {{-- 新規追加・編集フォーム --}}
                    <div class="admin-master-forms">
                        <form method="POST" action="{{ route('admin.masters.catalogs.store', $selectedCatalog['key']) }}" class="admin-master-form">
                            @csrf
                            <input type="hidden" name="current_sort" value="{{ $selectedSort }}">
                            <div class="admin-master-form-header">
                                <h3>新規追加</h3>
                                <p>{{ $selectedCatalog['title'] }}に新しい項目を追加します。</p>
                            </div>
                            <div class="admin-master-form-grid">
                                @foreach ($selectedCatalog['fields'] as $field)
                                    <label class="admin-master-field">
                                        <span>{{ $field['label'] }}</span>
                                        <input
                                            type="text"
                                            name="{{ $field['input'] }}"
                                            value="{{ old($field['input']) }}"
                                            placeholder="{{ $field['placeholder'] ?? '' }}"
                                        >
                                    </label>
                                @endforeach
                                <button type="submit" class="admin-master-submit">{{ $selectedCatalog['title'] }}を追加</button>
                            </div>
                        </form>

                        @if ($editingRecord)
                            <form method="POST" action="{{ route('admin.masters.catalogs.update', [$selectedCatalog['key'], $editingRecord->id]) }}" class="admin-master-form admin-master-form-edit">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="current_sort" value="{{ $selectedSort }}">
                                <div class="admin-master-form-header">
                                    <div>
                                        <h3>選択中の項目を編集（ID: {{ $editingRecord->id }}）</h3>
                                        <p>必要な内容を変更して保存してください。</p>
                                    </div>
                                    <a href="{{ route('admin.masters.index', ['catalog' => $selectedCatalog['key'], 'sort' => $selectedSort]) }}" class="admin-master-cancel">編集をやめる</a>
                                </div>
                                <div class="admin-master-form-grid">
                                    @foreach ($selectedCatalog['fields'] as $field)
                                        <label class="admin-master-field">
                                            <span>{{ $field['label'] }}</span>
                                            <input
                                                type="text"
                                                name="{{ $field['input'] }}"
                                                value="{{ old($field['input'], $editingRecord->{$field['column']} ?? '') }}"
                                                placeholder="{{ $field['placeholder'] ?? '' }}"
                                            >
                                        </label>
                                    @endforeach
                                    <button type="submit" class="admin-master-submit">変更を保存</button>
                                </div>
                            </form>
                        @endif
                    </div>

                    {{-- 一覧 --}}
                    <div class="admin-master-records">
                        <div class="admin-records-head">
                            <div>
                                <div class="admin-records-title">登録済み一覧</div>
                                <div class="admin-records-copy">表示順は数値が小さいほど上に並びます。空欄の場合は登録日順で表示されます。</div>
                            </div>
                            <div class="admin-records-toolbar">
                                <label class="admin-search-box">
                                    <i class="fas fa-magnifying-glass"></i>
                                    <input
                                        type="text"
                                        id="master-record-search"
                                        placeholder="{{ $selectedCatalog['title'] }}を検索"
                                        autocomplete="off"
                                    >
                                </label>
                                <div class="admin-sort-switch" role="group" aria-label="一覧の並び順">
                                    @foreach ($sortLabels as $sortValue => $sortLabel)
                                        <a
                                            href="{{ route('admin.masters.index', ['catalog' => $selectedCatalog['key'], 'sort' => $sortValue]) }}"
                                            class="admin-sort-link {{ $selectedSort === $sortValue ? 'is-active' : '' }}"
                                        >
                                            {{ $sortLabel }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        {{-- テーブル → レコードカードのリスト（スマホでも横スクロールなしで全操作可能） --}}
                        <ul class="master-list" id="master-records-body">
                            @forelse ($selectedCatalog['records'] as $item)
                                <li class="master-record-row master-item {{ $editingRecord && $editingRecord->id === $item->id ? 'is-editing' : '' }}"
                                    data-search="{{ strtolower(trim(($item->id ?? '') . ' ' . $item->name . ' ' . ($item->directory ?? '') . ' ' . (($item->is_active ?? 1) ? '有効' : '無効') . ' ' . ($item->created_at ? \Illuminate\Support\Carbon::parse($item->created_at)->format('Y-m-d') : ''))) }}">

                                    {{-- 1段目：名称（タップでその場編集） + 操作 --}}
                                    <div class="master-item__row">
                                        <div class="master-item__name" data-name-cell>
                                            <button type="button"
                                                    class="master-inline-name"
                                                    data-inline-edit-toggle
                                                    title="タップして名称を編集">
                                                <span class="master-inline-name__text">{{ $item->name }}</span>
                                                <i class="fas fa-pen master-inline-name__icon" aria-hidden="true"></i>
                                            </button>
                                            {{-- 編集モード：name 以外のフィールドは hidden で現値を送る（バリデーション対応） --}}
                                            <form method="POST"
                                                  action="{{ route('admin.masters.catalogs.update', [$selectedCatalog['key'], $item->id]) }}"
                                                  class="master-inline-form"
                                                  hidden>
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="current_sort" value="{{ $selectedSort }}">
                                                @foreach ($selectedCatalog['fields'] as $fieldIdx => $field)
                                                    @if ($fieldIdx === 0)
                                                        <input type="text"
                                                               name="{{ $field['input'] }}"
                                                               value="{{ $item->{$field['column']} ?? $item->name }}"
                                                               class="master-inline-form__input"
                                                               aria-label="{{ $field['label'] }}"
                                                               required>
                                                    @else
                                                        <input type="hidden"
                                                               name="{{ $field['input'] }}"
                                                               value="{{ $item->{$field['column']} ?? '' }}">
                                                    @endif
                                                @endforeach
                                                <button type="submit" class="master-inline-form__save" title="保存">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="master-inline-form__cancel" data-inline-edit-cancel title="キャンセル">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        </div>
                                        <div class="master-item__actions">
                                            <a href="{{ route('admin.masters.index', ['catalog' => $selectedCatalog['key'], 'edit' => $item->id, 'sort' => $selectedSort]) }}"
                                               class="admin-row-icon-btn {{ $editingRecord && $editingRecord->id === $item->id ? 'is-active' : '' }}"
                                               title="詳細編集">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            <form method="POST"
                                                  action="{{ route('admin.masters.catalogs.destroy', [$selectedCatalog['key'], $item->id]) }}"
                                                  onsubmit="return confirm('「{{ addslashes($item->name) }}」を削除しますか？\nこの操作は取り消せません。');"
                                                  style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="current_sort" value="{{ $selectedSort }}">
                                                <button type="submit" class="admin-row-icon-btn admin-row-icon-delete" title="削除">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    {{-- 2段目：メタ情報（ID / 状態 / 登録日 / ディレクトリ）+ 表示順 --}}
                                    <div class="master-item__meta">
                                        <span class="master-item__chip master-item__chip--id">ID {{ $item->id }}</span>
                                        @if ($hasActive)
                                            <span class="admin-status-badge {{ ($item->is_active ?? 1) ? 'is-success' : 'is-inactive' }}">
                                                {{ ($item->is_active ?? 1) ? '有効' : '無効' }}
                                            </span>
                                        @endif
                                        @if ($hasDirectory && !empty($item->directory))
                                            <span class="master-item__chip"><i class="fas fa-folder"></i>{{ $item->directory }}</span>
                                        @endif
                                        <span class="master-item__chip">{{ $item->created_at ? \Illuminate\Support\Carbon::parse($item->created_at)->format('Y-m-d') : '-' }}</span>
                                        @if ($hasSortOrder)
                                            <form method="POST" action="{{ route('admin.masters.catalogs.sort-order', [$selectedCatalog['key'], $item->id]) }}" class="sort-order-form">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="current_sort" value="{{ $selectedSort }}">
                                                <span class="sort-order-label">表示順</span>
                                                <input type="number" name="sort_order" value="{{ $item->sort_order ?? 0 }}" min="0" max="99999" class="sort-order-input" aria-label="表示順" inputmode="numeric">
                                                <button type="submit" class="sort-order-save" title="表示順を保存">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </li>
                            @empty
                                <li id="master-records-empty" class="master-list__empty">まだ登録されていません。</li>
                            @endforelse
                            @if ($selectedCatalog['records']->isNotEmpty())
                                <li id="master-records-no-result" class="master-list__empty" hidden>検索条件に一致する項目がありません。</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </section>
        @endif
    </div>

@endsection

@push('admin-scripts')
<script>
    (function () {
        var recordSearchInput = document.getElementById('master-record-search');
        var rows = document.querySelectorAll('.master-record-row');
        var noResultRow = document.getElementById('master-records-no-result');

        function normalize(value) {
            return (value || '').toLowerCase().replace(/\s+/g, ' ').trim();
        }

        if (recordSearchInput && rows.length) {
            function applyRecordSearch() {
                var keyword = normalize(recordSearchInput.value);
                var visibleCount = 0;
                rows.forEach(function (row) {
                    var haystack = normalize(row.getAttribute('data-search'));
                    var matched = keyword === '' || haystack.indexOf(keyword) !== -1;
                    row.classList.toggle('is-hidden', !matched);
                    if (matched) visibleCount += 1;
                });
                if (noResultRow) noResultRow.hidden = visibleCount !== 0;
            }
            recordSearchInput.addEventListener('input', applyRecordSearch);
        }

        // ---- インライン名称編集：表示テキスト ⇔ フォームの切替 ----
        document.querySelectorAll('[data-inline-edit-toggle]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var cell = btn.closest('[data-name-cell]');
                if (!cell) return;
                var form = cell.querySelector('.master-inline-form');
                if (!form) return;
                // 他の編集中セルを閉じる
                document.querySelectorAll('.master-inline-form:not([hidden])').forEach(function (f) {
                    f.hidden = true;
                    var b = f.closest('[data-name-cell]').querySelector('[data-inline-edit-toggle]');
                    if (b) b.hidden = false;
                });
                btn.hidden = true;
                form.hidden = false;
                var input = form.querySelector('.master-inline-form__input');
                if (input) { input.focus(); input.select(); }
            });
        });
        document.querySelectorAll('[data-inline-edit-cancel]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var form = btn.closest('.master-inline-form');
                if (!form) return;
                form.hidden = true;
                var toggle = form.closest('[data-name-cell]').querySelector('[data-inline-edit-toggle]');
                if (toggle) toggle.hidden = false;
            });
        });
        // Escape でキャンセル
        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            var open = document.querySelector('.master-inline-form:not([hidden])');
            if (!open) return;
            open.hidden = true;
            var toggle = open.closest('[data-name-cell]').querySelector('[data-inline-edit-toggle]');
            if (toggle) toggle.hidden = false;
        });

        // ---- 編集フォームへ自動スクロール（?edit= でリロードされた直後） ----
        var editForm = document.querySelector('.admin-master-form-edit');
        if (editForm) {
            window.setTimeout(function () {
                editForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
                var firstInput = editForm.querySelector('input[type="text"]');
                if (firstInput) firstInput.focus();
            }, 120);
        }
    })();
</script>
@endpush

@push('admin-styles')
<style>
    /* ---- インライン名称編集 ---- */
    .master-inline-name {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: transparent;
        border: 0;
        padding: 4px 6px;
        margin: -4px -6px;
        border-radius: 6px;
        color: inherit;
        font: inherit;
        cursor: pointer;
        text-align: left;
        transition: background 0.15s ease;
    }
    .master-inline-name:hover {
        background: rgba(168, 85, 247, 0.10);
    }
    .master-inline-name:hover .master-inline-name__icon { opacity: 1; }
    .master-inline-name__icon {
        font-size: 0.68rem;
        color: #a78bfa;
        opacity: 0;
        transition: opacity 0.15s ease;
    }
    .master-inline-form {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .master-inline-form[hidden] { display: none; }
    .master-inline-form__input {
        min-width: 160px;
        padding: 6px 10px;
        border-radius: 8px;
        border: 1px solid rgba(168, 85, 247, 0.5);
        background: rgba(20, 14, 24, 0.9);
        color: #fff;
        font-size: 0.9rem;
    }
    .master-inline-form__input:focus {
        outline: 2px solid rgba(196, 181, 253, 0.6);
        outline-offset: 1px;
    }
    .master-inline-form__save,
    .master-inline-form__cancel {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        border: 1px solid transparent;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 0.78rem;
        transition: filter 0.15s ease;
    }
    .master-inline-form__save {
        background: linear-gradient(135deg, #6ee7b7, #34d399);
        color: #052e1c;
    }
    .master-inline-form__save:hover { filter: brightness(1.08); }
    .master-inline-form__cancel {
        background: transparent;
        border-color: rgba(255, 255, 255, 0.18);
        color: #a1a1aa;
    }
    .master-inline-form__cancel:hover { color: #fff; border-color: rgba(255, 255, 255, 0.4); }

    /* ---- マスタ選択プルダウン（スマホでも押しやすい大型） ---- */
    .master-picker { display: flex; flex-direction: column; gap: 8px; }
    .master-picker__label {
        font-size: 0.8rem;
        font-weight: 800;
        color: #c4b5fd;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .master-picker__select {
        width: 100%;
        min-height: 48px;
        font-size: 16px;
        border-radius: 12px;
        padding: 12px 40px 12px 14px;
    }
    .master-picker__desc {
        margin: 0;
        font-size: 0.78rem;
        line-height: 1.7;
        color: #a1a1aa;
    }

    /* ---- レコードカードリスト（テーブル廃止・スマホ横スクロールなし） ---- */
    .master-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 8px; }
    .master-item {
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.02);
        padding: 10px 12px;
    }
    .master-item.is-editing {
        border-color: rgba(168, 85, 247, 0.55);
        background: rgba(168, 85, 247, 0.06);
    }
    .master-item.is-hidden { display: none; }
    .master-item__row {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .master-item__name { flex: 1; min-width: 0; }
    .master-item__name .master-inline-name__text {
        font-size: 0.95rem;
        font-weight: 700;
        color: #f5f5f5;
        word-break: break-all;
    }
    .master-item__actions {
        flex: 0 0 auto;
        display: flex;
        gap: 6px;
    }
    /* タップしやすいサイズに拡大 */
    .master-item__actions .admin-row-icon-btn {
        width: 38px;
        height: 38px;
    }
    .master-item__meta {
        margin-top: 8px;
        padding-top: 8px;
        border-top: 1px solid rgba(255, 255, 255, 0.06);
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        font-size: 0.72rem;
        color: #a1a1aa;
    }
    .master-item__chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 9px;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, 0.10);
        background: rgba(255, 255, 255, 0.03);
        font-size: 0.7rem;
        white-space: nowrap;
    }
    .master-item__chip--id { font-variant-numeric: tabular-nums; color: #c4b5fd; }
    .master-item__chip i { font-size: 0.62rem; opacity: 0.7; }
    .master-list__empty {
        padding: 28px 12px;
        text-align: center;
        color: #a1a1aa;
        font-size: 0.85rem;
        border: 1px dashed rgba(255, 255, 255, 0.12);
        border-radius: 12px;
    }

    /* 表示順フォームはメタ行の右端へ */
    .sort-order-form {
        margin-left: auto;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .sort-order-label { font-size: 0.68rem; color: #a1a1aa; }
    .sort-order-input { width: 72px; min-height: 36px !important; text-align: right; }
    .sort-order-save {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        border: 1px solid rgba(110, 231, 183, 0.4);
        background: rgba(110, 231, 183, 0.12);
        color: #6ee7b7;
        cursor: pointer;
    }

    /* ---- 検索・並び替えツールバー：スマホでは縦積み ---- */
    @media (max-width: 640px) {
        .admin-records-toolbar { flex-direction: column; align-items: stretch; gap: 8px; }
        .admin-search-box { width: 100%; }
        .admin-sort-switch { display: flex; }
        .admin-sort-switch .admin-sort-link { flex: 1; text-align: center; padding: 10px 8px; }
        .admin-master-layout { display: flex; flex-direction: column; }
        .sort-order-form { margin-left: 0; width: 100%; justify-content: flex-end; }
    }
</style>
@endpush
