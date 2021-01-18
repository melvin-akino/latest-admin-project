<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\{WithoutMiddleware,WithFaker};
use Tests\TestCase;

class ProviderListTest extends AdminAccountTestCase
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

    public function testProviderListWithoutToken() {
        $response = $this->get('/api/providers', [
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization'    => 'Bearer '
        ]);

        $response->assertJson(['message' => 'Unauthenticated.']);
    }
}
