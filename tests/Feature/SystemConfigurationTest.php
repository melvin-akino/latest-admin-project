<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\{WithoutMiddleware,WithFaker};
use Tests\TestCase;

class SystemConfigurationTest extends AdminAccountTestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testSystemConfigurationListWithToken()
    {
        $this->initialUser();
        $response = $this->get('/api/system-configurations', [
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer ' . $this->loginJsonResponse->token
        ]);


        $response->assertStatus(200);
    }

    public function testSystemConfigurationListWithoutToken() {
        $response = $this->get('/api/system-configurations', [
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer XXX'
        ]);

        $response->assertJson(['message' => 'Unauthenticated.']);
    }

    public function testSystemConfigurationManageWithoutToken() {
        $response = $this->post('/api/system-configurations/manage', [
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer XXX'
        ]);

        $response->assertJson(['message' => 'Unauthenticated.']);
    }

    /** @test */
    public function ManageSystemConfigurationNodataTest() {
              
        $this->initialUser();
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer ' . $this->loginJsonResponse->token
            ])->json('POST', '/api/system-configurations/manage', 
                [
                    'id'   => null,
                    'type' => null,
                    'value' => null
                ]
            );
       
         $response->assertStatus(422);
        
    }
     /** @test */
    public function ManageSystemConfigurationRecordTest() {
         
        $this->initialUser();
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer ' . $this->loginJsonResponse->token
        ])->json('POST', '/api/system-configurations/manage', 
            [
                'id'   => 1,
                'type' => 'SCHEDULE_INPLAY_TIMER',
                'value' => 10
            ]
            );
       
         $response->assertStatus(200);
    }
}
