<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\{WithoutMiddleware,WithFaker};
use Tests\TestCase;

class ProviderAccountTest extends AdminAccountTestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testProviderAccountsListWithToken()
    {
        $this->initialUser();
        $response = $this->get('/api/provider-accounts', [
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer ' . $this->loginJsonResponse->token
        ]);


        $response->assertStatus(200);
    }

    public function testProviderAccountsListWithoutToken() {
        $response = $this->get('/api/provider-accounts', [
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer XXX'
        ]);

        $response->assertJson(['message' => 'Unauthenticated.']);
    }

    /** @test */
    public function InsertProviderAccountNodataTest() {
              
        $this->initialUser();
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer ' . $this->loginJsonResponse->token
        ])->json('POST', '/api/provider-accounts/manage', 
                [
                    'username'   => '',
                    'password' => '',
                    'provider_id' => 0,
                    'type' => '',
                    'credits' => 0,
                    'punter_percentage'   => 0,
                    'is_enabled' => 0,
                    'is_idle' => 0
                ]
            );
       
         $response->assertStatus(422);
        
    }
     /** @test */
     public function InsertProviderAccountwithRecordTest() {
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer xxx' 
        ])->json('POST', '/api/provider-accounts/manage', 
                [
                    'id' => '',
                    'username'   => $this->faker->text(8),
                    'password' => $this->faker->text(16),
                    'provider_id' => '2',
                    'type' => 'BET_NORMAL',
                    'credits' => 0,
                    'punter_percentage'   => '45',
                    'is_enabled' => 1,
                    'is_idle' => 1
                ]
            );
       
         $response->assertStatus(401);
    }
     /** @test */
    public function InsertProviderAccountnoTokenwithRecordTest() {
         
        $this->initialUser();
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer ' . $this->loginJsonResponse->token
        ])->json('POST', '/api/provider-accounts/manage', 
                [
                    'id' => '',
                    'username'   => $this->faker->text(8),
                    'password' => $this->faker->text(16),
                    'provider_id' => '1',
                    'type' => 'BET_NORMAL',
                    'credits' => 0,
                    'punter_percentage'   => '45',
                    'is_enabled' => 1,
                    'is_idle' => 1
                ]
            );
       
         $response->assertStatus(200);
    }
}
