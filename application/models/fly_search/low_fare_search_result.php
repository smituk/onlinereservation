<?php
 include_once APPPATH . '/models/common/error_dto.php';
   class LowFareSearchResult extends ErrorDTO{
       public $apiCode;
       public $combinedAirPriceSolutionArray;
       public $airPriceSolutionArray;
       public $airSegmentArray;
       public $fareInfoArray;
       public $airportArray;
       public $airlineArray;
       
   }
?>