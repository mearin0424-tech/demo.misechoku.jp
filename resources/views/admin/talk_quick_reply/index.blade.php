@extends('layouts.admin')

@section('title', 'クイック定型文マスタ')

@section('content')
<div class="admin-page">
    @include('admin.parts.page-title', [
        'eyebrow' => 'TALK QUICK REPLY',
        'title' => 'トーククイック定型文マスタ',
        'info' => '
            <p>トークルーム下部のクイック定型文パネルに表示される候補文を、<strong>役割 (キャスト / 店舗) × 応募ステータス</strong>ごとに管理できます。</p>
            <ul>
                <li>並び順はそのまま画面上の表示順になります (ドラッグ不要・上から順に保存)。</li>
                <li>本文が空欄の行は保存時に削除扱いになります。「削除」チェックでも同様。</li>
                <li>グループを空にすると、コード内の既定値 (SPEC 準拠) にフォールバックします。</li>
            </ul>
        ',
    ])

    @if (session('status'))
        <div class="admin-alert admin-alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.talk-quick-replies.update') }}" id="tqr-form">
        @csrf
        @method('PUT')

        @foreach($groups as $group)
            @php
                $groupKey = $group['owner_type'] . '|' . $group['status_code'];
                $isDefault = collect($group['rows'])->every(fn ($r) => !empty($r['is_default']));
            @endphp
            <section class="admin-card admin-card-wide tqr-group" data-group-key="{{ $groupKey }}">
                <div class="admin-card-head">
                    <div>
                        <h2>
                            <span class="tqr-group__owner tqr-group__owner--{{ $group['owner_type'] }}">{{ $group['owner_label'] }}</span>
                            <span class="tqr-group__status">{{ $group['status_label'] }}</span>
                            <span class="tqr-group__count">（{{ count($group['rows']) }}件）</span>
                        </h2>
                        @if($isDefault)
                            <p class="tqr-group__hint"><i class="fas fa-info-circle"></i> 既定値を表示中（DB 未登録）</p>
                        @endif
                    </div>
                    <div class="tqr-group__actions">
                        <button type="button" class="btn-action ghost tqr-add-btn" data-group-key="{{ $groupKey }}">
                            <i class="fas fa-plus"></i> 追加
                        </button>
                    </div>
                </div>
                <div class="tqr-list" data-group-list="{{ $groupKey }}">
                    @foreach($group['rows'] as $index => $row)
                        <div class="tqr-item">
                            <div class="tqr-item__head">
                                <span class="tqr-item__index">{{ $index + 1 }}</span>
                                <select name="groups[{{ $groupKey }}][{{ $index }}][category]" class="tqr-item__category">
                                    @foreach($categories as $cKey => $cLabel)
                                        <option value="{{ $cKey }}" @selected($row['category'] === $cKey)>{{ $cLabel }}</option>
                                    @endforeach
                                </select>
                                <label class="tqr-item__delete">
                                    <input type="hidden" name="groups[{{ $groupKey }}][{{ $index }}][delete]" value="0">
                                    <input type="checkbox" name="groups[{{ $groupKey }}][{{ $index }}][delete]" value="1">
                                    <span>削除</span>
                                </label>
                            </div>
                            @if(!empty($row['id']))
                                <input type="hidden" name="groups[{{ $groupKey }}][{{ $index }}][id]" value="{{ $row['id'] }}">
                            @endif
                            <textarea name="groups[{{ $groupKey }}][{{ $index }}][body]"
                                      rows="2"
                                      maxlength="400"
                                      placeholder="本文（空欄で削除）">{{ $row['body'] }}</textarea>
                        </div>
                    @endforeach
                </div>

                <div class="tqr-group__reset">
                    <button type="submit"
                            form="tqr-reset-{{ $group['owner_type'] }}-{{ $group['status_code'] }}"
                            class="tqr-reset-btn"
                            onclick="return confirm('このグループの登録内容をすべて削除し、既定値に戻します。よろしいですか？')">
                        <i class="fas fa-rotate-left"></i> 既定値に戻す
                    </button>
                </div>
            </section>
        @endforeach

        <div class="tqr-save-bar">
            <button type="submit" class="btn-action manage">
                <i class="fas fa-floppy-disk"></i> 全グループを保存する
            </button>
        </div>
    </form>

    {{-- 「既定値に戻す」用のサブフォーム（1グループごと、メイン form の外に置く） --}}
    @foreach($groups as $group)
        <form id="tqr-reset-{{ $group['owner_type'] }}-{{ $group['status_code'] }}"
              method="POST"
              action="{{ route('admin.talk-quick-replies.reset') }}"
              style="display:none;">
            @csrf
            <input type="hidden" name="owner_type" value="{{ $group['owner_type'] }}">
            <input type="hidden" name="status_code" value="{{ $group['status_code'] }}">
        </form>
    @endforeach

    {{-- 「追加」ボタン用のテンプレート行 --}}
    <template id="tqr-row-template">
        <div class="tqr-item">
            <div class="tqr-item__head">
                <span class="tqr-item__index"></span>
                <select name="__NAME__[category]" class="tqr-item__category">
                    @foreach($categories as $cKey => $cLabel)
                        <option value="{{ $cKey }}">{{ $cLabel }}</option>
                    @endforeach
                </select>
                <label class="tqr-item__delete">
                    <input type="hidden" name="__NAME__[delete]" value="0">
                    <input type="checkbox" name="__NAME__[delete]" value="1">
                    <span>削除</span>
                </label>
            </div>
            <textarea name="__NAME__[body]" rows="2" maxlength="400" placeholder="本文（空欄で削除）"></textarea>
        </div>
    </template>
