<?php

include_once APPPATH . '/models/fly_search/air_pricing_solution.php';
include_once APPPATH . '/models/fly_search/journey.php';
include_once APPPATH . '/models/fly_search/air_pricing_info.php';
include_once APPPATH . '/models/fly_search/air_segment.php';
include_once APPPATH . '/models/fly_search/flight_detail.php';
include_once APPPATH . '/models/fly_search/book_info.php';
include_once APPPATH . '/models/fly_search/fly_search_criteria.php';
include_once APPPATH . '/models/fly_search/combined_air_price_solution.php';
include_once APPPATH . '/services/airport_service.php';
include_once 'corendon_account.php';
include_once 'corendon_common.php';

class CorendonGetAvaibleFareTransformer implements XmlTransformer {

    public $name = "CorendonGetAvaibleFare";
    private $searchCriteria;
    private $oneAdultFare;
    private $oneChildFare;
    private $oneInfFare;
    private $oneAdultTax;
    private $oneChildTax;
    private $oneInfTax;
    private $currency;
    private $airportArray;

    public function __construct($searchCriteria) {
        $this->searchCriteria = $searchCriteria;
        $this->airportArray = array();
    }

    public function convertObject($responseXml, $isConverted = FALSE) {
        if (!$isConverted) {
            return $responseXml;
        }

        $responseXML = new SimpleXMLElement($responseXml);
        $responseXML->registerXPathNamespace("ns", CorendonAccount::getDefaultNameSpace());
        $airPriceSolutionArray = array();

        foreach ($responseXML->xpath("//ns:RSGetAvailableFares") as $getAvaibleFareXML) {
            $this->oneAdultFare = $getAvaibleFareXML->FAREADT;
            $this->oneChildFare = $getAvaibleFareXML->FARECHD;
            $this->oneInfFare = $getAvaibleFareXML->FAREINF;
            $this->oneAdultTax = $getAvaibleFareXML->TAXADT;
            $this->oneChildTax = $getAvaibleFareXML->TAXCHD;
            $this->oneInfTax = $getAvaibleFareXML->TAXINF;
            $this->currency = $currency = $getAvaibleFareXML->CURRENCY;

            $totalBaseAmount = $this->searchCriteria->yetiskinnumber * (int) $this->oneAdultFare + $this->searchCriteria->cocuknumber * (int) $this->oneChildFare + $this->searchCriteria->bebeknumber * (int) $this->oneInfFare;
            $totalTaxAmount = $this->searchCriteria->yetiskinnumber * (int) $this->oneAdultTax + $this->searchCriteria->cocuknumber * (int) $this->oneChildTax + $this->searchCriteria->bebeknumber * (int) $this->oneInfTax;
            $airPriceSolutionObject = new AirPricingSolution();
            $airPriceSolutionObject->key = substr(md5(microtime()), 0, 10);
            $airPriceSolutionObject->fareIndentifier = $getAvaibleFareXML->FAREIDENTIFIER;
            $airPriceSolutionObject->total_price = $currency . ($totalBaseAmount + $totalTaxAmount);
            $airPriceSolutionObject->base_price = $currency . $totalBaseAmount;
            $airPriceSolutionObject->apprixomate_total_price = $currency . ($totalBaseAmount + $totalTaxAmount);
            $airPriceSolutionObject->approximate_base_price = $currency . $totalBaseAmount;
            $airPriceSolutionObject->equivalent_base_price = $airPriceSolutionObject->approximate_base_price;
            $airPriceSolutionObject->taxes = $currency . $totalTaxAmount;
            $airPriceSolutionObject->apprixomate_total_price_amount = $totalBaseAmount + $totalTaxAmount;
            $airPriceSolutionObject->taxes_amount = $totalTaxAmount;
            $airPriceSolutionObject->air_pricing_info = $this->airPriceInfoXMLToObject();
            $airPriceSolutionObject->journeys = $this->convertJourneyObject($getAvaibleFareXML, $airPriceSolutionObject);
            array_push($airPriceSolutionArray, $airPriceSolutionObject);
        }
            
        return $this->combineAirPriceSolutions($airPriceSolutionArray);
    }

