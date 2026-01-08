<?php

namespace WHMCS\Module\Addon\Resellerclubdomains\Services;

use WHMCS\Database\Capsule;

class WHMCSService
{
   private $TBL_CLIENTS = "tblclients";
   private $TBL_USERS = "tblusers";
   private $TBL_USERS_CLIENTS = "tblusers_clients";
   private $TBL_CUSTOM_FIELDS = "tblcustomfields";
   private $TBL_CUSTOM_FIELD_VALUES = "tblcustomfieldsvalues";
   private $TBL_REGISTRARS = "tblregistrars";
   private $TBL_ADDON_MODULES = "tbladdonmodules";
   private $TBL_PRODUCT_GROUPS = "tblproductgroups";
   private $TBL_PRODUCT_CONFIG_GROUPS = "tblproductconfiggroups";
   private $TBL_PRODUCT_CONFIG_OPTIONS = "tblproductconfigoptions";
   private $TBL_PRODUCT_CONFIG_OPTIONS_SUB = "tblproductconfigoptionssub";
   private $TBL_PRODUCT_CONFIG_LINKS = "tblproductconfiglinks";
   private $TBL_PRODUCT_UPGRADE_PRODUCTS = "tblproduct_upgrade_products";
   private $TBL_UPGRADES = "tblupgrades";
   private $TBL_HOSTING = "tblhosting";
   private $TBL_PRODUCTS = "tblproducts";
   private $API_ACTION_ADD_CLIENT = "AddClient";
   private $API_ACTION_ADD_USER = "AddUser";
   private $API_ACTION_UPDATE_CLIENT = "UpdateClient";
   private $API_ACTION_UPDATE_USER = "UpdateUser";
   private $API_CURRENCIES = "GetCurrencies";
   private $API_ACTION_GET_CLIENT_DETAILS = "GetClientsDetails";
   private $API_ACTION_ADD_ORDER = "AddOrder";
   private $API_ACTION_GET_CLIENT_DOMAINS = "GetClientsDomains";
   private $API_ACTION_GET_CLIENT_PRODUCTS = "GetClientsProducts";
   private $API_ACTION_ACCEPT_ORDER = "AcceptOrder";
   private $API_ACTION_UPDATE_CLIENT_DOMAIN = "UpdateClientDomain";
   private $API_ACTION_SEND_ADMIN_EMAIL = "SendAdminEmail";
   private $API_ACTION_DECRYPT_STRING = "DecryptPassword";
   private $API_ACTION_GET_CONFIGURATION_VALUE = "GetConfigurationValue";
   private $API_ACTION_GET_PAYMENT_METHODS = "GetPaymentMethods";
   private $API_ACTION_GET_PRODUCTS = "GetProducts";
   private $API_ACTION_ADD_PRODUCT = "AddProduct";
   private $CONFIGURATION_SYSTEM_URL = "SystemURL";
   private $CONFIGURATION_NUMBER_OF_RECORDS_TO_DISPLAY = "NumRecordstoDisplay";
   private $CONFIGURATION_SYSTEM_THEME = "Template";


