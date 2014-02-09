<?php

class Journey {
    
    public $key;
    public $airSegmentKeys;
    public $airSegmentItems;
    public $travelTime;
    public $totalTravelTime;
    public $type; //  bu alan yoculugun gişemi yoksa donuşemi ait oldugunu gosterir. Gidiş için D / dönüş için R kullan
    public $airPriceSolutionRefArray;// bu journeyin sahip airPriceSolutionları tutar.
    public $airPriceSolutionKeyRef;
    public $route; // aramadaki hangi route ait oldugu bilgisi tasır.
    public $identifier;//Corendon da bir flight/journey için belirtilen idenfifier;
    public $bookingInfoArray;// ilgili Journeyde  hangi segment için  hangi cabin class ve booking code ki bilgiyi verir
    public $viaAirport; // corendendoki vai infoya karsılık gelir. 
    public  function addAirPriceSolutionKeyRef($airPriceSolutionKey){
        if(!isset($this->airPriceSolutionRefArray) || count($this->airPriceSolutionRefArray) < 1){
            $this->airPriceSolutionRefArray = array();
        }
         array_push($this->airPriceSolutionRefArray, $airPriceSolutionKey);
         //$this->airPriceSolutionRefArray = array_unique($this->airPriceSolutionRefArray);
    }
    
    public function removeAirPriceSolutionKeyRef($airPriceSolutionKey){
        if(isset($this->airPriceSolutionRefArray)){
            $offset = 0;
            foreach($this->airPriceSolutionRefArray as $airPriceSolutionRefKey){
                if($airPriceSolutionRefKey == $airPriceSolutionKey){
                    array_slice($this->airPriceSolutionRefArray, $offset, 1);
                    break;
                }
            }
        }
    }
    
    public function getAirSolutionKeyRefArray(){
        return $this->airPriceSolutionRefArray;
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
    
    public function setAirSegments($airSegments , $isOnlyKey = FALSE){
        $this->clearAirSegments();
        foreach($airSegments as $airSegment){
            $this->addAirSegment($airSegment , $isOnlyKey);
        }
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
    
    public function clearBookingInfoArray(){
        $this->bookingInfoArray = null;
    }
    
    public function addBookingInfo(BookingInfo $bookingInfoObject, $passengerType){
        if(!isset($this->bookingInfoArray)){
            $this->bookingInfoArray = array();
        }
        $this->bookingInfoArray[$passengerType][$bookingInfoObject->airSegmentRef] = $bookingInfoObject;
        return true;
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
