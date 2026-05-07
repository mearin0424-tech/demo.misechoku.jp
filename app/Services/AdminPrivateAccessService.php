<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * 管理画面で「非公開情報（連絡先・本名・口座など）」を一時的に解除するためのゲート。
 *
 * - パスワード再入力でセッションに「unlock キー」を保存
 * - キーは {entityType, entityId} 単位で発行され、TTL を持つ
 * - TTL を超えると自動的に再入力を求める
 */
class AdminPrivateAccessService
{
    /** TTL（秒）。デフォルト 15 分 */
    private const UNLOCK_TTL_SECONDS = 900;

    private const SESSION_KEY = 'admin.private_unlocks';

    public function isUnlocked(string $entityType, string $entityId): bool
    {
        $store = (array) session(self::SESSION_KEY, []);
        $key = $this->makeKey($entityType, $entityId);
        $expiresAt = (int) ($store[$key] ?? 0);
        if ($expiresAt <= 0) {
            return false;
        }
        if ($expiresAt < time()) {
            $this->lock($entityType, $entityId);
            return false;
        }
        return true;
    }

    public function unlockedSecondsRemaining(string $entityType, string $entityId): int
    {
        $store = (array) session(self::SESSION_KEY, []);
        $key = $this->makeKey($entityType, $entityId);
        $expiresAt = (int) ($store[$key] ?? 0);
        return max(0, $expiresAt - time());
    }

    /**
     * 現在ログイン中の管理者のパスワードを照合し、合えばセッションを延長してロック解除する。
     */
    public function unlockWithPassword(string $entityType, string $entityId, string $password): bool
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin || !is_string($admin->password ?? null) || $admin->password === '') {
            return false;
        }
        if (!Hash::check($password, $admin->password)) {
            return false;
        }
        $store = (array) session(self::SESSION_KEY, []);
        $store[$this->makeKey($entityType, $entityId)] = time() + self::UNLOCK_TTL_SECONDS;
        session([self::SESSION_KEY => $store]);
        return true;
    }

    public function lock(string $entityType, string $entityId): void
    {
        $store = (array) session(self::SESSION_KEY, []);
        unset($store[$this->makeKey($entityType, $entityId)]);
        session([self::SESSION_KEY => $store]);
    }

    private function makeKey(string $entityType, string $entityId): string
    {
        return $entityType . ':' . $entityId;
    }

    public function ttlSeconds(): int
    {
        return self::UNLOCK_TTL_SECONDS;
    }
}
