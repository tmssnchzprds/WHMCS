<?php

namespace WHMCS\Module\Addon\Resellerclubdomains\Controllers;

use WHMCS\Module\Addon\Resellerclubdomains\Library\Utils;
use WHMCS\Module\Addon\Resellerclubdomains\Services\CronService;
use WHMCS\Module\Addon\Resellerclubdomains\Services\OrderboxService;
use WHMCS\Module\Addon\Resellerclubdomains\Services\ErrorHandler;
use WHMCS\Module\Addon\Resellerclubdomains\Services\WHMCSService;
use Smarty;

class TitanController extends ProductsController
{
   private function _setPaidProductsAsConfigOptionsUpgradable()
   {
      try {
         (new WHMCSService())->updateProducts([
             "configoptionsupgrade" => 1,
         ], [
             "paytype" => "recurring"
         ]);
      } catch (\Exception $e) {
         throw $e;
      }
   }

   private function _setAutoTerminateDaysForFreeProducts()
   {
      try {
         (new WHMCSService())->updateProducts([
             "autoterminatedays" => (OB_NO_OF_MONTHS_TITAN_FREE * 30),
         ], [
             "paytype" => "free"
         ]);
      } catch (\Exception $e) {
         throw $e;
      }
   }

   private function _getProductIdsFromConfig()
   {
      try {
         return (new CronService())->getConfigs([
             "select_params" => [
                 "value as productIds",
             ],
             "type" => CONFIG_OB_RESELLERCLUB_MAP_PLAN_PRODUCT
         ]);
      } catch (\Exception $e) {
         throw $e;
      }
   }

   private function _resetLocalTitanConfigs()
   {
      try {
         $cronService = new CronService();

         $configTypes = [
             CONFIG_OB_RESELLERCLUB_PRODUCT_GROUP_ID,
             CONFIG_OB_RESELLERCLUB_MAP_PLAN_PRODUCT,
             CONFIG_OB_RESELLERCLUB_PRODUCT_KEY_PLANID_FREE,
             CONFIG_OB_RESELLERCLUB_PRODUCT_KEY_PLANS
         ];
         foreach ($configTypes as $config) {
            $cronService->deleteConfigs([
                "type" => $config
            ]);
         }
      } catch (Exception $e) {
         throw $e;
      }
   }

   protected function displayNameFromProductKey($productKey)
   {
      try {
         if ($productKey == OB_PRODUCT_KEY_TITANEMAIL_INDIA) {
            return "India";
         }
         return "Global";
      } catch (Exception $e) {
         throw $e;
      }
   }

   private function _addSaveProductGroup()
   {
      try {
         $productGroupId = (new WHMCSService())->addProductGroupIfNotExists([
             "name" => PRODUCT_GROUP_NAME,
             "hidden" => true
         ]);

         (new CronService())->addConfig([
             'type' => CONFIG_OB_RESELLERCLUB_PRODUCT_GROUP_ID,
             'key' => '',
             'value' => $productGroupId
         ]);

         return $productGroupId;
      } catch (Exception $e) {
         throw $e;
      }
   }

   private function _addSaveConfigurableOptions()
   {
      try {
         $productConfigGroupNames = PRODUCT_CONFIG_GROUP_NAME_TITAN;
         $productConfigOptionIds = null;
         $whmcsService = new WHMCSService();
         foreach ($productConfigGroupNames as $key => $productConfigGroupName) {
            $productConfigGroupId = $whmcsService->addProductConfigGroupIfNotExists([
                "name" => $productConfigGroupName
            ]);

            // adding corresponding product config option
            $paramsAddProductConfigOption = [
                "gid" => $productConfigGroupId,
                "optionname" => PRODUCT_CONFIG_OPTION_NAME_TITAN,
                "optiontype" => PRODUCT_CONFIG_OPTION_TYPE_QUANTITY
            ];

            if ($productConfigGroupName == PRODUCT_CONFIG_GROUP_NAME_TITAN['business_free']) {
               $paramsAddProductConfigOption["qtyminimum"] = OB_NO_OF_ACCOUNTS_TITAN_FREE;
               $paramsAddProductConfigOption["qtymaximum"] = OB_NO_OF_ACCOUNTS_TITAN_FREE;
            } else {
               $paramsAddProductConfigOption["qtyminimum"] = OB_NO_OF_ACCOUNTS_TITAN_FREE;

            }

            $productConfigOptionIds[$key] = $whmcsService->addProductConfigOptionIfNotExists($paramsAddProductConfigOption);

            $whmcsService->addProductConfigOptionSubIfNotExists([
                'configid' => $productConfigOptionIds[$key],
                'optionname' => PRODUCT_CONFIG_OPTION_SUB_TITAN[$key]
            ]);
         }

         return $productConfigOptionIds;
      } catch (Exception $e) {
         throw $e;
      }
   }


