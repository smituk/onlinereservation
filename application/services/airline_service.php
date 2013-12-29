<?php
  include_once  APPPATH.'/resource/airline_data.php';
   class AirlineService {
       public function  getAllAirlines(){
           
       }
       
       public  static function  getAirlineByIATACode($iataCode){
            $airlineDataService = AirlineData::getInstance();
            $allAirlineData = $airlineDataService->getAirlineDataArray();
            if(isset($allAirlineData[$iataCode])){
            return $allAirlineData[$iataCode];
            }
            return null;
       }
       public function getAirlineByICAOCode(){
           
       }
       
   }
?>
