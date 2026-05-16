<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ログインユーザの「いまの探索拠点」と「半径フィルタ」を扱うサービス。
 *
 * 探索拠点の解決順:
 *   1. 自プロフィールの search_location_mode に従う:
 *      - 'profile'  : cast_profiles / shop_profiles の latitude/longitude
 *      - 'passport' : cast_profiles / shop_profiles の search_passport_latitude/longitude
 *      - 'current'  : セッションに保存された端末 geolocation
 *   2. 設定が無い場合は、セッションの一時保存（旧 location-modal フロー）にフォールバック
 *   3. それでも解決できなければ、自プロフィールの住所緯度経度
 *   4. 解決不能 → null（距離フィルタ無効）
 */
class UserLocationService
{
    public const SESSION_KEY = 'user_location';
    public const MODE_CURRENT = 'current';   // 端末の geolocation
    public const MODE_PASSPORT = 'passport'; // 任意位置（住所→ジオコーディング）
    public const MODE_PROFILE = 'profile';   // 自プロフィール住所

    public const ALL_MODES = [self::MODE_PROFILE, self::MODE_PASSPORT, self::MODE_CURRENT];

    /** プルダウン用：選択肢（半径 km）。0 は「制限なし」。 */
    public const DISTANCE_OPTIONS_KM = [0, 1, 3, 5, 10, 20, 30, 50, 100];

    /**
     * 現在の探索拠点を返す（永続設定 → セッション → プロフィール住所 の順）。
     *
     * @return array{lat: float, lng: float, mode: string, label: string}|null
     */
    public function getActiveLocation(): ?array
    {
        $persisted = $this->resolvePersistedOrigin();
        if ($persisted) {
            return $persisted;
        }

        $session = (array) session(self::SESSION_KEY, []);
        if (
            isset($session['lat'], $session['lng']) &&
            is_numeric($session['lat']) && is_numeric($session['lng'])
        ) {
            return [
                'lat'   => (float) $session['lat'],
                'lng'   => (float) $session['lng'],
                'mode'  => (string) ($session['mode'] ?? self::MODE_CURRENT),
                'label' => (string) ($session['label'] ?? '指定位置'),
            ];
        }

        $profile = $this->resolveSelfProfileLocation();
        if ($profile) {
            return $profile + ['mode' => self::MODE_PROFILE, 'label' => 'プロフィール住所'];
        }

        return null;
    }

    /**
     * 永続設定（cast_profiles / shop_profiles のカラム）から探索拠点を解決する。
     */
    private function resolvePersistedOrigin(): ?array
    {
        $settings = $this->loadProfileSettings();
        if (!$settings) {
            return null;
        }

        $mode = (string) ($settings['mode'] ?? '');
        if ($mode === self::MODE_PROFILE) {
            $row = $settings['profile_location'];
            if ($row !== null) {
                return [
                    'lat'   => $row['lat'],
                    'lng'   => $row['lng'],
                    'mode'  => self::MODE_PROFILE,
                    'label' => 'プロフィール住所',
                ];
            }
            return null;
        }

        if ($mode === self::MODE_PASSPORT) {
            if (
                $settings['passport_lat'] !== null &&
                $settings['passport_lng'] !== null
            ) {
                return [
                    'lat'   => (float) $settings['passport_lat'],
                    'lng'   => (float) $settings['passport_lng'],
                    'mode'  => self::MODE_PASSPORT,
                    'label' => $settings['passport_label'] !== ''
                        ? $settings['passport_label']
                        : '指定位置',
                ];
            }
            return null;
        }

        if ($mode === self::MODE_CURRENT) {
            $session = (array) session(self::SESSION_KEY, []);
            if (
                isset($session['lat'], $session['lng']) &&
                is_numeric($session['lat']) && is_numeric($session['lng'])
            ) {
                return [
                    'lat'   => (float) $session['lat'],
                    'lng'   => (float) $session['lng'],
                    'mode'  => self::MODE_CURRENT,
                    'label' => '現在地',
                ];
            }
            return null;
        }

        return null;
    }

    /**
     * 永続設定の半径フィルタ（km）。0 は「制限なし」、null は「未設定」。
     */
    public function getEffectiveMaxDistanceKm(): ?int
    {
        $settings = $this->loadProfileSettings();
        if (!$settings) {
            return null;
        }
        $km = $settings['max_distance_km'];
        return ($km === null) ? null : (int) $km;
    }

