<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\{WithoutMiddleware,WithFaker};
use Tests\TestCase;
use Carbon\Carbon;
use App\Services\WalletService;
class WalletCurrencyTest extends AdminAccountTestCase
{
  public function testWalletCurrencyListWithToken()
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

  public function testWalletCurrencyListWithoutToken()
  {
    $response = $this->withHeaders([
      'X-Requested-With' => 'XMLHttpRequest',
      'Authorization'    => 'Bearer xxx'
    ])->json('GET', '/api/wallet/currencies', [
      'wallet_token' => 'xxx'
    ]);
    $response->assertStatus(401);
  }

  public function testAddWalletCurrencyWithValidData()
  {
    $this->initialUser();
    $this->getWalletToken();

    $data = [
      "name" => "CAD", 
      "is_enabled" => true, 
      "wallet_token" => $this->walletResponse->token
    ]; 

    $headers = [
      'Authorization' => 'Bearer ' . $this->loginJsonResponse->token
    ];

    $expectedResponse = [
      "status" => true, 
      "status_code" => 200, 
      "message" => "success", 
      "data" => [
        "name" => "CAD", 
        "is_enabled" => true, 
        "created_at" => Carbon::now()->format('Y-m-d H:i:s')
      ] 
    ]; 

    $this->mockExternalAPI(WalletService::class, 'createCurrency', $expectedResponse['status_code'], $headers, $expectedResponse);
    $response = $this->post('/api/wallet/currencies/create', $data);
    $response->assertStatus(200);
  }

  public function testAddWalletCurrencyWithInvalidData()
  {
    $this->initialUser();
    $this->getWalletToken();

    $data = [
      "name" => "", 
      "is_enabled" => "", 
      "wallet_token" => $this->walletResponse->token
    ]; 

    $headers = [
      'Authorization' => 'Bearer ' . $this->loginJsonResponse->token
    ];

    $expectedResponse = [
      "status" => false, 
      "status_code" => 400, 
      "errors" => [
        "name" => [
            "The name field is required." 
        ] 
      ] 
    ]; 

    $this->mockExternalAPI(WalletService::class, 'createCurrency', $expectedResponse['status_code'], $headers, $expectedResponse);
    $response = $this->post('/api/wallet/currencies/create', $data);
    $response->assertStatus(400);
  }

  public function testAddWalletCurrencyWithoutToken()
  {
    $data = [
      "name" => "CAD", 
      "is_enabled" => true, 
      "wallet_token" => "xxx"
    ]; 

    $headers = [
      'Authorization' => 'Bearer xxx'
    ];

    $expectedResponse = [
      "status" => false, 
      "status_code" => 401, 
      "error" => "Unauthorized." 
    ]; 

    $this->mockExternalAPI(WalletService::class, 'createCurrency', $expectedResponse['status_code'], $headers, $expectedResponse);
    $response = $this->post('/api/wallet/currencies/create', $data);
    $response->assertStatus(401);
  }

  public function testUpdateWalletCurrencyWithValidData()
  {
    $this->initialUser();
    $this->getWalletToken();

    $data = [
      "name" => "CAD", 
      "is_enabled" => false, 
      "wallet_token" => $this->walletResponse->token
    ]; 

    $headers = [
      'Authorization' => 'Bearer ' . $this->loginJsonResponse->token
    ];

    $expectedResponse =  [
      "status" => true, 
      "status_code" => 200, 
      "message" => "Currency successfully updated." 
    ]; 

    $this->mockExternalAPI(WalletService::class, 'updateCurrency', $expectedResponse['status_code'], $headers, $expectedResponse);
    $response = $this->post('/api/wallet/currencies/update', $data);
    $response->assertStatus(200);
  }

  public function testUpdateWalletCurrencyWithInvalidData()
  {
    $this->initialUser();
    $this->getWalletToken();

    $data = [
      "name" => "", 
      "is_enabled" => "", 
      "wallet_token" => $this->walletResponse->token
    ]; 

    $headers = [
      'Authorization' => 'Bearer ' . $this->loginJsonResponse->token
    ];

    $expectedResponse = [
      "status" => false, 
      "status_code" => 400, 
      "errors" => [
        "name" => [
            "The name field is required." 
        ] 
      ] 
    ]; 

    $this->mockExternalAPI(WalletService::class, 'updateCurrency', $expectedResponse['status_code'], $headers, $expectedResponse);
    $response = $this->post('/api/wallet/currencies/update', $data);
    $response->assertStatus(400);
  }

  public function testUpdateWalletCurrencyWithoutToken()
  {
    $data = [
      "name" => "CAD", 
      "is_enabled" => false, 
      "wallet_token" => "xxx"
    ]; 

    $headers = [
      'Authorization' => 'Bearer xxx'
    ];

    $expectedResponse = [
      "status" => false, 
      "status_code" => 401, 
      "error" => "Unauthorized." 
    ]; 

    $this->mockExternalAPI(WalletService::class, 'updateCurrency', $expectedResponse['status_code'], $headers, $expectedResponse);
    $response = $this->post('/api/wallet/currencies/update', $data);
    $response->assertStatus(401);
  }
}
