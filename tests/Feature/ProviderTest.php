<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\{WithoutMiddleware,WithFaker};
use Tests\TestCase;

class ProviderTest extends AdminAccountTestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testProviderListWithToken()
    {
        $this->initialUser();
        $response = $this->get('/api/providers', [
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer ' . $this->loginJsonResponse->token
        ]);


        $response->assertStatus(200);
    }

    public function testProvidersWithoutToken() {
        $response = $this->get('/api/providers', [
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer XXX'
        ]);

        $response->assertJson(['message' => 'Unauthenticated.']);
    }

    /** @test */
    public function InsertProviderNodataTest() {
              
        $this->initialUser();
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer ' . $this->loginJsonResponse->token
        ])->json('POST', '/api/providers/create', 
                [
                    'name'              => '',
                    'alias'             => '',
                    'is_enabled'        => false,
                    'punter_percentage' => 0,
                    'currency_id'       => 0,
                ]
            );
       
         $response->assertStatus(400);
        
    }
     /** @test */
     private function InsertProviderwithRecordTest() {
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer ' . $this->loginJsonResponse->token 
        ])->json('POST', '/api/providers/create', 
                [
                    'name'              => 'SSSSS',
                    'alias'             => 'SIN',
                    'is_enabled'        => true,
                    'punter_percentage' => 45,
                    'currency_id'       => 1,
                ]
            );
       
         $response->assertStatus(200);
    }
     /** @test */
    public function InsertProviderAccountnoTokenwithRecordTest() {
         
        $this->initialUser();
        $this->InsertProviderwithRecordTest();
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer ' . $this->loginJsonResponse->token
        ])->json('POST', '/api/providers/update', 
                [
                    'name'              => 'SSSSS',
                    'alias'             => 'SIN',
                    'is_enabled'        => true,
                    'punter_percentage' => 45,
                    'currency_id'       => 1,
                ]
            );
       
         $response->assertStatus(200);
    }
}
