@php
    $isEdit = isset($column);
@endphp
<div class="admin-panel">
    <h2 class="admin-panel-title">{{ $isEdit ? 'コラムを編集' : 'コラムを新規作成' }}</h2>

    @if ($errors->any())
        <div class="admin-alert admin-alert-error">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($categories->isEmpty())
        <div class="admin-alert admin-alert-warning" style="margin-bottom: 16px;">
            カテゴリマスタ（column_categories）に有効なデータがありません。先にマスタメンテナンス等でカテゴリを登録してください。
        </div>
    @endif

    <form method="post" action="{{ $isEdit ? route('admin.columns.update', $column) : route('admin.columns.store') }}" class="admin-bank-form">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="admin-form-row">
            <label class="admin-label">タイトル</label>
            <input type="text" name="title" class="admin-input" value="{{ old('title', optional($column)->title) }}" required maxlength="200">
        </div>

        <div class="admin-form-row">
            <label class="admin-label">カテゴリ</label>
            <select name="column_category_id" class="admin-input" required>
                <option value="" disabled {{ old('column_category_id', optional($column)->column_category_id) ? '' : 'selected' }}>選択してください</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ (string) old('column_category_id', optional($column)->column_category_id) === (string) $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
            <small class="admin-note u-block u-mt-8">カテゴリはマスタメンテナンス画面から追加可能です。</small>
        </div>

        <div class="admin-form-row">
            <label class="admin-label">本文</label>
            <textarea name="body" class="admin-input" rows="14" required placeholder="本文（プレーンテキスト。改行はそのまま表示されます）">{{ old('body', optional($column)->body) }}</textarea>
        </div>

        <div class="admin-form-row">
            <label class="admin-label">公開する</label>
            <label class="admin-check-row">
                <input type="checkbox" name="is_published" value="1" {{ old('is_published', optional($column)->is_published) ? 'checked' : '' }}>
                <span>公開（オフの場合は下書き扱い）</span>
            </label>
        </div>

        <div class="admin-form-row">
            <label class="admin-label">公開日時</label>
            <input type="datetime-local" name="published_at" class="admin-input"
                value="{{ old('published_at', optional($column)->published_at ? optional($column)->published_at->format('Y-m-d\TH:i') : '') }}">
            <small class="admin-note u-block u-mt-8">公開ONかつ日時が未来の場合、予約公開として扱います。</small>
        </div>

        <div class="admin-form-row">
            <label class="admin-label">閲覧対象</label>
            <div class="admin-checks-stack">
                <label class="admin-check-row">
                    <input type="checkbox" name="visible_to_cast" value="1" {{ old('visible_to_cast', optional($column)->visible_to_cast ?? true) ? 'checked' : '' }}>
                    <span>キャストアプリ内</span>
                </label>
                <label class="admin-check-row">
                    <input type="checkbox" name="visible_to_shop" value="1" {{ old('visible_to_shop', optional($column)->visible_to_shop ?? true) ? 'checked' : '' }}>
                    <span>店舗アプリ内</span>
                </label>
                <label class="admin-check-row">
                    <input type="checkbox" name="visible_to_guest" value="1" {{ old('visible_to_guest', optional($column)->visible_to_guest ?? false) ? 'checked' : '' }}>
                    <span>未ログイン（/support/column）</span>
                </label>
            </div>
        </div>

        <div class="admin-form-actions">
            @include('admin.parts.back-link', ['url' => route('admin.columns.index')])
            <button type="submit" class="btn-action manage" @if($categories->isEmpty()) disabled @endif>
                <i class="fas fa-save"></i> {{ $isEdit ? '更新する' : '登録する' }}
            </button>
        </div>
    </form>
</div>
