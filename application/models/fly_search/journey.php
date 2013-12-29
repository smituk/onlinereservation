<?php

class Journey {
    
    public $key;
    public $airSegmentKeys;
    public $airSegmentItems;
    public $travelTime;
    public $totalTravelTime;
    public $type; //  bu alan yoculugun gişemi yoksa donuşemi ait oldugunu gosterir. Gidiş için D / dönüş için R kullan
    public $air_company; // burda eger journey 
    public $related_journey_id; // bu id , dönüs tipinde olan journeylerin  hangi gidis journeye ait olduklarını bilmek için kullanılır. 
    public $airPriceSolutionRefArray;// bu journeyin sahip airPriceSolutionları tutar.
    public $airPriceSolutionKeyRef;
    public $route; // aramadaki hangi route ait oldugu bilgisi tasır.
    public $identifier;//Corendon da bir flight/journey için belirtilen idenfifier;
    public $bookingInfoArray;// ilgili Journeyde  hangi segment için  hangi cabin class ve booking code ki bilgiyi verir
     
    public  function addAirPriceSolutionKeyRef($airPriceSolutionKey){
        if(!isset($this->airPriceSolutionRefArray) || count($this->airPriceSolutionRefArray) < 1){
            $this->airPriceSolutionRefArray = array();
        }
         array_push($this->airPriceSolutionRefArray, $airPriceSolutionKey);       
    }
    
    public function getCarriers($airSegmentArray){
        $carrierArray = array();
        foreach($this->airSegmentKeys as $airSegmentKey){
            $airSegmentObject = $airSegmentArray[$airSegmentKey];
            if(!in_array($airSegmentObject->carrier, $carrierArray)){
                array_push($carrierArray, $airSegmentObject->carrier);
            }
        }
        return $carrierArray;
    }
    
    public function  addAirSegment(AirSegment $airSegment , $isOnlyKey = FALSE){
        if(!isset($this->airSegmentKeys)){
            $this->airSegmentKeys = array();
        }
        array_push($this->airSegmentKeys , $airSegment->key);
        
        if(!$isOnlyKey){
            if(!isset($this->airSegmentItems)){
                $this->airSegmentItems = array();
            }
            array_push($this->airSegmentItems, $airSegment);
        }
        return TRUE;
    }

    public function  getAirSegments($airSegmentArray = NULL){
       if($airSegmentArray == NULL){
           return $this->airSegmentItems;
       }
       if(!isset($this->airSegmentItems)){
        $airSegmentObjects  = array();
        foreach ($this->airSegmentKeys as $airSegmentKey){
            array_push($airSegmentObjects, $airSegmentArray[$airSegmentKey]);
        }
        $this->airSegmentItems = $airSegmentObjects;
        }
        return $this->airSegmentItems;
    }
    
    public function  clearAirSegments(){
        if(isset($this->airSegmentItems)){
            unset($this->airSegmentItems);
            $this->airSegmentItems = NULL;
        }
        
        if(isset($this->airSegmentKeys)){
            unset($this->airSegmentKeys);
            $this->airSegmentKeys = NULL;
        }
    }


    public function getSegmentBookingInfo($airSegmentKey , $passangerType = NULL){
        if($passangerType == NULL){
            $passangerType = "ADT";
        }
        return $this->bookingInfoArray[$passangerType][$airSegmentKey];
    }
    
    public function getStopCount(){
        return count($this->airSegmentKeys)-1;
    }
    
   
    public function getDepartureTime($airSegmentArray){
        $airSegmentItems = $this->getAirSegments($airSegmentArray);
        return $airSegmentItems[0]->departureTime;
    }
    
    public function getArrivalTime($airSegmentArray){
        $airSegmentItems = $this->getAirSegments($airSegmentArray);
        return $airSegmentItems[count($airSegmentItems)-1]->arrivalTime;
    }
    
    
    
}

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
