<?php

namespace App\Services;

use GuzzleHttp\Client;

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
    $response = $this->http->request('GET', $this->url.'/clients', [
      'headers' => [
        'Authorization' => 'Bearer '.$token
      ]
    ]);
    $response = json_decode($response->getBody());
    return $response;
  }

}