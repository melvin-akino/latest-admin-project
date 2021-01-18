<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\{WithoutMiddleware,WithFaker};
use Tests\TestCase;
use Carbon\Carbon;
use App\Services\WalletService;

class WalletClientTest extends AdminAccountTestCase
{
  public function testWalletClientList()
  {
    $this->initialUser();
    $this->getWalletToken();
    $response = $this->withHeaders([
      'Authorization'    => 'Bearer ' . $this->loginJsonResponse->token
    ])->json('GET', '/api/wallet/clients', [
      'wallet_token' => $this->walletResponse->token
    ]);
    $response->assertStatus(200);
  }

  public function testWalletClientListWithoutToken()
  {
    $response = $this->withHeaders([
      'Authorization'    => 'Bearer xxx'
    ])->json('GET', '/api/wallet/clients', [
      'wallet_token' => 'xxx'
    ]);
    $response->assertStatus(401);
  }

  public function testAddWalletClientWithValidData()
  {
    $this->initialUser();
    $this->getWalletToken();

    $data = [
      "name" => "Wallet Client", 
      "client_id" => "walletclient", 
      "client_secret" => "walletsecret", 
      "wallet_token" => $this->walletResponse->token
    ]; 

    $headers = [
      'Authorization' => 'Bearer ' . $this->loginJsonResponse->token
    ];

    $expectedResponse = [
      "status" => true, 
      "status_code" => 200, 
      "message" => "Client successfully created.", 
      "data" => [
        "name" => "Wallet Client", 
        "client_id" => "walletclient", 
        "client_secret" => "walletsecret", 
        "revoked" => false, 
        "created_at" => Carbon::now()->format('Y-m-d H:i:s')
      ] 
    ]; 

    $this->mockExternalAPI(WalletService::class, 'createClient', $expectedResponse['status_code'], $headers, $expectedResponse);
    $response = $this->post('/api/wallet/create', $data);
    $response->assertStatus(200);
  }

  public function testAddWalletClientWithInvalidData()
  {
    $this->initialUser();
    $this->getWalletToken();

    $data = [
      "name" => "", 
      "client_id" => "", 
      "client_secret" => "", 
      "wallet_token" => $this->walletResponse->token
    ]; 

    $headers = [
      'Authorization'    => 'Bearer ' . $this->loginJsonResponse->token
    ];

    $expectedResponse =  [
      "status" => false, 
      "status_code" => 400, 
      "errors" => [
        "client_id" => [
          "The client id field is required." 
        ], 
        "client_secret" => [
          "The client secret field is required." 
        ], 
        "name" => [
          "The name field is required." 
        ] 
      ] 
    ]; 

    $this->mockExternalAPI(WalletService::class, 'createClient', $expectedResponse['status_code'], $headers, $expectedResponse);
    $response = $this->post('/api/wallet/create', $data);
    $response->assertStatus(400);
  }

  public function testAddWalletClientWithoutToken()
  {
    $data = [
      "name" => "Wallet Client", 
      "client_id" => "walletclient", 
      "client_secret" => "walletsecret", 
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

    $this->mockExternalAPI(WalletService::class, 'createClient', $expectedResponse['status_code'], $headers, $expectedResponse);
    $response = $this->post('/api/wallet/create', $data);
    $response->assertStatus(401);
  }


  public function testRevokeWalletClientWithValidData()
  {
    $this->initialUser();
    $this->getWalletToken();

    $data = [
      "client_id" => "walletclient", 
      "client_secret" => "walletsecret", 
      "wallet_token" => $this->walletResponse->token
    ]; 

    $headers = [
      'Authorization' => 'Bearer ' . $this->loginJsonResponse->token
    ];

    $expectedResponse = [
      "status" => true, 
      "status_code" => 200, 
      "data" => [
        "message" => "Client successfully revoked." 
      ] 
    ]; 

    $this->mockExternalAPI(WalletService::class, 'revokeClient', $expectedResponse['status_code'], $headers, $expectedResponse);
    $response = $this->post('/api/wallet/revoke', $data);
    $response->assertStatus(200);
  }

  public function testRevokeWalletClientWithInvalidData()
  {
    $this->initialUser();
    $this->getWalletToken();

    $data = [
      "client_id" => "", 
      "client_secret" => "", 
      "wallet_token" => $this->walletResponse->token
    ]; 

    $headers = [
      'Authorization' => 'Bearer ' . $this->loginJsonResponse->token
    ];

    $expectedResponse =  [
      "status" => false, 
      "status_code" => 400, 
      "errors" => [
        "client_id" => [
          "The client id field is required." 
        ], 
        "client_secret" => [
          "The client secret field is required." 
        ] 
      ] 
    ];

    $this->mockExternalAPI(WalletService::class, 'revokeClient', $expectedResponse['status_code'], $headers, $expectedResponse);
    $response = $this->post('/api/wallet/revoke', $data);
    $response->assertStatus(400);
  }

  public function testRevokeWalletClientWithoutToken()
  {
    $data = [
      "client_id" => "walletclient", 
      "client_secret" => "walletsecret", 
      "wallet_token" => "xxx"
    ]; 

    $headers = [
      'Authorization' => 'Bearer xxx'
    ];

    $expectedResponse =  [
      "status" => false, 
      "status_code" => 401, 
      "error" => "Unauthorized." 
    ]; 

    $this->mockExternalAPI(WalletService::class, 'revokeClient', $expectedResponse['status_code'], $headers, $expectedResponse);
    $response = $this->post('/api/wallet/revoke', $data);
    $response->assertStatus(401);
  }
}
