<?php

namespace App\Repositories\Eloquents;

use App\Models\SystemAccount;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;


class UserRepository implements UserRepositoryInterface
{
    private $user;

    /**
     * constructor
     *
     * @param SystemAccount $user
     */
    public function __construct(SystemAccount $user)
    {
        $this->user = $user;
    }

    /**
     * @inheritDoc
     */
    public function findFromEmail(string $email): SystemAccount
    {
        return $this->user->where('email', $email)->firstOrFail();
    }

    public function updateUserPassword(string $password, int $id): void
    {
        $this->user->where('id', $id)->update(['password' => Hash::make($password)]);
    }
}
