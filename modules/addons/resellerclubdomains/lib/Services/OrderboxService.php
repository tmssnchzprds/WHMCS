<?php

namespace WHMCS\Module\Addon\Resellerclubdomains\Services;

use Exception;
use WHMCS\Module\Addon\Resellerclubdomains\Library\Utils;

class OrderboxService extends OrderboxHttpClient
{
   private $customerSignup = "/api/customers/v2/signup.json";
   private $customerUpdate = "/api/customers/modify.json";
   private $customerDelete = "/api/customers/delete.json";
   private $listDomains = "/api/domains/search.json";
   private $listCustomers = "/v2/reseller/{resellerId}/customers";
   private $domainDetails = "/api/domains/details.json";
   private $resendVerificationEmail = "/api/domains/raa/resend-verification.json";
   private $customerDetails = "/api/customers/details-by-id.json";
   private $listOrders = "/api/orders/list.json";
   private $titanOrderDetails = "/restapi/product/{productKey}";
   private $planDetails = "/api/products/plan-details.json";
   private $sitelockSSODetails = "/api/domains/sitelock/details.json";

   public function __construct()
   {
      parent::__construct();
   }

   public function listCustomers($params)
   {
      try {
         $mandatoryParams = ["limit", "skip"];
         array_walk($mandatoryParams, function ($param) use ($params) {
            if (false === array_key_exists($param, $params)) {
               throw new \Exception("Missing Parameter $param");
            }
         });

         $response = $this->get($this->listCustomers, $params);
         return json_decode($response["body"], true);
      } catch (\Exception $e) {
         throw $e;
      }
   }

