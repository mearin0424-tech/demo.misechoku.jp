<?php

namespace Tests\Feature\Smoke;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\SmokeRouteMatrix;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutExceptionHandling();
    }

    /**
     * @dataProvider publicPages
     */
    public function test_public_page_is_rendered_without_server_error(string $routeName, array $params, int $expectedStatus): void
    {
        $response = $this->get(route($routeName, $params, false));

        $response->assertStatus($expectedStatus);
    }

    public static function publicPages(): array
    {
        return SmokeRouteMatrix::publicPages();
    }
}
