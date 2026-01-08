<?php

namespace WHMCS\Module\Addon\Resellerclubdomains\Controllers;

use WHMCS\Module\Addon\Resellerclubdomains\Library\Utils;
use WHMCS\Module\Addon\Resellerclubdomains\Services\CronService;
use WHMCS\Module\Addon\Resellerclubdomains\Services\ErrorHandler;
use WHMCS\Module\Addon\Resellerclubdomains\Services\OrderboxService;
use WHMCS\Module\Addon\Resellerclubdomains\Services\WHMCSService;
use Smarty;

class CronController
{

    private function _isCronRunRecently($type, $status = DB_STATUS_COMPLETED)
    {
        try {
            if (DEBUG_MODE) return false;
            $lastCompletedCron = (new CronService())->getCron([
                'type' => $type,
                'status' => $status,
                'updated' => date('Y-m-d H:i:s', (time() - strtotime("6 hours ago")))
            ]);
            return (false === is_null($lastCompletedCron));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function importCustomers()
    {
        try {
            if ((new WHMCSService())->isModuleConfigOn([
                'setting' => CONFIG_DONT_IMPORT_CUSTOMERS
            ])) {
                return;
            }
            if ($this->_isCronRunRecently(CRON_TYPE_IMPORT_CUSTOMERS)) {
                return;
            }

            $batchToProcess = 0;
            $batchesTotal = 0;
            $totalItems = 0;
            $batchSize = CRON_BATCH_SIZE;
            $cronService = new CronService();
            $orderBoxService = new OrderboxService();

            $existingCron = $cronService->getCron([
                'type' => CRON_TYPE_IMPORT_CUSTOMERS,
                'status' => [DB_STATUS_ADDED, DB_STATUS_STARTED]
            ]);

            if (is_null($existingCron)) {
                $cronId = $cronService->addCron([
                    'type' => CRON_TYPE_IMPORT_CUSTOMERS,
                    'batch_size' => CRON_BATCH_SIZE,
                    'status' => DB_STATUS_ADDED
                ]);
            } else {
                if ($existingCron->status == DB_STATUS_STARTED) {
                    $batchToProcess = $existingCron->batch_completed;
                }
                $cronId = $existingCron->id;
                $totalItems = $existingCron->total_items;
                $batchesTotal = $totalItems > CRON_BATCH_SIZE ? ceil($totalItems / CRON_BATCH_SIZE) : 1;
            }

            do {
                try {
                    if ($batchToProcess == 0) {
                        $updateParams['status'] = DB_STATUS_STARTED;
                    }
                    $batchToProcess++;

                    $customers = $orderBoxService->listCustomers([
//                        'skip' => (($batchToProcess - 1) * $batchSize),
                        'skip' => ($batchToProcess - 1),
                        'limit' => $batchSize,
                        'status' => [OB_CUSTOMER_STATUS_ACTIVE, OB_CUSTOMER_STATUS_SUSPENDED]
                    ]);

                    if ($totalItems == 0) {
                        $totalItems = $customers['totalCustomersCount'];
                        $updateParams['total_items'] = $totalItems;
                    }

                    $batchesTotal = $totalItems > CRON_BATCH_SIZE ? ceil($totalItems / CRON_BATCH_SIZE) : 1;
                    if (!empty($customers['totalCustomersCount'])) {
                        $cronService->upsertCustomers($this->_formatCustomersForDB($customers['customersList']));
                    }
                    unset($customers);
                } catch (\Exception $e) {
                    (new ErrorHandler)->log([
                        'action' => __METHOD__,
                        'response' => $e->getMessage(),
                        'exception' => $e
                    ]);
                } finally {
                    $whereParams = [
                        'where_id' => $cronId,
                    ];
                    $updateParams['batch_completed'] = $batchToProcess;

                    if ($batchToProcess == $batchesTotal) {
                        $updateParams['status'] = DB_STATUS_COMPLETED;
                    }
                    $cronService->updateCron($whereParams, $updateParams);
                }
            } while ($batchToProcess < $batchesTotal);
        } catch (\Exception $e) {
            (new ErrorHandler())->log([
                'action' => __METHOD__,
                'response' => $e->getMessage(),
                'exception' => $e
            ]);
        }
    }

    public function syncClients()
    {
        try {
            if ((new WHMCSService())->isModuleConfigOn([
                'setting' => CONFIG_DONT_IMPORT_CUSTOMERS
            ])) {
                return;
            }
            if ($this->_isCronRunRecently(CRON_TYPE_SYNC_CLIENTS)) {
                return;
            }

            $batchToProcess = 0;
            $batchesTotal = 0;
            $totalItems = 0;
            $batchSize = CRON_BATCH_SIZE;
            $cronService = new CronService();
            $resellerId = (new WHMCSService())->getAddonModuleConfiguration(['setting' => CONFIG_RESELLER_ID]);

            $existingCron = $cronService->getCron([
                'type' => CRON_TYPE_SYNC_CLIENTS,
                'status' => [DB_STATUS_ADDED, DB_STATUS_STARTED]
            ]);

            if (is_null($existingCron)) {
                $cronId = $cronService->addCron([
                    'type' => CRON_TYPE_SYNC_CLIENTS,
                    'batch_size' => CRON_BATCH_SIZE,
                    'status' => DB_STATUS_ADDED
                ]);
            } else {
                if ($existingCron->status == DB_STATUS_STARTED) {
                    $batchToProcess = $existingCron->batch_completed;
                }
                $cronId = $existingCron->id;
                $totalItems = $existingCron->total_items;
                $batchesTotal = $totalItems > CRON_BATCH_SIZE ? ceil($totalItems / CRON_BATCH_SIZE) : 1;
            }
            if ($totalItems == 0) {
                $totalItems = $cronService->getCustomerCount([
                    'reseller_id' => $resellerId,
                   // 'last_modified_time' => date('Y-m-d H:i:s', (time() - CRON_BUFFER_IN_SECONDS)),
                ]);
                $batchesTotal = $totalItems > $batchSize ? ceil($totalItems / $batchSize) : 1;
            }

            do {
                try {
                    if ($batchToProcess == 0) {
                        $updateParams['total_items'] = $totalItems;
                        $updateParams['status'] = DB_STATUS_STARTED;
                    }
                    $batchToProcess++;

                    if ($totalItems > 0) {
                        $customers = $cronService->listCustomers([
                            //'last_modified_time' => date('Y-m-d H:i:s', (time() - CRON_BUFFER_IN_SECONDS)),
                            'reseller_id' => $resellerId,
                            'skip' => (($batchToProcess - 1) * $batchSize),
                            'limit' => $batchSize
                        ]);
                        if (!empty($customers)) {
                            $this->_importClients($customers);
                        }
                        unset($customers);
                    }
                } catch (\Exception $e) {
                    (new ErrorHandler())->log([
                        'action' => __METHOD__,
                        'response' => $e->getMessage(),
                        'exception' => $e
                    ]);

                    continue;
                } finally {
                    $whereParams = [
                        'where_id' => $cronId,
                    ];
                    $updateParams['batch_completed'] = $batchToProcess;
                    if ($batchToProcess == $batchesTotal) {
                        $updateParams['status'] = DB_STATUS_COMPLETED;
                    }
                    $cronService->updateCron($whereParams, $updateParams);
                }
            } while ($batchToProcess < $batchesTotal);
        } catch (\Exception $e) {
            (new ErrorHandler())->log([
                'action' => __METHOD__,
                'response' => $e->getMessage(),
                'exception' => $e
            ]);
        }
    }

    private function _importClients($customers)
    {
        try {
            $customerUsernames = array_column($customers, 'username');
            $clientAndUserDetails = (new WHMCSService())->getClientAndUserDetails($customerUsernames);
            $clientUsernames = array_column($clientAndUserDetails, 'email');
            $clientUsernamesFlipped = array_flip($clientUsernames);

            foreach ($customers as $customer) {
                try {
                    if (in_array($customer->username, $clientUsernames)) {
                        $client = $clientAndUserDetails[$clientUsernamesFlipped[$customer->username]];

                        $lastUpdatedDateCustomer = strtotime($customer->updated_at);
                        $lastUpdatedDateClient = strtotime($client->updated_at);

                        if (DEBUG_MODE || ($lastUpdatedDateCustomer > ($lastUpdatedDateClient + CRON_BUFFER_IN_SECONDS))) {
                            // client details seem outdated and should be updated using OB customer details
                            $customerDetails = json_decode($customer->metadata, true);

                            $name = explode(' ', $customerDetails['name']);
                            $lastName = $name[1] ? $name[1] : "-";

                            // update user
                            $userId = (new WHMCSService())->updateUser([
                                'user_id' => $client->auth_user_id,
                                'firstname' => $name[0],
                                'lastname' => $lastName,
                                'email' => $customerDetails['username']
                            ]);

                            // update client
                            $paramsUpdateClient = [
                                'clientid' => $client->client_id,
                                'firstname' => $name[0],
                                'lastname' => $lastName,
                                'address1' => $customerDetails['address1'],
                                'city' => $customerDetails['city'],
                                'state' => $customerDetails['state'],
                                'postcode' => $customerDetails['zip'],
                                'country' => $customerDetails['country'],
                                // 'phonenumber' => $customerDetails['telnocc'] . $customerDetails['telno'],
                                'phonenumber' => $customerDetails['telno'],
                                'customfields' => base64_encode(serialize([
                                    CUSTOM_FIELD_OB_CUSTOMER_ID => $customerDetails['customerId']
                                ])),
                            ];

                            if (strtolower($customerDetails['status']) != strtolower(OB_CUSTOMER_STATUS_ACTIVE)) {
                                $paramsUpdateClient['status'] = WHMCS_CLIENT_STATUS_INACTIVE;
                            }
                            if (!empty($customerDetails['companyname'])) {
                                $paramsUpdateClient['companyname'] = $customerDetails['companyname'];
                            }
                            if (!empty($customerDetails['address2'])) {
                                $paramsUpdateClient['address2'] = $customerDetails['address2'];
                            }
                            if (!empty($customerDetails['address3'])) {
                                $paramsUpdateClient['address2'] .= " " . $customerDetails['address3'];
                            }

                            $clientId = (new WHMCSService())->updateClient($paramsUpdateClient);
                            unset($paramsUpdateClient);
                        }
                    } else {
                        $customerDetails = json_decode($customer->metadata, true);
                        $customerDetails['customerId'] = $customer->customer_id;
                        $clientDetails = $this->_addUserAndClient($customerDetails);
                    }
                    unset($customerDetails);
                } catch (\Exception $e) {
                    (new ErrorHandler())->log([
                        'action' => __METHOD__,
                        'request' => $customer,
                        'response' => $e->getMessage(),
                        'exception' => $e
                    ]);
                    continue;
                } finally {
                    unset($customer);
                    unset($customerDetails);
                }
            }
            unset($customers);
            unset($customerUsernames);
            unset($clientAndUserDetails);
            unset($clientUsernames);
            unset($clientUsernamesFlipped);
        } catch (\Exception $e) {
            (new ErrorHandler())->log([
                'action' => __METHOD__,
                'response' => $e->getMessage(),
                'exception' => $e
            ]);
        }
    }

    private function _addUserAndClient($details)
    {
        try {
            $name = explode(' ', $details['name']);
            $lastName = $name[1] ? $name[1] : "-";

            // get user Id if user already exists
            $existingUser = (new WHMCSService())->getUserDetailsByEmail($details['username']);
            $existingUser = (array)$existingUser[0];

            if (array_key_exists('id', $existingUser) && !empty($existingUser)) {
                $userId = $existingUser['id'];
            } else {
                $userId = (new WHMCSService())->addUser([
                    'firstname' => $name[0],
                    'lastname' => $lastName,
                    'email' => $details['username'],
                    'password2' => (new Utils())->getRandomPassword(),
                ]);
            }

            if ($userId) {
                // add client
                $paramsAddClient = [
                    'owner_user_id' => $userId,
                    'firstname' => $name[0],
                    'lastname' => $lastName,
                    'email' => $details['username'],
                    'address1' => $details['address1'],
                    'city' => $details['city'],
                    'state' => $details['state'],
                    'postcode' => $details['zip'],
                    'country' => $details['country'],
                    // 'phonenumber' => $details['telnocc'] . $details['telno'],
                    'phonenumber' => $details['telno'],
                    'customfields' => base64_encode(serialize([
                        CUSTOM_FIELD_OB_CUSTOMER_ID => $details['customerId'],
                    ])),
                ];

                if (!empty($details['companyname'])) {
                    $paramsAddClient['companyname'] = $details['companyname'];
                }
                if (!empty($details['address2'])) {
                    $paramsAddClient['address2'] = $details['address2'];
                }
                if (!empty($details['address3'])) {
                    $paramsAddClient['address2'] .= " " . $details['address3'];
                }

                $clientId = (new WHMCSService())->addClient($paramsAddClient);

                if (strtolower($details['status']) != strtolower(OB_CUSTOMER_STATUS_ACTIVE)) {
                    (new WHMCSService())->updateClient([
                        'clientid' => $clientId,
                        'status' => WHMCS_CLIENT_STATUS_INACTIVE
                    ]);
                }
                unset($paramsAddClient);
                return $clientId;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function _formatCustomersForDB($customers)
    {
        $resellerCreds = (new WHMCSService())->getResellerAPICredsFromModuleConfig();
        $resellerId = $resellerCreds['resellerId'];
        return array_map(function ($customer) use ($resellerId) {
            return [
                'customer_id' => $customer['customerId'],
                'username' => $customer['username'],
                'status' => $customer['status'],
                'reseller_id' => !empty($resellerId) ? $resellerId : null,
                'last_modified_time' => date("Y-m-d H:i:s", $customer['lastModifiedTime'] / 1000), // since OB returns lastModifiedTime in milliseconds
                'metadata' => json_encode($customer),
            ];
        }, $customers);
    }

    public function exportClients()
    {
        try {
            if ((new WHMCSService())->isModuleConfigOn([
                'setting' => CONFIG_DONT_EXPORT_CLIENTS
            ])) {
                return;
            }
            if ($this->_isCronRunRecently(CRON_TYPE_EXPORT_CLIENTS)) {
                return;
            }

            $batchToProcess = 0;
            $batchesTotal = 0;
            $totalItems = 0;
            $batchSize = CRON_BATCH_SIZE;
            $cronService = new CronService();
            $whmcsService = new WHMCSService();
            $customFieldId = $whmcsService->getCustomFieldId(CUSTOM_FIELD_OB_CUSTOMER_ID);
            $customFieldId = $customFieldId->id;
            $resellerId = $whmcsService->getAddonModuleConfiguration(['setting' => CONFIG_RESELLER_ID]);

            $existingCron = $cronService->getCron([
                'type' => CRON_TYPE_EXPORT_CLIENTS,
                'status' => [DB_STATUS_ADDED, DB_STATUS_STARTED]
            ]);

            if (is_null($existingCron)) {
                $cronId = $cronService->addCron([
                    'type' => CRON_TYPE_EXPORT_CLIENTS,
                    'batch_size' => $batchSize,
                    'status' => DB_STATUS_ADDED
                ]);
            } else {
                if ($existingCron->status === DB_STATUS_STARTED) {
                    $batchToProcess = $existingCron->batch_completed;
                }
                $cronId = $existingCron->id;
                $totalItems = $existingCron->total_items;
                $batchesTotal = $totalItems > $batchSize ? ceil($totalItems / $batchSize) : 1;
            }

            if ($totalItems == 0) {
                $totalItems = $whmcsService->getClientsWithCustomField([
                    'custom_field_id' => $customFieldId,
                    'reseller_id' => $resellerId,
                    //                    'client_updated_at' => CRON_BUFFER_IN_SECONDS,
                    'count' => true
                ]);
                $batchesTotal = $totalItems > $batchSize ? ceil($totalItems / $batchSize) : 1;
            }

            do {
                try {
                    if ($batchToProcess == 0) {
                        $updateParams['total_items'] = $totalItems;
                        $updateParams['status'] = DB_STATUS_STARTED;
                    }
                    $batchToProcess++;

                    if ($totalItems > 0) {
                        $clients = $whmcsService->getClientsWithCustomField([
                            'custom_field_id' => $customFieldId,
                            'reseller_id' => $resellerId,
                            //                    'client_updated_at' => CRON_BUFFER_IN_SECONDS,
                            'skip' => (($batchToProcess - 1) * $batchSize),
                            'limit' => $batchSize
                        ]);
                        if (!empty($clients)) {
                            $this->_exportCustomers($clients);
                        }
                    }
                } catch (\Exception $e) {
                    (new ErrorHandler())->log([
                        'action' => __METHOD__,
                        'response' => $e->getMessage(),
                        'exception' => $e
                    ]);
                } finally {
                    $whereParams = [
                        'where_id' => $cronId,
                    ];
                    $updateParams['batch_completed'] = $batchToProcess;
                    if ($batchesTotal == $batchToProcess) {
                        $updateParams['status'] = DB_STATUS_COMPLETED;
                    }
                    $cronService->updateCron($whereParams, $updateParams);
                }
            } while ($batchToProcess < $batchesTotal);
        } catch (\Exception $e) {
            (new ErrorHandler())->log([
                'action' => __METHOD__,
                'response' => $e->getMessage(),
                'exception' => $e
            ]);
        }
    }

    private function _exportCustomers($clients)
    {
        try {
            $whmcsService = new WHMCSService();
            $orderboxService = new OrderboxService();

            foreach ($clients as $client) {
                try {
                    $clientDetails = $whmcsService->getClientDetailsByEmail($client->email);
                    if (!empty($client->ob_customer_id)) {
                        $clientDetails['customer-id'] = $client->ob_customer_id;
                        $responseCustomerUpdate = $orderboxService->customerUpdate($clientDetails);
                    } else {
                        $responseCustomerSignup = $orderboxService->handleCustomerSignup($clientDetails);
                        // @TODO if fails with response that customer already exists, fetch customer ID from username, send update call?
                    }
                } catch (\Exception $e) {
                    (new ErrorHandler())->log([
                        'action' => __METHOD__,
                        'request' => $client,
                        'response' => $e->getMessage(),
                        'exception' => $e
                    ]);
                    continue;
                }
            }
        } catch (\Exception $e) {
            (new ErrorHandler())->log([
                'action' => __METHOD__,
                'response' => $e->getMessage(),
                'exception' => $e
            ]);
        }
    }

    private function _formatDomainsForDB($domains)
    {
        $resellerCreds = (new WHMCSService())->getResellerAPICredsFromModuleConfig();
        $resellerId = $resellerCreds['resellerId'];
        return array_map(function ($domain) use ($resellerId) {
            return [
                'order_id' => $domain['orders.orderid'],
                'domain_name' => $domain['entity.description'],
                'status' => $domain['entity.currentstatus'],
                'reseller_id' => !empty($resellerId) ? $resellerId : null,
                'customer_id' => $domain['entity.customerid'],
                'last_modified_time' => date("Y-m-d H:i:s", strtotime($domain['orders.timestamp'])),
                'metadata' => json_encode($domain),
            ];
        }, $domains);
    }

    public function importDomains()
    {
        try {
            if ((new WHMCSService())->isModuleConfigOn([
                'setting' => CONFIG_DONT_IMPORT_DOMAINS
            ])) {
                return;
            }
            if ($this->_isCronRunRecently(CRON_TYPE_IMPORT_DOMAINS)) {
                return;
            }

            $batchToProcess = 0;
            $batchesTotal = 0;
            $totalItems = 0;
            $batchSize = CRON_BATCH_SIZE;
            $cronService = new CronService();
            $orderBoxService = new OrderboxService();
            $existingCron = $cronService->getCron([
                'type' => CRON_TYPE_IMPORT_DOMAINS,
                'status' => [DB_STATUS_ADDED, DB_STATUS_STARTED]
            ]);

            if (is_null($existingCron)) {
                $cronId = $cronService->addCron([
                    'type' => CRON_TYPE_IMPORT_DOMAINS,
                    'batch_size' => $batchSize,
                    'status' => DB_STATUS_ADDED
                ]);
            } else {
                if ($existingCron->status == DB_STATUS_STARTED) {
                    $batchToProcess = $existingCron->batch_completed;
                }
                $cronId = $existingCron->id;
                $totalItems = $existingCron->total_items;
                $batchesTotal = $totalItems > $batchSize ? ceil($totalItems / $batchSize) : 1;
            }

            do {
                try {
                    if ($batchToProcess == 0) {
                        $updateParams['total_items'] = $totalItems;
                        $updateParams['status'] = DB_STATUS_STARTED;
                    }
                    $batchToProcess++;
                    $responseListDomains = $orderBoxService->listDomains([
                        'no-of-records' => $batchSize,
                        'page-no' => ($batchToProcess),
                        'order-by' => ['order-id']
                    ]);
                    $domains = $responseListDomains['domains'];
                    $domainsItemsInfo = $responseListDomains['items_info'];

                    if (!empty($domainsItemsInfo['recsindb'])) {
                        if ($totalItems == 0) {
                            $totalItems = $domainsItemsInfo['recsindb'];
                            $updateParams['total_items'] = $totalItems;
                            $updateParams['status'] = DB_STATUS_STARTED;
                        }
                        $batchesTotal = $totalItems > $batchSize ? ceil($totalItems / $batchSize) : 1;

                        $cronService->upsertDomains($this->_formatDomainsForDB($domains));
                    }
                } catch (\Exception $e) {
                    (new ErrorHandler())->log([
                        'action' => __METHOD__,
                        'response' => $e->getMessage(),
                        'exception' => $e
                    ]);
                } finally {
                    $whereParams = [
                        'where_id' => $cronId,
                    ];
                    $updateParams['batch_completed'] = $batchToProcess;
                    if ($batchToProcess == $batchesTotal) {
                        $updateParams['status'] = DB_STATUS_COMPLETED;
                    }
                    $cronService->updateCron($whereParams, $updateParams);
                }
            } while ($batchToProcess < $batchesTotal);
        } catch (\Exception $e) {
            (new ErrorHandler())->log([
                'action' => __METHOD__,
                'response' => $e->getMessage(),
                'exception' => $e
            ]);
        }
    }

    public function importDomainDetails()
    {
        try {
            if ((new WHMCSService())->isModuleConfigOn([
                'setting' => CONFIG_DONT_IMPORT_DOMAINS
            ])) {
                return;
            }
            if ($this->_isCronRunRecently(CRON_TYPE_IMPORT_DOMAIN_DETAILS)) {
                return;
            }
            $batchToProcess = 0;
            $batchesTotal = 0;
            $totalItems = 0;
            $batchSize = 1; // since OB API will return details for one order at a time
            $cronService = new CronService();
            $orderBoxService = new OrderboxService();
            $resellerId = (new WHMCSService())->getAddonModuleConfiguration(['setting' => CONFIG_RESELLER_ID]);

            $existingCron = $cronService->getCron([
                'type' => CRON_TYPE_IMPORT_DOMAIN_DETAILS,
                'status' => [DB_STATUS_ADDED, DB_STATUS_STARTED]
            ]);

            if (is_null($existingCron)) {
                $cronId = $cronService->addCron([
                    'type' => CRON_TYPE_IMPORT_DOMAIN_DETAILS,
                    'batch_size' => $batchSize,
                    'status' => DB_STATUS_ADDED
                ]);
            } else {
                if ($existingCron->status == DB_STATUS_STARTED) {
                    $batchToProcess = $existingCron->batch_completed;
                }
                $cronId = $existingCron->id;
                $totalItems = $existingCron->total_items;
                $batchesTotal = $totalItems > $batchSize ? ceil($totalItems / $batchSize) : 1;
            }

            if ($totalItems == 0) {
                $totalItems = $cronService->getDomainsCount([
                    'reseller_id' => $resellerId
                ]);
                $batchesTotal = $totalItems > $batchSize ? ceil($totalItems / $batchSize) : 1;
            }
            do {
                try {
                    if ($batchToProcess == 0) {
                        $updateParams['total_items'] = $totalItems;
                        $updateParams['status'] = DB_STATUS_STARTED;
                    }
                    $batchToProcess++;

                    if ($totalItems > 0) {
                        $domains = $cronService->listDomains([
                            //                            'last_modified_time' => date('Y-m-d H:i:s', (time() - CRON_BUFFER_IN_SECONDS)),
                            'reseller_id' => $resellerId,
                            'skip' => ($batchToProcess * 1) - 1,
                            'order_by_id' => true,
                            'limit' => $batchSize,
                        ]);
                        $domains = (array)$domains[0];
                        if (!empty($domains['order_id'])) {
                            $domainDetails = $orderBoxService->getDomainDetails([
                                'order-id' => $domains['order_id'],
                                'options' => 'All'
                            ]);

                            $cronService->updateDomains([
                                'where_id' => $domains['id']
                            ], [
                                'details' => json_encode($domainDetails),
                                'raa_verification_pending' => strtolower($domainDetails['raaVerificationStatus'])
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    (new ErrorHandler())->log([
                        'action' => __METHOD__,
                        'response' => $e->getMessage(),
                        'exception' => $e
                    ]);
                } finally {
                    $whereParams = [
                        'where_id' => $cronId,
                    ];
                    $updateParams['batch_completed'] = $batchToProcess;
                    if ($batchToProcess == $batchesTotal) {
                        $updateParams['status'] = DB_STATUS_COMPLETED;
                    }
                    $cronService->updateCron($whereParams, $updateParams);
                }
            } while ($batchToProcess < $batchesTotal);
        } catch (\Exception $e) {
            (new ErrorHandler())->log([
                'action' => __METHOD__,
                'response' => $e->getMessage(),
                'exception' => $e
            ]);
        }
    }

    public function syncDomains()
    {
        try {
            if ((new WHMCSService())->isModuleConfigOn([
                'setting' => CRON_TYPE_SYNC_DOMAINS
            ])) {
                return;
            }

            if ($this->_isCronRunRecently(CRON_TYPE_SYNC_DOMAINS)) {
                return;
            }
            $batchToProcess = 0;
            $batchesTotal = 0;
            $totalItems = 0;
            $batchSize = CRON_BATCH_SIZE;
            $cronService = new CronService();
            $whmcsService = new WHMCSService();
            $customFieldId = $whmcsService->getCustomFieldId(CUSTOM_FIELD_OB_CUSTOMER_ID);
            $resellerId = $whmcsService->getAddonModuleConfiguration(['setting' => CONFIG_RESELLER_ID]);
            $customFieldId = $customFieldId->id;
            $paymentMethod = $whmcsService->getAddonModuleConfiguration([
                'setting' => CONFIG_DEFAULT_PAYMENT_METHOD
            ]);

            if (empty($paymentMethod)) {
                throw new \Exception("No payment method configured for " . MODULE_NAME . " addon module");
            }
            // checking if payment method configured in the module is an active payment gateway in this WHMCS account
            $paymentGatewaysEnabled = $whmcsService->getPaymentMethods();
            if (array_key_exists('paymentmethods', $paymentGatewaysEnabled)
                && array_key_exists('paymentmethod', $paymentGatewaysEnabled['paymentmethods'])) {
                if (false == in_array($paymentMethod, array_column($paymentGatewaysEnabled['paymentmethods']['paymentmethod'], 'module'))) {
                    throw new \Exception("Payment method configured - " . $paymentMethod . " for " . MODULE_NAME . " addon module is not active for this WHMCS setup");
                }
            } else {
                throw new \Exception("No payment gateway active for this WHMCS account");
            }

            $existingCron = $cronService->getCron([
                'type' => CRON_TYPE_SYNC_DOMAINS,
                'status' => [DB_STATUS_ADDED, DB_STATUS_STARTED]
            ]);

            if (is_null($existingCron)) {
                $cronId = $cronService->addCron([
                    'type' => CRON_TYPE_SYNC_DOMAINS,
                    'batch_size' => $batchSize,
                    'status' => DB_STATUS_ADDED
                ]);
            } else {
                if ($existingCron->status == DB_STATUS_ADDED) {
                    $batchToProcess = $existingCron->batch_completed;
                }
                $cronId = $existingCron->id;
                $totalItems = $existingCron->total_items;
                $batchesTotal = $totalItems > $batchSize ? ceil($totalItems / $batchSize) : 1;
            }

            if (empty($totalItems)) {
                $totalItems = $cronService->getDomainsCount([
                    'reseller_id' => $resellerId
                ]);
                $batchesTotal = $totalItems > $batchSize ? ceil($totalItems / $batchSize) : 1;
            }

            do {
                try {
                    if ($batchToProcess == 0) {
                        $updateParams['total_items'] = $totalItems;
                        $updateParams['status'] = DB_STATUS_STARTED;
                    }
                    $batchToProcess++;

                    if ($totalItems > 0) {
                        $domains = $cronService->listDomains([
//                            'last_modified_time' => CRON_BUFFER_IN_SECONDS,
                            'reseller_id' => $resellerId,
                            'order_by_customer_id' => true,
                            'order_by_order_id' => true,
                            'limit' => $batchSize,
                            'skip' => (($batchToProcess - 1) * $batchSize)
                        ]);

                        if (!empty($domains)) {
                            $this->_handleDomainsSync($domains, $customFieldId, $paymentMethod);
                        }
                    }
                } catch (\Exception $e) {
                    (new ErrorHandler())->log([
                        'action' => __METHOD__,
                        'response' => $e->getMessage(),
                        'exception' => $e
                    ]);

                    continue;
                } finally {
                    $whereParams = [
                        'where_id' => $cronId,
                    ];
                    $updateParams['batch_completed'] = $batchToProcess;

                    if ($batchToProcess == $batchesTotal) {
                        $updateParams['status'] = DB_STATUS_COMPLETED;
                    }
                    $cronService->updateCron($whereParams, $updateParams);
                }
            } while ($batchToProcess < $batchesTotal);
        } catch (\Exception $e) {
            (new ErrorHandler())->log([
                'action' => __METHOD__,
                'response' => $e->getMessage(),
                'exception' => $e
            ]);
        }
    }

    private function _handleDomainAddAcceptUpdate($clientId, $domain, $paymentMethod)
    {
        try {
            $whmcsService = new WHMCSService();
            $details = json_decode($domain['details'], true);
            // add
            $responseAdd = $whmcsService->addOrder([
                'clientid' => $clientId,
                'paymentmethod' => $paymentMethod,
                'domain' => [$domain['domain_name']],
                'domaintype' => ['register'],
                'regperiod' => [$details['currentOrderTenure'] / 12],
                'noinvoice' => true,
                'noinvoiceemail' => true,
                'noemail' => true,
                'dnsmanagement' => [true],
                'emailforwarding' => [true],
                'idprotection' => [filter_var($details['isprivacyprotected'], FILTER_VALIDATE_BOOLEAN)],
                //'domainfields' => '', // can add contact info here, else can ignore and let WHMCS pull contact info at runtime
            ]);

            if (
                array_key_exists('domainids', $responseAdd)
                && !empty($responseAdd['domainids'])
                && array_key_exists('orderid', $responseAdd)
                && !empty($responseAdd['orderid'])
            ) {
                // accept order
                $responseAcceptOrder = $whmcsService->acceptOrder([
                    'orderid' => $responseAdd['orderid']
                ]);

                // update client domain
                if ($responseAcceptOrder) {
                    $responseUpdateClientDomain = $whmcsService->updateClientDomain([
                        'domainid' => $responseAdd['domainids'],
                        'registrar' => REGISTRAR_RESELLERCLUB,
                        'regdate' => date("Y-m-d H:i:s", $details['creationtime']),
                        'expirydate' => date("Y-m-d H:i:s", $details['endtime']),
                    ]);
                }
            } else {
                throw new \Exception(serialize($responseAdd));
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getPendingVerificationDomainsCount()
    {
        try {
            $domains = (new OrderboxService())->getPendingVerificationDomains();
            if (!empty($domains) && array_key_exists('items_info', $domains)) {
                return $domains['items_info']['recsindb'] ? $domains['items_info']['recsindb'] : 0;
            }
            return 0;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function generatePendingVerificationDomainsReport()
    {
        try {
            if ((new WHMCSService())->isModuleConfigOn([
                'setting' => CONFIG_DONT_SEND_RAA_REPORT
            ])) {
                return;
            }
            $countPendingVerificationDomains = $this->getPendingVerificationDomainsCount();

            if ($countPendingVerificationDomains > 0) {
                $whmcsService = new WHMCSService();
                $systemUrl = $whmcsService->getSystemUrl();
                $moduleName = strtolower(MODULE_NAME); // @TODO look for a better alternative
                $queryParams = "&action=raa"; // @TODO look for a better alternative
                $raaLink = $systemUrl . "admin/addonmodules.php?module=" . $moduleName . $queryParams;

                $smarty = new Smarty();
                $smarty->setTemplateDir(dirname(__FILE__) . '/../../templates');
                $smarty->assign([
                    "title" => "Registrant Verification Pending",
                    "count" => $countPendingVerificationDomains,
                    "link" => $raaLink
                ]);
                $emailBody = $smarty->fetch('emailRAA.tpl');

                $whmcsService->sendAdminEmail([
                    'subject' => 'Pending Verification Domains', // @TODO fetch from language file
                    'body' => $emailBody
                ]);
            }
        } catch (\Exception $e) {
            (new ErrorHandler())->log([
                'action' => __METHOD__,
                'response' => $e->getMessage(),
                'exception' => $e
            ]);
        }
    }

    private function _handleDomainsSync($domains, $customFieldId, $paymentMethod)
    {
        try {
            $whmcsService = new WHMCSService();

            foreach ($domains as $domain) {
                try {
                    $domain = (array)$domain;
                    $responseSearch = $whmcsService->searchDomains([
                        'domain' => $domain['domain_name']
                    ]);

                    if ($responseSearch['totalresults'] == 0) {
                        // get client id from customer id
                        $clientId = $whmcsService->getClientIdFromCustomerId([
                            "field_id" => $customFieldId,
                            "customer_id" => $domain['customer_id']
                        ]);

                        if (empty($clientId)) {
                            $customer = (new CronService())->listCustomers([
                                'customer_id' => $domain['customer_id']
                            ]);
                            if (empty($customer)) {
                                $customerDetails = (new OrderboxService())->getCustomerDetails([
                                    'customer-id' => $domain['customer_id']
                                ]);
                            } else {
                                $customerDetails = $customer['metadata'];
                            }
                            $clientId = $this->_addUserAndClient($customerDetails);
                        }
                        if (!empty($clientId)) {
                            $clientId = $clientId->clientid;
                            // Handle add->accept->update domain
                            $this->_handleDomainAddAcceptUpdate($clientId, $domain, $paymentMethod);
                        }
                    } else {
                        // EXISTS - ignoring - TBD
                    }
                } catch (\Exception $e) {
                    (new ErrorHandler())->log([
                        'action' => __METHOD__,
                        'request' => $domain,
                        'response' => $e->getMessage(),
                        'exception' => $e
                    ]);
                    continue;
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
