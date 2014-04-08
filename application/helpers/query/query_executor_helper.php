<?php
     class QueryExecutor {
         
         public  function query($query,$isCacheAvaible = true ,$queryTimeout = 15){
            if($isCacheAvaible == true){
              include_once APPPATH.'/cache/cache_impl.php';
              $cacheServer = new CacheImpl();
              $result  = unserialize($cacheServer->get($query));
              if($result == FALSE){
                   $result = $this->executeQuery($query);
                   $cacheServer->set($query, serialize($result) ,$queryTimeout);
                   return $result;
              }
              return $result;
            }
            return $this->executeQuery($query);
         }
         
         private function executeQuery($query){
             $ci=& get_instance();
             $queryObject  = $ci->db->query($query);
             return $queryObject->result();
         }
         
         private function beginTransaciton(){
             
         }
          
         private function completeTransaction(){
             
         }
         
         public function insertQuery($tableName , $values){
              $ci=& get_instance();
              $ci->db->insert($tableName, $values); 
              return $ci->db->insert_id();
         }
         
         public function updateQuery($tableName , $values , $conditions){
             
         }
     }
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

?>