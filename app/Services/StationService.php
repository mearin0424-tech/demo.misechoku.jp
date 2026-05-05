<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * HeartRails Express API — 最寄駅情報取得（緯度・経度指定）
 *
 * @see https://express.heartrails.com/api.html
 */
class StationService
{
    private const STATION_URL = 'https://express.heartrails.com/api/json';

    /**
     * @return array<int, array{station_name: string}>
     */
    public function fetchNearbyStations(float $lat, float $lng, int $limit = 3): array
    {
        $response = Http::timeout(15)
            ->acceptJson()
            ->get(self::STATION_URL, [
                'method' => 'getStations',
                'x' => $lng,
                'y' => $lat,
            ]);

        if (!$response->ok()) {
            return [];
        }

        $json = $response->json();
        $raw = $json['response']['station'] ?? null;
        $stations = $this->normalizeStationList($raw);
        if ($stations === []) {
            return [];
        }

        return collect($stations)
            ->sortBy(fn ($s) => (float) ($s['distance'] ?? PHP_FLOAT_MAX))
            ->take($limit)
            ->map(function ($s) {
                $name = trim((string) ($s['name'] ?? ''));
                $dist = isset($s['distance']) ? (int) round((float) $s['distance']) : null;
                $label = $name !== '' && !str_ends_with($name, '駅') ? $name . '駅' : $name;
                $suffix = $dist !== null ? ' 徒歩約' . $dist . 'm' : '';

                return ['station_name' => $label . $suffix];
            })
            ->values()
            ->all();
    }

    /**
     * @param  mixed  $raw
     * @return array<int, array<string, mixed>>
     */
    private function normalizeStationList(mixed $raw): array
    {
        if ($raw === null) {
            return [];
        }
        if (is_array($raw) && isset($raw['name'])) {
            return [$raw];
        }
        if (is_array($raw)) {
            return array_values(array_filter($raw, fn ($row) => is_array($row)));
        }

        return [];
    }
}
