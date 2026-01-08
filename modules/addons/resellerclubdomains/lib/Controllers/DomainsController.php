<?php

namespace WHMCS\Module\Addon\Resellerclubdomains\Controllers;

use WHMCS\Module\Addon\Resellerclubdomains\Services\OrderboxService;
use WHMCS\Module\Addon\Resellerclubdomains\Services\ErrorHandler;
use Smarty;

class DomainsController
{
    public function handleBulkResendVerificationEmail($orderIds)
    {
        try {
            $response = null;
            if (!empty($orderIds)) {
                $orderboxService = new OrderboxService();
                foreach ($orderIds as $orderId) {
                    $response[$orderId] = $orderboxService->resendVerificationEmail([
                        "order-id" => $orderId,
                    ]);
                }
            }

            return $response;
        } catch (\Exception $e) {
            (new ErrorHandler())->log([
                "action" => __METHOD__,
                "request" => $orderIds,
                "response" => $e->getMessage(),
                "exception" => $e,
            ]);
            throw $e;
        }
    }

}
