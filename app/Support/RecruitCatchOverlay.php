<?php

declare(strict_types=1);

namespace App\Support;

/**
 * 求人カード／求人票メイン画像上のキャッチ表示（catch_copy ベース。pr は含めない）
 */
final class RecruitCatchOverlay
{
    /**
     * @param  array<string, mixed>  $meta  catch_copy, bonus_condition, bonus_other_conditions 等
     * @return array{show: bool, line1_html: string, line2: string, badge: string}
     */
    public static function buildFromMeta(array $meta, int $norumaReward, bool $forDiscoverySwipe = false): array
    {
        if ($forDiscoverySwipe) {
            return self::buildForDiscoverySwipe($meta);
        }

        $catch = trim((string) ($meta['catch_copy'] ?? ''));
        $line1 = '';
        $line2 = '';
        if ($catch !== '') {
            $parts = preg_split("/\r\n|\r|\n/u", $catch, 2);
            $line1 = trim((string) ($parts[0] ?? ''));
            $line2 = trim((string) ($parts[1] ?? ''));
        }
        $line1 = mb_strimwidth($line1, 0, 80, '…');
        $line2 = $line2 !== '' ? mb_strimwidth($line2, 0, 72, '…') : '';

        $bonusTxt = trim((string) ($meta['bonus_other_conditions'] ?? $meta['bonus_condition'] ?? ''));
        $badge = $bonusTxt !== '' ? mb_strimwidth($bonusTxt, 0, 56, '…') : '';
        if ($badge !== '' && (str_contains($badge, 'ご利用プラン') || str_contains($badge, '利用プラン'))) {
            $badge = '';
        }
        if ($badge === '' && $norumaReward > 0) {
            $badge = '💰 入店祝い金 ¥' . number_format($norumaReward) . '〜';
        }

        if ($line2 !== '' && str_contains($line2, 'ご利用プラン')) {
            $line2 = '';
        }
        if ($line1 !== '' && str_contains($line1, 'ご利用プラン')) {
            $line1 = '';
        }
        if ($line1 === '' && $line2 !== '') {
            $line1 = $line2;
            $line2 = '';
        }

        $show = $line1 !== '' || $line2 !== '' || $badge !== '';

        return [
            'show' => $show,
            'line1_html' => self::formatHighlights($line1),
            'line2' => $line2,
            'badge' => $badge,
        ];
    }

    /**
     * ディスカバリー求人カード用：キャッチコピーのみ（改行は &lt;br&gt;）、バッジ・第2ブロックなし
     *
     * @param  array<string, mixed>  $meta
     * @return array{show: bool, line1_html: string, line2: string, badge: string}
     */
    private static function buildForDiscoverySwipe(array $meta): array
    {
        $catch = trim((string) ($meta['catch_copy'] ?? ''));
        $chunks = [];
        if ($catch !== '') {
            $parts = preg_split("/\r\n|\r|\n/u", $catch);
            foreach ($parts as $part) {
                $t = trim((string) $part);
                if ($t === '' || str_contains($t, 'ご利用プラン') || str_contains($t, '利用プラン')) {
                    continue;
                }
                $chunks[] = self::formatHighlights(mb_strimwidth($t, 0, 100, '…'));
            }
        }
        $line1Html = implode('<br>', array_filter($chunks, static fn ($h) => $h !== ''));

        return [
            'show' => $line1Html !== '',
            'line1_html' => $line1Html,
            'line2' => '',
            'badge' => '',
        ];
    }

    /**
     * catch_copy 内の **強調** を黄色表示用の span に変換（Blade で {!! !!} 利用前提）
     */
    public static function formatHighlights(string $line): string
    {
        if ($line === '') {
            return '';
        }
        if (!preg_match('/\*\*.+?\*\*/us', $line)) {
            return e($line);
        }
        $segments = preg_split('/(\*\*.+?\*\*)/us', $line, -1, PREG_SPLIT_DELIM_CAPTURE);
        $out = '';
        foreach ($segments as $seg) {
            if ($seg === '') {
                continue;
            }
            if (preg_match('/^\*\*(.+)\*\*$/us', $seg, $m)) {
                $out .= '<span class="rc-msg-em">' . e($m[1]) . '</span>';
            } else {
                $out .= e($seg);
            }
        }

        return $out;
    }
}
