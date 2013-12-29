<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of service_locator
 *
 * @author pasa
 */

include_once APPPATH . 'libraries/travelport/Travelport.php';
include_once APPPATH . 'libraries/corendon/corendon.php';
class ServiceLocator {
    
     public static $instance;
     public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new ServiceLocator();
        }
        return self::$instance;
    }
    
    public function  getAirServiceProvider($apiCode){
        if($apiCode == Fly_Constant::TRAVELPORT_API_CODE){
          $travelPortApi = Travelport::getInstance();
          return $travelPortApi;
        }else if($apiCode == Fly_Constant::CORENDON_API_CODE){
            return new Corendon();
        }
    }
}


?>