   private function _addSaveProducts($productGroupId)
   {
      try {
         $productIds = [];
         $cronService = new CronService();
         $whmcsService = new WHMCSService();
         $productKeys = [OB_PRODUCT_KEY_TITANEMAIL_GLOBAL, OB_PRODUCT_KEY_TITANEMAIL_INDIA];

         foreach ($productKeys as $productKey) {
            $plans = (new OrderboxService())->getPlanDetails([
                "product-key" => $productKey
            ]);
            $plans = $plans[$productKey];

            if (!empty($plans)) {
               $cronService->addConfig([
                   'type' => CONFIG_OB_RESELLERCLUB_PRODUCT_KEY_PLANS,
                   'key' => $productKey,
                   'value' => json_encode(array_keys($plans))
               ]);

               foreach ($plans as $planId => $planDetails) {
                  if (array_key_exists("plan_status", $planDetails)
                      && !empty($planDetails["plan_status"])
                      && strtolower($planDetails["plan_status"]) == "active") {

                     $paramsAddProduct = [
                         'name' => $planDetails['plan_name'] . ' (' . $this->displayNameFromProductKey($productKey) . ')',
                         'gid' => $productGroupId,
                         'module' => PROVISIONING_MODULE_IDENTIFIER,
                         'shortdescription' => $planDetails['description'] ?? ' ',
                         'type' => 'other', // @TODO use some config for this?
                         'hidden' => true,
                     ];

                     $paramsAddProduct['paytype'] = 'recurring';
                     if (array_key_exists("is_free", $planDetails)
                         && !empty($planDetails["is_free"])) {
                        $paramsAddProduct['paytype'] = 'free';

                        $cronService->addConfig([
                            'type' => CONFIG_OB_RESELLERCLUB_PRODUCT_KEY_PLANID_FREE,
                            'key' => $productKey,
                            'value' => $planId
                        ]);
                     }

                     $productIds[$planId] = $whmcsService->addProductIfNotExists($paramsAddProduct);
                     $cronService->addConfig([
                         'type' => CONFIG_OB_RESELLERCLUB_MAP_PLAN_PRODUCT,
                         'key' => $planId,
                         'value' => $productIds[$planId]
                     ]);
                  }
               }
            }
         }
         return $productIds;
      } catch (Exception $e) {
         throw $e;
      }
   }

   private function _addProductConfigLinks($productConfigOptionIds, $productIds)
   {
      try {
         $whmcsService = new WHMCSService();
         foreach (OB_PLAN_ID_TITAN_GLOBAL as $key => $planId) {
            $whmcsService->addProductConfigLinkIfNotExists([
                "gid" => $productConfigOptionIds[$key],
                "pid" => $productIds[$planId]
            ]);
         }
         foreach (OB_PLAN_ID_TITAN_INDIA as $key => $planId) {
            $whmcsService->addProductConfigLinkIfNotExists([
                "gid" => $productConfigOptionIds[$key],
                "pid" => $productIds[$planId]
            ]);
         }
      } catch (Exception $e) {
         throw $e;
      }
   }

   private function _setupProductUpgrades($productIds)
   {
      try {
         $whmcsService = new WHMCSService();
         foreach (OB_PLAN_ID_UPGRADE_MAP_TITAN as $planId => $upgradesToPlans) {
            foreach ($upgradesToPlans as $upgradePlanId) {
               $whmcsService->addProductUpgradeProductsIfNotExists([
                   'product_id' => $productIds[$planId],
                   'upgrade_product_id' => $productIds[$upgradePlanId]
               ]);
            }
         }

         return true;
      } catch (Exception $e) {
         throw $e;
      }
   }

   /**
    * Handle Titan Product Creation
    *
    * - Reset (delete) all existing local configs
    * - Add Product Group (if not already existing)
    *    - Save to config
    * - Add Configurable Options Group per product (if not already existing)
    *    - Add corresponding configurable option
    *    - Add corresponding configurable option sub
    *
    * - From local OB Plan map for Titan productkeys
    *    - Save free plan ids per product key
    *    - Add Product (if not already existing)
    *       - Save to config  (plan id - product id)
    *       - Add corresponding Product Configurable Option link
    *
    * - Set Paid Products as configurable options upgradable
    * - From plan id upgrade map
    *    - Add upgrade map to tblproduct_upgrade_products
    *
    *
    * @return void
    *
    *
    */

   public function handleProductsSetup()
   {
      try {
         $this->_resetLocalTitanConfigs();
         $productGroupId = $this->_addSaveProductGroup();
         $productConfigOptionIds = $this->_addSaveConfigurableOptions();
         $productIds = $this->_addSaveProducts($productGroupId);
         $this->_addProductConfigLinks($productConfigOptionIds, $productIds);
         $this->_setPaidProductsAsConfigOptionsUpgradable();
         $this->_setAutoTerminateDaysForFreeProducts();
         $this->_setupProductUpgrades($productIds);
      } catch (Exception $e) {
         (new ErrorHandler())->log([
             "action" => __METHOD__,
             "request" => "",
             "response" => $e->getMessage(),
             "exception" => $e
         ]);
      }

   }

}
