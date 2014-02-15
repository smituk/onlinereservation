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
         $query = "";
         if(strlen($iataCode) == 2){
             $query = "SELECT * FROM airlines where iata='$iataCode'";
         }else if(strlen ($iataCode) == 3){
               $query = "SELECT * FROM airlines where icao='$iataCode'";
         }
         $result = $queryExecutor->query($query, true, 3600);
        foreach ($result as $airlineJsonObject) {
                $airlineData = new AirlineCompany();
                $airlineData->name = $airlineJsonObject->name;
                $airlineData->iataCode = $airlineJsonObject->iata;
                $airlineData->country = $airlineJsonObject->country;
                $airlineData->active = $airlineJsonObject->active;
                $airlineData->code = $airlineJsonObject->iata;
                if(strlen($iataCode) == 3){
                     $airlineData->iataCode = $airlineJsonObject->icao;
                     $airlineData->code = $airlineJsonObject->icao;
                }
                return $airlineData;
        }
            return null;
       }
       public function getAirlineByICAOCode(){
           
       }
       
   }
?>
