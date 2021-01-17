<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\{WithoutMiddleware,WithFaker};
use Tests\TestCase;

class WalletCurrencyTest extends AdminAccountTestCase
{
  public function testWalletCurrenciesWithToken()
  {
    $this->initialUser();
    $this->getWalletToken();
    $response = $this->withHeaders([
      'X-Requested-With' => 'XMLHttpRequest',
      'Authorization'    => 'Bearer ' . $this->loginJsonResponse->token
    ])->json('GET', '/api/wallet/currencies', [
      'wallet_token' => $this->walletResponse->token
    ]);
    $response->assertStatus(200);
  }

  public function testWalletCurrenciesWithoutToken()
  {
    $response = $this->withHeaders([
      'X-Requested-With' => 'XMLHttpRequest',
      'Authorization'    => 'Bearer xxx'
    ])->json('GET', '/api/wallet/currencies', [
      'wallet_token' => 'xxx'
    ]);
    $response->assertStatus(401);
  }
}
