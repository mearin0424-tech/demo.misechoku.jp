@extends('layouts.admin')

@section('title', 'マスタ設定管理')

@section('content')
    @php
        $catalogGroups = $catalogs->groupBy('group');
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
        $columnCount = 3 + ($hasDirectory ? 1 : 0) + ($hasActive ? 1 : 0);
    @endphp
    <div class="admin-page">
        <h1 class="admin-title">マスタコントロール</h1>
        <p class="admin-description">
            追加・編集したいマスタを選んで、対象ごとに管理する画面です。<br>
            すべてのマスタを同じ操作レイアウトで扱い、検索・並び替え・追加・編集を1つの導線で行えるようにしています。
        </p>

        @if (session('status'))
            <div class="admin-alert admin-alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if (!empty($error))
            <div class="admin-alert admin-alert-error">
                {{ $error }}
            </div>
        @endif

        <section class="admin-card admin-card-wide">
            <div class="admin-card-head">
                <div>
                    <h2>マスタを選択</h2>
                    <p>編集したいマスタを「カテゴリ」と「マスタ名」のプルダウンから選択してください。</p>
                </div>
            </div>

            <div class="admin-master-select-row">
                {{-- カテゴリ選択 --}}
                <label class="admin-master-select-label">
                    <span>カテゴリ</span>
                    <select id="master-group-select">
                        <option value="" @selected(empty($selectedCatalog))>すべて</option>
                        @foreach ($catalogGroups as $group => $items)
                            <option value="{{ $group }}" @selected(($selectedCatalog['group'] ?? '') === $group)>{{ $group }}</option>
                        @endforeach
                    </select>
                </label>

                {{-- マスタ選択 --}}
                <label class="admin-master-select-label">
                    <span>マスタ</span>
                    @php
                        $allCatalogs = $catalogGroups->flatten(1);
                    @endphp
                    <select
                        id="master-catalog-select"
                        onchange="if(this.value){window.location.href=this.value;}"
                    >
                        <option value="">マスタを選択してください</option>
                        @foreach ($allCatalogs as $catalog)
                            @php
                                $group = $catalog['group'] ?? '';
                            @endphp
                            <option
                                value="{{ route('admin.masters.index', ['catalog' => $catalog['key']]) }}"
                                data-group="{{ $group }}"
                                @selected(($selectedCatalog['key'] ?? null) === $catalog['key'])
                            >
                                {{ $catalog['title'] }}
                            </option>
                        @endforeach
                    </select>
                </label>
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
                                        <h3>選択中の項目を編集</h3>
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

                    <div class="admin-master-records">
                        <div class="admin-records-head">
                            <div>
                                <div class="admin-records-title">登録済み一覧</div>
                                <div class="admin-records-copy">IDは表示せず、運用で見たい情報だけを整理しています。</div>
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
                        <div class="table-wrapper">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>名称</th>
                                        @if ($hasDirectory)
                                            <th>ディレクトリ</th>
                                        @endif
                                        @if ($hasActive)
                                            <th>状態</th>
                                        @endif
                                        <th>登録日</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody id="master-records-body">
                                    @forelse ($selectedCatalog['records'] as $item)
                                        <tr class="master-record-row" data-search="{{ strtolower(trim($item->name . ' ' . ($item->directory ?? '') . ' ' . (($item->is_active ?? 1) ? '有効' : '無効') . ' ' . ($item->created_at ? \Illuminate\Support\Carbon::parse($item->created_at)->format('Y-m-d') : ''))) }}">
                                            <td data-label="名称" class="cell-main">{{ $item->name }}</td>
                                            <td colspan="{{ $columnCount - 1 }}" class="cell-detail">
                                                <div class="detail-row">
                                                    @if ($hasDirectory)
                                                        <div class="detail-item">
                                                            <span class="detail-label">ディレクトリ</span>
                                                            <span class="detail-value">{{ $item->directory ?? '-' }}</span>
                                                        </div>
                                                    @endif
                                                    @if ($hasActive)
                                                        <div class="detail-item">
                                                            <span class="detail-label">状態</span>
                                                            <span class="detail-value">{{ ($item->is_active ?? 1) ? '有効' : '無効' }}</span>
                                                        </div>
                                                    @endif
                                                    <div class="detail-item">
                                                        <span class="detail-label">登録日</span>
                                                        <span class="detail-value">{{ $item->created_at ? \Illuminate\Support\Carbon::parse($item->created_at)->format('Y-m-d') : '-' }}</span>
                                                    </div>
                                                    <div class="detail-item">
                                                        <span class="detail-label">操作</span>
                                                        <span class="detail-value">
                                                            <a
                                                                href="{{ route('admin.masters.index', ['catalog' => $selectedCatalog['key'], 'edit' => $item->id, 'sort' => $selectedSort]) }}"
                                                                class="admin-row-icon-btn {{ $editingRecord && $editingRecord->id === $item->id ? 'is-active' : '' }}"
                                                                title="編集"
                                                            >
                                                                <i class="fas fa-pen"></i>
                                                            </a>
                                                            <form
                                                                method="POST"
                                                                action="{{ route('admin.masters.catalogs.destroy', [$selectedCatalog['key'], $item->id]) }}"
                                                                onsubmit="return confirm('この項目を削除しますか？');"
                                                                style="display:inline-block;"
                                                            >
                                                                @csrf
                                                                @method('DELETE')
                                                                <button
                                                                    type="submit"
                                                                    class="admin-row-icon-btn admin-row-icon-delete"
                                                                    title="削除"
                                                                >
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            </form>
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr id="master-records-empty">
                                            <td colspan="{{ $columnCount }}" class="text-center">まだ登録されていません。</td>
                                        </tr>
                                    @endforelse
                                    @if ($selectedCatalog['records']->isNotEmpty())
                                        <tr id="master-records-no-result" hidden>
                                            <td colspan="{{ $columnCount }}" class="text-center">検索条件に一致する項目がありません。</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        @endif
    </div>

    <style>
        .admin-card {
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.03);
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.16);
        }

        .admin-grid {
            display: grid;
            gap: 16px;
        }

        .admin-card {
            padding: 20px;
        }

        .admin-card-wide {
            width: 100%;
        }

        .admin-card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        .admin-card-head h2 {
            margin: 0 0 6px;
        }

        .admin-card-head p {
            margin: 0;
            color: rgba(255, 255, 255, 0.68);
            line-height: 1.7;
        }

        .admin-card-count {
            display: inline-flex;
            align-items: center;
            min-height: 36px;
            padding: 0 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.06);
            color: rgba(255, 255, 255, 0.78);
            font-size: 0.8rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .admin-master-link {
            color: #f6d98b;
            text-decoration: none;
            white-space: nowrap;
        }

        .admin-catalog-groups {
            display: grid;
            gap: 18px;
        }

        .admin-master-select-label {
            display: flex;
            flex-direction: column;
            gap: 6px;
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.76);
        }

        .admin-master-select-row {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: flex-end;
        }

        .admin-master-select-label select {
            min-width: 260px;
            max-width: 100%;
            padding: 8px 10px;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.16);
            background: rgba(255, 255, 255, 0.02);
            color: #fff;
            font-size: 0.8rem;
            outline: none;
        }

        .admin-master-select-label select:focus {
            border-color: rgba(230, 208, 128, 0.4);
            box-shadow: 0 0 0 2px rgba(230, 208, 128, 0.16);
        }


        .admin-alert {
            margin-bottom: 18px;
            padding: 12px 14px;
            border-radius: 14px;
        }

        .admin-alert-error {
            background: rgba(127, 29, 29, 0.3);
            border: 1px solid rgba(248, 113, 113, 0.4);
            color: #fee2e2;
        }

        .admin-alert-success {
            background: rgba(20, 83, 45, 0.3);
            border: 1px solid rgba(74, 222, 128, 0.32);
            color: #dcfce7;
        }

        .admin-master-form {
            padding: 16px;
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.025);
        }

        .admin-master-layout {
            display: grid;
            grid-template-columns: minmax(300px, 420px) minmax(0, 1fr);
            gap: 18px;
        }

        .admin-master-forms {
            display: grid;
            gap: 16px;
            align-content: start;
        }

        .admin-master-form-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        .admin-master-form-header h3 {
            margin: 0 0 4px;
            font-size: 0.95rem;
        }

        .admin-master-form-header p {
            margin: 0;
            color: rgba(255, 255, 255, 0.64);
            font-size: 0.8rem;
            line-height: 1.6;
        }

        .admin-master-cancel {
            color: #f6d98b;
            text-decoration: none;
            white-space: nowrap;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .admin-master-form-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: 1fr;
            align-items: end;
        }

        .admin-master-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .admin-master-field span {
            font-size: 0.83rem;
            color: rgba(255, 255, 255, 0.76);
        }

        .admin-master-field input {
            width: 100%;
            min-height: 44px;
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.04);
            color: #fff;
        }

        .admin-master-submit {
            min-height: 44px;
            padding: 0 16px;
            border: none;
            border-radius: 999px;
            background: linear-gradient(135deg, #f4df9c, #c99722);
            color: #2a1208;
            font-weight: 700;
            cursor: pointer;
            white-space: nowrap;
        }

        .admin-master-records {
            min-width: 0;
        }

        .admin-records-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
        }

        .admin-records-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.86);
        }

        .admin-records-copy {
            margin-top: 4px;
            font-size: 0.76rem;
            color: rgba(255, 255, 255, 0.58);
        }

        .admin-records-toolbar {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
        }

        .admin-search-box {
            position: relative;
            display: inline-flex;
            align-items: center;
            min-width: 240px;
        }

        .admin-search-box i {
            position: absolute;
            left: 12px;
            color: rgba(255, 255, 255, 0.42);
            font-size: 0.78rem;
        }

        .admin-search-box input {
            width: 100%;
            min-height: 38px;
            padding: 0 12px 0 34px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.04);
            color: #fff;
            font-size: 0.8rem;
            outline: none;
        }

        .admin-search-box input:focus {
            border-color: rgba(230, 208, 128, 0.32);
            box-shadow: 0 0 0 3px rgba(230, 208, 128, 0.06);
        }

        .admin-sort-switch {
            display: inline-flex;
            gap: 4px;
            padding: 4px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .admin-sort-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 32px;
            padding: 0 12px;
            border-radius: 999px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 0.76rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .admin-sort-link.is-active {
            background: #f3f4f6;
            color: #111827;
        }

        .admin-row-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .admin-row-icon-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 10px;
            border: 1px solid rgba(230, 208, 128, 0.22);
            background: rgba(230, 208, 128, 0.08);
            color: #f6d98b;
            text-decoration: none;
            font-size: 0.72rem;
            cursor: pointer;
        }

        .admin-row-icon-btn.is-active {
            background: linear-gradient(135deg, #f4df9c, #c99722);
            color: #2a1208;
            border-color: transparent;
        }

        .admin-row-icon-delete {
            border-color: rgba(248, 113, 113, 0.4);
            background: rgba(127, 29, 29, 0.4);
            color: #fecaca;
        }

        .detail-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 6px 12px;
        }

        .detail-item {
            font-size: 0.78rem;
        }

        .detail-label {
            display: inline-block;
            margin-right: 6px;
            color: rgba(255, 255, 255, 0.6);
        }

        .detail-value {
            display: inline-block;
        }

        .master-record-row.is-hidden {
            display: none;
        }

        @media (max-width: 960px) {
            .admin-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .admin-master-layout {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .admin-summary-grid {
                grid-template-columns: 1fr;
            }

            .admin-card-head {
                flex-direction: column;
                align-items: flex-start;
            }

            .admin-master-form-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .admin-records-head {
                flex-direction: column;
                align-items: stretch;
            }

            .admin-records-toolbar {
                justify-content: stretch;
            }

            .admin-search-box {
                width: 100%;
                min-width: 0;
            }

            .admin-sort-switch {
                width: 100%;
            }

            .admin-sort-link {
                flex: 1 1 0;
            }

            .admin-catalog-grid {
                grid-template-columns: 1fr;
            }

            .admin-table thead {
                display: none;
            }

            .admin-table tbody {
                display: block;
            }

            .admin-table tbody tr {
                display: block;
                margin-bottom: 10px;
                border-top: 1px solid rgba(255, 255, 255, 0.06);
                cursor: pointer;
            }

            /* 行の1行目（名称）だけ常に表示 */
            .admin-table tbody tr .cell-main {
                display: block;
                padding: 10px 14px;
                white-space: nowrap;
                font-size: 0.8rem;
            }

            .admin-table tbody tr .cell-main::before {
                content: none;
            }

            /* 詳細エリア（有効・登録日・操作）は折りたたみ */
            .admin-table tbody tr .cell-detail {
                display: none;
                padding: 8px 14px 10px;
                border-top: 1px solid rgba(255, 255, 255, 0.06);
                font-size: 0.7rem;
            }

            .admin-table tbody tr.is-open .cell-detail {
                display: block;
            }

            .admin-table tbody tr.is-open {
                background: rgba(255, 255, 255, 0.02);
            }
        }
    </style>
@endsection

@push('admin-scripts')
<script>
    (function () {
        // 登録済み一覧の検索
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
                    if (matched) {
                        visibleCount += 1;
                    }
                });

                if (noResultRow) {
                    noResultRow.hidden = visibleCount !== 0;
                }
            }

            recordSearchInput.addEventListener('input', applyRecordSearch);
        }

        // カテゴリ選択に応じてマスタのプルダウンを絞り込み
        var groupSelect = document.getElementById('master-group-select');
        var catalogSelect = document.getElementById('master-catalog-select');

        if (groupSelect && catalogSelect) {
            var allOptions = Array.prototype.slice.call(catalogSelect.querySelectorAll('option'));

            function applyMasterSelectFilter() {
                var selectedGroup = groupSelect.value;

                allOptions.forEach(function (opt, index) {
                    if (index === 0) {
                        // 先頭の「マスタを選択してください」は常に表示
                        opt.hidden = false;
                        return;
                    }

                    var optionGroup = opt.getAttribute('data-group') || '';
                    var visible = !selectedGroup || optionGroup === selectedGroup;
                    opt.hidden = !visible;
                });

                // 選択中のマスタが現在のカテゴリに属さない場合はリセット
                var current = catalogSelect.options[catalogSelect.selectedIndex];
                if (current && current.hidden) {
                    catalogSelect.selectedIndex = 0;
                }
            }

            groupSelect.addEventListener('change', applyMasterSelectFilter);
            applyMasterSelectFilter();
        }

        // モバイル時：行タップで詳細の開閉
        if (window.matchMedia('(max-width: 640px)').matches && rows.length) {
            rows.forEach(function (row) {
                row.addEventListener('click', function (event) {
                    // フォーム送信やボタン押下は無視
                    if (event.target.closest('button') || event.target.closest('a') || event.target.closest('form')) {
                        return;
                    }
                    row.classList.toggle('is-open');
                });
            });
        }
    })();
</script>
@endpush

