<?php

namespace WHMCS\Module\Addon\Resellerclubdomains\Controllers;

use WHMCS\Module\Addon\Resellerclubdomains\Services\CronService;
use WHMCS\Module\Addon\Resellerclubdomains\Services\DatabaseService;
use WHMCS\Module\Addon\Resellerclubdomains\Services\ErrorHandler;
use WHMCS\Module\Addon\Resellerclubdomains\Services\OrderboxService;
use WHMCS\Module\Addon\Resellerclubdomains\Services\WHMCSService;
use Smarty;

class AdminController
{

   public function index($vars)
   {
      $smarty = new Smarty();
      $smarty->setTemplateDir(dirname(__FILE__) . '/../../templates');
      $smarty->assign([
          'moduleLink' => $vars['modulelink'],
      ]);
      $smarty->display('main.tpl');
   }


   public function handleActivate()
   {
      global $_ADDONLANG;
      try {
         $cronService = new CronService();
         $cronService->createCronTable();
         $cronService->createCustomersTable();
         $cronService->createDomainsTable();
         $cronService->createActionsTable();
         $cronService->createConfigsTable();
         (new WHMCSService())->createCustomFieldIfNotExists([
             'type' => 'client',
             'fieldtype' => 'text',
             'fieldname' => CUSTOM_FIELD_OB_CUSTOMER_ID
         ]);

         return [
             'status' => 'success',
             'description' => $_ADDONLANG['activate']['success']
         ];
      } catch (\Exception $e) {
         return [
             'status' => 'error',
             'description' => (DEBUG_MODE) ? $e->getMessage() : $_ADDONLANG['activate']['error']
         ];
      }
   }

   public function handleDeactivate()
   {
      global $_ADDONLANG;
      try {
         if (DEBUG_MODE === FALSE) {
            (new DatabaseService())->dropTables([
                TBL_CRONS,
                TBL_CUSTOMERS,
                TBL_DOMAINS,
                TBL_ACTIONS,
                TBL_CONFIGS
            ]);
         }
         return [
             'status' => 'success',
             'description' => $_ADDONLANG['deactivate']['success']
         ];
      } catch (\Exception $e) {
         return [
             'status' => 'error',
             'description' => (DEBUG_MODE) ? $e->getMessage() : $_ADDONLANG['deactivate']['error']
         ];
      }
   }

   public function sideBarNav($vars)
   {
      global $_ADDONLANG;
      $smarty = new Smarty();
      $smarty->setTemplateDir(dirname(__FILE__) . '/../../templates');
      $smarty->assign([
          'moduleHeader' => MODULE_NAME,
          'moduleLink' => $vars['modulelink'],
          'raaLinkText' => $_ADDONLANG['raa']['navBar']['link']
      ]);
      return $smarty->fetch('navSidebar.tpl');
      // return $smarty->fetch('nav.tpl');
   }

