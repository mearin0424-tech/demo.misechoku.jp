@extends('layouts.admin')

@section('title', $document->title . ' - 編集')
@section('admin_page_title', $document->title . ' の編集')

@section('content')
    @php
        $oldChapters = $document->isAbout()
            ? []
            : old('chapters', $document->chapters->map(fn ($c) => ['title' => $c->title, 'body' => $c->body])->all());
        if (! $document->isAbout() && (! is_array($oldChapters) || count($oldChapters) === 0)) {
            $oldChapters = [['title' => '', 'body' => '']];
        }
        $oldMeta = old('meta', $document->meta ?? []);
    @endphp

    <div class="admin-page">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
            <div>
                <h1 class="admin-title" style="margin-bottom:4px;">{{ $document->title }} を編集</h1>
                <p class="admin-description" style="margin:0;">
                    @if($document->isAbout())
                        リード見出し・リード本文・協会概要を編集します。リード本文・概要の各欄は<strong>Markdown</strong>（見出し・箇条書き・太字など）で記述できます。
                    @else
                        章タイトル＋本文の組み合わせで編集します。リード本文・各章の本文は<strong>Markdown</strong>（見出し・箇条書き・太字など）で記述できます。
                    @endif
                </p>
            </div>
            <div style="display:flex;gap:8px;">
                <a href="{{ route('admin.policies.show', ['key' => $document->key]) }}" class="btn-action manage" style="background:transparent;border-color:var(--admin-line);color:var(--admin-text);">
                    <i class="fas fa-arrow-left"></i> 閲覧へ戻る
                </a>
            </div>
        </div>

        @if(session('status'))
            <div class="admin-alert">{{ session('status') }}</div>
        @endif

        @if($errors->any())
            <div class="admin-alert" style="border-color: var(--admin-red);">
                <ul style="margin:0;padding-left:1.2em;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="admin-panel" style="border-color: rgba(248,113,113,.35); background: rgba(248,113,113,.06);">
            <div style="display:flex;gap:12px;align-items:flex-start;">
                <div style="font-size:1.4rem;color:#fda4af;">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
                <div style="font-size:.86rem;line-height:1.7;color:var(--admin-text);">
                    本ページは<strong>原則として編集不可</strong>です。編集には下部の<strong>「ロックを解除して更新する」</strong>のチェックが必要で、<strong>更新者と日時はすべて履歴に記録</strong>されます。<br>
                    現在の状態:
                    @if($document->is_locked)
                        <span class="admin-badge" style="background:rgba(248,113,113,.18);color:#fda4af;"><i class="fas fa-lock" style="margin-right:4px;"></i>ロック中</span>
                    @else
                        <span class="admin-badge" style="background:rgba(52,211,153,.18);color:#86efac;"><i class="fas fa-lock-open" style="margin-right:4px;"></i>編集可能</span>
                    @endif
                </div>
            </div>
        </div>

        <form method="post" action="{{ route('admin.policies.update', ['key' => $document->key]) }}" id="policy-edit-form">
            @csrf
            @method('PUT')

            <div class="admin-panel">
                <h2 class="admin-panel-title">基本情報</h2>

                <div class="admin-form-row">
                    <label class="admin-label">ページタイトル</label>
                    <input type="text" name="title" class="admin-input"
                        value="{{ old('title', $document->title) }}" required maxlength="200">
                </div>

                <div class="admin-form-row">
                    <label class="admin-label">リード見出し（任意）</label>
                    <input type="text" name="lead_title" class="admin-input"
                        value="{{ old('lead_title', $document->lead_title) }}" maxlength="200"
                        placeholder="例: GREETING / 理事長 挨拶">
                </div>

                <div class="admin-form-row">
                    <label class="admin-label">リード本文（任意・Markdown）</label>
                    <textarea name="lead_body" class="admin-input" rows="10"
                        placeholder="例: ## 見出し&#10;本文。 **太字** や箇条書き `- 項目` も利用できます。">{{ old('lead_body', $document->lead_body) }}</textarea>
                </div>
            </div>

            @if($document->isAbout())
                <div class="admin-panel">
                    <h2 class="admin-panel-title">OVERVIEW / 協会概要（運営協会のみ）</h2>
                    <p class="admin-note" style="margin-bottom:12px;">
                        会社概要情報（協会名・資本金など）はここで管理します。各欄は Markdown 可（改行のみでも表示されます）。
                    </p>
                    @foreach($metaSchema as $row)
                        @php
                            $entry = $oldMeta[$row['key']] ?? null;
                            $value = is_array($entry) ? ($entry['value'] ?? '') : '';
                            $label = is_array($entry) ? ($entry['label'] ?? $row['label']) : $row['label'];
                        @endphp
                        <div class="admin-form-row">
                            <label class="admin-label">{{ $row['label'] }}</label>
                            <input type="hidden" name="meta[{{ $row['key'] }}][label]" value="{{ $label }}">
                            <textarea name="meta[{{ $row['key'] }}][value]" class="admin-input" rows="2" placeholder="{{ $row['label'] }}">{{ $value }}</textarea>
                        </div>
                    @endforeach
                </div>
            @endif

            @unless($document->isAbout())
                <div class="admin-panel" id="policy-chapters-panel">
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:14px;">
                        <h2 class="admin-panel-title" style="margin:0;">章コンテンツ</h2>
                        <button type="button" class="btn-action manage" id="add-chapter-btn">
                            <i class="fas fa-plus"></i> 章を追加
                        </button>
                    </div>
                    <p class="admin-note" style="margin-bottom:12px;">
                        「章を追加」で枠を増やせます。各章は<strong>章タイトル（プレーンテキスト）</strong>と<strong>本文（Markdown）</strong>です。タイトルも本文も空の枠は保存時に除外されます。
                    </p>

                    <div id="chapters-list">
                        @foreach($oldChapters as $i => $ch)
                            <div class="policy-chapter-row" style="border:1px solid var(--admin-line);border-radius:14px;padding:14px;margin-bottom:14px;background:rgba(255,255,255,0.02);">
                                <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:10px;">
                                    <span style="color:var(--admin-gold);font-size:.78rem;font-weight:700;letter-spacing:.06em;">第<span class="policy-chapter-no">{{ $i + 1 }}</span>章</span>
                                    <button type="button" class="policy-chapter-remove" style="background:none;border:0;cursor:pointer;color:var(--admin-red);font-size:.78rem;">
                                        <i class="fas fa-trash"></i> 削除
                                    </button>
                                </div>
                                <div class="admin-form-row">
                                    <label class="admin-label">章タイトル</label>
                                    <input type="text" name="chapters[{{ $i }}][title]" class="admin-input"
                                        value="{{ is_array($ch) ? ($ch['title'] ?? '') : '' }}" maxlength="200">
                                </div>
                                <div class="admin-form-row" style="margin-bottom:0;">
                                    <label class="admin-label">本文（Markdown）</label>
                                    <textarea name="chapters[{{ $i }}][body]" class="admin-input" rows="8" placeholder="見出し・箇条書き・太字など">{{ is_array($ch) ? ($ch['body'] ?? '') : '' }}</textarea>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <template id="chapter-template">
                        <div class="policy-chapter-row" style="border:1px solid var(--admin-line);border-radius:14px;padding:14px;margin-bottom:14px;background:rgba(255,255,255,0.02);">
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:10px;">
                                <span style="color:var(--admin-gold);font-size:.78rem;font-weight:700;letter-spacing:.06em;">第<span class="policy-chapter-no">__NO__</span>章</span>
                                <button type="button" class="policy-chapter-remove" style="background:none;border:0;cursor:pointer;color:var(--admin-red);font-size:.78rem;">
                                    <i class="fas fa-trash"></i> 削除
                                </button>
                            </div>
                            <div class="admin-form-row">
                                <label class="admin-label">章タイトル</label>
                                <input type="text" name="chapters[__INDEX__][title]" class="admin-input" maxlength="200">
                            </div>
                            <div class="admin-form-row" style="margin-bottom:0;">
                                <label class="admin-label">本文（Markdown）</label>
                                <textarea name="chapters[__INDEX__][body]" class="admin-input" rows="8" placeholder="見出し・箇条書き・太字など"></textarea>
                            </div>
                        </div>
                    </template>
                </div>
            @endunless

            <div class="admin-panel" style="border-color: rgba(230,208,128,.3);">
                <h2 class="admin-panel-title">更新者情報（必須）</h2>
                <p class="admin-note" style="margin-bottom:12px;">
                    更新者と日時は履歴に記録されます。誰が更新したかを後から追えるよう、必ず正確な氏名を入力してください。
                </p>

                <div class="admin-form-row">
                    <label class="admin-label">更新者名（記録用）<span style="color:var(--admin-red);">*</span></label>
                    <input type="text" name="updater_name" class="admin-input"
                        value="{{ old('updater_name', optional(auth()->guard('admin')->user())->name) }}"
                        required maxlength="120" placeholder="例: 管理者 太郎">
                </div>

                <div class="admin-form-row">
                    <label class="admin-label">更新内容メモ（任意）</label>
                    <textarea name="change_summary" class="admin-input" rows="3"
                        placeholder="変更内容の概要（例: 第3条の表現修正、誤字修正 など）">{{ old('change_summary') }}</textarea>
                </div>

                <div class="admin-form-row" style="margin-bottom:0;">
                    <label style="display:flex;align-items:flex-start;gap:8px;font-size:.86rem;color:var(--admin-text);">
                        <input type="checkbox" name="confirm_unlock" value="1" style="margin-top:4px;" {{ old('confirm_unlock') ? 'checked' : '' }}>
                        <span>
                            <strong>ロックを解除して更新する</strong><br>
                            <span style="color:var(--admin-muted);font-size:.78rem;">本チェックを入れた場合のみ、保存処理が実行されます。保存後は再ロックされます。</span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="admin-form-actions" style="justify-content:space-between;">
                <a href="{{ route('admin.policies.show', ['key' => $document->key]) }}" class="btn-action manage" style="background:transparent;border-color:var(--admin-line);color:var(--admin-text);">
                    キャンセル
                </a>
                <button type="submit" class="btn-action manage">
                    <i class="fas fa-save"></i> 更新する（履歴に記録）
                </button>
            </div>
        </form>
    </div>
