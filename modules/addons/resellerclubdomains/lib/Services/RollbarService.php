<?php

namespace WHMCS\Module\Addon\Resellerclubdomains\Services;

use WHMCS\Module\Addon\Resellerclubdomains\Library\HTTPClient;

class RollbarService extends HTTPClient
{
    private $baseUri = "https://api.rollbar.com";
    private $urlCreateItem = "/api/1/item/";
    private $accessTokenPostServerItem = ROLLBAR_ACCESS_TOKEN_POST_SERVER_ITEM;

    public function __construct()
    {
        parent::__construct($this->baseUri);
    }

    private function _createRequestJSON($params)
    {
        extract($params);
        $json = [
            'data' => [
                "environment" => MODULE_ENVIRONMENT,
                "body" => [
                    "message" => [
                        "body" => (!is_null($responseProcessed) ? $responseProcessed : $response),
                        "request" => $request,
                    ]
                ],
                "timestamp" => time(),
                "code_version" => MODULE_VERSION,
                "language" => "php",
                "context" => $action,
                "request" => [
                    "user_ip" => $_SERVER['REMOTE_ADDR'],
                ],
                "person" => [
                    "id" => (new WHMCSService())->getAddonModuleConfiguration(['setting' => CONFIG_RESELLER_ID])
                ]
            ]
        ];

        return json_encode($json);
    }

    public function createItem($params = [])
    {
        try {
            $requestParams = [
                'headers' => [
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                    'X-Rollbar-Access-Token' => $this->accessTokenPostServerItem
                ],
                'body' => $this->_createRequestJSON($params)
            ];

            $response = parent::postAsync($this->urlCreateItem, $requestParams);
        } catch (\Exception $e) {
        }
    }
}
