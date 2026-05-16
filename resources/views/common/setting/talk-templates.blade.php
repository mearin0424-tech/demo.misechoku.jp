@extends('layouts.app')

@section('title', 'トーク定型文の設定')

@section('content')
<div class="setting-page">
    <div class="setting-header">
        <h1 class="setting-title">トーク定型文の設定</h1>
        <p class="setting-lead">トーク画面で「定型文を使う」から呼び出せる文面を、自由に追加・編集できます。</p>
    </div>

    @if (session('message'))
        <div class="setting-alert setting-alert-success">{{ session('message') }}</div>
    @endif
    @if ($errors->any())
        <div class="setting-alert setting-alert-error">
            @foreach ($errors->all() as $error) <div>{{ $error }}</div> @endforeach
        </div>
    @endif

    @if (!($isLoggedIn ?? false))
        <p class="setting-guest-note">定型文の設定はログイン後に利用できます。</p>
    @else
        <section class="setting-section">
            <div class="tpl-section-head">
                <h2 class="setting-section-title">登録済みの定型文</h2>
                @if(count($templates) === 0)
                    <form method="POST" action="{{ route('setting.talk-templates.import-defaults') }}">
                        @csrf
                        <button type="submit" class="setting-btn setting-btn-test">プリセットを取り込む</button>
                    </form>
                @endif
            </div>

            @if(count($templates) === 0)
                <p class="setting-row-desc" style="margin-top:6px;">
                    まだ登録された定型文はありません。下の「新規追加」から作成するか、上のボタンでプリセットを取り込めます。<br>
                    自分の定型文を1件でも登録すると、トーク画面ではプリセットの代わりに自分の定型文が表示されます。
                </p>
            @else
                <ul class="tpl-list">
                    @foreach($templates as $tpl)
                        <li class="tpl-item">
                            <form method="POST" action="{{ route('setting.talk-templates.update', ['id' => $tpl->id]) }}" class="tpl-form">
                                @csrf
                                @method('PUT')
                                <div class="tpl-row">
                                    <label class="tpl-field">
                                        <span class="tpl-field-label">カテゴリ</span>
                                        <input type="text" name="category" value="{{ old('category', $tpl->category) }}" maxlength="64" placeholder="例: 初回挨拶">
                                    </label>
                                    <label class="tpl-field">
                                        <span class="tpl-field-label">タイトル</span>
                                        <input type="text" name="title" value="{{ old('title', $tpl->title) }}" maxlength="80" placeholder="例: お礼" required>
                                    </label>
                                </div>
                                <label class="tpl-field">
                                    <span class="tpl-field-label">本文</span>
                                    <textarea name="body" rows="3" maxlength="2000" required>{{ old('body', $tpl->body) }}</textarea>
                                </label>
                                <div class="tpl-actions">
                                    <label class="setting-check">
                                        <input type="checkbox" name="is_active" value="1" {{ $tpl->is_active ? 'checked' : '' }}> 表示する
                                    </label>
                                    <button type="submit" class="setting-btn setting-btn-test">保存</button>
                                </div>
                            </form>
                            <form method="POST" action="{{ route('setting.talk-templates.destroy', ['id' => $tpl->id]) }}" class="tpl-delete-form" onsubmit="return confirm('この定型文を削除しますか？');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="setting-btn tpl-delete-btn">削除</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="setting-section">
            <h2 class="setting-section-title">新規追加</h2>
            <form method="POST" action="{{ route('setting.talk-templates.store') }}" class="tpl-form">
                @csrf
                <div class="tpl-row">
                    <label class="tpl-field">
                        <span class="tpl-field-label">カテゴリ</span>
                        <input type="text" name="category" value="{{ old('category') }}" maxlength="64" placeholder="例: 初回挨拶">
                    </label>
                    <label class="tpl-field">
                        <span class="tpl-field-label">タイトル</span>
                        <input type="text" name="title" value="{{ old('title') }}" maxlength="80" placeholder="例: お礼" required>
                    </label>
                </div>
                <label class="tpl-field">
                    <span class="tpl-field-label">本文</span>
                    <textarea name="body" rows="3" maxlength="2000" placeholder="トークで送る文面を入力してください。" required>{{ old('body') }}</textarea>
                </label>
                <div class="tpl-actions" style="justify-content:flex-end;">
                    <button type="submit" class="setting-btn setting-btn-test">追加する</button>
                </div>
            </form>
        </section>

        @if(!empty($defaults))
            <section class="setting-section">
                <h2 class="setting-section-title">プリセット（参考）</h2>
                <p class="setting-row-desc" style="margin-bottom:8px;">
                    自分の定型文が1件もないときは、以下のプリセットがトーク画面に表示されます。プリセットをそのまま編集したい場合は「プリセットを取り込む」をご利用ください。
                </p>
                <ul class="tpl-preset-list">
                    @foreach($defaults as $tpl)
                        <li class="tpl-preset-item">
                            <div class="tpl-preset-meta">
                                <span class="tpl-preset-cat">{{ $tpl['category'] ?? 'その他' }}</span>
                                <span class="tpl-preset-title">{{ $tpl['title'] }}</span>
                            </div>
                            <p class="tpl-preset-body">{{ $tpl['body'] }}</p>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    @endif
