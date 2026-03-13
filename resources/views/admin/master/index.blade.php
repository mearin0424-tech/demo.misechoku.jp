@extends('layouts.admin')

@section('title', 'マスタ設定管理')

@section('content')
    <div class="admin-page">
        <h1 class="admin-title">マスタメンテナンス</h1>
        <p class="admin-description">
            手動で準備した新マスタテーブルを一覧・追加する画面です。<br>
            キャストプロフィール用、店舗・求人用、コラム用のマスタをまとめて管理できます。
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

        <div class="admin-summary-grid">
            <div class="admin-summary-card">
                <span class="admin-summary-label">マスタテーブル数</span>
                <strong>{{ $summary['catalog_count'] }}</strong>
            </div>
            <div class="admin-summary-card">
                <span class="admin-summary-label">総レコード数</span>
                <strong>{{ $summary['record_count'] }}</strong>
            </div>
            <div class="admin-summary-card">
                <span class="admin-summary-label">プロフィール系</span>
                <strong>{{ $summary['profile_master_count'] }}</strong>
            </div>
            <div class="admin-summary-card">
                <span class="admin-summary-label">求人系</span>
                <strong>{{ $summary['recruit_master_count'] }}</strong>
            </div>
        </div>

        <div class="admin-grid">
            @foreach($catalogs as $catalog)
                @php
                    $hasDirectory = $catalog['records']->contains(fn($row) => isset($row->directory));
                    $hasActive = $catalog['records']->contains(fn($row) => isset($row->is_active));
                    $columnCount = 3 + ($hasDirectory ? 1 : 0) + ($hasActive ? 1 : 0);
                @endphp
                <section class="admin-card admin-card-wide">
                    <div class="admin-card-head">
                        <div>
                            <h2>{{ $catalog['title'] }}</h2>
                            <p>{{ $catalog['description'] }}</p>
                        </div>
                        <span class="admin-card-count">{{ $catalog['count'] }}件</span>
                    </div>

                    <form method="POST" action="{{ route('admin.masters.catalogs.store', $catalog['key']) }}" class="admin-master-form">
                        @csrf
                        <div class="admin-master-form-grid">
                            @foreach($catalog['fields'] as $field)
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
                            <button type="submit" class="admin-master-submit">{{ $catalog['title'] }}を追加</button>
                        </div>
                    </form>

                    <div class="table-wrapper">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>名称</th>
                                    @if($hasDirectory)
                                        <th>ディレクトリ</th>
                                    @endif
                                    @if($hasActive)
                                        <th>状態</th>
                                    @endif
                                    <th>登録日</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($catalog['records'] as $item)
                                    <tr>
                                        <td>{{ $item->id }}</td>
                                        <td>{{ $item->name }}</td>
                                        @if($hasDirectory)
                                            <td>{{ $item->directory ?? '-' }}</td>
                                        @endif
                                        @if($hasActive)
                                            <td>{{ ($item->is_active ?? 1) ? '有効' : '無効' }}</td>
                                        @endif
                                        <td>{{ $item->created_at ? \Illuminate\Support\Carbon::parse($item->created_at)->format('Y-m-d') : '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $columnCount }}" class="text-center">まだ登録されていません。</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            @endforeach

            <section class="admin-card admin-card-wide">
                <div class="admin-card-head">
                    <div>
                        <h2>NGワード一覧</h2>
                        <p>禁止ワードテーブルの内容を確認します。</p>
                    </div>
                    <a href="{{ route('admin.ngwords.index') }}" class="admin-master-link">NGワード管理へ</a>
                </div>

                <div class="table-wrapper">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>ワード</th>
                                <th>状態</th>
                                <th>登録日</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ngWords as $word)
                                <tr>
                                    <td>{{ $word->id }}</td>
                                    <td>{{ $word->word }}</td>
                                    <td>{{ $word->is_active ? '有効' : '無効' }}</td>
                                    <td>{{ $word->created_at ? \Illuminate\Support\Carbon::parse($word->created_at)->format('Y-m-d') : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">NGワードマスタはまだ登録されていません。</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    <style>
        .admin-summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }

        .admin-summary-card,
        .admin-card {
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.03);
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.16);
        }

        .admin-summary-card {
            padding: 18px;
        }

        .admin-summary-card strong {
            display: block;
            margin-top: 10px;
            font-size: 1.9rem;
            color: #fff;
        }

        .admin-summary-label {
            color: rgba(255, 255, 255, 0.68);
            font-size: 0.8rem;
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
            margin-bottom: 16px;
            padding: 16px;
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.025);
        }

        .admin-master-form-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
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

        @media (max-width: 960px) {
            .admin-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
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
        }
    </style>
@endsection

