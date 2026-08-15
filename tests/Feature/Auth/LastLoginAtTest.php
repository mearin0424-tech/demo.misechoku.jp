<?php

namespace Tests\Feature\Auth;

use App\Models\Cast;
use App\Models\ShopManager;
use App\Models\SystemAccount;
use Illuminate\Support\Carbon;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Scenario: last_login_at is updated centrally by App\Listeners\UpdateLastLoginAt
 * on the Illuminate\Auth\Events\Login event, regardless of the login channel
 * (standard email/password, demo login, LINE / MockLine, post-registration).
 *
 * Acceptance criteria mirrored from the task brief:
 *  1. Admin view shows the timestamp                (out of scope: read-side)
 *  2. Login as target user
 *  3. Admin view is re-rendered                     (out of scope: read-side)
 *  4. last_login_at reflects the current login time
 *  5. Failed login does NOT update
 *  6. Demo login updates
 *  7. Standard login updates
 *  8. LINE-flavored login (verified via programmatic Auth::login) updates
 */
class LastLoginAtTest extends TestCase
{
    /**
     * The project has no Laravel migrations (schema lives in database/schema.sql
     * per the MySQL-first workflow), so RefreshDatabase yields an empty DB.
     * Load the minimum three tables we touch — casts / shop_managers / shops /
     * system_accounts — directly into SQLite for each test and truncate them
     * afterwards for isolation.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->createMinimalSchema();
        // Login POST routes carry throttle:5,15 which persists in whatever
        // cache store is configured for tests. Reset before each test so
        // sequential test runs never surface as spurious 429s.
        Cache::flush();
        app(RateLimiter::class)->clear('login|127.0.0.1');
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('casts');
        Schema::dropIfExists('shop_managers');
        Schema::dropIfExists('shops');
        Schema::dropIfExists('system_accounts');
        parent::tearDown();
    }

    private function createMinimalSchema(): void
    {
        Schema::dropIfExists('casts');
        Schema::create('casts', function ($table) {
            $table->string('id', 20)->primary();
            $table->string('email', 255)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 255)->nullable();
            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('identity_status')->default(1);
            $table->timestamp('last_login_at')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::dropIfExists('shop_managers');
        Schema::create('shop_managers', function ($table) {
            $table->string('id', 20)->primary();
            $table->string('shop_id', 20);
            $table->string('name', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 255)->nullable();
            $table->tinyInteger('role')->default(0);
            $table->tinyInteger('status')->default(0);
            $table->timestamp('last_login_at')->nullable();
            $table->string('line_user_id')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('shops');
        Schema::create('shops', function ($table) {
            $table->string('id', 20)->primary();
            $table->string('email', 255)->nullable();
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
        });

        Schema::dropIfExists('system_accounts');
        Schema::create('system_accounts', function ($table) {
            // Deliberately NO last_login_at column — the listener must skip
            // the admin guard cleanly because this table does not track it.
            $table->bigIncrements('id');
            $table->string('name', 100);
            $table->string('email', 255)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 255);
            $table->string('role', 20)->default('staff');
            $table->boolean('is_active')->default(true);
            $table->string('remember_token', 100)->nullable();
            $table->timestamps();
        });
    }

    /** @test */
    public function standard_cast_login_updates_last_login_at(): void
    {
        $this->seedCast('c10000001', 'cast-scenario@test.example', 'CastPass1!', past: '2020-01-01 00:00:00');

        $response = $this->post(route('cast.login.post'), [
            'email'    => 'cast-scenario@test.example',
            'password' => 'CastPass1!',
        ]);

        $response->assertRedirect(route('cast.home'));
        $this->assertRecent('casts', 'c10000001');
    }

