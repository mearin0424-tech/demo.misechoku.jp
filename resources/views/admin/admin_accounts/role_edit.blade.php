@extends('layouts.admin')

@section('title', 'ロール権限編集 — ' . ($roleMeta['label'] ?? $role))

@section('content')
<div class="admin-page">
    <div class="u-flex-between u-flex-wrap u-gap-12">
        @include('admin.parts.page-title', ['eyebrow' => 'ROLE PERMISSIONS', 'title' => 'ロール権限：' . ($roleMeta['label'] ?? $role)])
        <a href="{{ route('admin.admin-accounts.index') }}" class="btn-action btn-action-secondary">
            <i class="fas fa-arrow-left"></i> 一覧へ戻る
        </a>
    </div>

    @if(session('status'))
        <div class="admin-alert admin-alert-success">{{ session('status') }}</div>
    @endif

    <section class="admin-panel">
        <p class="admin-note u-mb-12">{{ $roleMeta['description'] ?? '' }}</p>
        @if($isLocked)
            <div class="admin-alert admin-alert-warning u-mb-12">
                <i class="fas fa-lock"></i>
                このロールはシステム上ロックされています。権限はすべて自動的に付与され、変更はできません。
            </div>
        @endif

        <form method="POST" action="{{ route('admin.admin-accounts.roles.update', $role) }}" id="role-permission-form">
            @csrf
            @method('PUT')

            @if(!$isLocked)
                <div class="role-permission-toolbar">
                    <button type="button" class="btn-action btn-action-secondary" data-action="select-all">
                        <i class="fas fa-check-double"></i> すべて選択
                    </button>
                    <button type="button" class="btn-action btn-action-secondary" data-action="clear-all">
                        <i class="fas fa-eraser"></i> すべて解除
                    </button>
                    <button type="button" class="btn-action btn-action-secondary" data-action="reset-default" data-defaults='@json($defaultKeys)'>
                        <i class="fas fa-rotate-left"></i> デフォルトに戻す
                    </button>
                </div>
            @endif

            <div class="role-permission-groups">
                @foreach($permissionCatalog as $groupLabel => $caps)
                    <fieldset class="role-permission-group">
                        <legend class="role-permission-group__title">{{ $groupLabel }}</legend>
                        <div class="role-permission-group__items">
                            @foreach($caps as $cap)
                                @php $checked = in_array($cap['key'], $grantedKeys, true); @endphp
                                <label class="role-permission-item {{ $isLocked ? 'is-disabled' : '' }}">
                                    <input type="checkbox"
                                           name="permissions[]"
                                           value="{{ $cap['key'] }}"
                                           @if($checked) checked @endif
                                           @if($isLocked) disabled @endif>
                                    <span class="role-permission-item__body">
                                        <span class="role-permission-item__label">{{ $cap['label'] }}</span>
                                        <span class="role-permission-item__desc">{{ $cap['description'] }}</span>
                                        <code class="role-permission-item__key">{{ $cap['key'] }}</code>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                @endforeach
            </div>

            @if(!$isLocked)
                <div class="u-flex u-gap-8 u-mt-24">
                    <button type="submit" class="btn-action manage">
                        <i class="fas fa-floppy-disk"></i> 権限を保存
                    </button>
                    <a href="{{ route('admin.admin-accounts.index') }}" class="btn-action btn-action-secondary">キャンセル</a>
                </div>
            @endif
        </form>
    </section>
</div>

@push('admin-scripts')
<script>
(function () {
    var form = document.getElementById('role-permission-form');
    if (!form) return;
    function setAll(value) {
        form.querySelectorAll('input[type=checkbox][name="permissions[]"]:not([disabled])').forEach(function (cb) {
            cb.checked = value;
        });
    }
    var btnSelectAll = form.querySelector('[data-action="select-all"]');
    var btnClearAll = form.querySelector('[data-action="clear-all"]');
    var btnResetDefault = form.querySelector('[data-action="reset-default"]');
    if (btnSelectAll) btnSelectAll.addEventListener('click', function () { setAll(true); });
    if (btnClearAll) btnClearAll.addEventListener('click', function () { setAll(false); });
    if (btnResetDefault) btnResetDefault.addEventListener('click', function () {
        var defaults = [];
        try { defaults = JSON.parse(btnResetDefault.getAttribute('data-defaults') || '[]'); } catch (e) {}
        setAll(false);
        form.querySelectorAll('input[type=checkbox][name="permissions[]"]:not([disabled])').forEach(function (cb) {
            if (defaults.indexOf(cb.value) !== -1) cb.checked = true;
        });
    });
})();
</script>
@endpush
@endsection