    public function prepareXml() {

        $getAvaibleFareRequestXML = new SimpleXMLElement("<myxml></myxml>");
        $getAvaibleFareRequestXML = $getAvaibleFareRequestXML->addChild("GetAvailableFares", NULL, CorendonAccount::getDefaultNameSpace());
        $requestXML = $getAvaibleFareRequestXML->addChild("request");
        $requestXML->addChild("NUMBEROFADULTS", $this->searchCriteria->yetiskinnumber); //Adult Number
        $requestXML->addChild("NUMBEROFCHILDREN", $this->searchCriteria->cocuknumber); // Cocuk Number
        $requestXML->addChild("NUMBEROFINFANTS", $this->searchCriteria->bebeknumber); // Bebek Number
        $carriesCodesXML = $requestXML->addChild("CARRIERCODES");
        $carrierCodeStrcXML = $carriesCodesXML->addChild("CARRIERCODE_STRC");
        $carrierCodeStrcXML->addChild("CODE");
        $requestXML->addChild("LANGUAGECODE", "EN");
        $firstSearchAirLeg = $this->searchCriteria->searchAirLegs[0];
        $multipleDepartureCodesOutXML = $requestXML->addChild("MULTIDEPARTURECODESOUT");
        $airPortCodeSrcXML = $multipleDepartureCodesOutXML->addChild("AIRPORTCODE_STRC");
        $airPortCodeSrcXML->addChild("CODE", $firstSearchAirLeg->originSearchLocation->airport);
        $multipleArrivalCodeOutXML = $requestXML->addChild("MULTIARRIVALCODESOUT");
        $airPortArrivalCodeXML = $multipleArrivalCodeOutXML->addChild("AIRPORTCODE_STRC");
        $airPortArrivalCodeXML->addChild("CODE", $firstSearchAirLeg->destinationSearchLocation->airport);
        $departureTimeDateTime = new DateTime($firstSearchAirLeg->searchDepartureTime);
        $requestXML->addChild("DEPARTUREDATEOUT", $departureTimeDateTime->format("Ymd")); // departure date 
        $requestXML->addChild("DEPARTURETIMEOUT");

        $requestXML->addChild("CHECKAHEADOUT", 0);
        $requestXML->addChild("CHECKBACKOUT", 0);
        if ($this->searchCriteria->flydirection == 2) { // Tek yonler için 
            $secondSearchAirLeg = $this->searchCriteria->searchAirLegs[1];
            $multiDepartureCodesInXML = $requestXML->addChild("MULTIDEPARTURECODESIN");
            $airPortCodeSrcDepartureInXML = $multiDepartureCodesInXML->addChild("AIRPORTCODE_STRC");
            $airPortCodeSrcDepartureInXML->addChild("CODE", $secondSearchAirLeg->originSearchLocation->airport);
            $multipleArrivalCodeInXML = $requestXML->addChild("MULTIARRIVALCODESIN");
            $airPortCodeSrcArrivalInXML = $multipleArrivalCodeInXML->addChild("AIRPORTCODE_STRC");
            $airPortCodeSrcArrivalInXML->addChild("CODE", $secondSearchAirLeg->destinationSearchLocation->airport);
            $departureTimeDateTime = new DateTime($secondSearchAirLeg->searchDepartureTime);
            $requestXML->addChild("DEPARTUREDATEIN", $departureTimeDateTime->format("Ymd"));
            $requestXML->addChild("DEPARTURETIMEIN");
            $requestXML->addChild("CHECKAHEADIN", 0);
            $requestXML->addChild("CHECKBACKIN", 0);
        } else {
            $multiDepartureCodesInXML = $requestXML->addChild("MULTIDEPARTURECODESIN");
            $airPortCodeSrcDepartureInXML = $multiDepartureCodesInXML->addChild("AIRPORTCODE_STRC");
            $airPortCodeSrcDepartureInXML->addChild("CODE");
            $multipleArrivalCodeInXML = $requestXML->addChild("MULTIARRIVALCODESIN");
            $airPortCodeSrcArrivalInXML = $multipleArrivalCodeInXML->addChild("AIRPORTCODE_STRC");
            $airPortCodeSrcArrivalInXML->addChild("CODE");
            $requestXML->addChild("DEPARTUREDATEIN");
            $requestXML->addChild("DEPARTURETIMEIN");
            $requestXML->addChild("CHECKAHEADIN", 0);
            $requestXML->addChild("CHECKBACKIN", 0);
        }

        $requestXML->addChild("FAREIDENTIFIER");
        CorendonCommon::buildAgentXML($requestXML);
        $excludeCarrierCodesXML = $requestXML->addChild("EXCLUDECARRIERCODE");
        $exCarrierCodeStrcXML = $excludeCarrierCodesXML->addChild("CARRIERCODE_STRC");
        $exCarrierCodeStrcXML->addChild("CODE");
        $requestXML->addChild("TIMEWINDOWOUT", 0);
        $requestXML->addChild("TIMEWINDOWIN", 0);
        $requestXML->addChild("ARRIVALDATEOUT");
        $requestXML->addChild("ARRIVALTIMEOUT");
        $requestXML->addChild("ARRIVALDATEIN");
        $requestXML->addChild("ARRIVALTIMEIN");
        $requestXML->addChild("NUMBEROFFARES", 0);


        $getAvaibleFareRequestXMLMessage = $getAvaibleFareRequestXML->asXML();
        $message = <<<EOM
        <s:Envelope xmlns:s = "http://schemas.xmlsoap.org/soap/envelope/">
        <s:Body xmlns:xsi = "http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd = "http://www.w3.org/2001/XMLSchema">
         $getAvaibleFareRequestXMLMessage
        </s:Body>
        </s:Envelope>
EOM;
        return $message;
    }

