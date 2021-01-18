<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\{WithoutMiddleware,WithFaker};
use Tests\TestCase;

class GeneralErrorMessagesTest extends AdminAccountTestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testGeneralErrorListWithToken()
    {
        $this->initialUser();
        $response = $this->get('/api/general-errors', [
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer ' . $this->loginJsonResponse->token
        ]);
        $response->assertStatus(200);
    }

    public function testGeneralErrorListWithoutToken() {
        $response = $this->get('/api/general-errors', [
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer XXX'
        ]);

        $response->assertJson(['message' => 'Unauthenticated.']);
    }

     /** @test */
     public function testGeneralErrorManagewithoutToken() {

        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer XXX' 
        ])->json('POST', '/api/general-errors/manage', 
                [
                    'id' => 1,
                    'username'   => 'Bet was not placed. Please try again.'
                ]
            );
       
         $response->assertStatus(401);
    }

    /** @test */
    public function testGeneralErrorNodataTest() {
              
        $this->initialUser();
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer ' . $this->loginJsonResponse->token
        ])->json('POST', '/api/general-errors/manage', 
                [
                    'id'   => '',
                    'error' => ''
                ]
            );
         $response->assertStatus(422);
        
    }
     /** @test */
    public function testGeneralErrorwithRecordTest() {
         
        $this->initialUser();
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer ' . $this->loginJsonResponse->token
        ])->json('POST', '/api/general-errors/manage', 
                [
                    'id' => 1,
                    'error'   => 'Bet was not placed. Please try again.'
                ]
            );
         $response->assertStatus(200);
    }
}
