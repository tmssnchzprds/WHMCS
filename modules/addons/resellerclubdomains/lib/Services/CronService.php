<?php

namespace WHMCS\Module\Addon\Resellerclubdomains\Services;

use WHMCS\Database\Capsule;

class CronService
{
   public function __construct()
   {
      if (DEBUG_MODE) {
         Capsule::connection()->enableQueryLog();
      }
   }

   public function createCronTable()
   {
      try {
         if (!Capsule::schema()->hasTable(TBL_CRONS)) {
            Capsule::schema()->create(TBL_CRONS, function ($table) {
               $table->increments('id');
               $table->string('type');
               $table->enum('status', ['added', 'started', 'completed']);
               $table->integer('total_items');
               $table->integer('batch_completed');
               $table->integer('batch_size');
               $table->datetime('created_at')->default(Capsule::raw('CURRENT_TIMESTAMP'))->nullable();
               $table->datetime('updated_at')->default(Capsule::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->nullable();
            });
         }
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function createCustomersTable()
   {
      try {
         if (!Capsule::schema()->hasTable(TBL_CUSTOMERS)) {
            Capsule::schema()->create(TBL_CUSTOMERS, function ($table) {
               $table->increments('id');
               $table->integer('customer_id');
               $table->integer('reseller_id');
               $table->string('username');
               $table->string('status');
               $table->datetime('last_modified_time');
               $table->json('metadata');
               $table->datetime('created_at')->default(Capsule::raw('CURRENT_TIMESTAMP'))->nullable();
               $table->datetime('updated_at')->default(Capsule::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->nullable();
               $table->unique('customer_id');
            });
         }
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function createDomainsTable()
   {
      try {
         if (!Capsule::schema()->hasTable(TBL_DOMAINS)) {
            Capsule::schema()->create(TBL_DOMAINS, function ($table) {
               $table->increments('id');
               $table->integer('order_id');
               $table->string('domain_name');
               $table->string('status');
               $table->enum('raa_verification_pending', ['verified', 'pending', 'suspended'])->nullable();
               $table->string('reseller_id');
               $table->string('customer_id');
               $table->datetime('last_modified_time');
               $table->json('metadata')->default(null);
               $table->json('details')->default(null);
               $table->datetime('created_at')->default(Capsule::raw('CURRENT_TIMESTAMP'))->nullable();
               $table->datetime('updated_at')->default(Capsule::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->nullable();
               $table->unique('order_id');
            });
         }
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function createActionsTable()
   {
      try {
         if (!Capsule::schema()->hasTable(TBL_ACTIONS)) {
            Capsule::schema()->create(TBL_ACTIONS, function ($table) {
               $table->increments('id');
               $table->string('action');
               $table->string('entity');
               $table->datetime('timestamp')->default(Capsule::raw('CURRENT_TIMESTAMP'))->nullable();
            });
         }
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function createConfigsTable()
   {
      try {
         if (!Capsule::schema()->hasTable(TBL_CONFIGS)) {
            Capsule::schema()->create(TBL_CONFIGS, function ($table) {
               $table->increments('id');
               $table->string('type');
               $table->string('key');
               $table->string('value');
               $table->datetime('created_at')->default(Capsule::raw('CURRENT_TIMESTAMP'))->nullable();
               $table->datetime('updated_at')->default(Capsule::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->nullable();
               $table->unique(['type', 'key']);
            });
         }
      } catch (\Exception $e) {
         throw $e;
      }
   }


   public function getCron($params)
   {
      try {
         return Capsule::table(TBL_CRONS)
             ->where('type', $params['type'])
             ->when($params, function ($query, $params) {
                if (is_array($params['status'])) {
                   return $query->whereIn('status', $params['status']);
                }
                return $query->where('status', $params['status']);
             })
             ->when($params, function ($query, $params) {
                if (array_key_exists('updated', $params)) {
                   return $query->where('updated_at', '>=', $params['updated']);
                }
                return $query;
             })
             ->orderBy('id', 'desc')
             ->first();
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function addCron($params)
   {
      try {
         return Capsule::connection()->transaction(
             function ($connectionManager) use ($params) {
                return $connectionManager->table(TBL_CRONS)->insertGetId($params);
             }
         );
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function updateCron($whereParams, $updateParams)
   {
      return Capsule::connection()->transaction(
          function ($connectionManager) use ($whereParams, $updateParams) {
             return $connectionManager->table(TBL_CRONS)
                 ->where('id', $whereParams['where_id'])
                 ->update($updateParams);
          }
      );
   }

   private function _formatCustomersForInsertOnDuplicateKeyUpdate($data)
   {
      try {
         $values = [];
         $columns = [];
         $updateColumns = array(
             0 => 'customer_id',
             1 => 'username',
             2 => 'status',
             3 => 'reseller_id',
             4 => 'last_modified_time',
             5 => 'metadata',
         );

         foreach ($data as $row) {
            $values[] = '(' . implode(', ', array_map(function ($value) {
                   return "'" . addslashes($value) . "'";
                }, $row)) . ')';
         }

         foreach (['username', 'status', 'reseller_id', 'last_modified_time', 'metadata', 'updated_at'] as $column) {
            $columns[] = "$column = VALUES ($column)";
         }

         $query = "INSERT INTO " . TBL_CUSTOMERS . " (" . implode(",", $updateColumns) . ") VALUES";
         $query .= implode(', ', $values);
         $query .= " ON DUPLICATE KEY UPDATE ";
         $query .= implode(', ', $columns);

         return $query;
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function addCustomers($customers)
   {
      try {
         return Capsule::connection()->transaction(
             function ($connectionManager) use ($customers) {
                return $connectionManager->table(TBL_CUSTOMERS)->insert($customers);
             }
         );
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function upsertCustomers($customers)
   {
      try {
         return Capsule::connection()->transaction(
             function ($connectionManager) use ($customers) {
                return $connectionManager->statement($this->_formatCustomersForInsertOnDuplicateKeyUpdate($customers));
             }
         );
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function listCustomers($params)
   {
      try {
         $query = Capsule::table(TBL_CUSTOMERS);
         if (array_key_exists('last_modified_time', $params) && !empty($params['last_modified_time'])) {
            $query->where('last_modified_time', '>=', $params['last_modified_time']);
         }

         if (array_key_exists('order_by_id', $params) && !empty($params['order_by_id'])) {
            $query->orderBy('id', 'asc');
         }

         if (array_key_exists('reseller_id', $params) && !empty($params['reseller_id'])) {
            $query->where('reseller_id', $params['reseller_id']);
         }

         if (array_key_exists('customer_id', $params)) {
            $query->where('customer_id', $params['customer_id']);
         }

         if (array_key_exists('skip', $params)) {
            $query->skip($params['skip']);
         }

         if (array_key_exists('limit', $params)) {
            $query->take($params['limit']);
         }

         return $query->get()->toArray();
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function getCustomerCount($params = [])
   {
      try {
         return Capsule::table(TBL_CUSTOMERS)
             ->when($params, function ($query, $params) {
                if (array_key_exists('status', $params)) {
                   return $query->where('status', $params['status']);
                }
             })
             ->when($params, function ($query, $params) {
                if (array_key_exists('last_modified_time', $params)) {
                   return $query->where('last_modified_time', '>=', $params['last_modified_time']);
                }
             })
             ->when($params, function ($query, $params) {
                if (array_key_exists('reseller_id', $params)) {
                   return $query->where('reseller_id', $params['reseller_id']);
                }
             })
             ->count();
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function getCustomer($params)
   {
      try {
         return Capsule::table(TBL_CUSTOMERS)
             ->when($params, function ($query, $params) {
                if (array_key_exists('status', $params)) {
                   return $query->where('status', $params['status']);
                }
             })
             ->count();
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function listDomains($params)
   {
      try {
         $query = Capsule::table(TBL_DOMAINS);

         if (array_key_exists('last_modified_time', $params) && !empty($params['last_modified_time'])) {
            $query->where('last_modified_time', '>=', $params['last_modified_time']);
         }

         if (array_key_exists('reseller_id', $params) && !empty($params['reseller_id'])) {
            $query->where('reseller_id', $params['reseller_id']);
         }

         if (array_key_exists('order_by_customer_id', $params) && !empty($params['order_by_customer_id'])) {
            $query->orderBy('customer_id', 'asc');
         }

         if (array_key_exists('order_by_order_id', $params) && !empty($params['order_by_order_id'])) {
            $query->orderBy('order_id', 'asc');
         }

         if (array_key_exists('order_by_id', $params) && !empty($params['order_by_id'])) {
            $query->orderBy('id', 'asc');
         }

         if (array_key_exists('skip', $params)) {
            $query->skip($params['skip']);
         }

         if (array_key_exists('limit', $params)) {
            $query->take($params['limit']);
         }

         return $query->get()->toArray();
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function getDomainsCount($params = [])
   {
      try {
         return Capsule::table(TBL_DOMAINS)
             ->when($params, function ($query, $params) {
                if (array_key_exists('status', $params)) {
                   return $query->where('status', $params['status']);
                }
             })
             ->when($params, function ($query, $params) {
                if (array_key_exists('raa_verification_pending_status', $params)) {
                   return $query->where('raa_verification_pending', $params['raa_verification_pending_status']);
                }
             })
             ->when($params, function ($query, $params) {
                if (array_key_exists('reseller_id', $params)) {
                   return $query->where('reseller_id', $params['reseller_id']);
                }
             })
             ->when($params, function ($query, $params) {
                if (array_key_exists('customer_id', $params)) {
                   return $query->where('customer_id', $params['customer_id']);
                }
             })
             ->count();
      } catch (\Exception $e) {
         throw $e;
      }
   }

   private function _formatDomainsForInsertOnDuplicateKeyUpdate($data)
   {
      try {
         $values = [];
         $columns = [];
         $updateColumns = array(
             0 => 'order_id',
             1 => 'domain_name',
             2 => 'status',
             3 => 'reseller_id',
             4 => 'customer_id',
             5 => 'last_modified_time',
             6 => 'metadata',
         );

         foreach ($data as $row) {
            $values[] = '(' . implode(', ', array_map(function ($value) {
                   return "'" . addslashes($value) . "'";
                }, $row)) . ')';
         }

         foreach (['status', 'reseller_id', 'customer_id', 'last_modified_time', 'metadata', 'updated_at'] as $column) {
            $columns[] = "$column = VALUES ($column)";
         }

         $query = "INSERT INTO " . TBL_DOMAINS . " (" . implode(",", $updateColumns) . ") VALUES";
         $query .= implode(', ', $values);
         $query .= " ON DUPLICATE KEY UPDATE ";
         $query .= implode(', ', $columns);

         return $query;
      } catch (\Exception $e) {
         throw $e;
      }
   }


   public function upsertDomains($domains)
   {
      try {
         return Capsule::connection()->transaction(
             function ($connectionManager) use ($domains) {
                return $connectionManager->statement($this->_formatDomainsForInsertOnDuplicateKeyUpdate($domains));
             }
         );
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function addDomains($domains)
   {
      try {
         return Capsule::connection()->transaction(
             function ($connectionManager) use ($domains) {
                return $connectionManager->table(TBL_DOMAINS)->insert($domains);
             }
         );
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function updateDomains($whereParams, $updateParams)
   {
      try {
         return Capsule::connection()->transaction(
             function ($connectionManager) use ($whereParams, $updateParams) {
                return $connectionManager->table(TBL_DOMAINS)
                    ->where('id', $whereParams['where_id'])
                    ->update($updateParams);
             }
         );
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function addActionLog($params)
   {
      try {
         $mandatoryParams = ["action", "entity"];
         array_walk($mandatoryParams, function ($param) use ($params) {
            if (false === array_key_exists($param, $params)) {
               throw new \Exception("Missing Parameter $param");
            }
         });
         return Capsule::connection()->transaction(
             function ($connectionManager) use ($params) {
                return $connectionManager->table(TBL_ACTIONS)->insertGetId($params);
             }
         );
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function getActionLog($params)
   {
      try {
         $mandatoryParams = ["action", "entity"];
         array_walk($mandatoryParams, function ($param) use ($params) {
            if (false === array_key_exists($param, $params)) {
               throw new \Exception("Missing Parameter $param");
            }
         });

         return Capsule::table(TBL_ACTIONS)
             ->where("action", $params['action'])
             ->where("entity", $params['entity'])
             ->where("timestamp", ">", $params['timestamp'])
             ->orderBy('id', 'desc')
             ->first();
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function getConfigs($params)
   {
      try {
         $query = Capsule::table(TBL_CONFIGS);
         if (array_key_exists("select_params", $params) &&
             !empty($params["select_params"])) {
            $query->select($params["select_params"]);
         }

         foreach (["type", "key", "value"] as $param) {
            if (array_key_exists($param, $params) && !empty($params[$param])) {
               $query->where($param, $params[$param]);
            }
         }

         return $query->get()->toArray();

      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function addConfig($params)
   {
      try {
         $mandatoryParams = ["type", "key", "value"];
         array_walk($mandatoryParams, function ($param) use ($params) {
            if (false === array_key_exists($param, $params)) {
               throw new \Exception("Missing Parameter $param");
            }
         });
         return Capsule::connection()->transaction(
             function ($connectionManager) use ($params) {
                return $connectionManager->table(TBL_CONFIGS)->insertGetId($params);
             }
         );
      } catch (\Exception $e) {
         throw $e;
      }
   }

//   public function updateConfig($updateParams, $whereParams)
//   {
//      try {
//         $mandatoryParams = ["type", "key", "value"];
//
//         array_walk($mandatoryParams, function ($param) use ($whereParams, $updateParams) {
//            if (false === array_key_exists($param, array_merge($whereParams, $updateParams))) {
//               throw new \Exception("Missing Parameter $param");
//            }
//         });
//         return Capsule::connection()->transaction(
//             function ($connectionManager) use ($whereParams, $updateParams) {
//                return $connectionManager->table(TBL_CONFIGS)->where($whereParams)->update($updateParams);
//             }
//         );
//      } catch (\Exception $e) {
//         throw $e;
//      }
//   }

   public function deleteConfigs($whereParams)
   {
      try {
         return Capsule::table(TBL_CONFIGS)
             ->where($whereParams)
             ->delete();
      } catch (\Exception $e) {
         throw $e;
      }
   }

}
