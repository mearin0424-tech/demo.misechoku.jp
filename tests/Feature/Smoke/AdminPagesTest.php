<?php

namespace Tests\Feature\Smoke;

use Tests\Support\SmokeRouteMatrix;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutExceptionHandling();
        $this->actingAs($this->adminUser());
    }

    /**
     * @dataProvider adminPages
     */
    public function test_admin_page_is_rendered_without_server_error(string $routeName, array $params, int $expectedStatus): void
    {
        $response = $this->get(route($routeName, $params, false));

        $response->assertStatus($expectedStatus);
    }

    public static function adminPages(): array
    {
        return SmokeRouteMatrix::adminPages();
    }
}
