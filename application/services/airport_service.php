<?php 

include_once APPPATH . '/models/fly_search/airport.php';
  class AirportService{
      
          public static $instance;
      
          
          public static  function getInstance(){
              if(AirportService::$instance == null){
                  AirportService::$instance=new AirportService();
              }
              return AirportService::$instance;
          }
          
        public function getAirportDetail($airportCode){
          $ci=& get_instance();
          $query  = $ci->db->query("SELECT * FROM airports Where iata ='$airportCode'");
     
      
        foreach ($query->result() as $airportRow) {
            $airportObject = new Airport();
            $airportObject->iataCode = $airportCode;
            $airportObject->city = $airportRow->city;
            $airportObject->country = $airportRow->country;
            $airportObject->name = $airportRow->name;
            $airportObject->utcOffset = $airportRow->timezone;
            $airportObject->dstRegion = $airportRow->dst;
            return $airportObject;
                     
        }
    }
        
      
  }

?>