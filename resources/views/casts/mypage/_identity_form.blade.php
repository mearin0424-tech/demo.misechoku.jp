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
    // draft: アップロード済みだが「提出」ボタン未押下（=審査待ち行列に入っていない）
    $isDraft = $statusKey === 'draft';
    $isPending = $statusKey === 'pending';
    $frontId = $category . '_front_file';
    $backId  = $category . '_back_file';
@endphp
<section class="doc-card">
    <header class="doc-card__head">
        <div class="doc-card__head-body">
            <h3 class="doc-card__title">{{ $sectionTitle }}</h3>
            <p class="doc-card__meta">
                @if($currentDoc)
                    <strong>{{ $currentDoc['type_label'] ?? '書類' }}</strong>@if(!empty($currentDoc['updated_at_label']))・{{ $currentDoc['updated_at_label'] }}更新@endif
                @else
                    まだ提出されていません
                @endif
            </p>
        </div>
        <span class="doc-card__pill {{ $statusPillClass }}">
            {{ $currentDoc ? ($currentDoc['status_label'] ?? '提出済み') : '未提出' }}
        </span>
    </header>

    @if($currentDoc && !empty($currentDoc['ng_reason']))
        <p class="doc-card__ng"><i class="fas fa-circle-exclamation"></i> 差し戻し理由：{{ $currentDoc['ng_reason'] }}</p>
    @endif

    <form class="doc-form cast-identity-form" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="category" value="{{ $category }}">

        <div class="doc-form__field">
            <label class="doc-form__label" for="{{ $category }}_type">書類種別</label>
            <select id="{{ $category }}_type" name="type" class="doc-form__select bank-input" required>
                @foreach($allowedTypes as $t)
                    <option value="{{ $t }}" @selected(($currentDoc['type'] ?? null) === $t)>
                        {{ $typeLabels[$t] ?? $t }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="doc-form__field">
            <label class="doc-form__label">表面 <span class="doc-form__req">必須</span></label>
            <label for="{{ $frontId }}" class="doc-form__drop">
                <span class="doc-form__drop-icon"><i class="fas fa-cloud-arrow-up"></i></span>
                <span class="doc-form__drop-text">
                    <span class="doc-form__drop-name" id="{{ $frontId }}_name">タップしてファイルを選択</span>
                    <small>画像 / PDF・最大 8MB</small>
                </span>
            </label>
            <img class="doc-form__preview" id="{{ $frontId }}_preview" alt="表面プレビュー" hidden>
            <span class="doc-form__pdf-chip" id="{{ $frontId }}_pdf" hidden><i class="fas fa-file-pdf"></i><span></span></span>
            <input type="file" id="{{ $frontId }}" name="front_file" class="bank-input visually-hidden" accept=".pdf,image/*" required>
        </div>

        <div class="doc-form__field">
            <label class="doc-form__label">裏面 <span class="doc-form__req {{ $requireBack ? '' : 'is-optional' }}">{{ $requireBack ? '必須' : '任意' }}</span></label>
            <label for="{{ $backId }}" class="doc-form__drop">
                <span class="doc-form__drop-icon"><i class="fas fa-cloud-arrow-up"></i></span>
                <span class="doc-form__drop-text">
                    <span class="doc-form__drop-name" id="{{ $backId }}_name">タップしてファイルを選択</span>
                    <small>裏面がある書類のみ</small>
                </span>
            </label>
            <img class="doc-form__preview" id="{{ $backId }}_preview" alt="裏面プレビュー" hidden>
            <span class="doc-form__pdf-chip" id="{{ $backId }}_pdf" hidden><i class="fas fa-file-pdf"></i><span></span></span>
            <input type="file" id="{{ $backId }}" name="back_file" class="bank-input visually-hidden" accept=".pdf,image/*" @if($requireBack) required @endif>
        </div>

        @if($showExpiry)
            <div class="doc-form__field">
                <label class="doc-form__label" for="{{ $category }}_exp">有効期限 <span class="doc-form__req is-optional">任意</span></label>
                <input type="date" id="{{ $category }}_exp" name="expired_at" class="doc-form__input bank-input">
            </div>
        @endif

        {{-- アップロード完了バナー：ファイル選択後、サーバに draft 保存が完了したら表示 --}}
        <p class="cast-identity-upload-status" data-cast-upload-status hidden>
            <i class="fas fa-cloud-check"></i>
            <span data-cast-upload-status-text>アップロード完了。下の「運営に提出する」ボタンで審査依頼できます。</span>
        </p>

        <p class="cast-identity-error" role="alert" hidden></p>
        <p class="cast-identity-success" role="status" hidden></p>

        {{-- 2 段階フロー：
             1. 表面（+ 任意の裏面）を選択→自動アップロード（draft として保存）
             2. 「運営に提出する」ボタン押下→ DRAFT→PENDING の明示的アクション --}}
        <button type="submit" class="doc-form__submit" data-cast-submit-btn @if(!$isDraft) disabled @endif>
            <i class="fas fa-paper-plane"></i>
            運営に提出する
        </button>
        <p class="cast-identity-submit-hint" data-cast-submit-hint>
            <i class="fas fa-info-circle" aria-hidden="true"></i>
            ファイル選択後、明示的に「運営に提出する」ボタンを押すまで審査は始まりません。
        </p>
    </form>
</section>
