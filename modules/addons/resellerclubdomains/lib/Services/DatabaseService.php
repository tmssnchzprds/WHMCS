<?php


namespace WHMCS\Module\Addon\Resellerclubdomains\Services;

use WHMCS\Database\Capsule;

class DatabaseService
{

   public function __construct()
   {
      if (DEBUG_MODE)
         Capsule::connection()->enableQueryLog();
   }

   public function dropTables($tables)
   {
      try {
         //  Capsule::connection()->transaction(function ($connectionManager) use ($tables) {
         foreach ($tables as $table) {
            Capsule::schema()->dropIfExists($table);
         }
         //            });
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function select($tableName, $selectParams, $whereParams)
   {
      $c = Capsule::table($tableName);
      foreach ($whereParams as $key => $value) {
         $c->where($key, $value);
      }
      return $c->get($selectParams);
   }

   public function truncateTable($table)
   {
      try {
         Capsule::table($table)->truncate();
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public function debugData($last = true)
   {
      try {
         if ($last) {
            return end(Capsule::getQueryLog());
//            echo "<pre>";
//            echo "Last query:" . PHP_EOL;
//            print_r($lastQuery);
//            echo "</pre>";
         } else {
            $queries = Capsule::getQueryLog();
            foreach ($queries as $query) {
               echo "<hr>";
               echo "Execution Time: {$query['time']}ms" . "<br>";
               echo "Query: {$query['query']}" . "<br>";
               echo "Parameters: " . "<br>";
               echo "<pre>";
               print_r($query['bindings']);
               echo "</pre>";
               echo "<hr>";
            }
         }
      } catch (\Exception $e) {
         throw $e;
      }
   }
}
