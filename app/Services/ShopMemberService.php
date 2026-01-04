<?php

namespace App\Services;

use App\Repositories\ShopMemberRepository;

class ShopMemberService
{
    protected $shopMemberRepository;

    // リポジトリを依存性注入
    public function __construct(ShopMemberRepository $shopMemberRepository)
    {
        $this->shopMemberRepository = $shopMemberRepository;
    }

    // ビジネスロジックを管理する
    public function addMemberToShop($memberId, $shopId)
    {
        // リポジトリを使って関係を作成または取得
        return $this->shopMemberRepository->addMemberToShop($memberId, $shopId);
    }


    public function addShopToMember($memberId, $shopId)
    {
        return $this->shopMemberRepository->addShopToMember($memberId, $shopId);

    }

    public function deleteMemberFromShop($memberId, $shopId)
    {
        return $this->shopMemberRepository->deleteMemberFromShop($memberId, $shopId);

    }

    public function deleteShopFromMember($memberId, $shopId)
    {
        return $this->shopMemberRepository->deleteShopFromMember($memberId, $shopId);

    }


    public function isShopAdded($memberId, $shopId)
    {
        // リポジトリを使って関係を作成または取得
        return $this->shopMemberRepository->isShopAdded($memberId, $shopId);
    }

    public function isMemberAdded($memberId, $shopId)
    {
        // リポジトリを使って関係を作成または取得
        return $this->shopMemberRepository->isMemberAdded($memberId, $shopId);
    }




}
