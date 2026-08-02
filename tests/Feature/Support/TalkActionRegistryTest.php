<?php

namespace Tests\Feature\Support;

use App\Support\TalkActionRegistry;
use Tests\TestCase;

/**
 * Pure unit-style test for the talk action authorization matrix.
 * Doesn't touch the DB — verifies the policy constants are consistent.
 */
class TalkActionRegistryTest extends TestCase
{
    /** @test */
    public function all_types_contains_every_side_bucket(): void
    {
        $all = TalkActionRegistry::allTypes();
        foreach (TalkActionRegistry::CAST_ONLY as $t) $this->assertContains($t, $all);
        foreach (TalkActionRegistry::SHOP_ONLY as $t) $this->assertContains($t, $all);
        foreach (TalkActionRegistry::BOTH_SIDE as $t) $this->assertContains($t, $all);
    }

    /** @test */
    public function cast_only_actions_reject_shop_side(): void
    {
        foreach (TalkActionRegistry::CAST_ONLY as $type) {
            $this->assertTrue(TalkActionRegistry::isAllowed($type, true),  "$type should allow cast");
            $this->assertFalse(TalkActionRegistry::isAllowed($type, false), "$type should reject shop");
        }
    }

    /** @test */
    public function shop_only_actions_reject_cast_side(): void
    {
        foreach (TalkActionRegistry::SHOP_ONLY as $type) {
            $this->assertTrue(TalkActionRegistry::isAllowed($type, false), "$type should allow shop");
            $this->assertFalse(TalkActionRegistry::isAllowed($type, true),  "$type should reject cast");
        }
    }

    /** @test */
    public function both_side_actions_allow_either(): void
    {
        foreach (TalkActionRegistry::BOTH_SIDE as $type) {
            $this->assertTrue(TalkActionRegistry::isAllowed($type, true));
            $this->assertTrue(TalkActionRegistry::isAllowed($type, false));
        }
    }
}
