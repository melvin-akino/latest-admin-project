<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\{WithoutMiddleware,WithFaker};
use App\Models\Order;
use Tests\TestCase;

class OrderAdjustmentTest extends AdminAccountTestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    /** @test */
    public function UpdateOrderTest() {
              
        $this->initialUser();
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer ' . $this->loginJsonResponse->token
        ])->json('POST', '/api/orders/update', 
                [
                    'id'        => 1,
                    'status'    => 'REJECTED',
                    'pl'        => 0,
                    'reason'    => 'Test reason',
                ]
            );
       
         $response->assertStatus(200);
        
    }
}