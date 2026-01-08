<?php

namespace WHMCS\Module\Addon\Resellerclubdomains\Services;

use WHMCS\Module\Addon\Resellerclubdomains\Library\HTTPClient;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class OrderboxHttpClient extends HTTPClient
{
   public function __construct()
   {
      parent::__construct(OB_URL_HTTP_API, $this->_getMiddleWares());
   }

   private function _getMiddleWares()
   {
      $stack = HandlerStack::create();
      $resellerCredentials = (new WHMCSService())->getDecryptedResellerAPICredentials();
//      $resellerCredentials = (new WHMCSService())->getResellerAPICredsFromModuleConfig();
      $stack->push($this->_addCredentials([
          'auth-userid' => $resellerCredentials['resellerId'],
          'api-key' => $resellerCredentials['apiKey']
      ]));
      $stack->push($this->_replaceSlugs($resellerCredentials));
      $stack->push($this->_replaceParamSlugs());
      $stack->push($this->_handleResponse($resellerCredentials));
      return $stack;
   }

   private function _replaceSlugs($resellerCredentials)
   {
      try {
         return Middleware::mapRequest(function (RequestInterface $request) use ($resellerCredentials) {
            $uri = $request->getUri();
            $path = urldecode($uri->getPath());
            if (strpos($path, "{resellerId}") !== false) {
               $newUri = $uri->withPath(str_replace("{resellerId}", $resellerCredentials['resellerId'], $path));
               return $request->withUri($newUri);
            }
            return $request;
         });
      } catch (\Exception $e) {
         throw $e;
      }
   }


   private function _recursiveReplaceSlugsInPath($path, $slugs, $replacements)
   {
      foreach ($slugs as $slug) {
         if (strpos($path, "{" . $slug . "}") !== false) {
            $path = str_replace("{" . $slug . "}", $replacements[$slug], $path);
         }
         array_shift($slugs);
         $path = $this->_recursiveReplaceSlugsInPath($path, $slugs, $replacements);
      }
      return $path;
   }


   private function _replaceParamSlugs()
   {
      try {
         return Middleware::mapRequest(function (RequestInterface $request) {
            $uri = $request->getUri();
            $method = $request->getMethod();
            $path = urldecode($uri->getPath());
            if ($method == "POST" || $method == "PATCH" || $method == "DELETE") {
               $body = $request->getBody()->getContents();
               parse_str($body, $queryParams);
            } else {
               $queryString = $uri->getQuery();
               parse_str($queryString, $queryParams);
            }


            $newPath = $this->_recursiveReplaceSlugsInPath($path, OB_API_SLUGS, $queryParams);

            $newUri = $uri->withPath($newPath);
            return $request->withUri($newUri);

//            foreach (OB_API_SLUGS as $slug) {
//               if (strpos($path, "{" . $slug . "}") !== false) {
//                  $newUri = $uri->withPath(str_replace("{" . $slug . "}", $queryParams[$slug], $path));
//                  return $request->withUri($newUri);
//               }
//            }
//
//            return $request;
         });
      } catch (\Exception $e) {
         throw $e;
      }
   }

   private function _addCredentials($credentials)
   {
      return function (callable $handler) use ($credentials) {
         return function (RequestInterface $request, array $options) use ($handler, $credentials) {
            foreach ($credentials as $key => $value) {
               $request = $request->withUri(Uri::withQueryValue(
                   $request->getUri(),
                   $key,
                   $value
               ));
            }
            return $handler($request, $options);
         };
      };
   }


   private function _handleResponse($resellerCredentials)
   {
      return function (callable $handler) use ($resellerCredentials) {
         return function (RequestInterface $request, array $options) use ($handler, $resellerCredentials) {
            $promise = $handler($request, $options);
            return $promise->then(
                function (ResponseInterface $response) use ($request, $resellerCredentials) {
                   $code = $response->getStatusCode();
                   $uri = $request->getUri();
                   $path = $uri->getPath();
                   $queryParams = $uri->getQuery();

                   if ($code >= 400) {
                      $module = MODULE_NAME;
                      $action = $path;
                      if ($request->getMethod() == "POST") {
                         $body = (string)$request->getBody();
                         parse_str($body, $formParams);
                         $requestString = $formParams;
                      } else {
                         $requestString = $queryParams;
                      }
                      $responseData = $code;
                      $processedData = $response->getBody()->getContents();
                      $replaceVars = [$resellerCredentials['resellerId'], $resellerCredentials['apiKey']];
                      (new ErrorHandler())->log([
                          'action' => $action,
                          'request' => $requestString,
                          'response' => $processedData,
                          'replaceVars' => $replaceVars
                      ]);

                      if ($code == 403) {
                         (new WHMCSService())->sendAdminEmail(
                             [
                                 'subject' => "Error while processing WHMCS request for " . MODULE_NAME,
                                 'body' => (string)$processedData
                             ]
                         );
                         throw new \Exception("Request blocked at Orderbox. Please whitelist your IP from your control panel with Orderbox");
                      }

                      throw new \Exception($processedData);
                   }
                   return $response;
                }
            );
         };
      };
   }
}
