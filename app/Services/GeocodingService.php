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
        $candidates = $this->searchCandidates($address, 1);
        if ($candidates === []) {
            return null;
        }
        $top = $candidates[0];
        return [
            'longitude' => $top['longitude'],
            'latitude'  => $top['latitude'],
        ];
    }

    /**
     * 住所／駅名の入力に対して候補（緯度経度＋ラベル）を最大 $limit 件返す。
     * オートサジェスト用。
     *
     * @return array<int, array{label: string, latitude: float, longitude: float}>
     */
    public function searchCandidates(string $query, int $limit = 8): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }

        $response = Http::timeout(10)
            ->acceptJson()
            ->get(self::GEOCODE_URL, ['q' => $query]);

        if (!$response->ok()) {
            return [];
        }

        $json = $response->json();
        if (!is_array($json) || $json === []) {
            return [];
        }

        $out = [];
        foreach ($json as $item) {
            if (!is_array($item)) continue;
            $coords = $item['geometry']['coordinates'] ?? null;
            if (!is_array($coords) || count($coords) < 2) continue;
            $label = trim((string) ($item['properties']['title'] ?? ''));
            if ($label === '') continue;
            $out[] = [
                'label'     => $label,
                'latitude'  => (float) $coords[1],
                'longitude' => (float) $coords[0],
            ];
            if (count($out) >= $limit) break;
        }
        return $out;
    }
}
