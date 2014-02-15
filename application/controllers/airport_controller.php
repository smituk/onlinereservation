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
    
    
    public function getAiportSummaryWithId(){
        
        include_once APPPATH.'services/airport_service.php';
        $id = $this->input->post("airportid");
        //echo "yunus".$id;
        
        if($id == FALSE || $id == "" || !is_numeric($id)){
            return;
        }
        header('Content-type: application/json; charset=utf-8');
        $airPortService = AirportService::getInstance();
        $airportSummaryObject = $airPortService->getAirportSummaryFromId($id);
      
        echo json_encode($airportSummaryObject);
        return;
    }
    
}
?>
