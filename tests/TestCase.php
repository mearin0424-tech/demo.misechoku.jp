<?php

namespace Tests;

use App\Models\Cast;
use App\Models\ShopManager;
use App\Models\SystemAccount;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function adminUser(): SystemAccount
    {
        return SystemAccount::query()->findOrFail(1);
    }

    protected function shopManager(): ShopManager
    {
        return ShopManager::query()->findOrFail('m00000001');
    }

    protected function castMember(): Cast
    {
        return Cast::query()->findOrFail('c00000001');
    }
}