    private function airPriceInfoXMLToObject() {
        $airPriceInfoArray = array();
        if ($this->searchCriteria->yetiskinnumber > 0) {
            $airPriceInfoObject = new AirPricingInfo();
            $airPriceInfoObject->key = substr(md5(microtime()), 0, 10);
            $airPriceInfoObject->approximate_base_price = $this->currency . $this->oneAdultFare;
            $airPriceInfoObject->approximate_base_price_amount = (int) $this->oneAdultFare;
            $airPriceInfoObject->approximate_total_price = $this->currency . ((int) $this->oneAdultFare + (int) $this->oneAdultTax);
            $airPriceInfoObject->approximate_total_price_amout = (int) $this->oneAdultFare + (int) $this->oneAdultTax;
            $airPriceInfoObject->taxes = $this->currency . $this->oneAdultTax;
            $airPriceInfoObject->taxes_amount = (int) $this->oneAdultTax;

            $airPriceInfoObject->passenger_type = "ADT";
            $airPriceInfoObject->passenger_count = $this->searchCriteria->yetiskinnumber;
            $airPriceInfoObject->passenger_type_desc = "Yetişkin";
            array_push($airPriceInfoArray, $airPriceInfoObject);
        }
        if ($this->searchCriteria->cocuknumber > 0) {
            $airPriceInfoObject = new AirPricingInfo();
            $airPriceInfoObject->key = substr(md5(microtime()), 0, 10);
            $airPriceInfoObject->approximate_base_price = $this->currency . $this->oneChildFare;
            $airPriceInfoObject->approximate_base_price_amount = (int) $this->oneChildFare;
            $airPriceInfoObject->approximate_total_price = $this->currency . ((int) $this->oneChildFare + (int) $this->oneChildTax);
            $airPriceInfoObject->approximate_total_price_amout = (int) $this->oneChildFare + (int) $this->oneChildTax;
            $airPriceInfoObject->taxes = $this->currency . $this->oneChildTax;
            $airPriceInfoObject->taxes_amount = (int) $this->oneChildTax;

            $airPriceInfoObject->passenger_type = "CNN";
            $airPriceInfoObject->passenger_count = $this->searchCriteria->cocuknumber;
            $airPriceInfoObject->passenger_type_desc = "Çocuk";
            array_push($airPriceInfoArray, $airPriceInfoObject);
        }

        if ($this->searchCriteria->bebeknumber > 0) {
            $airPriceInfoObject = new AirPricingInfo();
            $airPriceInfoObject->key = substr(md5(microtime()), 0, 10);
            $airPriceInfoObject->approximate_base_price = $this->currency . $this->oneInfFare;
            $airPriceInfoObject->approximate_base_price_amount = (int) $this->oneInfFare;
            $airPriceInfoObject->approximate_total_price = $this->currency . ((int) $this->oneInfFare + (int) $this->oneInfTax);
            $airPriceInfoObject->approximate_total_price_amout = (int) $this->oneInfFare + (int) $this->oneInfTax;
            $airPriceInfoObject->taxes = $this->currency . $this->oneInfTax;
            $airPriceInfoObject->taxes_amount = (int) $this->oneInfTax;

            $airPriceInfoObject->passenger_type = "INF";
            $airPriceInfoObject->passenger_count = $this->searchCriteria->bebeknumber;
            $airPriceInfoObject->passenger_type_desc = "Bebek";
            array_push($airPriceInfoArray, $airPriceInfoObject);
        }
        return $airPriceInfoArray;
    }

