<?php

if (!defined("WHMCS")) {
   die("This file cannot be accessed directly");
}

use WHMCS\Module\Addon\Resellerclubdomains\Controllers\AdminController;

require_once "config" . DIRECTORY_SEPARATOR . "config.php";

function resellerclubdomains_config()
{
   return [
       "name" => MODULE_NAME,
       "description" => "Import, sync and manage all your Resellerclub customers and domain names",
       "version" => MODULE_VERSION,
       "author" => AUTHOR,
       "language" => "english",
       "fields" => (new AdminController())->getModuleConfigOptions()
   ];
}

function resellerclubdomains_activate()
{
   return (new AdminController())->handleActivate();
}

function resellerclubdomains_deactivate()
{
   return (new AdminController())->handleDeactivate();
}

function resellerclubdomains_output($vars)
{
   global $_ADDONLANG;
//   $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'index';
   $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'raa';

   $adminController = new AdminController();
   if (is_callable(array($adminController, $action))) {
      return (new AdminController())->$action($vars);
   }
   echo '<p>' . $_ADDONLANG['module_action_not_found'] . '</p>';
}

function resellerclubdomains_sidebar($vars)
{
   return (new AdminController())->sideBarNav($vars);
}
