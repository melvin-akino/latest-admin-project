<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\{WithoutMiddleware,WithFaker};
use Tests\TestCase;

class AdminActivityLogsTest extends AdminAccountTestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testAdminActivityLogsWithToken()
    {
        $this->initialUser();
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer ' . $this->loginJsonResponse->token
          ])->json('GET', '/api/admin-users/logs', [ 'id' => $this->faker->randomDigit ]);

        $response->assertStatus(200);
    }

    public function testAdminActivityLogsWithoutToken() {
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer xxx'
          ])->json('GET', '/api/admin-users/logs', [ 'id' => $this->faker->randomDigit ]);

        $response->assertStatus(401);
    }
}
