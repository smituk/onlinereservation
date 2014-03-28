<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of param_service
 *
 * @author pasa
 */
class ParamService {
    
    public static function getActiveApi(){
        $activeApies = array();
        //array_push($activeApies, "CRNDN");
        array_push($activeApies, "TRVPT");
     
        return $activeApies;
    }
    
}
?>