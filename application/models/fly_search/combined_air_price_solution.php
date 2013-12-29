<?php

class CombinedAirPriceSolution {

    public $combinedKey;
    public $apprixomateTotalPriceAmount;
    public $approximateBasePriceAmount;
    public $taxesAmount;
    public $totalPrice;
    public $basePrice;
    public $apprixomateTotalPrice;
    public $approximateBasePrice;
    public $taxes;
    public $airPricingInfoArray; //her bir pasenger type için aynı air price info vardır. [airPriceSolutionRef][passangerType] şeklinde tutulur
    public $apiCode;
    public $legs;
    public $allJourneys;

    public function addAirPricingInfo(AirPricingInfo $airPricingInfo, $airPriceSolutionKey) {
        if (!isset($this->airPricingInfoArray)) {
            $this->airPricingInfoArray = array();
        }
        $this->airPricingInfoArray[$airPriceSolutionKey][$airPricingInfo->passengerType] = $airPricingInfo;
    }

    public function addLeg(AirLeg $leg) {
        if (!isset($this->legs)) {
            $this->legs = array();
        }
        $this->legs[$leg->key] = $leg;
    }
    
    public function getLeg($legKey){
        return $this->legs[$legKey];
    }

}

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