   public function raa($vars)
   {
      global $_ADDONLANG;
      $language = $_ADDONLANG['raa'];
      $success = null;
      if (!empty($_POST['selectedorders'])) {
         try {
            $response = (new DomainsController())->handleBulkResendVerificationEmail($_POST['selectedorders']);
            $success = true;
            $messageTitle = $_ADDONLANG['raa']['resend']['success']['title'];
            $messageBody = $_ADDONLANG['raa']['resend']['success']['body'];
         } catch (\Exception $e) {
            $messageTitle = $_ADDONLANG['raa']['resend']['error']['title'];
            $messageBody = json_decode($e->getMessage(), true)['message'];
            $success = false;
         }
      }
      $orderboxService = new OrderboxService();
      $pageNo = $_GET['page'] ? $_GET['page'] : 1;
      $noOfRecords = (new WHMCSService())->getNumberOfRecordsToDisplay();
      $noOfRecords = ($noOfRecords < 10) ? 10 : $noOfRecords;
      $responseDomains = (new OrderboxService())->getPendingVerificationDomains([
          "page-no" => $pageNo,
          "no-of-records" => $noOfRecords, // min 10 to max 500,
      ]);

      $domains = $responseDomains['domains'];
      $domainsItemsInfo = $responseDomains['items_info'];

      foreach ($domains as $key => $domain) {
         $details = $orderboxService->getDomainDetails([
             "order-id" => $domain['orders.orderid'],
             "options" => "All"
         ]);
         $details['raa_suspension_date'] = null;
         if (
             array_key_exists('raaVerificationStartTime', $details)
             && !empty($details['raaVerificationStartTime'])
         ) {
            $details['raa_suspension_date'] = strtotime('+15 days', $details['raaVerificationStartTime']);
         }
         $domains[$key]['details'] = $details;
      }

      $totalItems = $domainsItemsInfo['recsindb'];
      $numberOfPages = ceil($totalItems / $noOfRecords);

      $smarty = new Smarty();
      $smarty->setTemplateDir(dirname(__FILE__) . '/../../templates');
      $smarty->assign(array(
          'moduleLink' => $vars['modulelink'],
          'moduleLinkRAA' => $vars['modulelink'] . "&action=raa",
          'breadcrumb' => array(
              'index.php?m=orderbox&type=raa' => 'Sample RAA Screen',
          ),
          'requirelogin' => true,
          'linkHome' => $vars['modulelink'],
          'linkRAA' => $vars['modulelink'] . "&action=raa",
          'domains' => $domains,
          'totalItems' => $totalItems,
          'pageNo' => $pageNo,
          'numberOfPages' => $numberOfPages,
          'success' => $success,
          'messageTitle' => $messageTitle,
          'messageBody' => $messageBody,
          'linkMoreDetails' => null, // "https://manage.?.com/kb/answer/2008",
          'pageText' => [
              'title' => $language['page']['title'],
              'description' => $language['page']['description'],
              'moreDetails' => $language['page']['link_more_details'],
              'labelDomainsFound' => $language['page']['domains_found'],
              'jumpToPage' => $language['page']['jump_to_page'],
              'tableHeaderDomain' => $language['page']['domain_name'],
              'tableHeaderRegistrantEmail' => $language['page']['registrant_email'],
              'tableHeaderStatus' => $language['page']['status'],
              'tableHeaderRegisteredOn' => $language['page']['registered_on'],
              'tableHeaderVerificationDeadline' => $language['page']['verification_deadline'],
              'withSelected' => $language['page']['with_selected'],
              "previousPage" => $language['page']['previous_page'],
              "nextPage" => $language['page']['next_page'],
              "resendVerificationEmail" => $language['page']['resend_verification_email'],
          ]
      ));
      $smarty->display('raa.tpl');
   }

