<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\{WithoutMiddleware,WithFaker};
use Tests\TestCase;

class LeagueTest extends AdminAccountTestCase
{
    public function testRawLeaguesByProviderIdWithToken()
    {
        $this->initialUser();
        $response = $this->get('/api/raw-leagues/1', [
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer ' . $this->loginJsonResponse->token
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'name',
                'sport_id',
                'provider_id'
            ]
        ]);
    }

    public function testRawLeaguesByProviderIdWithoutToken() {
        $response = $this->get('/api/raw-leagues/1', [
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer XXX'
        ]);

        $response->assertJson(['message' => 'Unauthenticated.']);
    }

    public function testLeaguesByProviderIdWithToken()
    {
        $this->initialUser();
        $response = $this->get('/api/matched-leagues', [
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer ' . $this->loginJsonResponse->token
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'name',
                'sport_id',
                'provider_id'
            ]
        ]);
    }

    public function testLeaguesByProviderIdWithoutToken() {
        $response = $this->get('/api/matched-leagues', [
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer XXX'
        ]);

        $response->assertJson(['message' => 'Unauthenticated.']);
    }
}