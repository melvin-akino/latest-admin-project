<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\{WithoutMiddleware,WithFaker};
use Tests\TestCase;

class EventsTest extends AdminAccountTestCase
{
    public function testRawEventsByProviderIdWithToken()
    {
        $this->initialUser();
        $response = $this->get('/api/raw-events/1', [
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer ' . $this->loginJsonResponse->token
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'ref_schedule',
                'missing_count',
                'game_schedule',
                'score',
                'running_time',
                'home_penalty',
                'away_penalty',
                'league',
                'teamHome',
                'teamAway'
            ]
        ]);
    }

    public function testRawEventsByProviderIdWithoutToken() {
        $response = $this->get('/api/raw-events/1', [
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer XXX'
        ]);

        $response->assertJson(['message' => 'Unauthenticated.']);
    }
}