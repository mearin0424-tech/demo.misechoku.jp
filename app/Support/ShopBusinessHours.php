<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

/**
 * shop_profiles.open_time / close_is_last / close_time の表示・正規化
 *
 * 例: 20:00～翌4:00 → open 20:00, close_is_last=0, close 04:00
 *     20:00～LAST   → open 20:00, close_is_last=1, close NULL
 *     未設定        → すべて NULL / close_is_last=0
 */
final class ShopBusinessHours
{
    public static function formatTimeHhmm(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        $s = (string) $value;

        return preg_match('/^(\d{2}:\d{2})/', $s, $m) ? $m[1] : '';
    }

    /**
     * 画面表示用（例: 20:00～翌4:00 / 20:00～LAST）
     */
    public static function formatDisplay(?string $openRaw, ?int $closeIsLast, ?string $closeRaw): string
    {
        $open = self::formatTimeHhmm($openRaw ?? null);
        if ($open === '') {
            return '';
        }
        if ((int) ($closeIsLast ?? 0) === 1) {
            return $open . '～LAST';
        }
        $close = self::formatTimeHhmm($closeRaw ?? null);
        if ($close === '') {
            return $open . '～';
        }
        $overnight = strcmp($close, $open) < 0;

        return $open . '～' . ($overnight ? '翌' : '') . self::formatClockHumane($close);
    }

    /**
     * 時刻ラベル（先頭ゼロを落としつつ分は2桁 — 例 4:00, 20:00）
     */
    public static function formatClockHumane(string $hhmm): string
    {
        $parts = explode(':', $hhmm);
        $h = (int) ($parts[0] ?? 0);
        $m = (int) ($parts[1] ?? 0);

        return $m > 0 ? sprintf('%d:%02d', $h, $m) : sprintf('%d:00', $h);
    }

    /**
     * @return array{open_time: ?string, close_is_last: int, close_time: ?string}
     *             open_time/close_time は DB 用に HH:MM:SS または null
     */
    public static function normalizeFromRequest(string $openInput, bool $closeLast, string $closeInput): array
    {
        $openTrim = trim($openInput);
        if ($openTrim === '' || !preg_match('/^\d{2}:\d{2}$/', $openTrim)) {
            return [
                'open_time' => null,
                'close_is_last' => 0,
                'close_time' => null,
            ];
        }
        if ($closeLast) {
            return [
                'open_time' => $openTrim . ':00',
                'close_is_last' => 1,
                'close_time' => null,
            ];
        }
        $closeTrim = trim($closeInput);
        if ($closeTrim === '' || !preg_match('/^\d{2}:\d{2}$/', $closeTrim)) {
            throw ValidationException::withMessages([
                'business_close' => '閉店時刻を入力するか、LAST を選択してください。',
            ]);
        }

        return [
            'open_time' => $openTrim . ':00',
            'close_is_last' => 0,
            'close_time' => $closeTrim . ':00',
        ];
    }
}