</div>
@endsection

@push('admin-styles')
<style>
.tqr-group__owner {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 700;
    margin-right: 8px;
    letter-spacing: 0.02em;
}
.tqr-group__owner--cast { background: rgba(214, 112, 162, 0.20); color: #f0a6c4; border: 1px solid rgba(214, 112, 162, 0.40); }
.tqr-group__owner--shop { background: rgba(139, 92, 246, 0.20); color: #c4b5fd; border: 1px solid rgba(139, 92, 246, 0.40); }
.tqr-group__status { font-weight: 700; }
.tqr-group__count { color: #a1a1aa; font-weight: 400; font-size: 0.88rem; margin-left: 4px; }
.tqr-group__hint {
    margin: 6px 0 0;
    font-size: 0.78rem;
    color: #a1a1aa;
}
.tqr-group__actions { display: flex; gap: 8px; }
.tqr-group__reset { text-align: right; margin-top: 10px; }
.tqr-reset-btn {
    background: transparent;
    border: 1px solid rgba(255, 255, 255, 0.12);
    color: #a1a1aa;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 0.78rem;
    cursor: pointer;
}
.tqr-reset-btn:hover { background: rgba(255, 255, 255, 0.05); color: #fff; }

.tqr-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.tqr-item {
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 10px;
    padding: 12px 14px;
    background: rgba(255, 255, 255, 0.02);
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.tqr-item__head {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.tqr-item__index {
    display: inline-flex;
    width: 26px;
    height: 26px;
    align-items: center;
    justify-content: center;
    background: rgba(139, 92, 246, 0.20);
    color: #c4b5fd;
    border-radius: 999px;
    font-size: 0.74rem;
    font-weight: 700;
    flex: 0 0 auto;
}
.tqr-item__category {
    padding: 6px 10px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    background: rgba(255, 255, 255, 0.05);
    color: var(--admin-text);
    border-radius: 8px;
    font-size: 0.85rem;
    min-width: 140px;
}
.tqr-item__delete {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.78rem;
    color: #a1a1aa;
    cursor: pointer;
    margin-left: auto;
}
.tqr-item__delete input[type="checkbox"] { cursor: pointer; }
.tqr-item textarea {
    width: 100%;
    min-height: 56px;
    padding: 10px 12px;
    border: 1px solid rgba(168, 85, 247, 0.30);
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.05);
    font-size: 0.9rem;
    color: var(--admin-text);
    resize: vertical;
    font-family: inherit;
    line-height: 1.5;
    box-sizing: border-box;
}
.tqr-item textarea:focus {
    outline: none;
    border-color: #a78bfa;
    box-shadow: 0 0 0 3px rgba(168, 85, 247, 0.18);
}

.tqr-save-bar {
    position: sticky;
    bottom: 0;
    background: linear-gradient(180deg, rgba(10,10,10,0), rgba(10,10,10,0.96) 35%);
    padding: 16px 0 8px;
    margin-top: 8px;
    text-align: right;
    z-index: 5;
}
</style>
@endpush

@push('admin-scripts')
<script>
(function () {
    'use strict';
    const template = document.getElementById('tqr-row-template');
    if (!template) return;

    document.querySelectorAll('.tqr-add-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const groupKey = btn.getAttribute('data-group-key');
            const list = document.querySelector('[data-group-list="' + CSS.escape(groupKey) + '"]');
            if (!list) return;
            const index = list.querySelectorAll('.tqr-item').length;
            const clone = template.content.cloneNode(true);
            clone.querySelectorAll('[name]').forEach(function (el) {
                const name = el.getAttribute('name').replace(/__NAME__/g, 'groups[' + groupKey + '][' + index + ']');
                el.setAttribute('name', name);
            });
            const idxEl = clone.querySelector('.tqr-item__index');
            if (idxEl) idxEl.textContent = String(index + 1);
            list.appendChild(clone);
        });
    });
})();
</script>
@endpush
