<?php

set_time_limit(100000000);
if (!defined("WHMCS")) {
   die("You cannot access this file directly.");
}

use WHMCS\Module\Addon\Resellerclubdomains\Controllers\ClientController;
use WHMCS\Module\Addon\Resellerclubdomains\Controllers\CronController;
use WHMCS\Module\Addon\Resellerclubdomains\Controllers\SitelockController;
use Smarty;

require_once "config" . DIRECTORY_SEPARATOR . "config.php";

class RAAWidgetOrderbox extends \WHMCS\Module\AbstractWidget
{
   protected $title = MODULE_NAME . " - Pending Verification Domains";
   protected $description = "";
   protected $weight = 200;
   protected $columns = 1;
   protected $cache = false;
   protected $requiredPermission = "";

   public function getData()
   {
      try {
         return (new CronController())->getPendingVerificationDomainsCount();
      } catch (\Exception $e) {
         // to ensure, no exceptions appear on the widget window
      }
   }

   public function generateOutput($count)
   {
      $smarty = new Smarty();
      $smarty->setTemplateDir(dirname(__FILE__) . "/templates");
      $moduleName = strtolower(MODULE_NAME); // @TODO look for a better alternative
      $queryParams = "&action=raa"; // @TODO look for a better alternative
      $raaLink = "addonmodules.php?module=" . $moduleName . $queryParams;
      $smarty->assign([
          "count" => $count,
          "link" => $raaLink,
      ]);
      if ($count == 0) {
         return $smarty->fetch("widgetRAAVerified.tpl");
      }
      return $smarty->fetch("widgetRAA.tpl");
   }
}

add_hook("AdminHomeWidgets", 1, function () {
   return new RAAWidgetOrderbox();
});

// ------------------------------ Cron Hooks ------------------------------

add_hook("AfterCronJob", 1, function () {
   (new CronController())->importCustomers();
});
add_hook("AfterCronJob", 2, function () {
   (new CronController())->syncClients();
});
add_hook("AfterCronJob", 3, function () {
   (new CronController())->importDomains();
});
add_hook("AfterCronJob", 4, function () {
   (new CronController())->importDomainDetails();
});

add_hook("AfterCronJob", 5, function () {
   (new CronController())->syncDomains();
});
add_hook("AfterCronJob", 6, function () {
   (new CronController())->exportClients();
});
add_hook("DailyCronJob", 1, function () {
   // (new CronController())->generatePendingVerificationDomainsReport(); // @TBD since Orderbox would already be sending a report
});

// ------------------------------ Client event hooks ------------------------------
add_hook("ClientAdd", 1, function ($vars) {
   (new ClientController())->addCustomer($vars);
});
add_hook("ClientEdit", 1, function ($vars) {
   (new ClientController())->updateCustomer($vars);
});
//add_hook("ClientClose", 1, function ($vars) {
//    (new ClientController())->deleteCustomer($vars);
//});
//add_hook("PreDeleteClient", 1, function ($vars) {
//    (new ClientController())->deleteCustomer($vars);
//});

add_hook("ClientAreaDomainDetailsOutput", 2, function ($vars) {
   if (!is_null($vars)) {
      return (new SitelockController())->handleOrder($vars);
   }
   return '';
});

