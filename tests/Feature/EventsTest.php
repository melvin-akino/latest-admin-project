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
                'sport_id',
                'provider_id',
                'ref_schedule',
                'league_name',
                'team_home_name',
                'team_away_name'
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

    public function testEventsByProviderIdWithToken()
    {
        $this->initialUser();
        $response = $this->get('/api/matched-events', [
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer ' . $this->loginJsonResponse->token
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'sport_id',
                'provider_id',
                'ref_schedule',
                'league_name',
                'team_home_name',
                'team_away_name'
            ]
        ]);
    }

    public function testEventsByProviderIdWithoutToken() {
        $response = $this->get('/api/matched-events', [
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer XXX'
        ]);

        $response->assertJson(['message' => 'Unauthenticated.']);
    }
}