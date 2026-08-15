<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Update last_login_at on any successful authentication.
 *
 * Fires for every path that ends in Illuminate\Auth\Events\Login:
 *   - Auth::guard($g)->attempt(...) on success (standard email/password)
 *   - Auth::guard($g)->login($user)        (LINE login, MockLine login, demo login, post-register)
 *   - Auth::guard($g)->loginUsingId($id)
 *
 * The Login event is dispatched only on successful auth; failed attempts fire
 * Illuminate\Auth\Events\Failed instead, so this listener never runs for them.
 *
 * Guard -> table mapping:
 *   member -> casts.last_login_at
 *   shop   -> shop_managers.last_login_at
 *   admin  -> not tracked (system_accounts has no last_login_at column)
 */
class UpdateLastLoginAt
{
    /**
     * Map auth guard name -> target table for the last-login timestamp.
     */
    private const GUARD_TABLE_MAP = [
        'member' => 'casts',
        'shop'   => 'shop_managers',
    ];

    public function handle(Login $event): void
    {
        // The Login event is dispatched synchronously from SessionGuard::login(),
        // so anything thrown here would bubble into the login flow and break
        // authentication. Wrap the entire body in try/Throwable to guarantee
        // that a failed audit write never blocks a successful login.
        try {
            $table = self::GUARD_TABLE_MAP[$event->guard] ?? null;
            if ($table === null) {
                return;
            }

            $userId = $event->user?->getAuthIdentifier();
            if ($userId === null || $userId === '') {
                return;
            }

            DB::table($table)
                ->where('id', $userId)
                ->update([
                    'last_login_at' => now(),
                    'updated_at'    => now(),
                ]);
        } catch (Throwable $e) {
            // Best-effort logging. Even Log::warning failures must not escape.
            try {
                Log::warning('UpdateLastLoginAt failed', [
                    'guard'   => $event->guard ?? null,
                    'user_id' => $event->user?->getAuthIdentifier(),
                    'error'   => $e->getMessage(),
                ]);
            } catch (Throwable) {
                // Swallow. Auth flow integrity outranks audit logging.
            }
        }
    }
}
