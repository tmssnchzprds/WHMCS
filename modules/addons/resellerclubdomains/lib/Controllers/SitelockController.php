<?php

namespace WHMCS\Module\Addon\Resellerclubdomains\Controllers;

use WHMCS\Module\Addon\Resellerclubdomains\Services\OrderboxService;
use WHMCS\Module\Addon\Resellerclubdomains\Services\WHMCSService;
use WHMCS\Module\Addon\Resellerclubdomains\Services\ErrorHandler;
use Smarty;

class SitelockController extends ProductsController
{
   public function handleOrder($params)
   {
      try {
         if (array_key_exists("domain", $params) &&
             !empty($params["domain"]) &&
             !empty($params["domain"]->domain) &&
             !empty($params["domain"]->userid) &&
             !empty($params["domain"]->registrar) &&
             $params["domain"]->registrar == REGISTRAR_RESELLERCLUB) {

            $whmcsService = new WHMCSService();
            $orderboxService = new OrderboxService();
            $domainName = $params["domain"]->domain;
            $clientId = $params["domain"]->userid;
            $resellerId = $whmcsService->getAddonModuleConfiguration(
                ["setting" => CONFIG_RESELLER_ID]
            );

            $customFieldId = $whmcsService->getCustomFieldId(
                CUSTOM_FIELD_OB_CUSTOMER_ID
            );
            $customFieldId = $customFieldId->id;
            $details = $whmcsService->getClientsWithCustomField([
                "custom_field_id" => $customFieldId,
                "client_id" => $clientId,
                "reseller_id" => $resellerId,
            ]);

            if (!empty($details)) {
               $customerId = $details[0]->ob_customer_id;
            }

            $responseListDomains = $orderboxService->listDomains([
                "page-no" => 1,
                "no-of-records" => 10,
                "order-by" => ["order-id"],
                "domain-name" => $domainName,
                "customer-id" => $customerId,
            ]);

            if (!empty($responseListDomains)) {

               if (array_key_exists("items_info", $responseListDomains) &&
                   array_key_exists("recsindb", $responseListDomains["items_info"])
                   && $responseListDomains["items_info"]["recsindb"] > 0
                   && array_key_exists("domains", $responseListDomains)
               ) {
                  $domain = reset($responseListDomains["domains"]);
                  $orderId = $domain["orders.orderid"];

                  $ssoDetails = $orderboxService->getSitelockDetails([
                      "order-id" => $orderId
                  ]);

                  if (is_array($ssoDetails)
                      && array_key_exists("ssoUrl", $ssoDetails)
                      && !empty($ssoDetails["ssoUrl"])) {
                     $smarty = new Smarty();
                     $smarty->setTemplateDir(dirname(__FILE__) . "/../../templates/sitelock");
                     $smarty->assign([
                         "orderId" => $orderId,
                         "ssoUrl" => $ssoDetails["ssoUrl"],
                         "theme" => $whmcsService->getSystemTheme()
                     ]);
                     return $smarty->fetch("manage.tpl");
                  }
               }
            }
         }
         return '';
      } catch (\Exception $e) {
         (new ErrorHandler())->log([
             "action" => __METHOD__,
             "request" => $params,
             "response" => $e->getMessage(),
             "exception" => $e,
         ]);
      }
   }
}
