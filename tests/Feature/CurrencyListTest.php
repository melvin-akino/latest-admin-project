<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\{WithoutMiddleware,WithFaker};
use Tests\TestCase;

class CurrencyListTest extends AdminAccountTestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testCurrencyListWithToken()
    {
        $this->initialUser();
        $response = $this->get('/api/currencies', [
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer ' . $this->loginJsonResponse->token
        ]);
        $response->assertStatus(200);
    }

    public function testCurrencyListWithoutToken() {
        $response = $this->get('/api/currencies', [
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer XXX'
        ]);

        $response->assertJson(['message' => 'Unauthenticated.']);
    }
}
