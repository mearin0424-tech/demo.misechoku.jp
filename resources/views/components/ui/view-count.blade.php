{{-- プロフィール閲覧数の共通表示（メダル付き）
     使い方: <x-ui.view-count :count="$viewCount" />
     仕様:
       - 表示単位は M（=千）: 1,000 → 1M / 5,500 → 5.5M / 12,000 → 12M（1,000未満は実数）
       - メダル: 1,000以上=銅 / 5,000以上=銀 / 10,000以上=金（未満は目のアイコン）
--}}
@props(['count' => 0])
@php
    $count = (int) $count;
    $tier = $count >= 10000 ? 'gold' : ($count >= 5000 ? 'silver' : ($count >= 1000 ? 'bronze' : null));
    if ($count >= 1000) {
        // 千単位（M）。小数1桁、末尾の .0 は省く
        $display = rtrim(rtrim(number_format($count / 1000, 1), '0'), '.') . 'M';
    } else {
        $display = number_format($count);
    }
@endphp
<span {{ $attributes->merge(['class' => 'view-count']) }}
      title="プロフィールが閲覧された回数：{{ number_format($count) }}回">
    @if($tier)
        <span class="view-medal view-medal--{{ $tier }}" aria-hidden="true"><i class="fas fa-medal"></i></span>
    @else
        <i class="fas fa-eye view-count__eye" aria-hidden="true"></i>
    @endif
    <span class="view-count__num">{{ $display }}</span><span class="view-count__unit">閲覧</span>
</span>
