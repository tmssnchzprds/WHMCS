<?php

namespace WHMCS\Module\Addon\Resellerclubdomains\Controllers;

use WHMCS\Module\Addon\Resellerclubdomains\Services\CronService;
use WHMCS\Module\Addon\Resellerclubdomains\Services\ErrorHandler;
use WHMCS\Module\Addon\Resellerclubdomains\Services\OrderboxService;
use WHMCS\Module\Addon\Resellerclubdomains\Services\WHMCSService;

class ClientController
{

   public function addCustomer($params)
   {
      try {
         $recentAction = (new CronService())->getActionLog([
             "action" => "addClient",
             "entity" => $params["email"], // client id
             "timestamp" => date("Y-m-d H:i:s", time() - 120),
         ]);
         // skip if there was a recent client update done via a cron that might have triggered this method
         if (!is_null($recentAction)) {
            return;
         }

         // get client details to get phone CC + phone split and then add Customer in OB
         $clientDetails = (new WHMCSService())->getClientDetails(
             $params["client_id"]
         );

         $customerId = (new OrderboxService())->handleCustomerSignup(
             $clientDetails
         );

         if ($customerId) {
            // add custom field value to map this OB Customer ID
            (new WHMCSService())->updateClient([
                "clientid" => $params["client_id"],
                "customfields" => base64_encode(
                    serialize([
                        CUSTOM_FIELD_OB_CUSTOMER_ID => $customerId,
                    ])
                ),
            ]);

            // @TODO map details in ob_customer??
         }
      } catch (\Exception $e) {
         (new ErrorHandler())->log([
             "action" => __METHOD__,
             "request" => $params,
             "response" => $e->getMessage(),
             "exception" => $e,
         ]);
      }
   }

   function updateCustomer($params)
   {
      try {
         $recentAction = (new CronService())->getActionLog([
             "action" => "updateClient",
             "entity" => $params["userid"], // client id
             "timestamp" => date("Y-m-d H:i:s", time() - 120),
         ]);
         // skip if there was a recent client update done via a cron that might have triggered this method
         if (!is_null($recentAction)) {
            return;
         }

         $whmcsService = new WHMCSService();
         $clientDetails = $whmcsService->getClientDetailsByEmail(
             $params["email"]
         );

         $customFieldId = (new WHMCSService())->getCustomFieldId(
             CUSTOM_FIELD_OB_CUSTOMER_ID
         );
         $customFieldId = $customFieldId->id;
         if ($customFieldId) {
            $customFieldsData = $clientDetails["customfields"];
            $customFieldsArrayKeys = array_flip(
                array_column($customFieldsData, "id")
            );
            $customerId = $customFieldsData[$customFieldsArrayKeys[$customFieldId]]["value"];
            $clientDetails["customer-id"] = $customerId;
            if (!empty($customerId)) {
               (new OrderboxService())->customerUpdate($clientDetails);
            }
         }
      } catch (\Exception $e) {
         (new ErrorHandler())->log([
             "action" => __METHOD__,
             "request" => $params,
             "response" => $e->getMessage(),
             "exception" => $e,
         ]);
      }
   }

   private function _getCustomFieldValueFromClientDetails(
       $clientDetails,
       $customFieldName = CUSTOM_FIELD_OB_CUSTOMER_ID
   )
   {
      $customFieldId = (new WHMCSService())->getCustomFieldId(
          $customFieldName
      );
      $customFieldId = $customFieldId->id;

      if ($customFieldId) {
         $customFieldsData = $clientDetails["customfields"];
         $customFieldsArrayKeys = array_flip(
             array_column($customFieldsData, "id")
         );
         $customerId =
             $customFieldsData[$customFieldsArrayKeys[$customFieldId]]["value"];
         if (!empty($customerId)) {
            return $customerId;
         }
      }

      return false;
   }

   function deleteCustomer($params)
   {
      try {
         if (
             array_key_exists("userid", $params) &&
             !empty($params["userid"])
         ) {
            $clientId = $params["userid"];
            $clientDetails = (new WHMCSService())->getClientDetails(
                $clientId
            );
            $customerId = $this->_getCustomFieldValueFromClientDetails(
                $clientDetails
            );
            if ($customerId) {
               $response = (new OrderboxService())->deleteCustomer([
                   "customer-id" => $customerId,
               ]);
            }
         }
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
