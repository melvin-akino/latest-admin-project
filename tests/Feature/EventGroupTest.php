<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\{WithoutMiddleware,WithFaker};
use Tests\TestCase;
class EventGroupTest extends AdminAccountTestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testAddEventGroupWithToken()
    {
        $this->initialUser();
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer ' . $this->loginJsonResponse->token
            ])->json('POST', '/api/events/match', [ 
                'primary_provider_event_id' => $this->faker->randomDigit,
                'match_event_id'            => $this->faker->randomDigit
            ]);

        $response->assertStatus(200);
    }

    public function testAddEventGroupWithoutToken() {
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer xxx'
          ])->json('POST', '/api/events/match', [ 'id' => $this->faker->randomDigit ]);

        $response->assertStatus(401);
    }

}