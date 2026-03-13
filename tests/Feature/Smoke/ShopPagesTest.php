<?php

namespace Tests\Feature\Smoke;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\SmokeRouteMatrix;
use Tests\TestCase;

class ShopPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutExceptionHandling();
        $this->actingAs($this->shopManager(), 'shop');
    }

    /**
     * @dataProvider shopPages
     */
    public function test_shop_page_is_rendered_without_server_error(string $routeName, array $params, int $expectedStatus): void
    {
        $response = $this->get(route($routeName, $params, false));

        $response->assertStatus($expectedStatus);
    }

    public static function shopPages(): array
    {
        return SmokeRouteMatrix::shopPages();
    }
}
