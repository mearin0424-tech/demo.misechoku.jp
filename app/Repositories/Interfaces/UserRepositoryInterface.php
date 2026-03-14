<?php

namespace App\Repositories\Interfaces;

use App\Models\SystemAccount;

interface UserRepositoryInterface
{
    /**
     * 引数に渡されたメールアドレスを持つユーザーを取得する
     *
     * @param string $email
     * @return SystemAccount
     */
    public function findFromEmail(string $email): SystemAccount;

    /**
     * 引数に渡されたIDのユーザーのパスワードを更新する
     *
     * @param string $password
     * @param int $id
     * @return void
     */
    public function updateUserPassword(string $password, int $id): void;
}