<?php

class Fly_seach_helper {

    public static function createFlightSummayTableData($carrierFlightTypePriceArray) {
        $airline_company_array = array();
        $no_stops_flight_array = array();
        $stop_flight_array = array();
        $moreone_stop_flight = array();
        foreach ($carrierFlightTypePriceArray as $carrier => $carrierFlightTypePrice) {
            if (isset($carrierFlightTypePrice->nonStopPrice)) {
                for ($k = 0; $k < 10; $k++) {
                    if (!isset($airline_company_array[$k]) || $airline_company_array[$k] === $carrier) {
                        if (!isset($no_stops_flight_array[$k])) {
                            $airline_company_array[$k] = $carrier;
                            $no_stops_flight_array[$k] = $carrierFlightTypePrice->nonStopPrice;
                            break;
                        } else {
                            break;
                        }
                    }
                }
            }

            if (isset($carrierFlightTypePrice->oneStopPrice)) {
                for ($k = 0; $k < 10; $k++) {
                    if (!isset($airline_company_array[$k]) || $airline_company_array[$k] === $carrier) {
                        if (!isset($stop_flight_array[$k])) {
                            $airline_company_array[$k] = $carrier;
                            $stop_flight_array[$k] = $carrierFlightTypePrice->oneStopPrice;
                            break;
                        } else {
                            break;
                        }
                    }
                }
            }

            if (isset($carrierFlightTypePrice->oneMoreStopPrice)) {
                for ($k = 0; $k < 10; $k++) {
                    if (!isset($airline_company_array[$k]) || $airline_company_array[$k] === $carrier) {
                        if (!isset($moreone_stop_flight[$k])) {
                            $airline_company_array[$k] = $carrier;
                            $moreone_stop_flight[$k] = $carrierFlightTypePrice->oneMoreStopPrice;
                            break;
                        } else {
                            break;
                        }
                    }
                }
            }
        }


        $data = array("airline_company_array" => $airline_company_array, "no_stop_flight_array" => $no_stops_flight_array, "stop_flight_array" => $stop_flight_array, "moreone_stop_flight" => $moreone_stop_flight);
        return $data;
    }

    public static function getCarrierFlightTypePrices(LowFareSearchResult $lowFareSearchResult) {

        loadClass(APPPATH . '/models/fly_search/carrier_flight_type_price.php');
        $carrierFlightTypePriceArray = array();

        foreach ($lowFareSearchResult->combinedAirPriceSolutionArray as $combinedAirPriceSolutionItem) {
            //$go_journeys = $combinedAirPriceSolutionItem->departure_journeys;
            //$return_journeys = $combinedAirPriceSolutionItem->return_journeys;
            $stopCount = 0;

            if (count($combinedAirPriceSolutionItem->legs) == 1) {
                foreach ($combinedAirPriceSolutionItem->legs as $legObject) {
                    foreach ($legObject->avaibleJourneyOptions as $journey) {
                        $stopCount = $journey->getStopCount();
                        $carriers = $journey->getCarriers($lowFareSearchResult->airSegmentArray);
                        $carrier = $carriers[0];
                        if (count($carriers) > 1) {
                            $carrier = Fly_Constant::COMBINATION_AIR_COMPANY;
                        }
                        $carrierFlightTypePriceArray = self::setCarrierFlightTypePrice($carrier, $carrierFlightTypePriceArray, $stopCount, $combinedAirPriceSolutionItem);
                    }
                }
            } else {
                $legObjects = array_values($combinedAirPriceSolutionItem->legs);
                $firstLegObject = $legObjects[0];
                for ($i = 1; $i < count($legObjects); $i++) {
                    $currentLegObject = $legObjects[$i];
                    foreach ($firstLegObject->avaibleJourneyOptions as $firstLegJourney) {
                        $stopCount = $firstLegJourney->getStopCount();
                        $firstLegJourneyCarrier = $firstLegJourney->getCarriers($lowFareSearchResult->airSegmentArray);
                        $firstLegJourneyCarrier = count($firstLegJourneyCarrier) == 1 ? $firstLegJourneyCarrier[0] : Fly_Constant::COMBINATION_AIR_COMPANY;
                        foreach ($currentLegObject->avaibleJourneyOptions as $currentLegJourney) {
                            if (self::isJourneysHaveSameAirPriceSolution($firstLegJourney, $currentLegJourney)) {
                                $stopCount = $currentLegJourney->getStopCount() > $stopCount ? $currentLegJourney->getStopCount() : $stopCount;
                                $currentLegJourneyCarrier = $currentLegJourney->getCarriers($lowFareSearchResult->airSegmentArray);
                                $currentLegJourneyCarrier = count($currentLegJourneyCarrier) == 1 ? $currentLegJourneyCarrier[0] : Fly_Constant::COMBINATION_AIR_COMPANY;
                                $carrier = $firstLegJourneyCarrier == $currentLegJourneyCarrier ? $firstLegJourneyCarrier : Fly_Constant::COMBINATION_AIR_COMPANY;
                                $carrierFlightTypePriceArray = self::setCarrierFlightTypePrice($carrier, $carrierFlightTypePriceArray, $stopCount, $combinedAirPriceSolutionItem);
                            }
                        }
                    }
                }
            }
        }
        return $carrierFlightTypePriceArray;
    }