    /**
     * セッションに位置を保存（端末 geolocation 用）。
     */
    public function setManualLocation(string $mode, float $lat, float $lng, string $label = ''): void
    {
        if (!in_array($mode, [self::MODE_CURRENT, self::MODE_PASSPORT], true)) {
            $mode = self::MODE_CURRENT;
        }
        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            return;
        }
        session([
            self::SESSION_KEY => [
                'mode'   => $mode,
                'lat'    => $lat,
                'lng'    => $lng,
                'label'  => $label !== '' ? $label : ($mode === self::MODE_CURRENT ? '現在地' : '指定位置'),
                'set_at' => time(),
            ],
        ]);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * MyPage 設定（mode / passport / max_km）を DB に保存する。
     *
     * @param array{
     *   mode: string,
     *   passport_address?: ?string,
     *   passport_latitude?: ?float,
     *   passport_longitude?: ?float,
     *   passport_label?: ?string,
     *   max_distance_km?: ?int,
     * } $payload
     */
    public function saveSearchSettings(array $payload): void
    {
        $mode = (string) ($payload['mode'] ?? '');
        if (!in_array($mode, self::ALL_MODES, true)) {
            return;
        }

        [$table, $idColumn, $idValue] = $this->resolveCurrentProfileTarget();
        if ($table === null) {
            return;
        }

        $update = [
            'search_location_mode'   => $mode,
            'search_max_distance_km' => isset($payload['max_distance_km'])
                ? (int) $payload['max_distance_km']
                : 0,
        ];

        if ($mode === self::MODE_PASSPORT) {
            $update['search_passport_address']   = $payload['passport_address']   ?? null;
            $update['search_passport_latitude']  = $payload['passport_latitude']  ?? null;
            $update['search_passport_longitude'] = $payload['passport_longitude'] ?? null;
            $update['search_passport_label']     = $payload['passport_label']     ?? null;
        } else {
            // 他モードに切り替えた場合、パスポート情報はクリアしておく
            $update['search_passport_address']   = null;
            $update['search_passport_latitude']  = null;
            $update['search_passport_longitude'] = null;
            $update['search_passport_label']     = null;
        }

        DB::table($table)
            ->where($idColumn, $idValue)
            ->update($update);
    }

    /**
     * MyPage 表示用：現在の保存設定を返す。
     *
     * @return array{
     *   mode: string,
     *   passport_address: ?string,
     *   passport_latitude: ?float,
     *   passport_longitude: ?float,
     *   passport_label: ?string,
     *   max_distance_km: ?int,
     *   profile_location: ?array{lat: float, lng: float},
     * }|null
     */
    public function loadProfileSettings(): ?array
    {
        [$table, $idColumn, $idValue] = $this->resolveCurrentProfileTarget();
        if ($table === null) {
            return null;
        }
        if (!Schema::hasColumn($table, 'search_location_mode')) {
            return null;
        }

        $row = DB::table($table)
            ->where($idColumn, $idValue)
            ->select(
                'pref',
                'city',
                'addr',
                'latitude',
                'longitude',
                'search_location_mode as mode',
                'search_passport_address as passport_address',
                'search_passport_latitude as passport_lat',
                'search_passport_longitude as passport_lng',
                'search_passport_label as passport_label',
                'search_max_distance_km as max_distance_km',
            )
            ->first();
        if (!$row) {
            return null;
        }

        $profileLocation = ($row->latitude !== null && $row->longitude !== null)
            ? ['lat' => (float) $row->latitude, 'lng' => (float) $row->longitude]
            : null;

        $addressText = trim(
            ((string) ($row->pref ?? ''))
            . ((string) ($row->city ?? ''))
            . ((string) ($row->addr ?? ''))
        );

        return [
            'mode'              => (string) ($row->mode ?? ''),
            'passport_address'  => $row->passport_address,
            'passport_latitude' => $row->passport_lat !== null ? (float) $row->passport_lat : null,
            'passport_longitude' => $row->passport_lng !== null ? (float) $row->passport_lng : null,
            'passport_label'    => (string) ($row->passport_label ?? ''),
            'max_distance_km'   => $row->max_distance_km !== null ? (int) $row->max_distance_km : null,
            'profile_location'  => $profileLocation,
            'profile_address'   => $addressText,
            'has_address'       => $addressText !== '',
        ];
    }

    /**
     * 自プロフィールの住所を国土地理院 API でジオコーディングし、
     * cast_profiles / shop_profiles の latitude/longitude を更新する。
     * 成功時は lat/lng を返す。失敗時は null。
     *
     * @return array{lat: float, lng: float}|null
     */
    public function geocodeAndSaveProfileLocation(GeocodingService $geocoding): ?array
    {
        [$table, $idColumn, $idValue] = $this->resolveCurrentProfileTarget();
        if ($table === null) {
            return null;
        }

        $row = DB::table($table)
            ->where($idColumn, $idValue)
            ->select('pref', 'city', 'addr')
            ->first();
        if (!$row) {
            return null;
        }
        $address = trim(((string) ($row->pref ?? ''))
            . ((string) ($row->city ?? ''))
            . ((string) ($row->addr ?? '')));
        if ($address === '') {
            return null;
        }

        $coords = $geocoding->fromAddress($address);
        if (!$coords) {
            return null;
        }

        DB::table($table)
            ->where($idColumn, $idValue)
            ->update([
                'latitude'  => $coords['latitude'],
                'longitude' => $coords['longitude'],
            ]);

        return ['lat' => (float) $coords['latitude'], 'lng' => (float) $coords['longitude']];
    }

    /**
     * 現在ログイン中のユーザに紐づく profile テーブル情報を返す。
     *
     * @return array{0: ?string, 1: ?string, 2: ?string} [tableName, idColumn, idValue]
     */
    private function resolveCurrentProfileTarget(): array
    {
        $cast = Auth::guard('member')->user();
        if ($cast && !empty($cast->id)) {
            return ['cast_profiles', 'cast_id', (string) $cast->id];
        }
        $manager = Auth::guard('shop')->user();
        if ($manager && !empty($manager->shop_id)) {
            return ['shop_profiles', 'shop_id', (string) $manager->shop_id];
        }
        return [null, null, null];
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
     *
     * @return array{0:string, 1:array<int, float>} [expression, bindings]
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
     * 自プロフィールの緯度経度を取得（住所→geocode 済みの値）。
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
