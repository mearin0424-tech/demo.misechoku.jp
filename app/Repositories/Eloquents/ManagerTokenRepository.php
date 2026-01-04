<?php

namespace App\Repositories\Eloquents;

use App\Models\ManagerToken;
use App\Repositories\Interfaces\ManagerTokenRepositoryInterface;
use Carbon\Carbon;



class ManagerTokenRepository implements ManagerTokenRepositoryInterface
{
    private $userToken;

    /**
     * constructor
     *
     * @param UserToken $userToken
     */
    public function __construct(ManagerToken $userToken)
    {
        $this->userToken = $userToken;
    }

    /**
     * @inheritDoc
     */
    public function updateOrCreateUserToken(int $userId): ManagerToken
    {
        $now = Carbon::now();

        $hashedToken = hash('sha256', $userId);

        return $this->userToken->updateOrCreate(
        [
            'user_id' => $userId,
        ],
        [
            'token' => uniqid(rand(), $hashedToken),
            'expire_at' => $now->addHours(48)->toDateTimeString(),
        ]);
    }

    public function getUserTokenfromToken(string $token): ManagerToken
    {
        return $this->userToken->where('token', $token)->firstOrFail();
    }
}