   public function getModuleConfigOptions()
   {
      try {
         if (!empty($_POST)
             && array_key_exists("fields", $_POST)
             && array_key_exists(strtolower(MODULE_NAME), $_POST["fields"])
             && array_key_exists("setup_titan_email_product", $_POST["fields"][strtolower(MODULE_NAME)])
         ) {
            if (!empty($_POST["fields"][strtolower(MODULE_NAME)]["setup_titan_email_product"])) {
               (new TitanController())->handleProductsSetup();
            }
         }

         $fields = [
             "title_cron_configs" => array(
                 "FriendlyName" => "",
                 "Description" => "<p><h1><strong>Cron Settings</strong></h1></p>"
             ),
             CONFIG_DONT_IMPORT_DOMAINS => array(
                 "FriendlyName" => "Disable automatic domains import?",
                 "Type" => "yesno",
                 "Size" => "25",
                 "Description" => "Don't import my domain names from ResellerClub",
             ),
             CONFIG_DONT_IMPORT_CUSTOMERS => array(
                 "FriendlyName" => "Disable automatic customer import?",
                 "Type" => "yesno",
                 "Size" => "25",
                 "Description" => "Don't import my customers from ResellerClub",
             ),
             CONFIG_DONT_EXPORT_CLIENTS => array(
                 "FriendlyName" => "Disable automatic client export?",
                 "Type" => "yesno",
                 "Size" => "25",
                 "Description" => "Don't export my clients as customers on ResellerClub",
             ),
//             CONFIG_DONT_SEND_RAA_REPORT => array(
//                 "FriendlyName" => "Disable daily RAA report?",
//                 "Type" => "yesno",
//                 "Size" => "25",
//                 "Description" => "Don't want to receive daily pending verification domains report",
//             ),
             "placeholder_after_crons" => array(
                 "FriendlyName" => "",
                 "Description" => "<br>"
             ),
         ];

         $whmcsService = new WHMCSService();

         $resellerCredentials = $whmcsService->getDecryptedResellerAPICredentials();
         $fields["title_reseller_credentials"] = array(
             "FriendlyName" => "",
             "Description" => "<p><h1><strong>Reseller API Credentials</strong></h1></p>"
         );
         $fields[CONFIG_RESELLER_ID] = [
             "FriendlyName" => "Orderbox Reseller ID",
             "Type" => "text",
             "Size" => "25",
             "Description" => "",
             "Default" => !empty($resellerCredentials['resellerId']) ? $resellerCredentials['resellerId'] : ""
         ];
         $fields[CONFIG_RESELLER_API_KEY] = [
             "FriendlyName" => "Orderbox Reseller API Key",
             "Type" => "text",
             "Size" => "25",
             "Description" => "",
             "Default" => !empty($resellerCredentials['apiKey']) ? $resellerCredentials['apiKey'] : "",
         ];

         $fields["placeholder_after_reseller_credentials"] = array(
             "FriendlyName" => "",
             "Description" => "<br>"
         );

         // show Titan config only if provisioning module present
         if (is_dir(__DIR__ . '/../../../../servers/' . PROVISIONING_MODULE_IDENTIFIER)) {
            $fields = array_merge($fields, [
                "title_titan_email" => array(
                    "FriendlyName" => "",
                    "Description" => "<p><h1><strong>Titan Email</strong></h1></p>"
                ),
                CONFIG_SETUP_TITAN_EMAIL_PRODUCT => array(
                    "FriendlyName" => "Configure Titan Email Product?",
                    "Type" => "yesno",
                    "Size" => "25",
                    "Description" => "Enable this to auto create Titan Email products in WHMCS by fetching plan details from your ResellerClub account",
                )
            ]);

            if ((new WHMCSService())->isConfiguredTitanProduct()) {
               $fields["help_text_after_titan_email"] = array(
                   "FriendlyName" => "",
                   "Description" => "<a href='/admin/configproducts.php?' target=
                   '_blank'>Click here to manage product configurations</a>"
               );
            }

            $fields["placeholder_after_titan_email"] = array(
                "FriendlyName" => "",
                "Description" => "<br>"
            );
         }


         $responsePaymentMethods = $whmcsService->getPaymentMethods();

         if (!empty($responsePaymentMethods['totalresults'])) {
            $methods = array_column($responsePaymentMethods['paymentmethods']['paymentmethod'], 'module');
            $methodLabels = array_column($responsePaymentMethods['paymentmethods']['paymentmethod'], 'displayname');
            $paymentMethods = array_combine($methods, $methodLabels);
            $fields[CONFIG_DEFAULT_PAYMENT_METHOD] = [
                "FriendlyName" => "Default Payment Method",
                "Description" => "Payment method to apply for domain names imported via cron",
                "Type" => "dropdown",
                "Options" => $paymentMethods,
                "Default" => array_keys($paymentMethods)[0]
            ];
         } else {
            $fields[CONFIG_DEFAULT_PAYMENT_METHOD] = [
                "FriendlyName" => "Default Payment Method",
                "Description" => "<i class='fas fa-exclamation-triangle color-orange'></i> <Strong style='color:red'>No payment method found</strong> <br/>Please add a payment method as per <a href='https://docs.whmcs.com/Payment_Gateways' target='_blank'>these instructions <i class='fa fa-external-link' aria-hidden='true' style='color:#3F4E6D;size:4;'></i></a><br>
                    Then come back here to choose and save a default payment method",
            ];
         }


      } catch (\Exception $e) {
         (new ErrorHandler())->log([
             'action' => __METHOD__,
             'response' => $e->getMessage(),
             'exception' => $e
         ]);
      } finally {
         return $fields;
      }
   }
}
