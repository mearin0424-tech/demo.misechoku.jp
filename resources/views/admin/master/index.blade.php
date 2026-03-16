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
                    <p>編集したいマスタを選ぶと、この下に追加フォームと一覧が表示されます。</p>
                </div>
            </div>

            <div class="admin-master-filters">
                <div class="admin-master-filter-select">
                    <select id="master-group-filter">
                        <option value="">すべてのカテゴリ</option>
                        @foreach ($catalogGroups as $group => $items)
                            <option value="{{ $group }}">{{ $group }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="admin-master-filter-search">
                    <i class="fas fa-magnifying-glass"></i>
                    <input
                        type="text"
                        id="master-name-search"
                        placeholder="マスタ名で検索（例: 業種、店舗タグ…）"
                        autocomplete="off"
                    >
                </div>
            </div>

            {{-- プルダウンでもマスタを選択できる --}}
            <div class="admin-master-select">
                @php
                    $allCatalogs = $catalogGroups->flatten(1);
                @endphp
                <label class="admin-master-select-label">
                    <span>マスタを直接選択</span>
                    <select
                        id="master-catalog-select"
                        onchange="if(this.value){window.location.href=this.value;}"
                    >
                        <option value="">マスタを選択してください</option>
                        @foreach ($allCatalogs as $catalog)
                            <option
                                value="{{ route('admin.masters.index', ['catalog' => $catalog['key']]) }}"
                                @selected(($selectedCatalog['key'] ?? null) === $catalog['key'])
                            >
                                {{ $catalog['title'] }}
                            </option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="admin-catalog-groups" id="master-catalog-groups">
                @foreach ($catalogGroups as $group => $items)
                    <section class="admin-catalog-group">
                        <h3>{{ $group }}</h3>
                        <div class="admin-catalog-grid">
                            @foreach ($items as $catalog)
                                <a
                                    href="{{ route('admin.masters.index', ['catalog' => $catalog['key']]) }}"
                                    class="admin-catalog-card {{ ($selectedCatalog['key'] ?? null) === $catalog['key'] ? 'is-active' : '' }}"
                                    data-group="{{ $group }}"
                                    data-name="{{ mb_strtolower($catalog['title']) }}"
                                >
                                    <div class="admin-catalog-card-head">
                                        <strong>{{ $catalog['title'] }}</strong>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endforeach
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
                                            <td data-label="名称">{{ $item->name }}</td>
                                            @if ($hasDirectory)
                                                <td data-label="ディレクトリ">{{ $item->directory ?? '-' }}</td>
                                            @endif
                                            @if ($hasActive)
                                                <td data-label="状態">{{ ($item->is_active ?? 1) ? '有効' : '無効' }}</td>
                                            @endif
                                            <td data-label="登録日">{{ $item->created_at ? \Illuminate\Support\Carbon::parse($item->created_at)->format('Y-m-d') : '-' }}</td>
                                            <td data-label="操作" class="admin-row-actions">
                                                {{-- 編集（鉛筆アイコン） --}}
                                                <a
                                                    href="{{ route('admin.masters.index', ['catalog' => $selectedCatalog['key'], 'edit' => $item->id, 'sort' => $selectedSort]) }}"
                                                    class="admin-row-icon-btn {{ $editingRecord && $editingRecord->id === $item->id ? 'is-active' : '' }}"
                                                    title="編集"
                                                >
                                                    <i class="fas fa-pen"></i>
                                                </a>

                                                {{-- 削除（×アイコン） --}}
                                                <form
                                                    method="POST"
                                                    action="{{ route('admin.masters.catalogs.destroy', [$selectedCatalog['key'], $item->id]) }}"
                                                    onsubmit="return confirm('この項目を削除しますか？');"
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

        .admin-master-filters {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        .admin-master-select {
            margin-bottom: 12px;
        }

        .admin-master-select-label {
            display: flex;
            flex-direction: column;
            gap: 6px;
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.76);
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

        .admin-master-filter-select select {
            min-width: 200px;
            padding: 8px 10px;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.16);
            background: rgba(255, 255, 255, 0.02);
            color: #fff;
            font-size: 0.8rem;
            outline: none;
        }

        .admin-master-filter-select select:focus {
            border-color: rgba(230, 208, 128, 0.4);
            box-shadow: 0 0 0 2px rgba(230, 208, 128, 0.16);
        }

        .admin-master-filter-search {
            position: relative;
            flex: 1;
            min-width: 220px;
        }

        .admin-master-filter-search i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.42);
            font-size: 0.78rem;
        }

        #master-name-search {
            width: 100%;
            min-height: 38px;
            padding: 8px 10px 8px 32px;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.14);
            background: rgba(255, 255, 255, 0.02);
            color: #fff;
            font-size: 0.8rem;
            outline: none;
        }

        #master-name-search::placeholder {
            color: rgba(255, 255, 255, 0.42);
        }

        #master-name-search:focus {
            border-color: rgba(230, 208, 128, 0.4);
            box-shadow: 0 0 0 2px rgba(230, 208, 128, 0.12);
        }

        .admin-catalog-group h3 {
            margin: 0 0 10px;
            font-size: 0.88rem;
            color: rgba(255, 255, 255, 0.82);
        }

        .admin-catalog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 8px;
        }

        .admin-catalog-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.02);
            text-decoration: none;
            transition: border-color 0.18s ease, transform 0.18s ease, background 0.18s ease;
        }

        .admin-catalog-card:hover,
        .admin-catalog-card.is-active {
            transform: translateY(-1px);
            border-color: rgba(230, 208, 128, 0.3);
            background: rgba(230, 208, 128, 0.06);
        }

        .admin-catalog-card-head {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .admin-catalog-card-head strong {
            font-size: 0.84rem;
            color: #fff;
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
                margin-bottom: 12px;
                border-top: 1px solid rgba(255, 255, 255, 0.06);
            }

            .admin-table tbody td {
                display: grid;
                grid-template-columns: 84px minmax(0, 1fr);
                gap: 8px;
                white-space: normal;
            }

            .admin-table tbody td::before {
                content: attr(data-label);
                color: rgba(255, 255, 255, 0.52);
                font-size: 0.7rem;
                font-weight: 700;
            }

            .admin-table tbody td.text-center {
                display: block;
            }

            .admin-table tbody td.text-center::before {
                content: none;
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

        // マスタ一覧のカテゴリ＆名称フィルタ
        var groupSelect = document.getElementById('master-group-filter');
        var nameInput = document.getElementById('master-name-search');
        var catalogCards = document.querySelectorAll('.admin-catalog-card');

        function applyCatalogFilter() {
            if (!catalogCards.length) return;

            var selectedGroup = groupSelect ? groupSelect.value : '';
            var keyword = nameInput ? normalize(nameInput.value) : '';

            catalogCards.forEach(function (card) {
                var cardGroup = card.getAttribute('data-group') || '';
                var cardName = normalize(card.getAttribute('data-name') || '');

                var matchGroup = !selectedGroup || cardGroup === selectedGroup;
                var matchName = !keyword || cardName.indexOf(keyword) !== -1;

                var visible = matchGroup && matchName;
                card.style.display = visible ? '' : 'none';
            });
        }

        if (groupSelect) {
            groupSelect.addEventListener('change', applyCatalogFilter);
        }
        if (nameInput) {
            nameInput.addEventListener('input', applyCatalogFilter);
        }
    })();
</script>
@endpush

