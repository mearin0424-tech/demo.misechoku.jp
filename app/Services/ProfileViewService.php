<?php

namespace App\Services;

use App\Models\ProfileView;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * プロフィール閲覧の記録・集計。
 * 閲覧数は「数値が積み上がる」ことを目的とするため、同一閲覧者の再訪も毎回加算する。
 */
class ProfileViewService
{
    public function record(string $viewerType, string $viewerId, string $targetType, string $targetId): void
    {
        if ($viewerId === '' || $targetId === '' || !Schema::hasTable('profile_views')) {
            return;
        }

        try {
            ProfileView::create([
                'viewer_type' => $viewerType,
                'viewer_id' => $viewerId,
                'target_type' => $targetType,
                'target_id' => $targetId,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // 閲覧記録の失敗でプロフィール表示自体は止めない
            Log::warning('ProfileView record failed: ' . $e->getMessage());
        }
    }

    public function countFor(string $targetType, string $targetId): int
    {
        if ($targetId === '' || !Schema::hasTable('profile_views')) {
            return 0;
        }

        return ProfileView::query()
            ->where('target_type', $targetType)
            ->where('target_id', $targetId)
            ->count();
    }

    /**
     * 複数対象の閲覧数をまとめて取得（SWIPE カード等の一覧用）。
     *
     * @param  array<int, string>  $targetIds
     * @return array<string, int>  target_id => 閲覧数
     */
    public function countForMany(string $targetType, array $targetIds): array
    {
        $targetIds = array_values(array_filter(array_map('strval', $targetIds)));
        if ($targetIds === [] || !Schema::hasTable('profile_views')) {
            return [];
        }

        return ProfileView::query()
            ->selectRaw('target_id, COUNT(*) as cnt')
            ->where('target_type', $targetType)
            ->whereIn('target_id', $targetIds)
            ->groupBy('target_id')
            ->pluck('cnt', 'target_id')
            ->map(fn ($cnt) => (int) $cnt)
            ->all();
    }
}
