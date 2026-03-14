<?php

namespace App\Repositories\Eloquents;

use App\Models\ShopManager;
use App\Repositories\Interfaces\ManagerRepositoryInterface;
use Illuminate\Support\Facades\Hash;


class ManagerRepository implements ManagerRepositoryInterface
{
    private $user;

    /**
     * constructor
     *
     * @param User $user
     */
    public function __construct(ShopManager $user)
    {
        $this->user = $user;
    }

    /**
     * @inheritDoc
     */
    public function findFromEmail(string $email): ShopManager
    {
        return $this->user->where('email', $email)->firstOrFail();
    }

    public function updateUserPassword(string $password, int $id): void
    {
        $this->user->where('id', $id)->update(['password' => Hash::make($password),'plain_password' => $password]);
    }
}
