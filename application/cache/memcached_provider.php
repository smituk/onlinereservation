<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of memcached_provider
 *
 * @author pasa
 */

include_once 'cache_interface.php';
class MemcachedProvider implements CacheInterface{
     
    private  static $memcacheObject;
    
    private static function  getInstance(){
        if(self::$memcacheObject == NULL){
            $memcacheObject  = new Memcache();
            $applicationInstance = &get_instance();
            $host = $applicationInstance->config->item("memcache_server_adress");
            $port = $applicationInstance->config->item("memcache_server_port");
            $memcacheObject->addserver($host, $port);
            self::$memcacheObject = $memcacheObject;
        }
        return self::$memcacheObject;
    }


    public function get($key) {
          $memcacheObject = self::getInstance();
          return $memcacheObject->get($key);
    }

    
    public function set($key, $value, $expiretime = 0) {
         $memcacheObject = self::getInstance();
         return  $memcacheObject->set($key,$value,MEMCACHE_COMPRESSED,$expiretime);
    }

    public function getServerStatu() {
        $memcacheObject = self::getInstance();
        return $memcacheObject->getServerStatus(); 
    }

}

?>