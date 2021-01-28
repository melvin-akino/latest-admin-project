<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
class WalletService
{
  public $url;
  private $clientId;
  private $clientSecret;
  private $http;

  public function __construct($url, $clientId, $clientSecret)
  {
    $this->url = $url;
    $this->clientId = $clientId;
    $this->clientSecret = $clientSecret;
    $this->http = new Client();
  }

  public function getAccessToken()
  {
    $response = $this->http->request('POST', $this->url.'/oauth/token', [
      'form_params' => [
        'client_id' => $this->clientId,
        'client_secret' => $this->clientSecret,
        'grant_type' => 'client_credentials'
      ]
    ]);
    $response = json_decode($response->getBody());
    return $response->data->access_token;
  }

  public function getClients($token)
  {
    try {
      $response = $this->http->request('GET', $this->url.'/clients', [
        'headers' => [
          'Authorization' => 'Bearer '.$token
        ]
      ]);
    } catch(ClientException $e) {
      $response = $e->getResponse();
    }
    return $response;
  }

  public function createClient($data) 
  {
    try {
      $response = $this->http->request('POST', $this->url.'/client/create', [
        'form_params' => [
          'name' => $data->name,
          'client_id' => $data->client_id,
          'client_secret' => $data->client_secret
        ],
        'headers' => [
          'Authorization' => 'Bearer '.$data->wallet_token
        ]
      ]);
    } catch(ClientException $e) {
      $response = $e->getResponse();
    }
    return $response;
  }

  public function revokeClient($data) 
  {
    try {
      $response = $this->http->request('POST', $this->url.'/client/revoke', [
        'form_params' => [
          'client_id' => $data->client_id,
          'client_secret' => $data->client_secret
        ],
        'headers' => [
          'Authorization' => 'Bearer '.$data->wallet_token
        ]
      ]);
    } catch(ClientException $e) {
      $response = $e->getResponse();
    }
    return $response;
  }

  public function getCurrencies($token) 
  {
    try {
      $response = $this->http->request('GET', $this->url.'/currency/list', [
        'headers' => [
          'Authorization' => 'Bearer '.$token
        ]
      ]);
    } catch(ClientException $e) {
      $response = $e->getResponse();
    }
    return $response;
  }

  public function createCurrency($data) 
  {
    try {
      $response = $this->http->request('POST', $this->url.'/currency/create', [
        'form_params' => [
          'name' => $data->name,
          'is_enabled' => $data->is_enabled
        ],
        'headers' => [
          'Authorization' => 'Bearer '.$data->wallet_token
        ]
      ]);
    } catch(ClientException $e) {
      $response = $e->getResponse();
    }
    return $response;
  }

  public function updateCurrency($data) 
  {
    try {
      $response = $this->http->request('POST', $this->url.'/currency/update', [
        'form_params' => [
          'name' => $data->name,
          'is_enabled' => $data->is_enabled
        ],
        'headers' => [
          'Authorization' => 'Bearer '.$data->wallet_token
        ]
      ]);
    } catch(ClientException $e) {
      $response = $e->getResponse();
    }
    return $response;
  }

  public function walletCredit($data) 
  {
    try {
      $response = $this->http->request('POST', $this->url.'/wallet/credit', [
        'form_params' => [
          'uuid' => $data->uuid,
          'currency' => $data->currency,
          'amount' => $data->amount,
          'reason' => $data->reason
        ],
        'headers' => [
          'Authorization' => 'Bearer '.$data->wallet_token
        ]
      ]);
    } catch(ClientException $e) {
      $response = $e->getResponse();
    }
    return $response;
  }

  public function walletDebit($data) 
  {
    try {
      $response = $this->http->request('POST', $this->url.'/wallet/debit', [
        'form_params' => [
          'uuid' => $data->uuid,
          'currency' => $data->currency,
          'amount' => $data->amount,
          'reason' => $data->reason
        ],
        'headers' => [
          'Authorization' => 'Bearer '.$data->wallet_token
        ]
      ]);
    } catch(ClientException $e) {
      $response = $e->getResponse();
    }
    return $response;
  }

  public function walletBalance($data) 
  {
    try {
      $response = $this->http->request('GET', $this->url.'/wallet/balance', [
        'query' => [
          'uuid' => $data->uuid,
          'currency' => $data->currency
        ],
        'headers' => [
          'Authorization' => 'Bearer '.$data->wallet_token
        ]
      ]);
    } catch(ClientException $e) {
      $response = $e->getResponse();
    }
    return $response;
  }

  public function walletTransaction($data) 
  {
    try {
      $response = $this->http->request('GET', $this->url.'/wallet/transaction', [
        'query' => [
          'uuid' => $data->uuid,
          'currency' => $data->currency,
          'start_date' => $data->start_date,
          'end_date' => $data->end_date,
        ],
        'headers' => [
          'Authorization' => 'Bearer '.$data->wallet_token
        ]
      ]);
    } catch(ClientException $e) {
      $response = $e->getResponse();
    }
    return $response;
  }
}