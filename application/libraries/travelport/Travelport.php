<?php

include_once APPPATH . '/models/fly_search/air_pricing_solution.php';
include_once APPPATH . '/models/fly_search/journey.php';
include_once APPPATH . '/models/fly_search/air_pricing_info.php';
include_once APPPATH . '/models/fly_search/air_segment.php';
include_once APPPATH . '/models/fly_search/flight_detail.php';
include_once APPPATH . '/models/fly_search/book_info.php';
include_once APPPATH . '/models/fly_search/fly_search_criteria.php';
include_once APPPATH . '/models/constants/flight_constants.php';
include_once APPPATH . '/services/airline_service.php';
include_once APPPATH . '/interface/air_service_provider.php';
include_once APPPATH . '/models/fly_booking/response_book_verify_data.php';
include_once APPPATH . '/models/fly_booking/fly_apply_book_result.php';
include_once 'travelport_low_fare_search_transformer.php';
include_once 'travelport_apply_book_transformer.php';
include_once 'travelport_booking_price_verify_transformer.php';
include_once 'travelport_universal_record_cancel_transformer.php';

include_once 'travelport_account.php';
include_once 'travelport_common.php';
include_once 'travelport_error.php';

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Travelport
 *
 * @author pasa
 */
class Travelport implements AirServiceProvider {

