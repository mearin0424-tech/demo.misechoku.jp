{{-- 本人確認書類カテゴリごとのアップロードフォーム
     必要パラメータ:
        category    : 'photo_id' / 'non_photo_id' / 'address_proof'
        sectionTitle: 表示するセクション見出し
        currentDoc  : 既存の提出書類（mapCastDocument の結果） or null
        allowedTypes: 選択可能な type 値の配列
        typeLabels  : type → 日本語ラベル の配列
        showExpiry  : 有効期限欄を表示するか
        requireBack : 裏面アップロードを必須にするか
--}}
@php
    $statusKey = $currentDoc['status_key'] ?? null;
    $statusPillClass = match ($statusKey) {
        'approved' => 'is-approved',
        'rejected' => 'is-rejected',
        'pending'  => 'is-pending',
        default    => '',
    };
@endphp
<div class="identity-form-section">
    <div class="identity-form-section__head">
        <span class="identity-form-section__title">{{ $sectionTitle }}</span>
        @if($currentDoc)
            <span class="identity-form-section__pill {{ $statusPillClass }}">
                {{ $currentDoc['status_label'] ?? '提出済み' }}
            </span>
        @else
            <span class="identity-form-section__pill">未提出</span>
        @endif
    </div>

    @if($currentDoc && !empty($currentDoc['ng_reason']))
        <div class="management-summary-note" style="margin-bottom:10px; color:#ffb4b4;">
            差し戻し理由：{{ $currentDoc['ng_reason'] }}
        </div>
    @endif

    @if($currentDoc)
        <div class="text-xs" style="margin-bottom:10px; color:#C9B8B8;">
            提出済み：<strong style="color:#e6dffc;">{{ $currentDoc['type_label'] ?? '' }}</strong>
            @if(!empty($currentDoc['updated_at_label']))
                <span style="margin-left:8px;">更新: {{ $currentDoc['updated_at_label'] }}</span>
            @endif
        </div>
    @endif

    <form class="management-bank-form cast-identity-form" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="category" value="{{ $category }}">

        <div class="bank-form-row">
            <label class="bank-label">書類種別</label>
            <select name="type" class="bank-input" required>
                @foreach($allowedTypes as $t)
                    <option value="{{ $t }}" @selected(($currentDoc['type'] ?? null) === $t)>
                        {{ $typeLabels[$t] ?? $t }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="bank-form-row">
            <label class="bank-label">表面（画像 or PDF）</label>
            <label for="front_file" class="file-upload-btn">
                <i class="fas fa-upload"></i> ファイルを選択
            </label>
            <span class="file-name-display" id="front_file_name">選択されていません</span>
            <input type="file" id="front_file" name="front_file" class="bank-input visually-hidden" accept=".pdf,image/*" required>
        </div>
        <div class="bank-form-row">
            <label class="bank-label">裏面（{{ $requireBack ? '必須' : '任意' }}）</label>
            <label for="back_file" class="file-upload-btn">
                <i class="fas fa-upload"></i> ファイルを選択
            </label>
            <span class="file-name-display" id="back_file_name">選択されていません</span>
            <input type="file" id="back_file" name="back_file" class="bank-input visually-hidden" accept=".pdf,image/*" @if($requireBack) required @endif>
        </div>
        @if($showExpiry)
            <div class="bank-form-row">
                <label class="bank-label">有効期限（任意）</label>
                <input type="date" name="expired_at" class="bank-input">
            </div>
        @endif
        <p class="cast-identity-error" role="alert" hidden></p>
        <p class="cast-identity-success" role="status" hidden></p>
        <div class="text-right" style="margin-top:10px;">
            <button type="submit" class="btn-action manage">
                <i class="fas fa-upload"></i>
                {{ $currentDoc ? '差し替えてアップロード' : 'アップロード' }}
            </button>
        </div>
    </form>
</div>
