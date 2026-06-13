{{--
    金融機関・支店の datalist 補完付き口座フィールド（form に data-bank-autocomplete が必要）
    @param string $variant 'admin' | 'management'
    @param array $bankValues
    @param string|null $bankListId datalist id（management 時は必須推奨）
    @param string|null $branchListId
    @param string|null $inputIdPrefix management 時: label の for 用（例: cast-bank-modal）
--}}
@php
    $variant = $variant ?? 'admin';
    $isManagement = $variant === 'management';
    $rowClass = $isManagement ? 'bank-form-row' : 'admin-form-row';
    $labelClass = $isManagement ? 'bank-label' : 'admin-label';
    $inputClass = $isManagement ? 'bank-input' : 'admin-input';
    $bankListId = $bankListId ?? 'admin-bank-suggestions';
    $branchListId = $branchListId ?? 'admin-branch-suggestions';
    $prefix = $inputIdPrefix ?? null;
    $v = is_array($bankValues ?? null) ? $bankValues : [];

    $bankName = old('bank_name', $v['bank_name'] ?? '');
    $bankCode = old('bank_code', $v['bank_code'] ?? '');
    $branchName = old('branch_name', $v['branch_name'] ?? '');
    $branchCode = old('branch_code', $v['branch_code'] ?? '');
    $accountType = old('account_type', $v['account_type'] ?? 'ordinary');
    $accountNumber = old('account_number', $v['account_number'] ?? '');
    $accountName = old('account_name', $v['account_name'] ?? $v['account_holder_name'] ?? '');

    $idBank = $prefix ? $prefix . '-bank' : null;
    $idBranch = $prefix ? $prefix . '-branch' : null;
    $idType = $prefix ? $prefix . '-type' : null;
    $idNumber = $prefix ? $prefix . '-number' : null;
    $idName = $prefix ? $prefix . '-name' : null;
@endphp
<div class="{{ $rowClass }}">
    <label class="{{ $labelClass }}" @if($idBank) for="{{ $idBank }}" @endif>金融機関名</label>
    <input
        @if($idBank) id="{{ $idBank }}" @endif
        type="text"
        name="bank_name"
        class="{{ $inputClass }}"
        value="{{ $bankName }}"
        placeholder="{{ $isManagement ? '例: みずほ銀行' : '' }}"
        autocomplete="off"
        list="{{ $bankListId }}"
        data-bank-name-input
        required
    >
    <input type="hidden" name="bank_code" value="{{ $bankCode }}" data-bank-code-input>
    <datalist id="{{ $bankListId }}" data-bank-list></datalist>
</div>
<div class="{{ $rowClass }}">
    <label class="{{ $labelClass }}" @if($idBranch) for="{{ $idBranch }}" @endif>支店名</label>
    <input
        @if($idBranch) id="{{ $idBranch }}" @endif
        type="text"
        name="branch_name"
        class="{{ $inputClass }}"
        value="{{ $branchName }}"
        placeholder="{{ $isManagement ? '例: 渋谷支店' : '' }}"
        autocomplete="off"
        list="{{ $branchListId }}"
        data-branch-name-input
        required
    >
    <input type="hidden" name="branch_code" value="{{ $branchCode }}" data-branch-code-input>
    <datalist id="{{ $branchListId }}" data-branch-list></datalist>
</div>
<div class="{{ $rowClass }}">
    <label class="{{ $labelClass }}" @if($idType) for="{{ $idType }}" @endif>口座種別</label>
    <select @if($idType) id="{{ $idType }}" @endif name="account_type" class="{{ $inputClass }}" required>
        <option value="ordinary" {{ $accountType === 'ordinary' ? 'selected' : '' }}>普通</option>
        <option value="current" {{ $accountType === 'current' ? 'selected' : '' }}>当座</option>
    </select>
</div>
<div class="{{ $rowClass }}">
    <label class="{{ $labelClass }}" @if($idNumber) for="{{ $idNumber }}" @endif>{{ $isManagement ? '口座番号 (7桁)' : '口座番号' }}</label>
    <input
        @if($idNumber) id="{{ $idNumber }}" @endif
        type="text"
        name="account_number"
        class="{{ $inputClass }}"
        value="{{ $accountNumber }}"
        placeholder="{{ $isManagement ? '1234567' : '' }}"
        inputmode="numeric"
        maxlength="8"
        pattern="[0-9]*"
        data-account-number-input
        required
    >
    @unless($isManagement)
        <small style="display:block; margin-top:6px; color:#a0a0a0;">口座番号は7桁または8桁の数字で入力してください。</small>
    @endunless
</div>
<div class="{{ $rowClass }}">
    <label class="{{ $labelClass }}" @if($idName) for="{{ $idName }}" @endif>{{ $isManagement ? '口座名義 (全角カナ)' : '口座名義（カナ）' }}</label>
    <input
        @if($idName) id="{{ $idName }}" @endif
        type="text"
        name="account_name"
        class="{{ $inputClass }}"
        value="{{ $accountName }}"
        placeholder="{{ $isManagement ? '例: ヤマダ タロウ' : 'ヤマダタロウ' }}"
        required
    >
    @unless($isManagement)
        <small style="display:block; margin-top:6px; color:#a0a0a0;">銀行側の登録カナ表記に合わせて入力してください。</small>
    @endunless
</div>