    public static $instance;

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new Travelport();
        }
        return self::$instance;
    }

    private function sendMessageTravelportApi($message, $endpoint = null) {
        $CREDENTIALS = TravelportAccount::$user . ":" . TravelportAccount::$password;
        $auth = base64_encode("$CREDENTIALS");
        if ($endpoint == null) {
            $endpoint = TravelportAccount::$airServiceEndPoint;
        }
        $soap_do = curl_init($endpoint);
        $header = array(
            "Content-Type: text/xml;charset=UTF-8", "Accept: gzip,deflate",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: \"\"",
            "Authorization: Basic $auth",
            "Content-length: " . strlen($message),
        );
        curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 300);
        curl_setopt($soap_do, CURLOPT_TIMEOUT, 300);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($soap_do, CURLOPT_POST, true);
        curl_setopt($soap_do, CURLOPT_SSLVERSION, 3);
        curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($soap_do, CURLOPT_POSTFIELDS, $message);
        curl_setopt($soap_do, CURLOPT_HTTPHEADER, $header);
        return $result_xml = curl_exec($soap_do);
    }

    public function getFareRule() {
        
    }

    public function parseLowFareSearchTravelPortApi($responseXmlData = null, Fly_search_criteria $search_criteria = null) {
        if (!isset($responseXmlData)) {
            return null;
        }

        $xml = new SimpleXMLElement($responseXmlData);

        $xml->formatOutput = true;
        $xml->registerXPathNamespace('air', TravelportAccount::$air_scheme_version);

        $errorDto = TravelportCommon::getErrorStatu($xml, null);
        loadClass(APPPATH . "/models/fly_search/low_fare_search_result.php");
       
        $lowFareSearchResult = new LowFareSearchResult();
        $lowFareSearchResult->errorCode = $errorDto->code;
        if ($errorDto->code != TravelPortErrorCodes::SUCCESS) {
            return $lowFareSearchResult;
        }
        loadClass(APPPATH . '/services/airport_service.php');
        loadClass(APPPATH . '/services/airline_service.php');
        $airPortService = AirportService::getInstance();
        $air_price_solution_arrays = array();
        $air_segment_arrays = array();
        $airPortArray = array();
        $airlineArray = array();
        foreach ($xml->xpath('//air:AirSegment') as $air_segment_item) {
            $air_segment_object = TravelportCommon::airSegmentXMLToObject($air_segment_item);
            $air_segment_arrays[$air_segment_object->key] = $air_segment_object;
            if(!isset($airPortArray[$air_segment_object->origin])){
                $airPortArray[$air_segment_object->origin] = $airPortService->getAirportDetail($air_segment_object->origin);
            }
            if(!isset($airPortArray[$air_segment_object->destination])){
                $airPortArray[$air_segment_object->destination] = $airPortService->getAirportDetail($air_segment_object->destination);
            }
            
            if(!isset($airlineArray[$air_segment_object->carrier])){
               $airlineArray[$air_segment_object->carrier] = AirlineService::getAirlineByIATACode($air_segment_object->carrier);
            }
            
        }
        $airlineArray[Fly_Constant::COMBINATION_AIR_COMPANY] = AirlineService::getAirlineByIATACode(Fly_Constant::COMBINATION_AIR_COMPANY);
        
        $flight_detail_arrays = array();
        foreach ($xml->xpath('//air:FlightDetails') as $flight_detail_item) {
            array_push($flight_detail_arrays, $this->flightDetailXMLToObject($flight_detail_item));
        }

        $fareInfoArray = array();
        foreach ($xml->xpath("//air:FareInfoList") as $fareInfoInfoList) {
            $children = $fareInfoInfoList->children("air", TRUE);
            foreach ($children->FareInfo as $fareInfoXML) {
                // print_r($fareInfoXML);
                $fareInfoObject = TravelportCommon::fareInfoXMLToXML($fareInfoXML);
                $fareInfoArray[$fareInfoObject->key] = $fareInfoObject;
            }
        }

        $legArray = array();
        loadClass(APPPATH . "/models/fly_search/air_leg.php");
        $legCount = 0;
        foreach ($xml->xpath("//air:RouteList/air:Route/air:Leg") as $legXML) {

            $legXMLAttributes = $legXML->attributes();
            $legObject = new AirLeg();
            $legObject->key = (string) $legXMLAttributes["Key"][0];
            $legObject->origin = (string) $legXMLAttributes["Origin"][0];
            $legObject->destination = (string) $legXMLAttributes["Destination"][0];
            $legObject->direction = "G"; //Gidis;
            if ($search_criteria->flydirection == "2" && $legCount == 1) {
                $legObject->direction = "R"; //Dönüs;
            }
            $legArray[$legObject->key] = $legObject;
            $legCount++;
        }

        foreach ($xml->xpath('//air:AirPricePoint') as $air_price_solution_item) {
            $air_price_solution_object = new AirPricingSolution();
            $air_price_solution_item_attributes = $air_price_solution_item->attributes();
            $air_price_solution_object->key = (string) $air_price_solution_item_attributes["Key"][0];
            $air_price_solution_object->total_price = (string) $air_price_solution_item_attributes["TotalPrice"][0];
            $air_price_solution_object->base_price = (string) $air_price_solution_item_attributes["BasePrice"][0];
            $air_price_solution_object->apprixomate_total_price = (string) $air_price_solution_item_attributes["ApproximateTotalPrice"][0];
            $air_price_solution_object->approximate_base_price = (string) $air_price_solution_item_attributes["ApproximateBasePrice"][0];
            $air_price_solution_object->equivalent_base_price = (string) $air_price_solution_item_attributes["EquivalentBasePrice"][0];
            $air_price_solution_object->taxes = (string) $air_price_solution_item_attributes["Taxes"][0];
            $this->setAirPricingInfoAndJourney($air_price_solution_item, $air_price_solution_object, $legArray, $fareInfoArray, $air_segment_arrays);
            preg_match('/([^a-zA-Z]+)/', $air_price_solution_object->apprixomate_total_price, $total_price_match);
            $air_price_solution_object->apprixomate_total_price_amount = $total_price_match[0];
            preg_match('/([^a-zA-Z]+)/', $air_price_solution_object->taxes, $tax_price_match);
            $air_price_solution_object->taxes_amount = $tax_price_match[0];
            array_push($air_price_solution_arrays, $air_price_solution_object);
        }

        $lowFareSearchResult->airPriceSolutionArray = $air_price_solution_arrays;
        $lowFareSearchResult->airSegmentArray = $air_segment_arrays;
        $lowFareSearchResult->fareInfoArray = $fareInfoArray;
        $lowFareSearchResult->airportArray = $airPortArray;
        $lowFareSearchResult->airlineArray = $airlineArray;
        unset($xml);
        return $lowFareSearchResult;
    }

    public function airPriceInfoXMLToObject($air_price_solution_item = null, $fareInfoArray = null, $airSegmentArray = null, $legArray = null) {

        $air_price_info_object_list = array();
        foreach ($air_price_solution_item->children('air', TRUE) as $air_price_solution_item_node) {
            if ($air_price_solution_item_node->getName() == "AirPricingInfo") {
                $air_price_info_object = TravelportCommon::airPriceInfoXMLToObject($air_price_solution_item_node, $fareInfoArray, $airSegmentArray, $legArray);

                array_push($air_price_info_object_list, $air_price_info_object);
            }
        }
        return $air_price_info_object_list;
    }

    private function setAirPricingInfoAndJourney(SimpleXMLElement $airPricePointXML, AirPricingSolution $airPricingSolution, $legArray, $fareInfoArray, $airSegmentArray) {

        $airPricingSolution->legs = array();
        $airPricingSolution->airPricingInfoArray = array();
        $airPricePointXMLChildren = $airPricePointXML->children("air", TRUE);
        $isFirstAirPricinInfo = TRUE;
        foreach ($airPricePointXMLChildren->AirPricingInfo as $airPricingInfoXML) {
            $airPricingInfoObject = TravelportCommon::airPriceInfoXMLToObject($airPricingInfoXML, $fareInfoArray);
            $airPricingInfoXMLChildren = $airPricingInfoXML->children("air", TRUE);

            foreach ($airPricingInfoXMLChildren->FlightOptionsList->FlightOption as $flightOptionXML) {

                $flightOptionsAttributesXML = $flightOptionXML->attributes();
                $legRef = (string) $flightOptionsAttributesXML["LegRef"][0];
                $legObject = NULL;
                if ($isFirstAirPricinInfo) {
                    $legObject = clone $legArray[$legRef];
                    $legObject->avaibleJourneyOptions = array();
                } else {
                    $legObject = $airPricingSolution->legs[$legRef];
                }
                $optionIndex = 0;
                foreach ($flightOptionXML->Option as $optionXml) {

                    $optionXmlAttributes = $optionXml->attributes();
                    $journeyObject = NULL;
                    if (!isset($legObject->avaibleJourneyOptions) || !isset($legObject->avaibleJourneyOptions[$optionIndex])) {
                        $journeyObject = new Journey();
                        $journeyObject->key = (string) $optionXmlAttributes["Key"][0];
                        $journeyObject->airPriceSolutionKeyRef = $airPricingSolution->key;
                        $journeyObject->travelTime = (string) $optionXmlAttributes["TravelTime"][0];
                        $journeyObject->totalTravelTime = $this->convert_journey_travel_time($journeyObject->travelTime);
                        $journeyObject->airSegmentKeys = array();
                        $journeyObject->bookingInfoArray = array();
                    } else {
                        $journeyObject = $legObject->avaibleJourneyOptions[$optionIndex];
                    }

                    unset($optionXmlAttributes);
                    $optionXmlChildren = $optionXml->children("air", TRUE);
                    foreach ($optionXmlChildren->BookingInfo as $bookingInfoXml) {

                        $bookingXmlAttributes = $bookingInfoXml->attributes();
                        $bookingInfoObject = new BookingInfo();
                        $bookingInfoObject->fareInfoRef = (string) $bookingXmlAttributes["FareInfoRef"][0];
                        $bookingInfoObject->airSegmentRef = (string) (string) $bookingXmlAttributes["SegmentRef"][0];
                        $bookingInfoObject->cabinClass = (string) $bookingXmlAttributes["CabinClass"][0];
                        $bookingInfoObject->bookingCode = (string) $bookingXmlAttributes["BookingCode"][0];
                        $journeyObject->bookingInfoArray[$airPricingInfoObject->passengerType][$bookingInfoObject->airSegmentRef] = $bookingInfoObject;
                        if ($isFirstAirPricinInfo == TRUE) {
                            array_push($journeyObject->airSegmentKeys, $bookingInfoObject->airSegmentRef);
                        }
                        unset($bookingXmlAttributes);
                    }
                    $optionIndex++;
                    if ($isFirstAirPricinInfo) {
                        array_push($legObject->avaibleJourneyOptions, $journeyObject);
                        //$legObject->avaibleJourneyOptions[$journeyObject->key] = $journeyObject;
                    }
                }

                if ($isFirstAirPricinInfo) {
                    $airPricingSolution->addLeg($legObject);
                }
            }
            unset($airPricingInfoXMLChildren);
            unset($airPricingInfoXML);


            array_push($airPricingSolution->airPricingInfoArray, $airPricingInfoObject);
            $isFirstAirPricinInfo = FALSE;
        }
    }

   

    private function flightDetailXMLToObject($flight_detail_item) {
        $flight_detail_item_attributes = $flight_detail_item->attributes();
        $flight_detail_object = new FlightDetail();
        $flight_detail_object->key = (string) $flight_detail_item_attributes["Key"][0];
        $flight_detail_object->origin = (string) $flight_detail_item_attributes["Origin"][0];
        $flight_detail_object->destination = (string) $flight_detail_item_attributes["Destination"][0];
        $flight_detail_object->departure_time = (string) $flight_detail_item_attributes["DepartureTime"][0];
        $flight_detail_object->arrival_time = (string) $flight_detail_item_attributes["ArrivalTime"][0];
        $flight_detail_object->flight_time = (string) $flight_detail_item_attributes["FlightTime"][0];
        $flight_detail_object->origin_terminal = (string) $flight_detail_item_attributes["OriginTerminal"][0];
        $flight_detail_object->destination_terminal = (string) $flight_detail_item_attributes["DestinationTerminal"][0];
        return $flight_detail_object;
    }

    public function combineAirPriceSolutions2(LowFareSearchResult $lowSearchResult) {
        if ($lowSearchResult->errorCode != TravelPortErrorCodes::SUCCESS) {
            return $lowSearchResult;
        }
        $samePricedSolutionArray = array(); // aynı fiyata sahip solutionları tutar;
        $airPriceSolutionArray = $lowSearchResult->airPriceSolutionArray;
        foreach ($airPriceSolutionArray as $airPriceSolution) {
            if (isset($samePricedSolutionArray[$airPriceSolution->apprixomate_total_price])) {
                array_push($samePricedSolutionArray[$airPriceSolution->apprixomate_total_price], $airPriceSolution);
            } else {
                $samePricedSolutionArray[$airPriceSolution->apprixomate_total_price] = array();
                array_push($samePricedSolutionArray[$airPriceSolution->apprixomate_total_price], $airPriceSolution);
            }
        }

        loadClass(APPPATH . '/models/fly_search/combined_air_price_solution.php');
        $lowSearchResult->combinedAirPriceSolutionArray = array();
        foreach ($samePricedSolutionArray as $samedPricedSolutions) {
            $combinedAirPriceSolutionObject = new CombinedAirPriceSolution();
            $isFirstSamePricedSolution = TRUE;
            foreach ($samedPricedSolutions as $airPriceSolution) {
                if ($isFirstSamePricedSolution) {
                    $combinedAirPriceSolutionObject->apprixomateTotalPrice = $airPriceSolution->apprixomate_total_price;
                    $combinedAirPriceSolutionObject->apprixomateTotalPriceAmount = $airPriceSolution->apprixomate_total_price_amount;
                    $combinedAirPriceSolutionObject->approximateBasePrice = $airPriceSolution->approximate_base_price;
                    //$combinedAirPriceSolutionObject->approximateBasePriceAmount = $airPriceSolution->approximate_base_price_amount;
                    $combinedAirPriceSolutionObject->taxesAmount = $airPriceSolution->taxes_amount;
                    $combinedAirPriceSolutionObject->taxes = $airPriceSolution->taxes;
                    $combinedAirPriceSolutionObject->combinedKey = Fly_seach_helper::create_unique_solution_key();
                    $combinedAirPriceSolutionObject->apiCode = Fly_Constant::TRAVELPORT_API_CODE;
                    foreach ($airPriceSolution->legs as $legObject) {
                        $combinedAirPricingSolutionLegObject = clone $legObject;
                        unset($combinedAirPricingSolutionLegObject->avaibleJourneyOptions);
                        $combinedAirPriceSolutionObject->addLeg($legObject);
                      }
                }

                foreach ($airPriceSolution->airPricingInfoArray as $airPricingInfo) {
                    $combinedAirPriceSolutionObject->addAirPricingInfo($airPricingInfo, $airPriceSolution->key);
                }
                foreach ($airPriceSolution->legs as $legObject) {
                    $combinedAirPricingSolutionLegObject = $combinedAirPriceSolutionObject->legs[$legObject->key];
                    foreach ($legObject->avaibleJourneyOptions as $journeyObject) {
                        $combinedAirPricingSolutionLegObject->addAvaibleJourney($journeyObject);
                    }
                }
                $isFirstSamePricedSolution = FALSE;
            }
            $lowSearchResult->combinedAirPriceSolutionArray[$combinedAirPriceSolutionObject->combinedKey]=$combinedAirPriceSolutionObject;
        }
        //unset($lowSearchResult->airPriceSolutionArray);
        return $lowSearchResult;
    }

   
    // P1DT1H15M0.000S or PT1H15M0.000S seklinde gelmekte
    public function convert_journey_travel_time($travel_time) {
        $isDayFlag = strpos($travel_time, "D");
        $totalMinute = 0;
        if ($isDayFlag) {
            $day_count = substr($travel_time, $isDayFlag - 1, 1);
            $totalMinute = $totalMinute + intval($day_count) * 24 * 60;
        }
        $isTFlag = strpos($travel_time, "T");
        if ($isTFlag) {
            $hourFlag = strpos($travel_time, "H");
            $hour_count = substr($travel_time, $isTFlag + 1, $isTFlag + 1 - $hourFlag);
            $totalMinute = $totalMinute + intval($hour_count) * 60;
            $minuteFlag = strpos($travel_time, "M");
            $minute_count = substr($travel_time, $hourFlag + 1, $hourFlag + 1 - $minuteFlag);
            $totalMinute = $totalMinute + intval($minute_count);
        }
        return $totalMinute;
    }

   

    
   

    public static function air_segment_compare($air_segment1, $air_segemnt2) {
        $ad = new DateTime($air_segment1->departure_time);
        $bd = new DateTime($air_segemnt2->departure_time);

        if ($ad == $bd) {
            return 0;
        }
        return $ad < $bd ? -1 : 1;
    }

    public static function journey_travel_time_compare2($journey1, $journey2) {
        if ($journey1->total_travel_time == $journey2->total_travel_time) {
            return 0;
        }
        return $journey1->total_travel_time < $journey2->total_travel_time ? -1 : 1;
    }

    private function sendRequestWebService($transformer, $endpoint = null) {
        $requestXml = $transformer->prepareXML();
       
        file_put_contents($transformer->name . "Request.xml", $requestXml);
        $responseXml = $this->sendMessageTravelportApi($requestXml, $endpoint);
        //$responseXml = file_get_contents($transformer->name."Response.xml");
        file_put_contents($transformer->name . "Response.xml", $responseXml);
        return $transformer->convertObject($responseXml);
    }

    public function bookPriceVerify($combinedAirPriceSolution, $selectedJourneyArray,$airSegmentArray, $searchCriteria) {
        $travelportBookingPriceVerifyTransformer = new TravelportBookingPriceVerifyTransformer();
        $travelportBookingPriceVerifyTransformer->combinedAirPriceSolution = $combinedAirPriceSolution;
        $travelportBookingPriceVerifyTransformer->selectedJourneys = $selectedJourneyArray;
        $travelportBookingPriceVerifyTransformer->searchCriteria = $searchCriteria;
        $travelportBookingPriceVerifyTransformer->airSegmentArray = $airSegmentArray;
        $responseBookPriceVerifyData = new ResponseBookPriceVerifyData();
        $responseBookPriceVerifyData->combinedAirPriceSolution = $combinedAirPriceSolution;
        $responseBookPriceVerifyData->verifiedAirPriceSolution = $this->sendRequestWebService($travelportBookingPriceVerifyTransformer);
        $responseBookPriceVerifyData->searchCriteria = $searchCriteria;
        $legKeyArray = array();
        foreach($combinedAirPriceSolution->legs as $leg){
            array_push($legKeyArray, $leg->key);
        }
        $responseBookPriceVerifyData->legKeyArray = $legKeyArray;
       // file_put_contents("xxx.json", json_encode($responseBookPriceVerifyData));
        return $responseBookPriceVerifyData;
        
    }

    public function searchFlight($searchCriteria) {
        $travelportLowFareSearchTransformer = new TravelportLowFareSearchTransformer($searchCriteria);
        return $this->sendRequestWebService($travelportLowFareSearchTransformer);
        //return file_get_contents("low_fare_searchResponse.xml");
    }

    public function convertXMLToCombinedAirPriceSolutions($xmlData, $search_criteria) {
        $lowFareSearchResult = $this->parseLowFareSearchTravelPortApi($xmlData, $search_criteria);
        $lowFareSearchResult->apiCode = TravelportCommon::APICODE;
        if ($lowFareSearchResult->errorCode != TravelPortErrorCodes::SUCCESS) {
            return $lowFareSearchResult;
        }
        $lowFareSearchResult->errorCode = ErrorCodes::SUCCESS;
        $this->combineAirPriceSolutions2($lowFareSearchResult);
        return $lowFareSearchResult;
    }

    public function applyBook($applyBookInformation) {
        $travelportApplyBookTransformer = new TravelportApplyBookTransformer();
        $travelportApplyBookTransformer->applyBookInformation = $applyBookInformation;
        $flyApplyBookResult = $this->sendRequestWebService($travelportApplyBookTransformer);
        if ($flyApplyBookResult->errorCode != ErrorCodes::SUCCESS) {
            if (isset($flyApplyBookResult->universalRecord)) {
                $cancelErrorDto = $this->cancelUniversalRecord($flyApplyBookResult->universalRecord);
                
            }
            if($flyApplyBookResult->errorCode == ErrorCodes::PRICEORSCHEDULECHANGED){
                
                throw  new PriceChangedException($flyApplyBookResult->errorDesc);
            }else if($flyApplyBookResult->errorCode == ErrorCodes::AIRSEGMENTSELLFAILURE){
                throw  new AirSegmentSellFailureException($flyApplyBookResult->errorDesc);
            }else if($flyApplyBookResult->erroCode == ErrorCodes::AIRSEGMENTNOTHK){
                throw new AirSegmentNotHKStatuException($flyApplyBookResult->errorDesc);
            }else {
                throw new PnrNotCreatedException($flyApplyBookResult->errorDesc);
            }
            return $flyApplyBookResult;
        }
        return $flyApplyBookResult;
    }

    public function cancelUniversalRecord(\UniversalRecord $universalRecord) {
        $travelportUniversalRecordCancelTransformer = new TravelportUniversalRecordCancelTransformer($universalRecord);
        return $this->sendRequestWebService($travelportUniversalRecordCancelTransformer, TravelportAccount::$universalRecordEndPoint);
    }

}

?>