   private function _transformClientDetailsToCustomerDetails($client)
   {
      try {
         $customer = [
             "username" => $client["email"],
             "passwd" => (new Utils())->getRandomPassword(),
             "name" =>
                 array_key_exists("fullname", $client) &&
                 !empty($client["fullname"])
                     ? $client["fullname"]
                     : $client["firstname"] . " " . $client["lastname"],
             "company" => !empty($client["companyname"])
                 ? $client["companyname"]
                 : "N/A",
             "address-line-1" => $client["address1"],
             "city" => $client["city"],
             "state" => !empty($client["state"])
                 ? $client["state"]
                 : "Not Applicable",
             "country" => $client["country"],
             "zipcode" => !empty($client["postcode"])
                 ? $client["postcode"]
                 : "Not Applicable",
             "phone-cc" => $client["phonecc"],
             "phone" => $client["phonenumber"],
             "lang-pref" => !empty($client["language"])
                 ? (new Utils())->getISOLanguageCode($client["language"])
                 : DEFAULT_LANGUAGE_CODE,
         ];

         if (
             array_key_exists("address2", $client) &&
             !empty($client["address2"])
         ) {
            $customer["address-line-2"] = $client["address2"];
         }

         if (
             array_key_exists("tax_id", $client) &&
             !empty($client["tax_id"])
         ) {
            $customer["vat-id"] = $client["tax_id"];
         }

         return $customer;
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function handleCustomerSignup($params)
   {
      try {
         return $this->customerSignup(
             $this->_transformClientDetailsToCustomerDetails($params)
         );
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function customerSignup($params)
   {
      try {
         $mandatoryParams = [
             "username",
             "passwd",
             "name",
             "company",
             "address-line-1",
             "city",
             "state",
             "country",
             "zipcode",
             "phone-cc",
             "phone",
             "lang-pref",
         ];
         array_walk($mandatoryParams, function ($param) use ($params) {
            if (false === array_key_exists($param, $params)) {
               throw new \Exception("Missing Parameter $param");
            }
         });

         $response = $this->post($this->customerSignup, $params);
         $response = json_decode($response["body"], true);

         if (
             is_array($response) &&
             array_key_exists("status", $response) &&
             strtolower($response["status"]) == "error"
         ) {
            throw new Exception(serialize($response));
         }

         return $response;
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function customerUpdate($params)
   {
      try {
         $paramsCustomerUpdate = $this->_transformClientDetailsToCustomerDetails(
             $params
         );
         $paramsCustomerUpdate["customer-id"] = $params["customer-id"];
         $mandatoryParams = [
             "customer-id",
             "username",
             "name",
             "company",
             "address-line-1",
             "city",
             "state",
             "country",
             "zipcode",
             "phone-cc",
             "phone",
             "lang-pref",
         ];
         array_walk($mandatoryParams, function ($param) use (
             $paramsCustomerUpdate
         ) {
            if (false === array_key_exists($param, $paramsCustomerUpdate)) {
               throw new \Exception("Missing Parameter $param");
            }
         });

         $response = $this->post(
             $this->customerUpdate,
             $paramsCustomerUpdate
         );
         $response = json_decode($response["body"], true);

         if (
             is_array($response) &&
             array_key_exists("status", $response) &&
             strtolower($response["status"]) == "error"
         ) {
            throw new Exception(serialize($response));
         }

         return $response;
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function deleteCustomer($params)
   {
      try {
         $mandatoryParams = ["customer-id"];
         array_walk($mandatoryParams, function ($param) use ($params) {
            if (false === array_key_exists($param, $params)) {
               throw new \Exception("Missing Parameter $param");
            }
         });

         $response = $this->post($this->customerDelete, $params);
         $response = json_decode($response["body"], true);
         if (
             is_array($response) &&
             array_key_exists("status", $response) &&
             strtolower($response["status"]) == "error"
         ) {
            throw new Exception(serialize($response));
         }

         return $response;
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function listDomains($params)
   {
      try {
         $mandatoryParams = ["no-of-records", "page-no"];
         array_walk($mandatoryParams, function ($param) use ($params) {
            if (false === array_key_exists($param, $params)) {
               throw new \Exception("Missing Parameter $param");
            }
         });

         $response = $this->get($this->listDomains, $params);
         $response = $response["body"];
         $response = json_decode($response, true);
         $toReturn = [];
         $toReturn["items_info"] = [
             "recsindb" => $response["recsindb"],
             "recsonpage" => $response["recsonpage"],
         ];
         unset($response["recsonpage"]);
         unset($response["recsindb"]);
         $toReturn["domains"] = $response;

         return $toReturn;
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function getPendingVerificationDomains($params = [])
   {
      try {
         return $this->listDomains([
             "page-no" => $params["page-no"] ? $params["page-no"] : 1,
             "no-of-records" => $params["no-of-records"]
                 ? $params["no-of-records"]
                 : 20,
             "status" => OB_DOMAIN_STATUS_PENDING_VERIFICATION,
         ]);
      } catch (\Exception $e) {
         (new ErrorHandler())->log([
             "action" => __METHOD__,
             "response" => $e->getMessage(),
             "exception" => $e,
         ]);
      }
   }

   public function getDomainDetails($params)
   {
      try {
         $mandatoryParams = ["order-id", "options"];
         array_walk($mandatoryParams, function ($param) use ($params) {
            if (false === array_key_exists($param, $params)) {
               throw new \Exception("Missing Parameter $param");
            }
         });

         $response = $this->get($this->domainDetails, $params);
         return json_decode($response["body"], true);
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function resendVerificationEmail($params)
   {
      try {
         $mandatoryParams = ["order-id"];
         array_walk($mandatoryParams, function ($param) use ($params) {
            if (false === array_key_exists($param, $params)) {
               throw new \Exception("Missing Parameter $param");
            }
         });

         $response = $this->post($this->resendVerificationEmail, $params);
         $response = $response["body"];
         $response = json_decode($response, true);

         if (
             is_array($response) &&
             array_key_exists("status", $response) &&
             strtolower($response["status"]) == "error" &&
             array_key_exists("message", $response)
         ) {
            throw new \Exception($response["message"]);
         }

         return $response;
      } catch (\Exception $e) {
         (new ErrorHandler())->log([
             "action" => __METHOD__,
             "response" => $e->getMessage(),
             "exception" => $e,
         ]);
         throw $e;
      }
   }

   public function getCustomerDetails($params)
   {
      try {
         $mandatoryParams = ["customer-id"];
         array_walk($mandatoryParams, function ($param) use ($params) {
            if (false === array_key_exists($param, $params)) {
               throw new \Exception("Missing Parameter $param");
            }
         });

         $response = $this->get($this->customerDetails, $params);
         $response = json_decode($response['body'], true);
         if (
             is_array($response) &&
             array_key_exists("status", $response) &&
             strtolower($response["status"]) == "error" &&
             array_key_exists("message", $response)
         ) {
            throw new \Exception($response["message"]);
         }

         return $response;
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function getCustomerCountryFromId($customerId)
   {
      try {
         $customerDetails = $this->getCustomerDetails([
             'customer-id' => $customerId
         ]);

         if (!empty($customerDetails['country'])) {
            return $customerDetails['country'];
         }
         return null;
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function listOrders($params)
   {
      try {
         $mandatoryParams = ["no-of-records", "page-no"];
         array_walk($mandatoryParams, function ($param) use ($params) {
            if (false === array_key_exists($param, $params)) {
               throw new \Exception("Missing Parameter $param");
            }
         });

         $response = $this->get($this->listOrders, $params);
         $response = $response["body"];
         $response = json_decode($response, true);
         $toReturn = [];
         $toReturn["items_info"] = [
             "recsindb" => $response["totalCount"],
             "pages" => $response["totalPages"],
         ];
         unset($response["totalCount"]);
         unset($response["totalPages"]);
         $toReturn["orders"] = $response["orderList"];

         return $toReturn;
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function addTitanOrder($params)
   {
      try {
         $mandatoryParams = [
             "domain-name",
             "customer-id",
             "plan-id",
             "noOfMonths",
             "invoice-option",
             "additional-param-json",
             "productKey"
         ];
         array_walk($mandatoryParams, function ($param) use ($params) {
            if (false === array_key_exists($param, $params)) {
               throw new \Exception("Missing Parameter $param");
            }
         });

         $response = $this->post($this->titanAddOrder, $params);
         $response = $response["body"];
         $response = json_decode($response, true);

         if (
             is_array($response) &&
             array_key_exists("status", $response) &&
             strtolower($response["status"]) == "error" &&
             array_key_exists("message", $response)
         ) {
            throw new \Exception($response["message"]);
         }

         return $response;
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function getPlanDetails($params)
   {
      try {
         $response = $this->get($this->planDetails, $params);
         return json_decode($response["body"], true);
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function getSitelockDetails($params)
   {
      try {
         $mandatoryParams = ["order-id"];
         array_walk($mandatoryParams, function ($param) use ($params) {
            if (false === array_key_exists($param, $params)) {
               throw new \Exception("Missing Parameter $param");
            }
         });

         $response = $this->get($this->sitelockSSODetails, $params);
         $response = json_decode($response["body"], true);
         if (
             is_array($response) &&
             array_key_exists("status", $response) &&
             strtolower($response["status"]) == "error" &&
             array_key_exists("message", $response)
         ) {
            throw new \Exception($response["message"]);
         }

         return $response;
      } catch (\Exception $e) {
         throw $e;
      }
   }
}
