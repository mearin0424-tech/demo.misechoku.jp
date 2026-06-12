@extends('layouts.admin')

@section('title', 'NGワード管理')

@section('content')
    <div class="admin-page">
        @include('admin.parts.page-title', [
            'eyebrow' => 'NG WORDS',
            'title' => 'NGワード管理',
            'info' => '
                <p>メッセージなどで使用できない<strong>キーワード</strong>を登録・管理します。</p>
                <ul>
                    <li>新規追加・編集・削除（無効化）が可能</li>
                    <li>表示順は ID 昇順で固定</li>
                </ul>
            ',
        ])

        @if (session('status'))
            <div class="admin-alert admin-alert-success">{{ session('status') }}</div>
        @endif
        @if (!empty($error))
            <div class="admin-alert admin-alert-error">{{ $error }}</div>
        @endif

        {{-- 新規追加 --}}
        <section class="admin-card admin-card-wide">
            <div class="admin-card-head">
                <div>
                    <h2>NGワードを追加</h2>
                    <p>メッセージ送信時にブロックされるキーワードを追加します。</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.ngwords.store') }}" class="ngword-add-form">
                @csrf
                <label class="ngword-add-form__field">
                    <span>NGワード</span>
                    <input type="text" name="word" required maxlength="255" placeholder="例: 連絡先" autocomplete="off">
                </label>
                <button type="submit" class="btn-action manage">
                    <i class="fas fa-plus"></i> 追加
                </button>
            </form>
        </section>

        {{-- 編集中フォーム（行内編集の代わりに上に出す） --}}
        @if (!empty($editingWord))
            <section class="admin-card admin-card-wide ngword-edit-card">
                <div class="admin-card-head">
                    <div>
                        <h2>NGワードを編集（ID: {{ $editingWord->id }}）</h2>
                        <p>内容を変更して「変更を保存」を押してください。無効に切り替えると検出処理から除外されます。</p>
                    </div>
                    <a href="{{ route('admin.ngwords.index') }}" class="admin-master-cancel">編集をやめる</a>
                </div>
                <form method="POST" action="{{ route('admin.ngwords.update', $editingWord->id) }}" class="ngword-edit-form">
                    @csrf
                    @method('PUT')
                    <label class="ngword-edit-form__field">
                        <span>NGワード</span>
                        <input type="text" name="word" value="{{ old('word', $editingWord->word) }}" required maxlength="255">
                    </label>
                    <div class="u-flex u-gap-8">
                        <button type="submit" class="btn-action manage">
                            <i class="fas fa-floppy-disk"></i> 変更を保存
                        </button>
                        <a href="{{ route('admin.ngwords.index') }}" class="btn-action btn-action-secondary">キャンセル</a>
                    </div>
                </form>
            </section>
        @endif

        {{-- 一覧 --}}
        <section class="admin-card admin-card-wide">
            <div class="admin-card-head">
                <div>
                    <h2>登録済みNGワード（{{ $words->count() }}件）</h2>
                    <p>削除すると一覧上は「無効」と表示され、検出処理からは除外されます。</p>
                </div>
            </div>
            <div class="table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="u-w-60">ID</th>
                            <th>NGワード</th>
                            <th class="u-w-100">状態</th>
                            <th>登録日</th>
                            <th class="u-w-140">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($words as $word)
                            @php $isActive = (int) ($word->is_active ?? 0) === 1; @endphp
                            <tr class="{{ $isActive ? '' : 'is-suspended' }}">
                                <td><code>{{ $word->id }}</code></td>
                                <td>{{ $word->word }}</td>
                                <td>
                                    <span class="admin-status-badge {{ $isActive ? 'is-success' : 'is-inactive' }}">
                                        {{ $isActive ? '有効' : '無効' }}
                                    </span>
                                </td>
                                <td>{{ $word->created_at ? \Illuminate\Support\Carbon::parse($word->created_at)->format('Y-m-d') : '-' }}</td>
                                <td>
                                    <div class="u-flex u-gap-6">
                                        <a href="{{ route('admin.ngwords.index', ['edit' => $word->id]) }}"
                                           class="admin-row-icon-btn {{ ($editingWord->id ?? null) === $word->id ? 'is-active' : '' }}"
                                           title="編集">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        @if($isActive)
                                            <form method="POST" action="{{ route('admin.ngwords.destroy', $word->id) }}"
                                                  onsubmit="return confirm('このNGワードを削除（無効化）しますか？');" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="admin-row-icon-btn admin-row-icon-delete" title="削除">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">登録されているNGワードはありません。</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

@push('admin-styles')
<style>
.ngword-add-form,
.ngword-edit-form {
    display: flex;
    align-items: flex-end;
    flex-wrap: wrap;
    gap: 12px;
}
.ngword-add-form__field,
.ngword-edit-form__field {
    display: flex;
    flex-direction: column;
    gap: 4px;
    flex: 1 1 260px;
    min-width: 240px;
}
.ngword-add-form__field span,
.ngword-edit-form__field span {
    font-size: 0.74rem;
    font-weight: 700;
    color: var(--admin-muted);
    letter-spacing: 0.05em;
}
.ngword-add-form__field input,
.ngword-edit-form__field input {
    height: 40px;
    padding: 0 12px;
    border: 1px solid rgba(74, 18, 42, 0.18);
    border-radius: 10px;
    background: #fff;
    font-size: 0.95rem;
    color: var(--admin-text);
}
.ngword-edit-card { border-left: 4px solid #a78bfa; }
</style>
@endpush
