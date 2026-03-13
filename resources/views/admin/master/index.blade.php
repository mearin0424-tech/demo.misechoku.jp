@extends('layouts.admin')

@section('title', 'マスタ設定管理')

@section('content')
    <div class="admin-page">
        <h1 class="admin-title">マスタ設定管理</h1>
        <p class="admin-description">
            レビュー項目や検索タグ、NGワードなど、全体で共通利用するマスタデータをデータベースから表示しています。<br>
            デモ環境でも、管理画面で確認する内容は DB の状態に追従します。
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
                <span class="admin-summary-label">レビュー項目</span>
                <strong>{{ $summary['review_content_count'] }}</strong>
            </div>
            <div class="admin-summary-card">
                <span class="admin-summary-label">タグ種別</span>
                <strong>{{ $summary['tag_type_count'] }}</strong>
            </div>
            <div class="admin-summary-card">
                <span class="admin-summary-label">タグ総数</span>
                <strong>{{ $summary['tag_count'] }}</strong>
            </div>
            <div class="admin-summary-card">
                <span class="admin-summary-label">NGワード</span>
                <strong>{{ $summary['ng_word_count'] }}</strong>
            </div>
        </div>

        <div class="admin-grid">
            <section class="admin-card admin-card-wide">
                <div class="admin-card-head">
                    <div>
                        <h2>レビュー項目マスタ</h2>
                        <p>レビュー詳細で参照される評価項目一覧です。</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.masters.review-contents.store') }}" class="admin-master-form">
                    @csrf
                    <div class="admin-master-form-grid admin-master-form-grid-review">
                        <label class="admin-master-field">
                            <span>項目名</span>
                            <input type="text" name="name" value="{{ old('name') }}" placeholder="例: 接客">
                        </label>
                        <label class="admin-master-field">
                            <span>表示順</span>
                            <input type="number" name="sort_order" value="{{ old('sort_order', 1) }}" min="1" max="999">
                        </label>
                        <label class="admin-master-check">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', '1'))>
                            <span>登録直後から有効化する</span>
                        </label>
                        <button type="submit" class="admin-master-submit">レビュー項目を登録</button>
                    </div>
                </form>

                <div class="table-wrapper">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>項目名</th>
                                <th>表示順</th>
                                <th>利用件数</th>
                                <th>状態</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reviewContents as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->sort_order }}</td>
                                    <td>{{ $item->usage_count }}</td>
                                    <td>{{ $item->is_active ? '有効' : '無効' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">レビュー項目マスタはまだ登録されていません。</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="admin-card admin-card-wide">
                <div class="admin-card-head">
                    <div>
                        <h2>検索タグマスタ</h2>
                        <p>タグ種別ごとに、キャスト・店舗で使われている検索タグを表示します。</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.masters.tags.store') }}" class="admin-master-form">
                    @csrf
                    <div class="admin-master-form-grid admin-master-form-grid-tag">
                        <label class="admin-master-field">
                            <span>タグ種別</span>
                            <select name="type">
                                <option value="salary" @selected(old('type') === 'salary')>待遇タグ</option>
                                <option value="howto" @selected(old('type') === 'howto')>働き方タグ</option>
                                <option value="casttag" @selected(old('type') === 'casttag')>キャストタグ</option>
                            </select>
                        </label>
                        <label class="admin-master-field">
                            <span>タグ名</span>
                            <input type="text" name="name" value="{{ old('type') ? old('name') : '' }}" placeholder="例: 交通費支給">
                        </label>
                        <button type="submit" class="admin-master-submit">検索タグを登録</button>
                    </div>
                </form>

                <div class="admin-tag-groups">
                    @forelse($tagGroups as $type => $items)
                        <section class="admin-tag-group">
                            <div class="admin-tag-group-head">
                                <h3>{{ $items->first()->type_label }}</h3>
                                <span>{{ $type }}</span>
                            </div>

                            <div class="table-wrapper">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>タグ名</th>
                                            <th>キャスト利用</th>
                                            <th>店舗利用</th>
                                            <th>合計</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($items as $item)
                                            <tr>
                                                <td>{{ $item->id }}</td>
                                                <td>{{ $item->name }}</td>
                                                <td>{{ $item->cast_usage_count }}</td>
                                                <td>{{ $item->shop_usage_count }}</td>
                                                <td>{{ $item->usage_count }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    @empty
                        <p class="admin-empty-copy">検索タグマスタはまだ登録されていません。</p>
                    @endforelse
                </div>
            </section>

            <section class="admin-card admin-card-wide">
                <div class="admin-card-head">
                    <div>
                        <h2>NGワードマスタ</h2>
                        <p>メッセージ・投稿チェックで参照する禁止ワードです。</p>
                    </div>
                    <a href="{{ route('admin.ngwords.index') }}" class="admin-master-link">NGワード管理へ</a>
                </div>

                <form method="POST" action="{{ route('admin.masters.ngwords.store') }}" class="admin-master-form">
                    @csrf
                    <div class="admin-master-form-grid admin-master-form-grid-ng">
                        <label class="admin-master-field">
                            <span>NGワード</span>
                            <input type="text" name="word" value="{{ old('word') }}" placeholder="例: 直引き">
                        </label>
                        <label class="admin-master-check">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', '1'))>
                            <span>登録直後から有効化する</span>
                        </label>
                        <button type="submit" class="admin-master-submit">NGワードを登録</button>
                    </div>
                </form>

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
                            @forelse($ngWords->take(10) as $word)
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

        .admin-card-head,
        .admin-tag-group-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        .admin-card-head h2,
        .admin-tag-group-head h3 {
            margin: 0 0 6px;
        }

        .admin-card-head p,
        .admin-tag-group-head span,
        .admin-empty-copy {
            margin: 0;
            color: rgba(255, 255, 255, 0.68);
            line-height: 1.7;
        }

        .admin-tag-groups {
            display: grid;
            gap: 14px;
        }

        .admin-tag-group {
            padding: 16px;
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.02);
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
            align-items: end;
        }

        .admin-master-form-grid-review {
            grid-template-columns: minmax(0, 2fr) minmax(120px, 0.7fr) minmax(210px, 1fr) auto;
        }

        .admin-master-form-grid-tag {
            grid-template-columns: minmax(180px, 0.9fr) minmax(0, 2fr) auto;
        }

        .admin-master-form-grid-ng {
            grid-template-columns: minmax(0, 2fr) minmax(210px, 1fr) auto;
        }

        .admin-master-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .admin-master-field span,
        .admin-master-check span {
            font-size: 0.83rem;
            color: rgba(255, 255, 255, 0.76);
        }

        .admin-master-field input,
        .admin-master-field select {
            width: 100%;
            min-height: 44px;
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.04);
            color: #fff;
        }

        .admin-master-check {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            min-height: 44px;
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

            .admin-master-form-grid-review,
            .admin-master-form-grid-tag,
            .admin-master-form-grid-ng {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 640px) {
            .admin-summary-grid {
                grid-template-columns: 1fr;
            }

            .admin-card-head,
            .admin-tag-group-head {
                flex-direction: column;
                align-items: flex-start;
            }

            .admin-master-form-grid-review,
            .admin-master-form-grid-tag,
            .admin-master-form-grid-ng {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

