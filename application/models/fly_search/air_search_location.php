<?php
   class SearchLocation {
       public $airport;
       public $city;
       public $cityOrAirport;
       public $associatedAirports;
       public $isAll = false;
       
       
       public function buildSearchLocation(Airport $airport){
            if($airport->isAll == 1){
                $this->isAll = true;
                $this->city = $airport->cityCode;
                $this->associatedAirports = $airport->associatedAirports;
                return;
            }
            $this->airport = $airport->iataCode;
            $this->city = $airport->cityCode;  
       }
   }
?>
