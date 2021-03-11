<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\{WithoutMiddleware,WithFaker};
use Tests\TestCase;

class TeamTest extends AdminAccountTestCase
{
    public function testRawTeamsByProviderIdWithToken()
    {
        $this->initialUser();
        $response = $this->get('/api/raw-teams/1', [
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

    public function testRaweamsByProviderIdWithoutToken() {
        $response = $this->get('/api/raw-teams/1', [
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer XXX'
        ]);

        $response->assertJson(['message' => 'Unauthenticated.']);
    }

    public function testTeamsByProviderIdWithToken()
    {
        $this->initialUser();
        $response = $this->get('/api/matched-teams', [
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

    public function testTeamsByProviderIdWithoutToken() {
        $response = $this->get('/api/matched-teams', [
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer XXX'
        ]);

        $response->assertJson(['message' => 'Unauthenticated.']);
    }
}