<?php

namespace WHMCS\Module\Addon\Resellerclubdomains\Library;

use GuzzleHttp\Client;

class HTTPClient
{
   private $client;

   public function __construct($baseURI, $stack = [])
   {
      $options = [
          'http_errors' => false,
          'base_uri' => $baseURI,
          'verify' => !DEBUG_MODE, // disable SSL verification in DEBUG MODE
      ];

      if (!empty($stack)) {
         $options['handler'] = $stack;
      }
      $this->client = new Client($options);
   }

   private function _request($method, $url, $requestParams = [])
   {
      $requestParams['debug'] = false;
      $response = $this->client->request($method, $url, $requestParams);
      return [
          'body' => $response->getBody()->getContents(),
         // 'http_code' => $response->getStatusCode()
      ];
   }

   public function get($url, $params = [])
   {
      return $this->_request('GET', $url, ['query' => $params]);
   }

   public function post($url, $params)
   {
      return $this->_request('POST', $url, ['form_params' => $params]);
   }

   public function postAsync($url, $params)
   {
      return $this->client->requestAsync('POST', $url, $params)->wait(); // not going for truly async for now, since this handling is temp
   }

   public function patch($url, $params = [])
   {
      return $this->_request("PATCH", $url, ['form_params' => $params]);
   }

   public function delete($url, $params = [])
   {
      return $this->_request("DELETE", $url, ['form_params' => $params]);
   }
}
