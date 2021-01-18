<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\{WithoutMiddleware,WithFaker};
use Tests\TestCase;

class ProviderErrorMessageTest extends AdminAccountTestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testProviderErrorListWithToken()
    {
        $this->initialUser();
        $response = $this->get('/api/provider-errors', [
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer ' . $this->loginJsonResponse->token
        ]);


        $response->assertStatus(200);
    }

    public function testProviderErrorListWithoutToken() {
        $response = $this->get('/api/provider-errors', [
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer XXX'
        ]);

        $response->assertJson(['message' => 'Unauthenticated.']);
    }

     /** @test */
     public function testProviderErrorManagewithoutToken() {

        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer XXX' 
        ])->json('POST', '/api/provider-errors/manage', 
                [
                    'id'                => 1,
                    'message'           => 'Abnormal bets',
                    'error_message_id'  => 6
                ]
            );
       
         $response->assertStatus(401);
    }

    /** @test */
    public function testProviderErrorNodataTest() {
              
        $this->initialUser();
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer ' . $this->loginJsonResponse->token
        ])->json('POST', '/api/provider-errors/manage', 
                [
                    'id'                => '',
                    'message'           => '',
                    'error_message_id'  => ''
                ]
            );
       
         $response->assertStatus(422);
        
    }
     /** @test */
    public function testProviderErrorwithRecordTest() {
         
        $this->initialUser();
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer ' . $this->loginJsonResponse->token
        ])->json('POST', '/api/provider-errors/manage', 
                [
                    'id'                => 1,
                    'message'           => 'Abnormal bets',
                    'error_message_id'  => 6
                ]
            );
       
         $response->assertStatus(200);
    }
}
