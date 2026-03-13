<?php

namespace Tests;

use App\Models\Manager;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function adminUser(): User
    {
        return User::query()->findOrFail('admin001');
    }

    protected function shopManager(): Manager
    {
        return Manager::query()->findOrFail('m00000001');
    }

    protected function castMember(): Member
    {
        return Member::query()->findOrFail('c00000001');
    }
}