    private function convertJourneyObject($rSGetAvailableFaresXML, $airPriceSolution) {
        $journeyArrays = array();
        foreach ($rSGetAvailableFaresXML->FLIGHTSOUT->FLIGHT_STRC as $flightXML) {
            $journeyObject = $this->createJourneyObject($flightXML);
            $journeyObject->type = Fly_Constant::DEPARTURE_JOURNEY_TYPE;
            $journeyObject->air_price_solution_key_ref = $airPriceSolution->key;
            array_push($journeyArrays, $journeyObject);
        }
         if(!isset($rSGetAvailableFaresXML->FLIGHTSIN)){
             return $journeyArrays;
         }
        foreach ($rSGetAvailableFaresXML->FLIGHTSIN->FLIGHT_STRC as $flightXML) {
            $journeyObject = $this->createJourneyObject($flightXML);
            $journeyObject->type = Fly_Constant::RETURN_JOURNEY_TYPE;
            $journeyObject->air_price_solution_key_ref = $airPriceSolution->key;
            array_push($journeyArrays, $journeyObject);
        }
        return $journeyArrays;
    }

    private function createJourneyObject($flightXML) {
        $bookingClass = $flightXML->CLASSOFFLIGHT;
        $journeyObject = new Journey();
        $journeyObject->key = Fly_seach_helper::create_unique_solution_key();
        $journeyObject->identifier = (string)$flightXML->FLIGHTIDENTIFIER;
        $journeyObject->type = Fly_Constant::DEPARTURE_JOURNEY_TYPE;
        $journeyObject->air_segment_keys = array();
        $journeyObject->air_segment_items = array();
        foreach ($flightXML->SEGMENTS->FSEGMENT_STRC as $flightSegmentXML) {
            $airSegmentObject = new AirSegment();
            $airSegmentObject->key = substr(md5(microtime()), 0, 10);
            $airSegmentObject->carrier = (string) $flightSegmentXML->CARRIERCODE;
            

            $airCompanyObject = AirlineService::getAirlineByIATACode($airSegmentObject->carrier);
            if (isset($airCompanyObject)) {
                $airSegmentObject->carrierName = $airCompanyObject->name;
            } else {
                $airSegmentObject->carrierName = $airSegmentObject->carrier;
            }


            $airSegmentObject->flight_number = (string) $flightSegmentXML->FLIGHTNUMBER;
            $airSegmentObject->origin = (string) $flightSegmentXML->DEPARTURECODE;
            $originAirportObject  = null;
           if(!isset($this->airportArray[ $airSegmentObject->origin])){
                $airportService = AirportService::getInstance();
                $originAirportObject = $airportService->getAirportDetail($airSegmentObject->origin);  
                $this->airportArray[$airSegmentObject->origin] = $originAirportObject;
            }else{
                 $originAirportObject = $this->airportArray[ $airSegmentObject->origin];
            }
             $airSegmentObject->destination = (string) $flightSegmentXML->ARRIVALCODE;
            $destinationAirportObject = null;
            if(!isset($this->airportArray[$airSegmentObject->destination])){
                $airportService = AirportService::getInstance();
                $destinationAirportObject = $airportService->getAirportDetail( $airSegmentObject->destination);
                $this->airportArray[$airSegmentObject->destination] = $destinationAirportObject;
            }else{
                $destinationAirportObject = $this->airportArray[$airSegmentObject->destination];
            }
        
            $airSegmentObject->booking_code = (string) $bookingClass;
            $airSegmentObject->avaible_booking_count = (string) $flightSegmentXML->AVAILSEATS;
            $airSegmentObject->booking_counts = (string) $bookingClass . $airSegmentObject->avaible_booking_count;
            $airSegmentObject->booking_cabin_class = 'Economy';

            $departureDate = (string) $flightSegmentXML->DEPARTUREDATE;
            $departureTime = (string) $flightSegmentXML->DEPARTURETIME;
            $arrivalDate = (string) $flightSegmentXML->ARRIVALDATE;
            $arrivalTime = (string) $flightSegmentXML->ARRIVALTIME;

            $departureDateTime = DateTime::createFromFormat("Ymd Hi", $departureDate . ' ' . $departureTime);
            $depatureDateTimezoneName = timezone_name_from_abbr("", intval($originAirportObject->utcOffset)*3600,null);
           // 
            $airSegmentObject->departure_time = $departureDateTime->format(DateTime::ISO8601);
            $airSegmentObject->departure_date = $departureDateTime->format("d.m.Y");
            $airSegmentObject->departure_hours = $departureDateTime->format('H:i');
            $departureDateTime->setTimezone(new DateTimeZone($depatureDateTimezoneName));
            $arrivalDateTime = DateTime::createFromFormat("Ymd Hi", $arrivalDate .' '.$arrivalTime);
            $arrivalDateTimezoneName  = timezone_name_from_abbr("", intval($destinationAirportObject->utcOffset)*3600,null);
            //
            $airSegmentObject->arrival_time = $arrivalDateTime->format(DateTime::ISO8601);
            $airSegmentObject->arrival_date = $arrivalDateTime->format("d.m.Y");
            $airSegmentObject->arrival_hours = $arrivalDateTime->format("H:i");
           $arrivalDateTime->setTimezone(new DateTimeZone($arrivalDateTimezoneName));
                $diffTimestamp =  $arrivalDateTime->getTimestamp()-$departureDateTime->getTimestamp();
            $airSegmentObject->flight_time = $diffTimestamp/60;
            array_push($journeyObject->air_segment_keys, $airSegmentObject->key);
            array_push($journeyObject->air_segment_items, $airSegmentObject);
            //@TODO tarih ile ilgili kısmı yapmayı unutma.
        }
        return $journeyObject;
    }

