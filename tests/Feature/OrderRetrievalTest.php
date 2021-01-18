<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\{WithoutMiddleware,WithFaker};
use Tests\TestCase;

class OrderRetrievalTest extends AdminAccountTestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testOrdersListWithToken()
    {
        $this->initialUser();
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer ' . $this->loginJsonResponse->token
            ])->json('GET', '/api/orders', 
                [
                    'id'   => $this->faker->randomDigit
                ]
            );
        $response->assertStatus(200);
    }

    public function testOrdersListListWithoutToken() {
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer '
            ])->json('GET', '/api/orders', 
                [
                    'id'   => $this->faker->randomDigit,
                ]
            );

        $response->assertJson(['message' => 'Unauthenticated.']);
    }
}
