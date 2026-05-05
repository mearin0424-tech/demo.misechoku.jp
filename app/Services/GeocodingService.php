<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * 国土地理院 住所検索 API（ジオコーディング）
 *
 * @see https://msearch.gsi.go.jp/docs/api/address-search/index.html
 */
class GeocodingService
{
    private const GEOCODE_URL = 'https://msearch.gsi.go.jp/address-search/AddressSearch';

    /**
     * @return array{latitude: float, longitude: float}|null
     */
    public function fromAddress(string $address): ?array
    {
        $address = trim($address);
        if ($address === '') {
            return null;
        }

        $response = Http::timeout(15)
            ->acceptJson()
            ->get(self::GEOCODE_URL, ['q' => $address]);

        if (!$response->ok()) {
            return null;
        }

        $json = $response->json();
        if (!is_array($json) || $json === []) {
            return null;
        }

        $first = $json[0] ?? null;
        $coords = $first['geometry']['coordinates'] ?? null;
        if (!is_array($coords) || count($coords) < 2) {
            return null;
        }

        $lng = (float) $coords[0];
        $lat = (float) $coords[1];

        return [
            'longitude' => $lng,
            'latitude' => $lat,
        ];
    }
}
