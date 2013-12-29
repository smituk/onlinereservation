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
}

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
