<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


class Airport_controller extends CI_Controller{
   
    public function  getAirPortsWithPrefix(){
        include_once APPPATH.'services/airport_service.php';
        $prefix = $this->input->post('airportPrefix');
        
    
        $airPortService = AirportService::getInstance();
        $airportArray = $airPortService->getAirportsWithPrefix($prefix);
        echo json_encode($airportArray);
        
        
        
    }
    
}
?>
