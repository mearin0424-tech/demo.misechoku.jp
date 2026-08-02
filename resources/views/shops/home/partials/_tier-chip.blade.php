{{-- キャスト向けカードに表示する Tier チップ（DISCOVERY 用）
     Tier A（今すぐ入れる宣言中）→ 金色ピル + ⚡
     Tier B（オンライン中：直近ログイン & 位置あり）→ 緑ピル + ●

     引数（$tierChipItem として渡す配列）:
       - availability_active: bool                          Tier A 判定
       - availability_remaining_label: string|null          "残り2時間" など
       - is_online_now: bool                                Tier B 判定
       - distance_label: string|null                        "3.4km" など（あれば末尾に付与）

     チップは A/B いずれも該当しない場合は何も出力しない --}}
@php
    $tc = $tierChipItem ?? [];
    $tcActive   = !empty($tc['availability_active']);
    $tcOnline   = !empty($tc['is_online_now']);
    $tcRemain   = (string) ($tc['availability_remaining_label'] ?? '');
    $tcDistance = (string) ($tc['distance_label'] ?? '');
@endphp
@if($tcActive)
    <div class="tier-chip-row">
        <span class="tier-chip tier-chip--now">
            <i class="fas fa-bolt" aria-hidden="true"></i>
            今すぐ入れる{{ $tcRemain !== '' ? '・' . $tcRemain : '' }}@if($tcDistance !== '')・{{ $tcDistance }}@endif
        </span>
    </div>
@elseif($tcOnline)
    <div class="tier-chip-row">
        <span class="tier-chip tier-chip--online">
            <i class="fas fa-circle" aria-hidden="true"></i>
            オンライン中@if($tcDistance !== '')・{{ $tcDistance }}@endif
        </span>
    </div>
@endif
