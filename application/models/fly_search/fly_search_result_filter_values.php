<?php

include_once APPPATH . '/models/constants/flight_constants.php';
include_once APPPATH . '/models/fly_search/airline_company.php';
include_once APPPATH . '/helpers/fly_search_helper.php';
include_once APPPATH . '/services/airline_service.php';

class Fly_search_result_filter_values extends CI_Model {

    public $minPriceAmount;
    public $maxPriceAmount;
    public $isNoStopFlightExist = false;
    public $isOneStopFlightExist = false;
    public $isTwoMoreStopFlightExist = false;
    public $noStopFlightMinPrice;
    public $oneStopFlightMinPrice;
    public $twoMoreStopFlightMinPrice;
    public $existAirlineCompanies;
    public $minTotalTravelTime;
    public $maxTotalTravelTime;
    public $existAirlinrCompanyCodes;
    public $goDepartureTimeMinValue;
    public $goDepartureTimeMaxValue;
    public $returnDepartureTimeMinValue;
    public $returnDepartureTimeMaxValue;
    public $goArrivalTimeMinValue;
    public $goArrivalTimeMaxValue;
    public $returnArrivalTimeMinValue;
    public $returnArrivalTimeMaxValue;

    public function setFilterResultValues(LowFareSearchResult $lowFareSearchResult , $carrierFlightTypePriceArray) {
    
            foreach ($lowFareSearchResult->combinedAirPriceSolutionArray as $combinedAirPriceSolution) {

                if (!isset($this->minPriceAmount)) {
                    $this->minPriceAmount = $combinedAirPriceSolution->apprixomateTotalPriceAmount;
                } else if ($combinedAirPriceSolution->apprixomateTotalPriceAmount <= $this->minPriceAmount) {
                    $this->minPriceAmount = $combinedAirPriceSolution->apprixomateTotalPriceAmount;
                }

                if (!isset($this->maxPriceAmount)) {
                    $this->maxPriceAmount = $combinedAirPriceSolution->apprixomateTotalPriceAmount;
                } else if ($combinedAirPriceSolution->apprixomateTotalPriceAmount >= $this->maxPriceAmount) {
                    $this->maxPriceAmount = $combinedAirPriceSolution->apprixomateTotalPriceAmount;
                }

                // Burda no-stop ,  1 aktarma , veya 2 ve daha fazla aktarma olup olamadıgını belitliyoz
                $legtotalCount = count($combinedAirPriceSolution->legs);
                $legIndex = 0;

                foreach ($combinedAirPriceSolution->legs as $legObject) {
                    if ($legtotalCount == 2 && $legIndex == 1) {
                        foreach ($legObject->avaibleJourneyOptions as $journery) {
                            $returnDepartureTimeTotalMinute = Fly_seach_helper::getDepartureTimeAsTotalMinute($journery->getDepartureTime($lowFareSearchResult->airSegmentArray));
                            $returnArrivalTimeTotalMinute = Fly_seach_helper::getDepartureTimeAsTotalMinute($journery->getArrivalTime($lowFareSearchResult->airSegmentArray));
                            if (!isset($this->returnDepartureTimeMinValue) || !isset($this->returnDepartureTimeMaxValue)) {
                                $this->returnDepartureTimeMaxValue = $returnDepartureTimeTotalMinute;
                                $this->returnDepartureTimeMinValue = $returnDepartureTimeTotalMinute;
                            } else if ($returnDepartureTimeTotalMinute < $this->returnDepartureTimeMinValue) {
                                $this->returnDepartureTimeMinValue = $returnDepartureTimeTotalMinute;
                            } else if ($returnDepartureTimeTotalMinute > $this->returnDepartureTimeMaxValue) {
                                $this->returnDepartureTimeMaxValue = $returnDepartureTimeTotalMinute;
                            }

                            if (!isset($this->returnArrivalTimeMinValue) || !isset($this->returnArrivalTimeMaxValue)) {
                                $this->returnArrivalTimeMaxValue = $returnArrivalTimeTotalMinute;
                                $this->returnArrivalTimeMinValue = $returnArrivalTimeTotalMinute;
                            } else if ($returnArrivalTimeTotalMinute < $this->returnArrivalTimeMinValue) {
                                $this->returnArrivalTimeMinValue = $returnArrivalTimeTotalMinute;
                            } else if ($returnArrivalTimeTotalMinute > $this->returnArrivalTimeMaxValue) {
                                $this->returnArrivalTimeMaxValue = $returnArrivalTimeTotalMinute;
                            }
                            $journery->airSegmentItems = null;
                        }
                      
                    } else {
                        foreach ($legObject->avaibleJourneyOptions as $journery) {
                            $goDepartureTimeTotalMinute = Fly_seach_helper::getDepartureTimeAsTotalMinute($journery->getDepartureTime($lowFareSearchResult->airSegmentArray));
                            $goArrivalTimeTotalMinute = Fly_seach_helper::getDepartureTimeAsTotalMinute($journery->getArrivalTime($lowFareSearchResult->airSegmentArray));
                            if (!isset($this->goDepartureTimeMaxValue) || !isset($this->goDepartureTimeMinValue)) {
                                $this->goDepartureTimeMaxValue = $goDepartureTimeTotalMinute;
                                $this->goDepartureTimeMinValue = $goDepartureTimeTotalMinute;
                            } else if ($goDepartureTimeTotalMinute < $this->goDepartureTimeMinValue) {
                                $this->goDepartureTimeMinValue = $goDepartureTimeTotalMinute;
                            } else if ($goDepartureTimeTotalMinute > $this->goDepartureTimeMaxValue) {
                                $this->goDepartureTimeMaxValue = $goDepartureTimeTotalMinute;
                            }

                            if (!isset($this->goArrivalTimeMaxValue) || !isset($this->goArrivalTimeMinValue)) {
                                $this->goArrivalTimeMaxValue = $goArrivalTimeTotalMinute;
                                $this->goArrivalTimeMinValue = $goArrivalTimeTotalMinute;
                            } else if ($goArrivalTimeTotalMinute < $this->goArrivalTimeMinValue) {
                                $this->goArrivalTimeMinValue = $goArrivalTimeTotalMinute;
                            } else if ($goArrivalTimeTotalMinute > $this->goArrivalTimeMaxValue) {
                                $this->goArrivalTimeMaxValue = $goArrivalTimeTotalMinute;
                            }
                             $journery->airSegmentItems = null;
                        }
                    }
                    $legIndex++;
                }
            }
            
            foreach($carrierFlightTypePriceArray as $carrierFlightTypePrice){
                if(isset($carrierFlightTypePrice->nonStopPrice)){
                    $this->isNoStopFlightExist = true;
                   if(!isset($this->noStopFlightMinPrice) || ($carrierFlightTypePrice->nonStopPrice < $this->noStopFlightMinPrice)){
                       $this->noStopFlightMinPrice = $carrierFlightTypePrice->nonStopPrice;
                   } 
                }
                if(isset($carrierFlightTypePrice->oneStopPrice)){
                    $this->isOneStopFlightExist = true;
                     if(!isset($this->oneStopFlightMinPrice) || ($carrierFlightTypePrice->oneStopPrice < $this->oneStopFlightMinPrice)){
                         $this->oneStopFlightMinPrice = $carrierFlightTypePrice->oneStopPrice;
                     }
                }
                if(isset($carrierFlightTypePrice->oneMoreStopPrice)){
                    $this->isTwoMoreStopFlightExist = true;
                    if(!isset($this->twoMoreStopFlightMinPrice) ||($carrierFlightTypePrice->oneMoreStopPrice < $this->twoMoreStopFlightMinPrice)){
                        $this->twoMoreStopFlightMinPrice = $carrierFlightTypePrice->oneMoreStopPrice;
                    }
                }
            }
           
        }
        
    }



?>
