<?php

namespace WHMCS\Module\Addon\Resellerclubdomains\Services;

class ErrorHandler
{

   private function _logRemote($error)
   {
      try {
         (new RollbarService())->createItem($error);
      } catch (\Exception $e) {
      }
   }

   /**
    * WHMCS Log module call
    *
    * @param string $module The name of the module
    * @param string $action The name of the action being performed
    * @param string|array $requestString The input parameters for the API call
    * @param string|array $responseData The response data from the API call
    * @param string|array $processedData The resulting data after any post processing (eg. json decode, xml decode, etc...)
    * @param array $replaceVars An array of strings for replacement
    */

   /**
    * Log
    *
    * Central log handler for local and remote logging
    * @param array $params ['action', 'request', 'response', 'replaceVars', 'exception']
    * @return void
    */
   public function log($params = [])
   {
      try {
         foreach (['action', 'request', 'response', 'replaceVars', 'exception'] as $param) {
            if (array_key_exists($param, $params)) {
               $logData[$param] = $params[$param];
            } else {
               $logData[$param] = null;
            }
         }
         $processedData = (!is_null($params['exception'])) ? $params['exception']->getMessage() : null;
         // sending to WHMCS module log
         logModuleCall(
             $params['module'] ?? MODULE_NAME,
             $logData['action'],
             $logData['request'],
             $logData['response'],
             $processedData,
             $logData['replaceVars'],
         );
         if (!array_key_exists('do_not_log_to_remote', $params)
             && !empty($params['do_not_log_to_remote'])) {
            $this->_logRemote($logData);
         }
      } catch (\Exception $e) {
      }
   }

}
