<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * ログインユーザの「いまの探索拠点」を扱うサービス。
 *
 * 探索拠点は次の優先順で解決する：
 *   1. セッションに保存されたユーザ指定の位置（現在地 or パスポート）
 *   2. ログイン中のキャスト／店舗の自プロフィール（cast_profiles / shop_profiles の lat/lng）
 *   3. 解決不能（null）→ 距離は表示せず、距離フィルタも無効
 *
 * 距離計算は Haversine 公式（地球半径 6371km）で km 単位を返す。
 */
class UserLocationService
{
    public const SESSION_KEY = 'user_location';
    public const MODE_CURRENT = 'current';   // 端末の geolocation
    public const MODE_PASSPORT = 'passport'; // 任意位置を指定（住所→ジオコーディング）
    public const MODE_PROFILE = 'profile';   // 自プロフィールから

    /**
     * 現在の探索拠点を返す。
     *
     * @return array{lat: float, lng: float, mode: string, label: string}|null
     */
    public function getActiveLocation(): ?array
    {
        $session = (array) session(self::SESSION_KEY, []);
        if (
            isset($session['lat'], $session['lng']) &&
            is_numeric($session['lat']) && is_numeric($session['lng'])
        ) {
            return [
                'lat' => (float) $session['lat'],
                'lng' => (float) $session['lng'],
                'mode' => (string) ($session['mode'] ?? self::MODE_CURRENT),
                'label' => (string) ($session['label'] ?? '指定位置'),
            ];
        }

        // フォールバック：自プロフィールに緯度経度があればそれを使う
        $profile = $this->resolveSelfProfileLocation();
        if ($profile) {
            return $profile + ['mode' => self::MODE_PROFILE, 'label' => 'プロフィール住所'];
        }

        return null;
    }

    /**
     * セッションに位置を保存（現在地 or パスポート）。
     */
    public function setManualLocation(string $mode, float $lat, float $lng, string $label = ''): void
    {
        if (!in_array($mode, [self::MODE_CURRENT, self::MODE_PASSPORT], true)) {
            $mode = self::MODE_CURRENT;
        }
        // 範囲チェック
        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            return;
        }
        session([
            self::SESSION_KEY => [
                'mode' => $mode,
                'lat' => $lat,
                'lng' => $lng,
                'label' => $label !== '' ? $label : ($mode === self::MODE_CURRENT ? '現在地' : '指定位置'),
                'set_at' => time(),
            ],
        ]);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * 2点間の距離（km）。どちらかでも欠損なら null。
     */
    public function distanceKm(?float $lat1, ?float $lng1, ?float $lat2, ?float $lng2): ?float
    {
        if ($lat1 === null || $lng1 === null || $lat2 === null || $lng2 === null) {
            return null;
        }
        $earthRadiusKm = 6371.0;
        $latFrom = deg2rad((float) $lat1);
        $latTo = deg2rad((float) $lat2);
        $deltaLat = deg2rad((float) $lat2 - (float) $lat1);
        $deltaLng = deg2rad((float) $lng2 - (float) $lng1);
        $a = sin($deltaLat / 2) ** 2
            + cos($latFrom) * cos($latTo) * sin($deltaLng / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadiusKm * $c;
    }

    /**
     * 距離をユーザに見せる文字列にフォーマット。
     */
    public function formatDistance(?float $km): string
    {
        if ($km === null) {
            return '—';
        }
        if ($km < 1.0) {
            return number_format((int) round($km * 1000)) . ' m';
        }
        if ($km < 10.0) {
            return number_format($km, 1) . ' km';
        }
        return number_format((int) round($km)) . ' km';
    }

    /**
     * 距離計算用 SQL 式（MySQL）。
     * 戻り値は km 単位の単純 Haversine。プレースホルダ埋め用にバインド配列も返す。
     *
     * @return array{0:string, 1:array<int, float>}  [expression, bindings]
     */
    public function haversineSqlExpression(string $latColumn, string $lngColumn, float $originLat, float $originLng): array
    {
        $expr = "(6371 * 2 * ASIN(SQRT("
            . "POWER(SIN(RADIANS(? - {$latColumn}) / 2), 2)"
            . " + COS(RADIANS({$latColumn})) * COS(RADIANS(?))"
            . " * POWER(SIN(RADIANS(? - {$lngColumn}) / 2), 2)"
            . ")))";
        return [$expr, [$originLat, $originLat, $originLng]];
    }

    /**
     * 自プロフィールの緯度経度を取得。
     *
     * @return array{lat: float, lng: float}|null
     */
    private function resolveSelfProfileLocation(): ?array
    {
        $cast = Auth::guard('member')->user();
        if ($cast && !empty($cast->id)) {
            $row = DB::table('cast_profiles')
                ->where('cast_id', $cast->id)
                ->select('latitude', 'longitude')
                ->first();
            if ($row && $row->latitude !== null && $row->longitude !== null) {
                return ['lat' => (float) $row->latitude, 'lng' => (float) $row->longitude];
            }
        }
        $manager = Auth::guard('shop')->user();
        if ($manager && !empty($manager->shop_id)) {
            $row = DB::table('shop_profiles')
                ->where('shop_id', $manager->shop_id)
                ->select('latitude', 'longitude')
                ->first();
            if ($row && $row->latitude !== null && $row->longitude !== null) {
                return ['lat' => (float) $row->latitude, 'lng' => (float) $row->longitude];
            }
        }
        return null;
    }
}
