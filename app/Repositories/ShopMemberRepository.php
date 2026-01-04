<?php

namespace App\Repositories;

use App\Models\ShopMemberRelationship;

class ShopMemberRepository
{
    // キャストと店舗の関係を作成または取得する
    public function addMemberToShop($memberId, $shopId)
    {
        // 関係を作成または取得
        $relationship = ShopMemberRelationship::firstOrCreate([
            'member_id' => $memberId,
            'shop_id' => $shopId,
        ]);

        // 店舗側の追加フラグを更新
        $relationship->update(['shop_added' => true]);

        return $relationship;
    }

    public function addShopToMember($memberId, $shopId)
    {
        // 関係を作成または取得
        $relationship = ShopMemberRelationship::firstOrCreate([
            'member_id' => $memberId,
            'shop_id' => $shopId,
        ]);

        // 店舗側の追加フラグを更新
        $relationship->update(['member_added' => true]);

        return $relationship;
    }

    public function deleteMemberFromShop($memberId, $shopId)
    {
        // 該当する関係を取得
        $relationship = ShopMemberRelationship::where('member_id', $memberId)
                                              ->where('shop_id', $shopId)
                                              ->where('shop_added', true)
                                              ->first();
    
        // 関係が存在する場合は削除
        if ($relationship) {
            $relationship->delete();
            return true;
        }
    
        return false; // 削除する関係がない場合
    }
    
    public function deleteShopFromMember($memberId, $shopId)
    {
        // 該当する関係を取得
        $relationship = ShopMemberRelationship::where('member_id', $memberId)
                                              ->where('shop_id', $shopId)
                                              ->where('member_added', true)
                                              ->first();
    
        // 関係が存在する場合は削除
        if ($relationship) {
            $relationship->delete();
            return true;
        }
    
        return false; // 削除する関係がない場合
    }
    


    // 店舗がキャストを追加しているか確認するメソッド
    public function isShopAdded($memberId, $shopId)
    {
        // データベースからリレーションシップを取得
        $relationship = ShopMemberRelationship::where('member_id', $memberId)
                                              ->where('shop_id', $shopId)
                                              ->first();

        // リレーションシップが存在し、キャストが店舗を追加しているか確認
        return $relationship && $relationship->isShopAdded();
    }

    // キャストが店舗を追加しているか確認するメソッド
    public function isMemberAdded($memberId, $shopId)
    {
        // データベースからリレーションシップを取得
        $relationship = ShopMemberRelationship::where('member_id', $memberId)
                                              ->where('shop_id', $shopId)
                                              ->first();

        // リレーションシップが存在し、キャストが店舗を追加しているか確認
        return $relationship && $relationship->isMemberAdded();
    }







}
