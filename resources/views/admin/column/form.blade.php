@php
    $isEdit = isset($column);
@endphp
<div class="admin-panel">
    <h2 class="admin-panel-title">{{ $isEdit ? 'コラムを編集' : 'コラムを新規作成' }}</h2>

    @if ($errors->any())
        <div class="admin-alert" style="border-color: var(--admin-red);">
            <ul style="margin:0;padding-left:1.2em;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
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
            <label class="admin-label">スラッグ（省略可・英数字とハイフン）</label>
            <input type="text" name="slug" class="admin-input" value="{{ old('slug', optional($column)->slug) }}" maxlength="191" placeholder="例: first-guide">
            <small style="display:block; margin-top:6px; color:#7c8ba3;">空欄の場合はタイトルから自動生成します。URLに使われます。</small>
        </div>

        <div class="admin-form-row">
            <label class="admin-label">カテゴリ</label>
            <input type="text" name="category" class="admin-input" value="{{ old('category', optional($column)->category) }}" maxlength="100" placeholder="例: 運営ノウハウ">
        </div>

        <div class="admin-form-row">
            <label class="admin-label">一覧用抜粋</label>
            <textarea name="summary" class="admin-input" rows="3" maxlength="2000" placeholder="一覧に表示する短い説明">{{ old('summary', optional($column)->summary) }}</textarea>
        </div>

        <div class="admin-form-row">
            <label class="admin-label">本文</label>
            <textarea name="body" class="admin-input" rows="14" required placeholder="本文（プレーンテキスト。改行はそのまま表示されます）">{{ old('body', optional($column)->body) }}</textarea>
        </div>

        <div class="admin-form-row">
            <label class="admin-label">公開する</label>
            <label style="display:flex;align-items:center;gap:8px;">
                <input type="checkbox" name="is_published" value="1" {{ old('is_published', optional($column)->is_published) ? 'checked' : '' }}>
                <span>公開（オフの場合は下書き扱い）</span>
            </label>
        </div>

        <div class="admin-form-row">
            <label class="admin-label">公開日時</label>
            <input type="datetime-local" name="published_at" class="admin-input"
                value="{{ old('published_at', optional($column)->published_at ? optional($column)->published_at->format('Y-m-d\TH:i') : '') }}">
            <small style="display:block; margin-top:6px; color:#7c8ba3;">公開ONかつ日時が未来の場合、予約公開として扱います。</small>
        </div>

        <div class="admin-form-row">
            <label class="admin-label">閲覧対象</label>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <label style="display:flex;align-items:center;gap:8px;">
                    <input type="checkbox" name="visible_to_cast" value="1" {{ old('visible_to_cast', optional($column)->visible_to_cast ?? true) ? 'checked' : '' }}>
                    <span>キャストアプリ内</span>
                </label>
                <label style="display:flex;align-items:center;gap:8px;">
                    <input type="checkbox" name="visible_to_shop" value="1" {{ old('visible_to_shop', optional($column)->visible_to_shop ?? true) ? 'checked' : '' }}>
                    <span>店舗アプリ内</span>
                </label>
                <label style="display:flex;align-items:center;gap:8px;">
                    <input type="checkbox" name="visible_to_guest" value="1" {{ old('visible_to_guest', optional($column)->visible_to_guest ?? false) ? 'checked' : '' }}>
                    <span>未ログイン（/support/column）</span>
                </label>
            </div>
        </div>

        <div class="admin-form-actions">
            <button type="submit" class="btn-action manage">
                <i class="fas fa-save"></i> {{ $isEdit ? '更新する' : '登録する' }}
            </button>
            <a href="{{ route('admin.columns.index') }}" class="btn-action" style="margin-left:8px;text-decoration:none;display:inline-flex;align-items:center;">一覧へ戻る</a>
        </div>
    </form>
</div>
