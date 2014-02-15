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
         
          $queryExecutor = new QueryExecutor();
          $query = "SELECT * FROM airports Where iata ='$airportCode'";
          $result = $queryExecutor->query($query, true, 3600);
          
        foreach ($result as $airportRow) {
            $airportObject = new Airport();
            $airportObject->iataCode = $airportCode;
            $airportObject->city = $airportRow->city;
            $airportObject->country = $airportRow->country;
            $airportObject->name = $airportRow->name;
            $airportObject->utcOffset = $airportRow->timezone;
            $airportObject->dstRegion = $airportRow->dst;
            $airportObject->cityCode = $airportRow->citycode;
            return $airportObject;
                     
        }
    }
    
    public function getAirportSummaryFromId($id){
        
        if(!is_numeric($id)){
            return false;
        }
        $id  = intval($id);
        $queryExecutor  = new QueryExecutor();
        $query = "SELECT * FROM airport_summary WHERE id = $id";
        //echo $query;
        $result = $queryExecutor->query($query, true, 24*3600);
          foreach ($result as $airportRow) {
              
            $airportObject = new Airport();
            $airportObject->iataCode = $airportRow->airportcode;
            //$airportObject->city = $airportRow->city;
            //$airportObject->country = $airportRow->country;
            //$airportObject->name = $airportRow->name;
            //$airportObject->utcOffset = $airportRow->timezone;
            //$airportObject->dstRegion = $airportRow->dst;
            $airportObject->cityCode = $airportRow->citycode;
            //$airportObject->countryCode = $airportRow->countrycode;
            $airportObject->summary = $airportRow->summary;
            $airportObject->id  = $airportRow->id;
            $airportObject->isAll = $airportRow->isAll;
            $airportObject->associatedAirports = $airportRow->associatedairports;
            return $airportObject;
    
        }
    }   
    public function  getAirportsWithPrefix($prefix){
          
          $queryExecutor = new QueryExecutor();
          $query = "SELECT * FROM airport_summary WHERE upper(name) like upper('$prefix%') OR upper(citycode) like upper('$prefix%') OR upper(airportcode) like upper('$prefix%') OR upper(summary) like upper('$prefix%') order by citycode , frequency desc LIMIT 0,10";
          //$query = "SELECT * FROM airports WHERE iata IS NOT NULL  AND airporttype IN (1,2,3) AND countrycode IS NOT NULL AND ( NAME LIKE '$prefix%' OR city LIKE '$prefix%' OR iata LIKE '$prefix%' )  LIMIT 0,10 ";
          $result = $queryExecutor->query($query, true, 24*3600);
          $airportArray = array();
           foreach ($result as $airportRow) {
            $airportObject = new Airport();
            $airportObject->iataCode = $airportRow->airportcode;
            //$airportObject->city = $airportRow->city;
            //$airportObject->country = $airportRow->country;
            //$airportObject->name = $airportRow->name;
            //$airportObject->utcOffset = $airportRow->timezone;
            //$airportObject->dstRegion = $airportRow->dst;
            $airportObject->cityCode = $airportRow->citycode;
            //$airportObject->countryCode = $airportRow->countrycode;
            $airportObject->summary = $airportRow->summary;
            $airportObject->id  = $airportRow->id;
            array_push($airportArray, $airportObject);
                     
        }
        return $airportArray;
    }
        
      
  }

?>