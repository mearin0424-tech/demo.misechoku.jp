<?php

namespace App\Http\Concerns;

/**
 * Shared helpers for controllers/services that need to resolve the currently
 * authenticated actor (cast/shop/admin) and convert stored file paths into
 * public URLs.
 *
 * Extracted from ~11 duplicated implementations across controllers/services.
 * Include via `use \App\Http\Concerns\ResolvesActor;`.
 */
trait ResolvesActor
{
    /**
     * Currently logged-in cast id (member guard) or null.
     */
    protected function currentCastId(): ?string
    {
        return auth()->guard('member')->check()
            ? (string) auth()->guard('member')->id()
            : null;
    }

    /**
     * Currently logged-in shop id (shop guard). Resolves from the ShopManager row.
     */
    protected function currentShopId(): ?string
    {
        $manager = auth()->guard('shop')->user();
        return ($manager && !empty($manager->shop_id))
            ? (string) $manager->shop_id
            : null;
    }

    /**
     * Currently logged-in shop_manager id (shop guard).
     */
    protected function currentShopManagerId(): ?string
    {
        return auth()->guard('shop')->check()
            ? (string) auth()->guard('shop')->id()
            : null;
    }

    /**
     * Returns ['cast'|'shop'|'admin', id] tuple, or [null, null] if not logged in.
     * Order of precedence: member > shop > admin (matches existing app assumptions).
     *
     * @return array{0: ?string, 1: ?string}
     */
    protected function resolveActor(): array
    {
        if (auth()->guard('member')->check()) {
            return ['cast', (string) auth()->guard('member')->id()];
        }
        if (auth()->guard('shop')->check()) {
            $manager = auth()->guard('shop')->user();
            return ['shop', (string) ($manager->shop_id ?? '')];
        }
        if (auth()->guard('admin')->check()) {
            return ['admin', (string) auth()->guard('admin')->id()];
        }
        return [null, null];
    }

    /**
     * Convert a stored image path (from DB) into a public URL.
     * Handles:
     *   - null/empty              -> no-image.png fallback
     *   - http://... / https://...-> pass-through (external placeholder services)
     *   - uploads/xxx             -> /uploads/xxx
     *   - public/xxx              -> /storage/xxx (Laravel storage:link convention)
     *   - anything else           -> asset() with leading slash trimmed
     *
     * This is the single source of truth. Previously duplicated in 11 files.
     */
    protected function assetPathForStored(?string $path): string
    {
        if (empty($path)) {
            return asset('assets/images/common/no-image.png');
        }
        // External placeholders (i.pravatar.cc / randomuser.me / picsum.photos / etc.)
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        if (str_starts_with($path, 'uploads/')) {
            return asset($path);
        }
        if (str_starts_with($path, 'public/')) {
            return asset('storage/' . substr($path, 7));
        }
        return asset(ltrim($path, '/'));
    }
}
