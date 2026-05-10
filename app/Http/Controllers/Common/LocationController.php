<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Services\GeocodingService;
use App\Services\UserLocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * ログインユーザの探索拠点（現在地 or パスポートモード）を保存／解除する。
 */
class LocationController extends Controller
{
    public function __construct(
        private readonly UserLocationService $userLocation,
        private readonly GeocodingService $geocodingService,
    ) {
    }

    /**
     * POST /setting/location
     *
     * 入力モード：
     *  - current  : ブラウザの geolocation で取得した {lat, lng} を保存
     *  - passport : address（住所／駅名）をジオコーディングしてから保存
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'mode' => ['required', 'string', 'in:current,passport'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'address' => ['nullable', 'string', 'max:255'],
            'label' => ['nullable', 'string', 'max:80'],
        ]);

        $mode = (string) $data['mode'];
        $lat = isset($data['lat']) ? (float) $data['lat'] : null;
        $lng = isset($data['lng']) ? (float) $data['lng'] : null;
        $label = (string) ($data['label'] ?? '');

        if ($mode === UserLocationService::MODE_PASSPORT && (!isset($lat, $lng))) {
            $address = trim((string) ($data['address'] ?? ''));
            if ($address === '') {
                return response()->json([
                    'success' => false,
                    'message' => '住所または駅名を入力してください。',
                ], 422);
            }
            $coords = $this->geocodingService->fromAddress($address);
            if (!$coords) {
                return response()->json([
                    'success' => false,
                    'message' => '指定の住所から緯度経度を取得できませんでした。別の表現で試してください。',
                ], 422);
            }
            $lat = (float) $coords['latitude'];
            $lng = (float) $coords['longitude'];
            if ($label === '') {
                $label = $address;
            }
        }

        if (!isset($lat, $lng)) {
            return response()->json([
                'success' => false,
                'message' => '緯度／経度が指定されていません。',
            ], 422);
        }

        $this->userLocation->setManualLocation($mode, $lat, $lng, $label);

        $resolved = $this->userLocation->getActiveLocation();
        return response()->json([
            'success' => true,
            'location' => $resolved,
        ]);
    }

    public function destroy(): JsonResponse
    {
        $this->userLocation->clear();
        return response()->json(['success' => true, 'location' => $this->userLocation->getActiveLocation()]);
    }

    /**
     * GET /api/geocoding/lookup?q=...
     *
     * 住所・駅名を緯度経度に解決する（プレビュー用、保存はしない）。
     */
    public function lookup(Request $request): JsonResponse
    {
        $data = $request->validate([
            'q' => ['required', 'string', 'max:255'],
        ]);

        $address = trim((string) $data['q']);
        if ($address === '') {
            return response()->json([
                'success' => false,
                'message' => '住所または駅名を入力してください。',
            ], 422);
        }

        $coords = $this->geocodingService->fromAddress($address);
        if (!$coords) {
            return response()->json([
                'success' => false,
                'message' => '指定の住所から緯度経度を取得できませんでした。別の表現で試してください。',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'label' => $address,
            'latitude' => (float) $coords['latitude'],
            'longitude' => (float) $coords['longitude'],
        ]);
    }
}
