<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\{WithoutMiddleware,WithFaker};
use Tests\TestCase;

class LeagueTest extends AdminAccountTestCase
{
    public function testRawLeaguesByProviderIdWithToken()
    {
        $this->initialUser();
        $response = $this->get('/api/raw-leagues?providerId=1&page=1&limit=10&searchKey=Lea', [
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
                    'provider_id'
                ]
            ]
        ]);
    }

    public function testRawLeaguesByProviderIdWithoutToken() {
        $response = $this->get('/api/raw-leagues?providerId=1&page=1&limit=10', [
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer XXX'
        ]);

        $response->assertJson(['message' => 'Unauthenticated.']);
    }

    public function testRawLeaguesByProviderIdWithoutProviderId()
    {
        $this->initialUser();
        $response = $this->get('/api/raw-leagues', [
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer ' . $this->loginJsonResponse->token
        ]);

        $response->assertStatus(400);
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

    public function testMatchLeaguesWithToken()
    {
        $this->initialUser();

        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer ' . $this->loginJsonResponse->token
        ])->json('POST', '/api/leagues/match', [
            "primary_provider_team_id" => 1,
            "match_team_id"            => 7,
            "master_team_alias"        => "",
            "add_master_team"          => false
        ]);

        $response->assertStatus(200);
    }

    public function testMatchLeaguesNoWithToken()
    {
        $this->initialUser();

        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer XXX'
        ])->json('POST', '/api/leagues/match', [
            "primary_provider_league_id" => 1,
            "match_league_id"            => 7,
            "master_league_alias"        => "",
            "add_master_league"          => false
        ]);

        $response->assertJson(['message' => 'Unauthenticated.']);
    }
}