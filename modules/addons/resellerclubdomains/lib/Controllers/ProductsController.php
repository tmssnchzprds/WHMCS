<?php

namespace WHMCS\Module\Addon\Resellerclubdomains\Controllers;


use WHMCS\Module\Addon\Resellerclubdomains\Services\WHMCSService;
use WHMCS\Module\Addon\Resellerclubdomains\Services\CronService;

class ProductsController
{

   protected function getProductKindFromPlanId($planId)
   {
      try {
         $kind = null;
         $kind = array_search($planId, OB_PLAN_ID_TITAN_GLOBAL);
         if (empty($kind)) {
            $kind = array_search($planId, OB_PLAN_ID_TITAN_INDIA);
         }
         return $kind;

      } catch (Exception $e) {
         throw $e;
      }
   }

   protected function getPlanIdFromProductId($productId)
   {
      try {
         $response = (new CronService())->getConfigs([
             'select_params' => [
                 'key as planId',
             ],
             'value' => $productId,
             'type' => CONFIG_OB_RESELLERCLUB_MAP_PLAN_PRODUCT
         ]);

         if (!empty($response[0])) {
            return $response[0]->planId;
         }
         return null;
      } catch (\Exception $e) {
         throw $e;
      }
   }

   protected function getUpgradeDetails($upgradeId)
   {
      try {
         return (new WHMCSService())->getUpgradeDetails($upgradeId);
      } catch (\Exception $e) {
         throw $e;
      }
   }

   protected function getFreePlanIdFromProductKey($productKey)
   {
      try {
         $response = (new CronService())->getConfigs([
             'select_params' => [
                 'value as planId',
             ],
             'type' => CONFIG_OB_RESELLERCLUB_PRODUCT_KEY_PLANID_FREE,
             'key' => $productKey
         ]);

         return $response[0]->planId;
      } catch (\Exception $e) {
         throw $e;
      }
   }

   protected function getProductIdFreeFromProductKey($productKey)
   {
      try {
         $planId = $this->getFreePlanIdFromProductKey($productKey);
         return $this->getProductIdFromFromPlanId($planId);
      } catch (\Exception $e) {
         throw $e;
      }
   }

   protected function getProductIdFromFromPlanId($planId)
   {
      try {
         $response = (new CronService())->getConfigs([
             'select_params' => [
                 'value as productId',
             ],
             'key' => $planId,
             'type' => CONFIG_OB_RESELLERCLUB_MAP_PLAN_PRODUCT
         ]);

         if (!empty($response[0])) {
            return $response[0]->productId;
         }

         return null;
      } catch (\Exception $e) {
         throw $e;
      }
   }

   protected function getManageProductId($clientId, $productId, $domainName)
   {
      try {
         $manageProductId = null;
         $responseProducts = (new WHMCSService())->searchOrders([
             'clientid' => $clientId,
             'pid' => $productId,
             'domain' => $domainName
         ]);

         if ($responseProducts['totalresults'] > 0) {
            foreach ($responseProducts['products']['product'] as $product) {
               if ($product['pid'] == $productId && $product['domain'] == $domainName) {
                  $manageProductId = $product['id'];
               }
            }
         }

         return $manageProductId;
      } catch (\Exception $e) {
         throw $e;
      }
   }

}