    public static function isJourneysHaveSameAirPriceSolution(Journey $firstJourney, Journey $secondJourney) {
        $intersectJourney = array_intersect($firstJourney->airPriceSolutionRefArray, $secondJourney->airPriceSolutionRefArray);
        return count($intersectJourney) > 0 ? true : false;
    }

    public static function setCarrierFlightTypePrice($carrier, $carrierFlightTypePriceArray, $stopCount, $combinedAirPriceSolutionItem) {
        $carrierFlightTypePriceObject = null;
        if (isset($carrierFlightTypePriceArray[$carrier])) {
            $carrierFlightTypePriceObject = $carrierFlightTypePriceArray[$carrier];
        } else {
            $carrierFlightTypePriceObject = new CarrierFlightTypePrice();
        }

        if ($stopCount == 0) {
            if (!isset($carrierFlightTypePriceObject->nonStopPrice) || $carrierFlightTypePriceObject->nonStopPrice > $combinedAirPriceSolutionItem->apprixomateTotalPriceAmount) {
                $carrierFlightTypePriceObject->nonStopPrice = $combinedAirPriceSolutionItem->apprixomateTotalPriceAmount;
            }
        } else if ($stopCount == 1) {
            if (!isset($carrierFlightTypePriceObject->oneStopPrice) || $carrierFlightTypePriceObject->oneStopPrice > $combinedAirPriceSolutionItem->apprixomateTotalPriceAmount) {
                $carrierFlightTypePriceObject->oneStopPrice = $combinedAirPriceSolutionItem->apprixomateTotalPriceAmount;
            }
        } else if ($stopCount >= 2) {
            if (!isset($carrierFlightTypePriceObject->oneMoreStopPrice) || $carrierFlightTypePriceObject->oneMoreStopPrice > $combinedAirPriceSolutionItem->apprixomateTotalPriceAmount) {
                $carrierFlightTypePriceObject->oneMoreStopPrice = $combinedAirPriceSolutionItem->apprixomateTotalPriceAmount;
            }
        }

        $carrierFlightTypePriceArray[$carrier] = $carrierFlightTypePriceObject;
        return $carrierFlightTypePriceArray;
    }

    public static function addAirSolutionToLegJourneysMappingArray($airSolution, $legToJourneysMappingArray = null) {
        $legIndex = 0;
        if ($legToJourneysMappingArray == null) {
            $legToJourneysMappingArray = array();
        }
        foreach ($airSolution["journeys"] as $journey) {
            if (!isset($legToJourneysMappingArray[$legIndex])) {
                $legToJourneysMappingArray[$legIndex] = array();
            }
            $journeyArray = $legToJourneysMappingArray[$legIndex];
            array_push($journeyArray, $journey->key);
            $legToJourneysMappingArray[$legIndex] = array_unique($journeyArray);
            $legIndex++;
        }
        return $legToJourneysMappingArray;
    }
    
    public static function intersectLegJourneysMappingArray($legJourneysMappingArray,$otherLegJourneysMappingArray){
        for($i = 0; $i<count($legJourneysMappingArray); $i++){
            if(!isset($otherLegJourneysMappingArray[$i])){
                return null;
            }
            $legJourneysMappingArray[$i] = array_intersect($legJourneysMappingArray[$i],$otherLegJourneysMappingArray[$i]);
        }
        return $legJourneysMappingArray;
    }

    public static function removeNotAllowedJourneys($combinedAirPriceSolution, $legToJourneysMappingArray) {
        if(count($legToJourneysMappingArray) < 1){
            return;
        }
        $legIndex = 0;
        foreach ($combinedAirPriceSolution->legs as $legObject) {
            $allowedJourneyKeys = $legToJourneysMappingArray[$legIndex];
            foreach ($legObject->getJourneys() as $journey) {
                if (!(in_array($journey->key, $allowedJourneyKeys))) {
                    $legObject->removeJourney($journey);
                }
            }
            $legIndex++;
        }
        return $combinedAirPriceSolution;
    }

    public static function air_segment_compare($air_segment1, $air_segemnt2) {
        $ad = new DateTime($air_segment1->departure_time);
        $bd = new DateTime($air_segemnt2->departure_time);

        if ($ad == $bd) {
            return 0;
        }
        return $ad < $bd ? -1 : 1;
    }

    public static function journey_travel_time_compare($journey1, $journey2) {
        if ($journey1["travel_time"] == $journey2["travel_time"]) {
            return 0;
        }
        return $journey1["travel_time"] < $journey2["travel_time"] ? -1 : 1;
    }

    public static function create_unique_solution_key() {
        return substr(md5(uniqid(rand(), TRUE)), 0, 15);
    }

    public static function getTotalMinuteFromUnixTimeStamp($seconds) {
        return $seconds / 60 + $seconds % 60;
    }

    public static function getDepartureTimeAsTotalMinute($departure_time) {
        $time = new DateTime($departure_time);
        $hours_string = $time->format("H:i");
        $hours_string_array = explode(":", $hours_string);
        return intval($hours_string_array[0]) * 60 + intval($hours_string_array[1]);
    }

    public static function getDOBFormat($year, $month, $day) {
        if (!isset($year) || !isset($month) || !isset($day)) {
            return null;
        }
        if (intval($day) < 10) {
            $day = "0" . $day;
        }
        if (intval($month) < 10) {
            $month = "0" . $month;
        }
        return new DateTime($year . "-" . $month . "-" . $day);
    }

}

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