</div>
@endsection

@push('styles')
<style>
.setting-page { padding: 24px 16px 32px; color: #f9f5f5; }
@media (min-width: 768px) { .setting-page { padding: 32px 24px 40px; } }
.setting-header { margin-bottom: 24px; }
.setting-title { font-family: 'Shippori Mincho','Noto Sans JP',sans-serif; font-size: 1.4rem; margin-bottom: 8px; color: var(--color-gold, #d4af37); }
.setting-lead { font-size: 0.9rem; line-height: 1.6; color: #d1c1c1; }
.setting-section { margin-bottom: 18px; background: rgba(20, 7, 15, 0.9); border-radius: 16px; padding: 14px 12px 14px; border: 1px solid rgba(212, 175, 55, 0.4); }
@media (min-width: 768px) { .setting-section { padding: 16px; } }
.setting-section-title { font-size: 0.95rem; margin-bottom: 8px; color: #f9f5f5; }
.setting-row-desc { font-size: 0.78rem; color: #b69f9f; line-height: 1.6; }
.setting-check { font-size: 0.86rem; color: #f5f5f5; display: inline-flex; align-items: center; gap: 8px; white-space: nowrap; }
.setting-check input { width: 16px; height: 16px; }
.setting-alert { padding: 12px 14px; border-radius: 12px; margin-bottom: 16px; font-size: 0.85rem; line-height: 1.6; }
.setting-alert-success { background: rgba(22,163,74,0.2); border: 1px solid rgba(22,163,74,0.5); color: #bbf7d0; }
.setting-alert-error { background: rgba(185,28,28,0.2); border: 1px solid rgba(248,113,113,0.5); color: #fecaca; }
.setting-btn { display: inline-block; padding: 9px 16px; border-radius: 12px; font-size: 0.85rem; font-weight: 600; text-decoration: none; border: none; transition: opacity 0.2s; cursor: pointer; }
.setting-btn:hover { opacity: 0.9; }
.setting-btn-test { background: #065f46; color: #ecfdf5; border: 1px solid rgba(52,211,153,0.65); }
.setting-guest-note { color: #b69f9f; font-size: 0.9rem; margin-bottom: 20px; }

.tpl-section-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
.tpl-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 12px; }
.tpl-item { border: 1px solid rgba(212,175,55,0.25); border-radius: 12px; padding: 12px; background: rgba(0,0,0,0.25); display: flex; flex-direction: column; gap: 8px; }
.tpl-row { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
@media (max-width: 540px) { .tpl-row { grid-template-columns: 1fr; } }
.tpl-form { display: flex; flex-direction: column; gap: 8px; }
.tpl-field { display: flex; flex-direction: column; gap: 4px; }
.tpl-field-label { font-size: 0.75rem; color: #c4a566; }
.tpl-field input[type="text"], .tpl-field textarea { width: 100%; background: rgba(255,255,255,0.05); color: #fff; border: 1px solid rgba(229,193,88,0.22); border-radius: 10px; padding: 8px 10px; font-size: 0.9rem; resize: vertical; }
.tpl-field input[type="text"]:focus, .tpl-field textarea:focus { outline: none; border-color: rgba(229,193,88,0.6); }
.tpl-actions { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
.tpl-delete-form { display: flex; justify-content: flex-end; }
.tpl-delete-btn { background: rgba(185,28,28,0.25); color: #fecaca; border: 1px solid rgba(248,113,113,0.45); }
.tpl-preset-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 8px; }
.tpl-preset-item { background: rgba(0,0,0,0.2); border: 1px dashed rgba(229,193,88,0.25); border-radius: 10px; padding: 10px; }
.tpl-preset-meta { display: flex; gap: 8px; align-items: center; margin-bottom: 4px; }
.tpl-preset-cat { font-size: 0.7rem; padding: 2px 8px; border-radius: 999px; background: rgba(212,175,55,0.18); color: #f4e7c2; }
.tpl-preset-title { font-size: 0.88rem; color: #f4e7c2; }
.tpl-preset-body { font-size: 0.82rem; color: #d1c1c1; line-height: 1.6; margin: 0; white-space: pre-wrap; }
</style>
@endpush