@endsection

@push('admin-scripts')
<script>
    (function () {
        var listEl = document.getElementById('chapters-list');
        var addBtn = document.getElementById('add-chapter-btn');
        var tmplEl = document.getElementById('chapter-template');

        if (!listEl || !addBtn || !tmplEl) return;

        function renumber() {
            var rows = listEl.querySelectorAll('.policy-chapter-row');
            rows.forEach(function (row, idx) {
                var noEl = row.querySelector('.policy-chapter-no');
                if (noEl) noEl.textContent = String(idx + 1);
                row.querySelectorAll('input,textarea').forEach(function (input) {
                    if (!input.name) return;
                    input.name = input.name.replace(/chapters\[(\d+|__INDEX__)\]/, 'chapters[' + idx + ']');
                });
            });
        }

        addBtn.addEventListener('click', function () {
            var html = tmplEl.innerHTML
                .replaceAll('__INDEX__', String(listEl.querySelectorAll('.policy-chapter-row').length))
                .replaceAll('__NO__', String(listEl.querySelectorAll('.policy-chapter-row').length + 1));
            var wrap = document.createElement('div');
            wrap.innerHTML = html.trim();
            var node = wrap.firstChild;
            listEl.appendChild(node);
            renumber();
        });

        listEl.addEventListener('click', function (event) {
            var btn = event.target.closest('.policy-chapter-remove');
            if (!btn) return;
            if (!confirm('この章を削除しますか？（保存するまで反映されません）')) return;
            var row = btn.closest('.policy-chapter-row');
            if (row) row.remove();
            renumber();
        });

        var form = document.getElementById('policy-edit-form');
        if (form) {
            form.addEventListener('submit', function (event) {
                var nameInput = form.querySelector('input[name="updater_name"]');
                var unlock = form.querySelector('input[name="confirm_unlock"]');
                if (!nameInput || nameInput.value.trim() === '') {
                    alert('更新者名を入力してください。');
                    event.preventDefault();
                    return;
                }
                if (!unlock || !unlock.checked) {
                    alert('「ロックを解除して更新する」のチェックを入れてください。');
                    event.preventDefault();
                    return;
                }
                if (!confirm('更新者: ' + nameInput.value + ' として保存します。よろしいですか？')) {
                    event.preventDefault();
                }
            });
        }
    })();
</script>
@endpush
