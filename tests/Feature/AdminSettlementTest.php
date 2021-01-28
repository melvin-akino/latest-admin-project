<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminSettlementTest extends AdminAccountTestCase
{
    /** @test */
    public function createAdminSettlementTest() {
              
        $this->initialUser();
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer ' . $this->loginJsonResponse->token
        ])->json('POST', '/api/settlements/create', 
            [
                'provider'      => 'HG',
                'sport'         => 1,
                'username'      => 'abc12345',
                'status'        => 'WIN',
                'odds'          => 0.99,
                'score'         => '10-0',
                'stake'         => 100,
                'pl'            => 99,
                'reason'        => 'Admin settlement test reason',
                'payload'       => 'Sample serialized data of the payload array',
                'bet_id'        => 'AW123456789',
                'processed'     => false
            ]
        );
        $response->assertStatus(200);
        
    }

    /** @test */
    public function retrieveOpenOrders() {
              
        $this->initialUser();
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer ' . $this->loginJsonResponse->token
        ])->json('GET', '/api/provider-accounts/orders', 
            [
                'created_from'  => '2020-12-25',
                'created_to'    => '2021-01-31',
                'status'        => 'open'
            ]
        );
       
         $response->assertStatus(200);
        
    }
}