   public function getDecryptedResellerAPICredentials()
   {
      try {
         $responseCredentials = $this->getResellerAPICredentials([
             "settings" => [
                 REGISTRAR_RESELLERCLUB_SETTING_NAME_RESELLER_ID,
                 REGISTRAR_RESELLERCLUB_SETTING_NAME_API_KEY,
             ],
             "registrar" => REGISTRAR_RESELLERCLUB,
         ]);

         if (!empty($responseCredentials)) {
            foreach ($responseCredentials as $credential) {
               if (
                   $credential->setting ==
                   REGISTRAR_RESELLERCLUB_SETTING_NAME_RESELLER_ID
               ) {
                  $resellerId = $this->decryptString($credential->value);
               }
               if (
                   $credential->setting ==
                   REGISTRAR_RESELLERCLUB_SETTING_NAME_API_KEY
               ) {
                  $apiKey = $this->decryptString($credential->value);
               }
            }
         }

         return [
             "resellerId" => $resellerId,
             "apiKey" => $apiKey,
         ];
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function getClientDetails($clientId)
   {
      try {
         $response = localAPI($this->API_ACTION_GET_CLIENT_DETAILS, [
             "clientid" => $clientId,
             "stats" => false,
         ]);

         if (
             array_key_exists("result", $response) &&
             array_key_exists("client", $response) &&
             "success" === strtolower($response["result"])
         ) {
            return $response["client"];
         } else {
            throw new \Exception($response);
         }
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function getClientDetailsByEmail($email)
   {
      try {
         $response = localAPI($this->API_ACTION_GET_CLIENT_DETAILS, [
             "email" => $email,
             "stats" => false,
         ]);

         if (
             array_key_exists("result", $response) &&
             array_key_exists("client", $response) &&
             "success" === strtolower($response["result"])
         ) {
            return $response["client"];
         } else {
            throw new \Exception($response);
         }
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function getClientAndUserDetails($email, $lastModifiedTimestamp = null)
   {
      try {
         return Capsule::table($this->TBL_CLIENTS)
             ->join(
                 $this->TBL_USERS_CLIENTS,
                 $this->TBL_CLIENTS . ".id",
                 "=",
                 $this->TBL_USERS_CLIENTS . ".client_id"
             )
             ->where($this->TBL_USERS_CLIENTS . ".owner", 1)
             ->when($email, function ($query, $email) {
                if (is_array($email)) {
                   return $query->whereIn(
                       $this->TBL_CLIENTS . ".email",
                       $email
                   );
                }
                return $query->where($this->TBL_CLIENTS . ".email", $email);
             })
             ->when($lastModifiedTimestamp, function (
                 $query,
                 $lastModifiedTimestamp
             ) {
                // @TODO handle this
                // return $query->where($this->TBL_CLIENTS . '.updated_at', '<=', $lastModifiedTimestamp);
             })
             ->get()
             ->toArray();
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function getUserDetailsByEmail($email)
   {
      try {
         return Capsule::table($this->TBL_USERS)
             ->when($email, function ($query, $email) {
                if (is_array($email)) {
                   return $query->whereIn("email", $email);
                }
                return $query->where("email", $email);
             })
             ->get()
             ->toArray();
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function addClient($client)
   {
      try {
         $mandatoryParams = [
             "owner_user_id",
             "firstname",
             "lastname",
             "email",
             "address1",
             "city",
             "state",
             "postcode",
             "country",
             "phonenumber",
            // 'currency',
            // 'customfields'
         ];
         array_walk($mandatoryParams, function ($param) use ($client) {
            if (false === array_key_exists($param, $client)) {
               throw new \Exception("Missing Parameter $param");
            }
         });
         (new CronService())->addActionLog([
             "action" => __FUNCTION__,
             "entity" => $client["email"],
         ]);
         $response = localAPI($this->API_ACTION_ADD_CLIENT, $client);
         if (
             array_key_exists("result", $response) &&
             array_key_exists("clientid", $response) &&
             "success" === strtolower($response["result"])
         ) {
            return $response["clientid"];
         } else {
            throw new \Exception($response);
         }
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function addUser($user)
   {
      try {
         $mandatoryParams = ["firstname", "lastname", "email", "password2"];
         array_walk($mandatoryParams, function ($param) use ($user) {
            if (false === array_key_exists($param, $user)) {
               throw new \Exception("Missing Parameter $param");
            }
         });

         $response = localAPI($this->API_ACTION_ADD_USER, $user);

         if (
             array_key_exists("result", $response) &&
             array_key_exists("user_id", $response) &&
             "success" === strtolower($response["result"])
         ) {
            return $response["user_id"];
         } else {
            throw new \Exception(serialize($response));
         }
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function updateUser($user)
   {
      try {
         $mandatoryParams = ["user_id"];
         array_walk($mandatoryParams, function ($param) use ($user) {
            if (false === array_key_exists($param, $user)) {
               throw new \Exception("Missing Parameter $param");
            }
         });

         $response = localAPI($this->API_ACTION_UPDATE_USER, $user);
         if (
             array_key_exists("result", $response) &&
             array_key_exists("user_id", $response) &&
             "success" === strtolower($response["result"])
         ) {
            return $response["user_id"];
         } else {
            throw new \Exception(serialize($response));
         }
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function getCustomerIdFromClientId($clientId)
   {
      try {
         $customFieldId = $this->getCustomFieldId(
             CUSTOM_FIELD_OB_CUSTOMER_ID
         );
         $customFieldId = $customFieldId->id;

         $resellerCreds = $this->getResellerAPICredsFromModuleConfig();
         $resellerId = $resellerCreds['resellerId'];

         if ($customFieldId) {
            $details = $this->getClientsWithCustomField([
                "custom_field_id" => $customFieldId,
                "client_id" => $clientId,
                "reseller_id" => $resellerId,
            ]);
            return $details[0]->ob_customer_id;
         }

         return null;
      } catch (\Exception $e) {
         throw $e;
      }
   }

   /**
    * @sql select  tblclients.email, ob_customers.customer_id, ob_customers.reseller_id
    * from tblclients
    * left join tblcustomfieldsvalues on `tblclients`.`id` = `tblcustomfieldsvalues`.`relid` and `tblcustomfieldsvalues`.`fieldid` = <custom_field_id>
    * left join ob_customers on `tblcustomfieldsvalues`.`value` = `ob_customers`.`customer_id` and ob_customers.reseller_id = <reseller_id>
    */
   public function getClientsWithCustomField($params)
   {
      try {
         $query = Capsule::table($this->TBL_CLIENTS)
             ->select(
                 $this->TBL_CLIENTS . ".email",
                 $this->TBL_CUSTOM_FIELD_VALUES . ".value as ob_customer_id",
                 TBL_CUSTOMERS . ".metadata"
             )
             ->leftJoin($this->TBL_CUSTOM_FIELD_VALUES, function (
                 $join
             ) use ($params) {
                $join
                    ->on(
                        $this->TBL_CLIENTS . ".id",
                        "=",
                        $this->TBL_CUSTOM_FIELD_VALUES . ".relid"
                    )
                    ->where(
                        $this->TBL_CUSTOM_FIELD_VALUES . ".fieldid",
                        $params["custom_field_id"]
                    );
             })
             ->leftJoin(TBL_CUSTOMERS, function ($join) use ($params) {
                $join
                    ->on(
                        $this->TBL_CUSTOM_FIELD_VALUES . ".value",
                        "=",
                        TBL_CUSTOMERS . ".customer_id"
                    )
                    ->where(
                        TBL_CUSTOMERS . ".reseller_id",
                        $params["reseller_id"]
                    );
             });

         if (
             array_key_exists("client_updated_at", $params) &&
             !empty($params["client_updated_at"])
         ) {
            $query->where(
                $this->TBL_CLIENTS . ".updated_at",
                ">=",
                $params["client_updated_at"]
            );
         }

         if (
             array_key_exists("client_id", $params) &&
             !empty($params["client_id"])
         ) {
            $query->where($this->TBL_CLIENTS . ".id", $params["client_id"]);
         }

         if (
             array_key_exists("limit", $params) &&
             !empty($params["limit"])
         ) {
            $query->limit($params["limit"]);
         }

         if (array_key_exists("skip", $params)) {
            $query->skip($params["skip"]);
         }

         if (
             array_key_exists("count", $params) &&
             !empty($params["count"])
         ) {
            return $query->count();
         } else {
            return $query->get()->toArray();
         }
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function updateClient($client)
   {
      try {
         $mandatoryParams = ["clientid"];
         array_walk($mandatoryParams, function ($param) use ($client) {
            if (false === array_key_exists($param, $client)) {
               throw new \Exception("Missing Parameter $param");
            }
         });
         (new CronService())->addActionLog([
             "action" => __FUNCTION__,
             "entity" => $client["clientid"],
         ]);

         $response = localAPI($this->API_ACTION_UPDATE_CLIENT, $client);
         if (
             array_key_exists("result", $response) &&
             array_key_exists("clientid", $response) &&
             "success" === strtolower($response["result"])
         ) {
            return $response["clientid"];
         } else {
            throw new \Exception($response["message"]);
         }
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function createCustomFieldIfNotExists($params)
   {
      try {
         $mandatoryParams = ["type", "fieldname", "fieldtype"];
         array_walk($mandatoryParams, function ($param) use ($params) {
            if (false === array_key_exists($param, $params)) {
               throw new \Exception("Missing Parameter $param");
            }
         });
         $existingField = $this->getCustomFieldId($params["fieldname"]);
         if (empty($existingField)) {
            return $this->createCustomField($params);
         }
         return $existingField;
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function createCustomField($params)
   {
      try {
         $mandatoryParams = ["type", "fieldname", "fieldtype"];
         array_walk($mandatoryParams, function ($param) use ($params) {
            if (false === array_key_exists($param, $params)) {
               throw new \Exception("Missing Parameter $param");
            }
         });

         $timestamp = new \DateTime();
         $params["created_at"] = $timestamp;
         $params["updated_at"] = $timestamp;

         return Capsule::connection()->transaction(function (
             $connectionManager
         ) use ($params) {
            return $connectionManager
                ->table($this->TBL_CUSTOM_FIELDS)
                ->insertGetId($params);
         });
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function getCustomFieldId($fieldName)
   {
      try {
         return Capsule::table($this->TBL_CUSTOM_FIELDS)
             ->where("fieldname", $fieldName)
             ->first("id");
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function getCurrencies()
   {
      $response = localAPI($this->API_CURRENCIES);

      if (
          array_key_exists("result", $response) &&
          "success" === strtolower($response["result"]) &&
          array_key_exists("currencies", $response) &&
          array_key_exists("currency", $response["currencies"])
      ) {
         return $response["currencies"]["currency"];
      } else {
         throw new \Exception(serialize($response));
      }
   }

   public function getCurrencyId($currencyCode)
   {
      try {
         $currencyId = null;
         $currencies = $this->getCurrencies();
         $currenciesFlipped = array_flip(array_column($currencies, "code"));

         if (array_key_exists($currencyCode, $currenciesFlipped)) {
            $currencyId =
                $currencies[$currenciesFlipped[$currencyCode]]["id"];
         }

         return $currencyId;
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function addOrder($params)
   {
      try {
         $mandatoryParams = ["clientid", "paymentmethod"];
         array_walk($mandatoryParams, function ($param) use ($params) {
            if (false === array_key_exists($param, $params)) {
               throw new \Exception("Missing Parameter $param");
            }
         });

         $response = localAPI($this->API_ACTION_ADD_ORDER, $params);

         if (
             array_key_exists("result", $response) &&
             array_key_exists("orderid", $response) &&
             "success" === strtolower($response["result"])
         ) {
            return $response;
         } else {
            throw new \Exception(serialize($response));
         }
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function getClientIdFromCustomerId($params)
   {
      try {
         $mandatoryParams = ["field_id", "customer_id"];
         array_walk($mandatoryParams, function ($param) use ($params) {
            if (false === array_key_exists($param, $params)) {
               throw new \Exception("Missing Parameter $param");
            }
         });
         return Capsule::table($this->TBL_CUSTOM_FIELD_VALUES)
             ->where("value", $params["customer_id"])
             ->where("fieldid", $params["field_id"])
             ->first("relid as clientid");
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function getDomainsWithClientId($params)
   {
      try {
         return Capsule::table(TBL_DOMAINS)
             ->select(
                 TBL_DOMAINS . ".*",
                 $this->TBL_CUSTOM_FIELD_VALUES . ".relid as clientId"
             )
             ->leftJoin(
                 $this->TBL_CUSTOM_FIELD_VALUES,
                 TBL_DOMAINS . ".customer_id",
                 "=",
                 $this->TBL_CUSTOM_FIELD_VALUES . ".value"
             )
             ->where(
                 $this->TBL_CUSTOM_FIELD_VALUES . ".fieldid",
                 $params["custom_field_id"]
             )
             ->where(
                 $this->TBL_CUSTOM_FIELD_VALUES . ".relid",
                 $params["client_id"]
             )
             ->when($params, function ($query, $params) {
                if (array_key_exists("last_modified_time", $params)) {
                   return $query->where(
                       TBL_DOMAINS . ".last_modified_time",
                       ">=",
                       $params["last_modified_time"]
                   );
                }
             })
             ->skip($params["skip"])
             ->take($params["limit"])
             ->orderBy("id", "asc")
             ->get()
             ->toArray();
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function searchDomains($params)
   {
      try {
         $response = localAPI($this->API_ACTION_GET_CLIENT_DOMAINS, $params);
         if (
             array_key_exists("result", $response) &&
             array_key_exists("totalresults", $response) &&
             "success" === strtolower($response["result"])
         ) {
            return $response;
         } else {
            throw new \Exception($response);
         }
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function searchOrders($params)
   {
      try {
         $response = localAPI($this->API_ACTION_GET_CLIENT_PRODUCTS, $params);
         if (
             array_key_exists("result", $response) &&
             array_key_exists("totalresults", $response) &&
             "success" === strtolower($response["result"])
         ) {
            return $response;
         } else {
            throw new \Exception($response);
         }
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function getHostingOrders($params)
   {
      try {
         return Capsule::table($this->TBL_HOSTING)
             ->where($params)
             ->get()
             ->toArray();
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function acceptOrder($params)
   {
      try {
         $mandatoryParams = ["orderid"];
         array_walk($mandatoryParams, function ($param) use ($params) {
            if (false === array_key_exists($param, $params)) {
               throw new \Exception("Missing Parameter $param");
            }
         });

         $response = localAPI($this->API_ACTION_ACCEPT_ORDER, $params);

         if (
             array_key_exists("result", $response) &&
             "success" === strtolower($response["result"])
         ) {
            return true;
         } else {
            throw new \Exception(serialize($response));
         }
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function updateClientDomain($params)
   {
      try {
         $mandatoryParams = ["domainid"];
         array_walk($mandatoryParams, function ($param) use ($params) {
            if (false === array_key_exists($param, $params)) {
               throw new \Exception("Missing Parameter $param");
            }
         });

         $response = localAPI(
             $this->API_ACTION_UPDATE_CLIENT_DOMAIN,
             $params
         );

         if (
             array_key_exists("result", $response) &&
             "success" === strtolower($response["result"])
         ) {
            return true;
         } else {
            throw new \Exception(serialize($response));
         }
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function getResellerAPICredentials($params)
   {
      try {
         return Capsule::table($this->TBL_REGISTRARS)
             ->select("setting", "value")
             ->whereIn(
                 $this->TBL_REGISTRARS . ".setting",
                 $params["settings"]
             )
             ->where("registrar", $params["registrar"])
             ->get()
             ->toArray();
      } catch (\Exception $e) {
         throw $e;
      }
   }

   // @TODO Allow enable/disable toggle in module config for this?
   public function sendAdminEmail($params)
   {
      try {
         $response = localAPI($this->API_ACTION_SEND_ADMIN_EMAIL, [
             "customsubject" => $params["subject"],
             "custommessage" => $params["body"],
             "type" => "system",
         ]);

         if (
             array_key_exists("result", $response) &&
             "success" === strtolower($response["result"])
         ) {
            return true;
         } else {
            throw new \Exception(serialize($response));
         }
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function decryptString($string)
   {
      try {
         $response = localAPI($this->API_ACTION_DECRYPT_STRING, [
             "password2" => $string,
         ]);
         if (
             array_key_exists("result", $response) &&
             "success" === strtolower($response["result"]) &&
             array_key_exists("password", $response) &&
             !empty($response["password"])
         ) {
            return $response["password"];
         } else {
            throw new \Exception(serialize($response));
         }
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function getConfigurationValue($setting)
   {
      try {
         $response = localAPI($this->API_ACTION_GET_CONFIGURATION_VALUE, [
             "setting" => $setting,
         ]);
         if (
             array_key_exists("result", $response) &&
             "success" === strtolower($response["result"]) &&
             array_key_exists("setting", $response) &&
             !empty($response["setting"]) &&
             array_key_exists("value", $response) &&
             !empty($response["value"])
         ) {
            return $response["value"];
         } else {
            throw new \Exception(serialize($response));
         }
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function getSystemUrl()
   {
      try {
         return $this->getConfigurationValue(
             $this->CONFIGURATION_SYSTEM_URL
         );
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function getNumberOfRecordsToDisplay()
   {
      try {
         return $this->getConfigurationValue(
             $this->CONFIGURATION_NUMBER_OF_RECORDS_TO_DISPLAY
         );
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function getSystemTheme()
   {
      try {
         return $this->getConfigurationValue(
             $this->CONFIGURATION_SYSTEM_THEME
         );
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function getAddonModuleConfiguration($params)
   {
      try {
         $query = Capsule::table($this->TBL_ADDON_MODULES);
         if (array_key_exists("module", $params)) {
            $query->where("module", $params["module"]);
         } else {
            $query->where("module", strtolower(MODULE_NAME));
         }
         if (array_key_exists("setting", $params)) {
            $query->where("setting", $params["setting"]);
         }
         return $query->pluck("value")->first();
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function isModuleConfigOn($config)
   {
      try {
         return !empty($this->getAddonModuleConfiguration(["setting" => $config]));
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function getPaymentMethods()
   {
      try {
         return localAPI($this->API_ACTION_GET_PAYMENT_METHODS);
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function getDefaultPaymentMethodFromModuleConfig()
   {
      try {
         return $this->getAddonModuleConfiguration([
             'setting' => CONFIG_DEFAULT_PAYMENT_METHOD,
         ]);
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function isConfiguredTitanProduct()
   {
      try {
         return $this->getAddonModuleConfiguration([
             'setting' => CONFIG_SETUP_TITAN_EMAIL_PRODUCT,
         ]);
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function getResellerAPICredsFromModuleConfig()
   {
      try {
         return [
             "resellerId" => $this->getAddonModuleConfiguration([
                 "setting" => CONFIG_RESELLER_ID,
             ]),
             "apiKey" => $this->getAddonModuleConfiguration([
                 "setting" => CONFIG_RESELLER_API_KEY,
             ]),
         ];
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function getProductGroupId($name)
   {
      try {
         return Capsule::table($this->TBL_PRODUCT_GROUPS)
             ->where("name", $name)
             ->first("id");
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function addProductGroupIfNotExists($params)
   {
      try {
         $mandatoryParams = ["name"];
         array_walk($mandatoryParams, function ($param) use ($params) {
            if (false === array_key_exists($param, $params)) {
               throw new \Exception("Missing Parameter $param");
            }
         });

         $existingProductGroupId = $this->getProductGroupId($params["name"]);
         if (empty($existingProductGroupId)) {
            return $this->addProductGroup($params);
         }

         return $existingProductGroupId->id;
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function addProductGroup($params)
   {
      try {
         $mandatoryParams = ["name"];
         array_walk($mandatoryParams, function ($param) use ($params) {
            if (false === array_key_exists($param, $params)) {
               throw new \Exception("Missing Parameter $param");
            }
         });

         return Capsule::connection()->transaction(function (
             $connectionManager
         ) use ($params) {
            return $connectionManager
                ->table($this->TBL_PRODUCT_GROUPS)
                ->insertGetId($params);
         });
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function updateProducts($updateParams, $whereParams)
   {
      try {
         return Capsule::table($this->TBL_PRODUCTS)
             ->where($whereParams)
             ->update($updateParams);
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function addProduct($params)
   {
      try {
         $mandatoryParams = ["name", "gid"];
         array_walk($mandatoryParams, function ($param) use ($params) {
            if (false === array_key_exists($param, $params)) {
               throw new \Exception("Missing Parameter $param");
            }
         });

         $response = localAPI($this->API_ACTION_ADD_PRODUCT, $params);

         if (
             array_key_exists("result", $response) &&
             "success" === strtolower($response["result"])
         ) {
            return $response;
         } else {
            throw new \Exception(serialize($response));
         }
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function addProductIfNotExists($params)
   {
      try {
         $mandatoryParams = ["name", "gid"];
         array_walk($mandatoryParams, function ($param) use ($params) {
            if (false === array_key_exists($param, $params)) {
               throw new \Exception("Missing Parameter $param");
            }
         });
         $existingProducts = $this->getProducts([
             "gid" => $params["gid"]
         ]);

         if (!empty($existingProducts)) {
            foreach ($existingProducts["product"] as $existingProduct) {
               if ($existingProduct["name"] == $params["name"]) {
                  return $existingProduct["pid"];
               }
            }
         }

         // no existing matching product found, adding new product
         $responseProduct = $this->addProduct($params);
         return $responseProduct["pid"];
      } catch (\Exception $e) {
         throw $e;
      }

   }

   public function getProducts($params)
   {
      try {
         $response = localAPI($this->API_ACTION_GET_PRODUCTS);

         if (
             array_key_exists("result", $response) &&
             "success" === strtolower($response["result"]) &&
             array_key_exists("totalresults", $response)
         ) {
            return $response["products"];
         } else {
            throw new \Exception(serialize($response));
         }
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function getProductConfigGroupId($name)
   {
      try {
         return Capsule::table($this->TBL_PRODUCT_CONFIG_GROUPS)
             ->where("name", $name)
             ->first("id");
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function addProductConfigGroupIfNotExists($params)
   {
      try {
         $mandatoryParams = ["name"];
         array_walk($mandatoryParams, function ($param) use ($params) {
            if (false === array_key_exists($param, $params)) {
               throw new \Exception("Missing Parameter $param");
            }
         });

         $existingProductConfigGroupId = $this->getProductConfigGroupId($params["name"]);
         if (empty($existingProductConfigGroupId)) {
            return $this->addProductConfigGroup($params);
         }

         return $existingProductConfigGroupId->id;
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function addProductConfigGroup($params)
   {
      try {
         $mandatoryParams = ["name"];
         array_walk($mandatoryParams, function ($param) use ($params) {
            if (false === array_key_exists($param, $params)) {
               throw new \Exception("Missing Parameter $param");
            }
         });

         return Capsule::connection()->transaction(function (
             $connectionManager
         ) use ($params) {
            return $connectionManager
                ->table($this->TBL_PRODUCT_CONFIG_GROUPS)
                ->insertGetId($params);
         });
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function getProductConfigOptionId($params)
   {
      try {
         return Capsule::table($this->TBL_PRODUCT_CONFIG_OPTIONS)
             ->where($params)
             ->first("id");
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function addProductConfigOptionIfNotExists($params)
   {
      try {
         $mandatoryParams = ["gid", "optionname", "optiontype"];
         array_walk($mandatoryParams, function ($param) use ($params) {
            if (false === array_key_exists($param, $params)) {
               throw new \Exception("Missing Parameter $param");
            }
         });

         $existingProductGroupId = $this->getProductConfigOptionId($params);
         if (empty($existingProductGroupId)) {
            return $this->addProductConfigOption($params);
         }

         return $existingProductGroupId->id;
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function addProductConfigOption($params)
   {
      try {
         $mandatoryParams = ["gid", "optionname", "optiontype"];
         array_walk($mandatoryParams, function ($param) use ($params) {
            if (false === array_key_exists($param, $params)) {
               throw new \Exception("Missing Parameter $param");
            }
         });

         return Capsule::connection()->transaction(function (
             $connectionManager
         ) use ($params) {
            return $connectionManager
                ->table($this->TBL_PRODUCT_CONFIG_OPTIONS)
                ->insertGetId($params);
         });
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function getProductConfigOptionSubId($params)
   {
      try {
         return Capsule::table($this->TBL_PRODUCT_CONFIG_OPTIONS_SUB)
             ->where($params)
             ->first("id");
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function addProductConfigOptionSub($params)
   {
      try {
         $mandatoryParams = ["configid", "optionname"];
         array_walk($mandatoryParams, function ($param) use ($params) {
            if (false === array_key_exists($param, $params)) {
               throw new \Exception("Missing Parameter $param");
            }
         });

         return Capsule::connection()->transaction(function (
             $connectionManager
         ) use ($params) {
            return $connectionManager
                ->table($this->TBL_PRODUCT_CONFIG_OPTIONS_SUB)
                ->insertGetId($params);
         });
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function addProductConfigOptionSubIfNotExists($params)
   {
      try {
         $mandatoryParams = ["configid", "optionname"];
         array_walk($mandatoryParams, function ($param) use ($params) {
            if (false === array_key_exists($param, $params)) {
               throw new \Exception("Missing Parameter $param");
            }
         });

         $existingId = $this->getProductConfigOptionSubId($params);
         if (empty($existingId)) {
            return $this->addProductConfigOptionSub($params);
         }

         return $existingId->id;
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function getProductConfigLink($params)
   {
      try {
         return Capsule::table($this->TBL_PRODUCT_CONFIG_LINKS)
             ->where($params)
             ->first("id");
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function addProductConfigLink($params)
   {
      try {
         $mandatoryParams = ["gid", "pid"];
         array_walk($mandatoryParams, function ($param) use ($params) {
            if (false === array_key_exists($param, $params)) {
               throw new \Exception("Missing Parameter $param");
            }
         });

         return Capsule::connection()->transaction(function (
             $connectionManager
         ) use ($params) {
            return $connectionManager
                ->table($this->TBL_PRODUCT_CONFIG_LINKS)
                ->insertGetId($params);
         });
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function addProductConfigLinkIfNotExists($params)
   {
      try {
         $mandatoryParams = ["gid", "pid"];
         array_walk($mandatoryParams, function ($param) use ($params) {
            if (false === array_key_exists($param, $params)) {
               throw new \Exception("Missing Parameter $param");
            }
         });

         $existingProductConfigLinkId = $this->getProductConfigLink($params);
         if (empty($existingProductConfigLinkId)) {
            return $this->addProductConfigLink($params);
         }

         return $existingProductConfigLinkId->id;
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function getProductUpgradeProducts($params)
   {
      try {
         return Capsule::table($this->TBL_PRODUCT_UPGRADE_PRODUCTS)
             ->where("product_id", $params['productId'])
             ->where("upgrade_product_id", $params['upgradeProductId'])
             ->first("id");
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function addProductUpgradeProducts($params)
   {
      try {
         $mandatoryParams = ["product_id", "upgrade_product_id"];
         array_walk($mandatoryParams, function ($param) use ($params) {
            if (false === array_key_exists($param, $params)) {
               throw new \Exception("Missing Parameter $param");
            }
         });

         return Capsule::connection()->transaction(function (
             $connectionManager
         ) use ($params) {
            return $connectionManager
                ->table($this->TBL_PRODUCT_UPGRADE_PRODUCTS)
                ->insertGetId($params);
         });
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function addProductUpgradeProductsIfNotExists($params)
   {
      try {
         $mandatoryParams = ["product_id", "upgrade_product_id"];
         array_walk($mandatoryParams, function ($param) use ($params) {
            if (false === array_key_exists($param, $params)) {
               throw new \Exception("Missing Parameter $param");
            }
         });

         $existingId = $this->getProductUpgradeProducts($params);
         if (empty($existingId)) {
            return $this->addProductUpgradeProducts($params);
         }

         return $existingId->id;
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function getUpgradeDetails($upgradeId)
   {
      try {
         return Capsule::table($this->TBL_UPGRADES)
             ->where("id", $upgradeId)
             ->first();
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function getNoOfMonthsFromCycle($cycle)
   {
      try {

         switch (strtolower($cycle)) {
            case "monthly" :
               return "1";
            case "quarterly":
               return "3";
            case "semiannually":
            case "semi-annually":
               return "6";
            case "annually":
               return "12";
            default:
               return null;
         }

      } catch (\Exception $e) {
         throw $e;
      }
   }
}
