<?php


include_once 'cache_interface.php';
class CacheImpl  implements CacheInterface {  
    private $provider;
    
    public function __construct($provider = null) {
        if(isset($provider)){
            $this->provider = $provider;
        }else{
            include_once 'memcached_provider.php';
            $this->provider = new MemcachedProvider();
        }
    }

    public  function get($key) {
       return  $this->provider->get($key);
    }

    public function getServerStatu() {
      
    }

    public function set($key, $value, $expiretime = 0) {
        return $this->provider->set($key, $value, $expiretime);
    }

//put your code here
}


?>