    /** @test */
    public function failed_cast_login_does_not_update_last_login_at(): void
    {
        $past = '2020-01-01 00:00:00';
        $this->seedCast('c10000002', 'cast-fail@test.example', 'CastPass1!', past: $past);

        $response = $this->post(route('cast.login.post'), [
            'email'    => 'cast-fail@test.example',
            'password' => 'WrongPassword!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertUnchanged('casts', 'c10000002', $past);
    }

    /** @test */
    public function standard_shop_login_updates_last_login_at(): void
    {
        $this->seedShopManager('m10000001', 'shop-scenario@test.example', 'ShopPass1!', past: '2020-01-01 00:00:00');

        $response = $this->post(route('shop.login.post'), [
            'email'    => 'shop-scenario@test.example',
            'password' => 'ShopPass1!',
        ]);

        $response->assertRedirect(route('shop.home'));
        $this->assertRecent('shop_managers', 'm10000001');
    }

    /** @test */
    public function failed_shop_login_does_not_update_last_login_at(): void
    {
        $past = '2020-01-01 00:00:00';
        $this->seedShopManager('m10000002', 'shop-fail@test.example', 'ShopPass1!', past: $past);

        $response = $this->post(route('shop.login.post'), [
            'email'    => 'shop-fail@test.example',
            'password' => 'WrongPassword!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertUnchanged('shop_managers', 'm10000002', $past);
    }

    /** @test */
    public function demo_login_updates_cast_last_login_at(): void
    {
        $this->seedCast('c10000003', 'cast-demo@test.example', 'CastPass1!', past: '2020-01-01 00:00:00');

        $response = $this->post(route('login.demo.post'), [
            'role'         => 'cast',
            'account_id'   => 'c10000003',
            'auth_channel' => 'standard',
        ]);

        $response->assertRedirect(route('cast.home'));
        $this->assertRecent('casts', 'c10000003');
    }

    /** @test */
    public function demo_login_updates_shop_last_login_at(): void
    {
        $this->seedShopManager('m10000003', 'shop-demo@test.example', 'ShopPass1!', past: '2020-01-01 00:00:00');

        $response = $this->post(route('login.demo.post'), [
            'role'         => 'shop',
            'account_id'   => 'm10000003',
            'auth_channel' => 'standard',
        ]);

        $response->assertRedirect(route('shop.home'));
        $this->assertRecent('shop_managers', 'm10000003');
    }

    /**
     * Admin has no last_login_at column on system_accounts, so the listener
     * must skip it silently. If the listener incorrectly tried to update the
     * column, the SQL would fail and this test would blow up.
     *
     * @test
     */
    public function demo_login_for_admin_does_not_error_even_without_column(): void
    {
        $admin = SystemAccount::query()->create([
            'name'      => 'Scenario Admin',
            'email'     => 'admin-scenario@test.example',
            'password'  => Hash::make('AdminPass1!'),
            'role'      => SystemAccount::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $response = $this->post(route('login.demo.post'), [
            'role'         => 'admin',
            'account_id'   => (string) $admin->id,
            'auth_channel' => 'standard',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        // Sanity: schema has no last_login_at on system_accounts.
        $this->assertFalse(
            \Illuminate\Support\Facades\Schema::hasColumn('system_accounts', 'last_login_at'),
            'system_accounts is expected to NOT have last_login_at; listener must skip admin guard.',
        );
    }

    /**
     * The LINE login path ends in auth()->guard('member')->login($cast).
     * We exercise the same terminal call directly (the OAuth handshake itself
     * is out of scope for a unit-level test) to prove the listener fires for
     * programmatic logins too.
     *
     * @test
     */
    public function programmatic_member_guard_login_updates_last_login_at(): void
    {
        $this->seedCast('c10000004', 'cast-line@test.example', 'CastPass1!', past: '2020-01-01 00:00:00');
        $cast = Cast::query()->findOrFail('c10000004');

        Auth::guard('member')->login($cast);

        $this->assertTrue(Auth::guard('member')->check());
        $this->assertRecent('casts', 'c10000004');
    }

    /**
     * Mirror of the above for the shop guard (covers LINE / MockLine shop path).
     *
     * @test
     */
    public function programmatic_shop_guard_login_updates_last_login_at(): void
    {
        $this->seedShopManager('m10000004', 'shop-line@test.example', 'ShopPass1!', past: '2020-01-01 00:00:00');
        $manager = ShopManager::query()->findOrFail('m10000004');

        Auth::guard('shop')->login($manager);

        $this->assertTrue(Auth::guard('shop')->check());
        $this->assertRecent('shop_managers', 'm10000004');
    }

    // ---------------------------------------------------------------------
    // helpers
    // ---------------------------------------------------------------------

    private function seedCast(string $id, string $email, string $password, string $past): void
    {
        DB::table('casts')->insert([
            'id'            => $id,
            'email'         => $email,
            'password'      => Hash::make($password),
            'status'        => 1,
            'last_login_at' => $past,
            'created_at'    => $past,
            'updated_at'    => $past,
        ]);
    }

    private function seedShopManager(string $id, string $email, string $password, string $past): void
    {
        // shop row is required by the shop_managers.shop_id FK convention in
        // production, but there is no enforced FK in the SQLite test schema;
        // we still insert one to keep the fixture realistic.
        $shopId = 's' . substr($id, 1);
        DB::table('shops')->insert([
            'id'         => $shopId,
            'email'      => $email,
            'status'     => 1,
            'created_at' => $past,
            'updated_at' => $past,
        ]);

        DB::table('shop_managers')->insert([
            'id'            => $id,
            'shop_id'       => $shopId,
            'name'          => 'Scenario Manager',
            'email'         => $email,
            'password'      => Hash::make($password),
            'role'          => ShopManager::ROLE_OWNER,
            'status'        => ShopManager::STATUS_ACTIVE,
            'last_login_at' => $past,
            'created_at'    => $past,
            'updated_at'    => $past,
        ]);
    }

    private function assertRecent(string $table, string $id): void
    {
        $row = DB::table($table)->where('id', $id)->first();
        $this->assertNotNull($row, "Row {$table}#{$id} disappeared during login.");
        $this->assertNotNull($row->last_login_at, "last_login_at is null after login on {$table}#{$id}.");

        $stamp = Carbon::parse($row->last_login_at);
        $this->assertTrue(
            $stamp->diffInSeconds(now()) < 5,
            sprintf(
                'last_login_at was not updated to a recent time on %s#%s (got %s, now %s).',
                $table,
                $id,
                $stamp->toDateTimeString(),
                now()->toDateTimeString(),
            ),
        );
    }

    private function assertUnchanged(string $table, string $id, string $expected): void
    {
        $row = DB::table($table)->where('id', $id)->first();
        $this->assertNotNull($row);

        $stamp = Carbon::parse($row->last_login_at);
        $this->assertSame(
            Carbon::parse($expected)->toDateTimeString(),
            $stamp->toDateTimeString(),
            "last_login_at unexpectedly changed on failed login for {$table}#{$id}.",
        );
    }
}
