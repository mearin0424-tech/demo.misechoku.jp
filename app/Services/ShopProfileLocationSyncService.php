<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 住所文字列から緯度経度（国土地理院）と最寄り駅（HeartRails）を解決する。
 */
class ShopProfileLocationSyncService
{
    public function __construct(
        private readonly GeocodingService $geocodingService,
        private readonly StationService $stationService,
    ) {
    }

    /**
     * プロフィール更新・店舗新規登録で共通の、ジオコーディング用住所1行。
     * 登録は `address`、編集は `addr`/`building` または `addr1` を解釈する。
     */
    public function buildFullAddressLineForGeocode(Request $request): string
    {
        $pref = trim((string) $request->input('pref', ''));
        $city = trim((string) $request->input('city', ''));
        if (Schema::hasColumn('shop_profiles', 'addr')) {
            $addr = trim((string) $request->input('addr', ''));
            $building = trim((string) $request->input('building', ''));

            return implode('', array_filter([$pref, $city, $addr, $building]));
        }

        $addr1 = trim((string) $request->input('addr1', ''));
        $address = trim((string) $request->input('address', ''));
        $street = $addr1 !== '' ? $addr1 : $address;

        return implode('', array_filter([$pref, $city, $street]));
    }

    /**
     * @return array{
     *   latitude: string|null,
     *   longitude: string|null,
     *   station_rows: array<int, array{station_name: string}>
     * }
     */
    public function resolveFromAddressLine(string $fullAddressLine): array
    {
        $empty = [
            'latitude' => null,
            'longitude' => null,
            'station_rows' => [],
        ];

        if (!Schema::hasColumn('shop_profiles', 'latitude')
            || !Schema::hasColumn('shop_profiles', 'longitude')) {
            return $empty;
        }

        $fullAddressLine = trim($fullAddressLine);
        if ($fullAddressLine === '') {
            return $empty;
        }

        $coords = $this->geocodingService->fromAddress($fullAddressLine);
        if ($coords === null) {
            return $empty;
        }

        $lat = number_format($coords['latitude'], 7, '.', '');
        $lng = number_format($coords['longitude'], 7, '.', '');

        $stationRows = [];
        if (Schema::hasTable('shop_stations')) {
            $stationRows = $this->stationService->fetchNearbyStations(
                $coords['latitude'],
                $coords['longitude']
            );
        }

        return [
            'latitude' => $lat,
            'longitude' => $lng,
            'station_rows' => $stationRows,
        ];
    }

    /**
     * 新規店舗登録直後など、shop_profiles 行が既にある状態で緯度経度・shop_stations を保存する。
     */
    public function persistResolvedLocation(string $shopId, string $fullAddressLine): void
    {
        $resolved = $this->resolveFromAddressLine($fullAddressLine);
        if (!Schema::hasColumn('shop_profiles', 'latitude')) {
            return;
        }

        DB::table('shop_profiles')->where('shop_id', $shopId)->update([
            'latitude' => $resolved['latitude'],
            'longitude' => $resolved['longitude'],
            'updated_at' => now(),
        ]);

        $this->replaceShopStationsRows($shopId, $resolved['station_rows']);
    }

    /**
     * @param  array<int, array{station_name: string}>  $rows
     */
    public function replaceShopStationsRows(string $shopId, array $rows): void
    {
        if (!Schema::hasTable('shop_stations')) {
            return;
        }

        DB::table('shop_stations')->where('shop_id', $shopId)->delete();
        $order = 0;
        foreach ($rows as $row) {
            $name = trim((string) ($row['station_name'] ?? ''));
            if ($name === '') {
                continue;
            }
            DB::table('shop_stations')->insert([
                'shop_id' => $shopId,
                'station_name' => $name,
                'sort_order' => $order,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $order++;
        }
    }
}
