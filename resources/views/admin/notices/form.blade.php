@php
    $isEdit = isset($notice);
@endphp
<div class="admin-panel">
    <h2 class="admin-panel-title">{{ $isEdit ? 'お知らせを編集' : 'お知らせを新規作成' }}</h2>

    @if ($errors->any())
        <div class="admin-alert admin-alert-error">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="post" action="{{ $isEdit ? route('admin.notices.update', $notice) : route('admin.notices.store') }}" class="admin-bank-form">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="admin-form-row">
            <label class="admin-label">タイトル</label>
            <input type="text" name="title" class="admin-input" value="{{ old('title', optional($notice)->title) }}" required maxlength="200">
        </div>

        <div class="admin-form-row">
            <label class="admin-label">本文</label>
            <textarea name="body" class="admin-input" rows="14" required placeholder="本文（プレーンテキスト。改行はそのまま表示されます）">{{ old('body', optional($notice)->body) }}</textarea>
        </div>

        <div class="admin-form-row">
            <label class="admin-label">公開する</label>
            <label class="admin-check-row">
                <input type="checkbox" name="is_published" value="1" {{ old('is_published', optional($notice)->is_published) ? 'checked' : '' }}>
                <span>公開（オフの場合は下書き扱い）</span>
            </label>
        </div>

        <div class="admin-form-row">
            <label class="admin-label">公開日時</label>
            <input type="datetime-local" name="published_at" class="admin-input"
                value="{{ old('published_at', optional($notice)->published_at ? optional($notice)->published_at->format('Y-m-d\TH:i') : '') }}">
            <small class="admin-note u-block u-mt-8">公開ONかつ日時が未来の場合、予約公開として扱います。</small>
        </div>

        <div class="admin-form-row">
            <label class="admin-label">配信対象</label>
            <div class="admin-checks-stack">
                <label class="admin-check-row">
                    <input type="checkbox" name="visible_to_cast" value="1" {{ old('visible_to_cast', optional($notice)->visible_to_cast ?? true) ? 'checked' : '' }}>
                    <span>キャストアプリ内</span>
                </label>
                <label class="admin-check-row">
                    <input type="checkbox" name="visible_to_shop" value="1" {{ old('visible_to_shop', optional($notice)->visible_to_shop ?? true) ? 'checked' : '' }}>
                    <span>店舗アプリ内</span>
                </label>
                <label class="admin-check-row">
                    <input type="checkbox" name="visible_to_guest" value="1" {{ old('visible_to_guest', optional($notice)->visible_to_guest ?? false) ? 'checked' : '' }}>
                    <span>未ログイン（/support/notices）</span>
                </label>
            </div>
        </div>

        <div class="admin-form-actions">
            @include('admin.parts.back-link', ['url' => route('admin.notices.index')])
            <button type="submit" class="btn-action manage">
                <i class="fas fa-save"></i> {{ $isEdit ? '更新する' : '登録する' }}
            </button>
        </div>
    </form>
</div>
