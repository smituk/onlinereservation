<?php

  include_once APPPATH . '/models/fly_search/airline_company.php';
   class AirlineService {
       public function  getAllAirlines(){
           
       }
       
       public  static function  getAirlineByIATACode($iataCode){
        if($iataCode == "XXXX"){
            $combinanationAirline = new AirlineCompany();
            $combinanationAirline->code="XXXX";
            $combinanationAirline->iataCode = "XXXX";
            $combinanationAirline->name = "Kombinasyon";
            return $combinanationAirline;
            
        }     
   
         $queryExecutor = new QueryExecutor();
         $query = "SELECT * FROM airlines where iata='$iataCode'";
         $result = $queryExecutor->query($query, true, 3600);
        
        foreach ($result as $airlineJsonObject) {
            if ($airlineJsonObject->iata != null && $airlineJsonObject->iata != "") {
                $airlineData = new AirlineCompany();
                $airlineData->name = $airlineJsonObject->name;
                $airlineData->iataCode = $airlineJsonObject->iata;
                $airlineData->country = $airlineJsonObject->country;
                $airlineData->active = $airlineJsonObject->active;
                $airlineData->code = $airlineJsonObject->iata;
                return $airlineData;
            }
        }
            return null;
       }
       public function getAirlineByICAOCode(){
           
       }
       
   }
?>
