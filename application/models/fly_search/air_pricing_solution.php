<?php

class AirPricingSolution  {

    public $key;
    public $total_price;
    public $base_price;
    public $apprixomate_total_price;
    public $approximate_base_price;
    public $legs; //<air:legRef  key="xx" icin)
    public $journeys;
    public $airPricingInfoArray;
    public $taxes; // tum vergi toplamına karsılık geliyor herhalde :
    public $equivalent_base_price;
    public $apprixomate_total_price_amount;
    public $taxes_amount;
    public $fareIndentifier;
   
    
   public function getPassengerAirPriceInfo($passengerType){
       if(isset($this->airPricingInfoArray)){
           foreach($this->airPricingInfoArray as $airPricingInfo){
               if($airPricingInfo->passenger_type == $passengerType){
                   return $airPricingInfo;
               }
           }
       }
       return null;
   }
   
   public function addLeg(AirLeg $legObject){
       if(!isset($this->legs)){
           $this->legs = array();
       }
       $this->legs[$legObject->key] = $legObject;
   }
   
   public function getLeg($legKey){
       if(isset($this->legs[$legKey])){
           return $this->legs[$legKey];
       }
       return NULL;
   }
   
   public function getLegByIndex($indexCount){
       if(!isset($this->legs)){
           return NULL;
       }
       
       $legObjects = array_values($this->legs);
       if(isset($legObjects[$indexCount])){
           return $legObjects[$indexCount];
       }
       return NULL;
   }
}

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