    private function combineAirPriceSolutions($airPriceSolutions) {
        $combinedAirPriceSolutionsArray = array();
        $airPriceSolutionMapArray = array();

        foreach ($airPriceSolutions as $airPriceSolution) {
            if (isset($airPriceSolutionMapArray[$airPriceSolution->apprixomate_total_price])) {
                $samePricedArray = $airPriceSolutionMapArray[$airPriceSolution->apprixomate_total_price];
                array_push($samePricedArray, $airPriceSolution);
                $airPriceSolutionMapArray[$airPriceSolution->apprixomate_total_price] = $samePricedArray;
            } else {
                $samePricedArray = array();
                array_push($samePricedArray, $airPriceSolution);
                $airPriceSolutionMapArray[$airPriceSolution->apprixomate_total_price] = $samePricedArray;
            }
        }

        foreach ($airPriceSolutionMapArray as $samePricedSolutions) {
            $combinedAirPriceSolutionObject = new CombinedAirPriceSolution();
            $combinedAirPriceSolutionObject->apprixomate_total_price = $samePricedSolutions[0]->apprixomate_total_price;
            $combinedAirPriceSolutionObject->apprixomate_total_price_amount = $samePricedSolutions[0]->apprixomate_total_price_amount;
            $combinedAirPriceSolutionObject->approximate_base_price = $samePricedSolutions[0]->approximate_base_price;
            $combinedAirPriceSolutionObject->taxes_amount = $samePricedSolutions[0]->taxes_amount;
            $combinedAirPriceSolutionObject->taxes = $samePricedSolutions[0]->taxes;
            $combinedAirPriceSolutionObject->air_price_info_items = $samePricedSolutions[0]->air_pricing_info;
            $combinedAirPriceSolutionObject->combined_key = Fly_seach_helper::create_unique_solution_key();
            $combinedAirPriceSolutionObject->api_code = Fly_Constant::CORENDON_API_CODE;
            $departureJourneys = array();
            $returnJourneys = array();
            $departureJourneysUniqueKeys = array();
            $returnJourneysUniqueKeys = array();
            $goJourneyRelatedIdKey = array();
            $returnJourneyRelatedIdKey = array();

            foreach ($samePricedSolutions as $airPriceSolution) {
                $departureJourney = null;
                $returnJourney = null;
                $isExistDepartureJourney = FALSE;
                $isExistReturnJourney = FALSE;
                $uniqueDepartureJourneyKey = "";
                $uniqueReturnJourneyKey = "";
                $departureJourneyAirCompany = Fly_Constant::COMBINATION_AIR_COMPANY;
                $returnJourneyAirCompany = Fly_Constant::COMBINATION_AIR_COMPANY;

                foreach ($airPriceSolution->journeys as $journey) {
                    if ($journey->type == Fly_Constant::DEPARTURE_JOURNEY_TYPE) {
                        $departureJourney = $journey;
                    } else if ($journey->type == Fly_Constant::RETURN_JOURNEY_TYPE) {
                        $returnJourney = $journey;
                    }
                }

                if ($departureJourney != null) {
                    
                    $uniqueDepartureJourneyKey = md5($departureJourney->identifier);
                    if (isset($departureJourneysUniqueKeys[$uniqueDepartureJourneyKey])) {
                        $isExistDepartureJourney = TRUE;
                    } else {
                        $isExistDepartureJourney = FALSE;
                    }
                    $departureJourneyAirCompany = $this->getJourneyAirCompany($departureJourney);
                }

                if ($returnJourney != null) {
                    $uniqueReturnJourneyKey = md5($returnJourney->identifier);
                    if (isset($returnJourneysUniqueKeys[$uniqueReturnJourneyKey])) {
                        $isExistReturnJourney = TRUE;
                    } else {
                        $isExistReturnJourney = FALSE;
                    }
                    $returnJourneyAirCompany = $this->getJourneyAirCompany($returnJourney);
                }
                
                
                if (!$isExistDepartureJourney && !$isExistReturnJourney) {
                    if ($returnJourney != null) {
                        $returnJourney->related_journey_id = $uniqueReturnJourneyKey;
                        $departureJourney->related_journey_id = $uniqueReturnJourneyKey;
                        $goJourneyRelatedIdKey[$uniqueDepartureJourneyKey] = $uniqueReturnJourneyKey;
                        $returnJourneyRelatedIdKey[$uniqueReturnJourneyKey] = $uniqueReturnJourneyKey;
                    }
                } else if (!$isExistDepartureJourney && $isExistReturnJourney) {
                    if (isset($returnJourneyRelatedIdKey[$uniqueReturnJourneyKey])) {
                        $departureJourney->related_journey_id = $returnJourneyRelatedIdKey[$uniqueReturnJourneyKey];
                    }
                } else if ($isExistDepartureJourney && !$isExistReturnJourney) {
                    if ($returnJourney != null) {
                        if (isset($goJourneyRelatedIdKey[$uniqueReturnJourneyKey])) {
                            $returnJourney->related_journey_id = $goJourneyRelatedIdKey[$uniqueDepartureJourneyKey];
                        }
                    }
                }

                if (!$isExistDepartureJourney && $departureJourney != null) {
                    $departureJourney->key = $uniqueReturnJourneyKey;
                    if ($returnJourney != null) {
                        if ($departureJourneyAirCompany == $returnJourneyAirCompany) {
                            $departureJourney->air_company = $departureJourneyAirCompany;
                        } else {
                            $departureJourney->air_company = Fly_Constant::COMBINATION_AIR_COMPANY;
                        }
                    } else {
                        $departureJourney->air_company = $departureJourneyAirCompany;
                    }
                    array_push($departureJourneys, $departureJourney);
                    $departureJourneysUniqueKeys[$uniqueDepartureJourneyKey] = $uniqueDepartureJourneyKey;
                }

                if (!$isExistReturnJourney && $returnJourney != null) {

                    $returnJourney->key = $uniqueReturnJourneyKey;
                    if ($departureJourneyAirCompany == $returnJourneyAirCompany) {
                        $returnJourney->air_company = $returnJourneyAirCompany;
                    } else {
                        $returnJourney->air_company = Fly_Constant::COMBINATION_AIR_COMPANY;
                    }

                    array_push($returnJourneys, $returnJourney);
                    $returnJourneysUniqueKeys[$uniqueReturnJourneyKey] = $uniqueReturnJourneyKey;  
                }
            }
           
            $combinedAirPriceSolutionObject->departure_journeys = $departureJourneys;
            $combinedAirPriceSolutionObject->return_journeys = $returnJourneys;
            $combinedAirPriceSolutionsArray[$combinedAirPriceSolutionObject->combined_key] = $combinedAirPriceSolutionObject;
          
            }
            
            return $combinedAirPriceSolutionsArray;
        }
        
        
        private function getJourneyAirCompany($journey){
              $previousCarrier = null;
              foreach ($journey->air_segment_items as $airSegment){
                  if($previousCarrier == null){
                      $previousCarrier  = $airSegment->carrier;
                  }else if($previousCarrier != $airSegment->carrier){
                      return Fly_Constant::COMBINATION_AIR_COMPANY;
                  }
              }
              return $previousCarrier;
        }
    
}
?>

