<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\{WithoutMiddleware,WithFaker};
use Tests\TestCase;

class TeamTest extends AdminAccountTestCase
{
    public function testRawTeamsByProviderIdWithToken()
    {
        $this->initialUser();
        $response = $this->get('/api/raw-teams?providerId=1&page=1&limit=10&searchKey=Tea', [
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer ' . $this->loginJsonResponse->token
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total',
            'pageNum',
            'pageData' => [
                '*' => [
                    'name',
                    'sport_id',
                    'provider_id',
                    'master_league_ids'
                ]
            ]
        ]);
    }

    public function testRawTeamsByProviderIdWithoutToken() {
        $response = $this->get('/api/raw-teams?providerId=1&page=1&limit=10', [
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer XXX'
        ]);

        $response->assertJson(['message' => 'Unauthenticated.']);
    }

    public function testRawTeamsByProviderIdWithoutProviderId()
    {
        $this->initialUser();
        $response = $this->get('/api/raw-teams', [
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer ' . $this->loginJsonResponse->token
        ]);

        $response->assertStatus(400);
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

    public function testMatchTeamsWithToken()
    {
        $this->initialUser();

        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer ' . $this->loginJsonResponse->token
        ])->json('POST', '/api/teams/match', [
            "primary_provider_team_id" => 1,
            "match_team_id"            => 7,
            "master_team_alias"        => "",
            "add_master_team"          => false
        ]);

        $response->assertStatus(200);
    }

    public function testMatchTeamsNoWithToken()
    {
        $this->initialUser();

        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer XXX'
        ])->json('POST', '/api/teams/match', [
            "primary_provider_team_id" => 1,
            "match_team_id"            => 7,
            "master_team_alias"        => "",
            "add_master_team"          => false
        ]);

        $response->assertJson(['message' => 'Unauthenticated.']);
    }
}