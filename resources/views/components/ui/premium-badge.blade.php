{{-- 優良店バッヂ（全画面共通デザイン）
     使い方: <x-ui.premium-badge />（通常） / <x-ui.premium-badge size="sm" />（一覧行など）
     off: 未取得状態（店舗マイページの自店舗表示用） --}}
@props(['size' => 'md', 'off' => false, 'label' => '優良店'])
<span {{ $attributes->merge(['class' => 'premium-badge premium-badge--' . $size . ($off ? ' premium-badge--off' : '')]) }}
      role="img" aria-label="{{ $label }}"
      title="優良店：過去3ヶ月の採用ボーナス請求をすべて期日内に入金した店舗">
    <i class="fas fa-crown" aria-hidden="true"></i><span class="premium-badge__label">{{ $label }}</span>
</span>
