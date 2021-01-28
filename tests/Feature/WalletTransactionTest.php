<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\{WithoutMiddleware,WithFaker};
use Tests\TestCase;
use Carbon\Carbon;
use App\Services\WalletService;

class WalletTransactionTest extends AdminAccountTestCase
{
  public function testWalletTransactionWithValidData()
  {
    $this->initialUser();
    $this->getWalletToken();

    $data = [
      "uuid" => "60112995012ac", 
      "start_date" => "2021-01-27", 
      "end_date" => "2021-01-27", 
      "currency" => "CNY",
      "wallet_token" => $this->walletResponse->token
    ]; 

    $headers = [
      'Authorization' => 'Bearer ' . $this->loginJsonResponse->token
    ];

    $expectedResponse = [
      "status" => true, 
      "status_code" => 200, 
      "data" => [
        [
          "amount" => "500", 
          "type" => "credit", 
          "reason" => "Initial deposit", 
          "timestamp" => "2021-01-27T08:51:39.000000Z" 
        ] 
      ] 
    ];  

    $this->mockExternalAPI(WalletService::class, 'walletTransaction', $expectedResponse['status_code'], $headers, $expectedResponse);
    $response = $this->get('/api/wallet/transaction', $data);
    $response->assertStatus(200);
  }

  public function testWalletTransactionWithInvalidData()
  {
    $this->initialUser();
    $this->getWalletToken();

    $data = [
      "uuid" => "", 
      "start_date" => "", 
      "end_date" => "", 
      "currency" => "",
      "wallet_token" => $this->walletResponse->token
    ]; 

    $headers = [
      'Authorization' => 'Bearer ' . $this->loginJsonResponse->token
    ];

    $expectedResponse = [
      "status" => false, 
      "status_code" => 400, 
      "errors" => [
        "uuid" => [
          "The uuid field is required." 
        ], 
        "start_date" => [
          "The start date field is required." 
        ], 
        "end_date" => [
          "The end date field is required." 
        ] 
      ] 
    ];  

    $this->mockExternalAPI(WalletService::class, 'walletTransaction', $expectedResponse['status_code'], $headers, $expectedResponse);
    $response = $this->get('/api/wallet/transaction', $data);
    $response->assertStatus(400);
  }

  public function testWalletTransactionWithoutToken()
  {
    $data = [
      "uuid" => "60112995012ac", 
      "start_date" => "2021-01-27", 
      "end_date" => "2021-01-27", 
      "currency" => "CNY",
      "wallet_token" => 'xxx'
    ]; 

    $headers = [
      'Authorization' => 'Bearer xxx'
    ];

    $expectedResponse = [
      "status" => false, 
      "status_code" => 401, 
      "error" => "Unauthorized." 
    ];   

    $this->mockExternalAPI(WalletService::class, 'walletTransaction', $expectedResponse['status_code'], $headers, $expectedResponse);
    $response = $this->get('/api/wallet/transaction', $data);
    $response->assertStatus(401);
  }
}
