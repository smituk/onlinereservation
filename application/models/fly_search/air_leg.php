<?php
   class AirLeg{
        public $key;
        public $origin;
        public $destination;
        public $avaibleJourneyOptions;
        public $direction; //Gidis;G , Donüs:R;
         
        
       public function getJourneys(){
           return $this->avaibleJourneyOptions;
       }
       
       
        
       public function  addAvaibleJourney(Journey $journey){
           if(!isset($this->avaibleJourneyOptions)){
               $this->avaibleJourneyOptions = array();
                array_push($this->avaibleJourneyOptions,$journey);
                $journey->addAirPriceSolutionKeyRef($journey->airPriceSolutionKeyRef);
                return TRUE;
           }
           $isExsistJourney = FALSE;
           $journeyIndex = 0;
           foreach($this->avaibleJourneyOptions as $avaibleJourney){
               if($avaibleJourney->airSegmentKeys == $journey->airSegmentKeys){
                    $isExsistJourney = TRUE;
                    break;
               }
               $journeyIndex++;
           }
           
           if($isExsistJourney){
               $this->avaibleJourneyOptions[$journeyIndex]->addAirPriceSolutionKeyRef($journey->airPriceSolutionKeyRef);
              
           }else{
               array_push($this->avaibleJourneyOptions,$journey);
                $journey->addAirPriceSolutionKeyRef($journey->airPriceSolutionKeyRef);
           }
           return TRUE;
       }
       
       public function removeJourney(Journey $journey){
           $offset = 0;
           foreach ($this->avaibleJourneyOptions  as $journeyOption){
               if($journeyOption->key == $journey->key){
                  array_splice($this->avaibleJourneyOptions, $offset,1);
                   break;
               }
              $offset++; 
           }
           return TRUE;
       }
       
       public function getJourney($journeyKey){
           foreach($this->avaibleJourneyOptions as $journey){
              if($journey->key == $journeyKey){
                  return $journey;
              }
           }
       }
       
       public function  resetJourneys(){
           unset($this->avaibleJourneyOptions);
       }
   }
?>
