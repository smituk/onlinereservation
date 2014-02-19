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
    private $airSegmentArray;
    private $fareInfoArray;
    private $airlineArray;
    private $legArray;
    public  $checkAheadOut = 0;
    public  $checkBackOut = 0;
    public  $checkAheadIn = 0;
    public  $checkBackIn  = 0;
    public  $isCombined = true;

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
        loadClass(APPPATH . "/models/fly_search/low_fare_search_result.php");
        $lowFareSearchResult = new LowFareSearchResult();

        $this->airSegmentArray = array();
        $this->airportArray = array();
        $this->fareInfoArray = array();
        $this->airlineArray = array();
        $lowFareSearchResult->errorCode = ErrorCodes::SUCCESS;
        foreach ($responseXML->xpath("//ns:RSGetAvailableFares") as $getAvaibleFareXML) {
            $this->oneAdultFare = $getAvaibleFareXML->FAREADT;
            $this->oneChildFare = $getAvaibleFareXML->FARECHD;
            $this->oneInfFare = $getAvaibleFareXML->FAREINF;
            $this->oneAdultTax = $getAvaibleFareXML->TAXADT;
            $this->oneChildTax = $getAvaibleFareXML->TAXCHD;
            $this->oneInfTax = $getAvaibleFareXML->TAXINF;
            $this->currency = $currency = $getAvaibleFareXML->CURRENCY;

            $totalBaseAmount = $this->searchCriteria->yetiskinnumber * (int) $this->oneAdultFare + $this->searchCriteria->cocuknumber * (int) $this->oneChildFare + $this->searchCriteria->bebeknumber * (int) $this->oneInfFare + count($this->searchCriteria->searchAirLegs)*3*0;
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
            $airPriceSolutionObject = $this->setAirPricingInfoAndJourney($airPriceSolutionObject, $getAvaibleFareXML);
            //$airPriceSolutionObject->air_pricing_info = $this->airPriceInfoXMLToObject();
            //$airPriceSolutionObject->journeys = $this->convertJourneyObject($getAvaibleFareXML, $airPriceSolutionObject);
            array_push($airPriceSolutionArray, $airPriceSolutionObject);
        }
        $lowFareSearchResult->airPriceSolutionArray = $airPriceSolutionArray;
        $lowFareSearchResult->airSegmentArray = $this->airSegmentArray;
        //$lowFareSearchResult->airSegmentArray[Fly_Constant::COMBINATION_AIR_COMPANY] = AirlineService::getAirlineByIATACode(Fly_Constant::COMBINATION_AIR_COMPANY);
        $lowFareSearchResult->fareInfoArray = $this->fareInfoArray;
        $lowFareSearchResult->airlineArray = $this->airlineArray;
        $lowFareSearchResult->airlineArray[Fly_Constant::COMBINATION_AIR_COMPANY] = AirlineService::getAirlineByIATACode(Fly_Constant::COMBINATION_AIR_COMPANY);
        $lowFareSearchResult->airportArray = $this->airportArray;
        $lowFareSearchResult->apiCode = Fly_Constant::CORENDON_API_CODE;
        if(!$this->isCombined){
            return $lowFareSearchResult;
        }
        //file_put_contents("cdfefe.json", json_encode($lowFareSearchResult));
        return $this->combineAirPriceSolutions($lowFareSearchResult);
    }

    public function prepareXml() {

        $getAvaibleFareRequestXML = new SimpleXMLElement("<myxml></myxml>");
        $getAvaibleFareRequestXML = $getAvaibleFareRequestXML->addChild("GetAvailableFares", NULL, CorendonAccount::getDefaultNameSpace());
        $firstSearchAirLeg = $this->searchCriteria->searchAirLegs[0];
        $firstOriginSearchLocation = $firstSearchAirLeg->originSearchLocation;
        $firstDestinationSearchLocation = $firstSearchAirLeg->destinationSearchLocation;
        $requestXML = $getAvaibleFareRequestXML->addChild("request");
        $requestXML->addChild("NUMBEROFADULTS", $this->searchCriteria->yetiskinnumber); //Adult Number
        $requestXML->addChild("NUMBEROFCHILDREN", $this->searchCriteria->cocuknumber); // Cocuk Number
        $requestXML->addChild("NUMBEROFINFANTS", $this->searchCriteria->bebeknumber); // Bebek Number
        $carriesCodesXML = $requestXML->addChild("CARRIERCODES");
        $carrierCodeStrcXML = $carriesCodesXML->addChild("CARRIERCODE_STRC");
        $carrierCodeStrcXML->addChild("CODE");
        $requestXML->addChild("LANGUAGECODE", "EN");
        
        //Gidişteki kalkabilecek airportlar ekleniyor
        $multipleDepartureCodesOutXML = $requestXML->addChild("MULTIDEPARTURECODESOUT");
        $this->buildAirPortXML($multipleDepartureCodesOutXML, $firstSearchAirLeg->originSearchLocation);
      
        
        //Varış airportları ekleniyor
        $multipleArrivalCodeOutXML = $requestXML->addChild("MULTIARRIVALCODESOUT");
        $this->buildAirPortXML($multipleArrivalCodeOutXML, $firstSearchAirLeg->destinationSearchLocation);
        
      
        $departureTimeDateTime = new DateTime($firstSearchAirLeg->searchDepartureTime);
        $requestXML->addChild("DEPARTUREDATEOUT", $departureTimeDateTime->format("Ymd")); // departure date 
        $requestXML->addChild("DEPARTURETIMEOUT");

        $requestXML->addChild("CHECKAHEADOUT", $this->searchCriteria->aheadDateInterval);
        $requestXML->addChild("CHECKBACKOUT", $this->searchCriteria->backDateInterval);
        if ($this->searchCriteria->flydirection == 2) { // Tek yonler için 
            $secondSearchAirLeg = $this->searchCriteria->searchAirLegs[1];
            $multiDepartureCodesInXML = $requestXML->addChild("MULTIDEPARTURECODESIN");
            $this->buildAirPortXML($multiDepartureCodesInXML, $secondSearchAirLeg->originSearchLocation);
           
             $multipleArrivalCodeInXML = $requestXML->addChild("MULTIARRIVALCODESIN");
             $this->buildAirPortXML($multipleArrivalCodeInXML,  $secondSearchAirLeg->destinationSearchLocation);
           
            $departureTimeDateTime = new DateTime($secondSearchAirLeg->searchDepartureTime);
            $requestXML->addChild("DEPARTUREDATEIN", $departureTimeDateTime->format("Ymd"));
            $requestXML->addChild("DEPARTURETIMEIN");
            $requestXML->addChild("CHECKAHEADIN", $this->searchCriteria->aheadDateInterval);
            $requestXML->addChild("CHECKBACKIN", $this->searchCriteria->backDateInterval);
        } else {
            $multiDepartureCodesInXML = $requestXML->addChild("MULTIDEPARTURECODESIN");
            $airPortCodeSrcDepartureInXML = $multiDepartureCodesInXML->addChild("AIRPORTCODE_STRC");
            $airPortCodeSrcDepartureInXML->addChild("CODE");
            $multipleArrivalCodeInXML = $requestXML->addChild("MULTIARRIVALCODESIN");
            $airPortCodeSrcArrivalInXML = $multipleArrivalCodeInXML->addChild("AIRPORTCODE_STRC");
            $airPortCodeSrcArrivalInXML->addChild("CODE");
            $requestXML->addChild("DEPARTUREDATEIN");
            $requestXML->addChild("DEPARTURETIMEIN");
            $requestXML->addChild("CHECKAHEADIN",  $this->searchCriteria->aheadDateInterval);
            $requestXML->addChild("CHECKBACKIN", $$this->searchCriteria->backDateInterval);
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
            $airPriceInfoObject->approximateBasePrice = $this->currency .$this->oneAdultFare;
            $airPriceInfoObject->approximateBasePriceAmount = (int) $this->oneAdultFare;
            $airPriceInfoObject->approximateTotalPrice = $this->currency . ((int) $this->oneAdultFare + (int) $this->oneAdultTax);
            $airPriceInfoObject->approximateTotalPriceAmout = (int) $this->oneAdultFare + (int) $this->oneAdultTax;
            $airPriceInfoObject->taxes = $this->currency . $this->oneAdultTax;
            $airPriceInfoObject->taxesAmount = (int) $this->oneAdultTax;

            $airPriceInfoObject->passengerType = "ADT";
            $airPriceInfoObject->passengerCount = $this->searchCriteria->yetiskinnumber;
            $airPriceInfoObject->passengerTypeDesc = "Yetişkin";
            array_push($airPriceInfoArray, $airPriceInfoObject);
        }
        if ($this->searchCriteria->cocuknumber > 0) {
            $airPriceInfoObject = new AirPricingInfo();
            $airPriceInfoObject->key = substr(md5(microtime()), 0, 10);
            $airPriceInfoObject->approximateBasePrice = $this->currency . $this->oneChildFare;
            $airPriceInfoObject->approximateBasePriceAmount = (int) $this->oneChildFare;
            $airPriceInfoObject->approximateTotalPrice = $this->currency . ((int) $this->oneChildFare + (int) $this->oneChildTax);
            $airPriceInfoObject->approximateTotalPriceAmout = (int) $this->oneChildFare + (int) $this->oneChildTax;
            $airPriceInfoObject->taxes = $this->currency . $this->oneChildTax;
            $airPriceInfoObject->taxesAmount = (int) $this->oneChildTax;

            $airPriceInfoObject->passengerType = "CNN";
            $airPriceInfoObject->passengerCount = $this->searchCriteria->cocuknumber;
            $airPriceInfoObject->passengerTypeDesc = "Çocuk";
            array_push($airPriceInfoArray, $airPriceInfoObject);
        }

        if ($this->searchCriteria->bebeknumber > 0) {
            $airPriceInfoObject = new AirPricingInfo();
            $airPriceInfoObject->key = substr(md5(microtime()), 0, 10);
            $airPriceInfoObject->approximateBasePrice = $this->currency . $this->oneInfFare;
            $airPriceInfoObject->approximateBasePriceAmount = (int) $this->oneInfFare;
            $airPriceInfoObject->approximateTotalPrice = $this->currency . ((int) $this->oneInfFare + (int) $this->oneInfTax);
            $airPriceInfoObject->approximateTotalPriceAmout = (int) $this->oneInfFare + (int) $this->oneInfTax;
            $airPriceInfoObject->taxes = $this->currency . $this->oneInfTax;
            $airPriceInfoObject->taxesAmount = (int) $this->oneInfTax;

            $airPriceInfoObject->passengerType = "INF";
            $airPriceInfoObject->passengerCount = $this->searchCriteria->bebeknumber;
            $airPriceInfoObject->passengerTypeDesc = "Bebek";
            array_push($airPriceInfoArray, $airPriceInfoObject);
        }
        return $airPriceInfoArray;
    }

    private function setAirPricingInfoAndJourney(AirPricingSolution $airPriceSolution, $rSGetAvailableFaresXML) {
        $airPriceSolution->legs = array();
        $airPriceSolution->airPricingInfoArray = $this->airPriceInfoXMLToObject();
        $isFirstFlightOut = true;
        $isFirstFlightIn = true;
        $legIndexCount = 0;
        $legObject = null;
        foreach ($rSGetAvailableFaresXML->FLIGHTSOUT->FLIGHT_STRC as $flightXML) {

            if ($isFirstFlightOut) {
                $legObject = $this->createLeg($legIndexCount, $this->searchCriteria);
                $airPriceSolution->addLeg($legObject);
                $isFirstFlightOut = false;
                $legIndexCount++;
            }
            $journey = $this->createJourneyObject($airPriceSolution, $flightXML);
            $legObject->addAvaibleJourney($journey);
        }

        if (isset($rSGetAvailableFaresXML->FLIGHTSIN->FLIGHT_STRC)) {
            foreach ($rSGetAvailableFaresXML->FLIGHTSIN->FLIGHT_STRC as $flightXML) {
                if ($isFirstFlightIn) {
                    $legObject = $this->createLeg($legIndexCount, $this->searchCriteria);
                    $airPriceSolution->addLeg($legObject);
                    $isFirstFlightIn = false;
                    $legIndexCount++;
                }

                $journey = $this->createJourneyObject($airPriceSolution, $flightXML);
                $legObject->addAvaibleJourney($journey);
            }
        }
        return $airPriceSolution;
    }

    private function createLeg($legIndexCount, Fly_search_criteria $searchCriteria) {
        loadClass(APPPATH . "/models/fly_search/air_leg.php");

        if (!isset($this->legArray)) {
            $this->legArray = array();
            $legObject = new AirLeg();
            $legObject->key = substr(md5(microtime()), 0, 10);
            $searchAirLeg = $this->searchCriteria->searchAirLegs[$legIndexCount];
            $legObject->origin = $searchAirLeg->originSearchLocation->airport;
            $legObject->destination = $searchAirLeg->destinationSearchLocation->airport;
            $legObject->direction = "G";
            if ($legIndexCount == 1 && $searchCriteria->flydirection == "2") {
                $legObject->direction = "R";
            }
            array_push($this->legArray, $legObject);
        }else{
            if(!isset($this->legArray[$legIndexCount])){
                 $legObject = new AirLeg();
            $legObject->key = substr(md5(microtime()), 0, 10);
            $searchAirLeg = $this->searchCriteria->searchAirLegs[$legIndexCount];
            $legObject->origin = $searchAirLeg->originSearchLocation->airport;
            $legObject->destination = $searchAirLeg->destinationSearchLocation->airport;
            $legObject->direction = "G";
            if ($legIndexCount == 1 && $searchCriteria->flydirection == "2") {
                $legObject->direction = "R";
            }
            array_push($this->legArray, $legObject);
            }
        }
        
        return clone $this->legArray[$legIndexCount];
    }

    private function convertJourneyObject($rSGetAvailableFaresXML, $airPriceSolution) {
        $journeyArrays = array();
        foreach ($rSGetAvailableFaresXML->FLIGHTSOUT->FLIGHT_STRC as $flightXML) {
            $journeyObject = $this->createJourneyObject($flightXML);
            $journeyObject->type = Fly_Constant::DEPARTURE_JOURNEY_TYPE;
            $journeyObject->air_price_solution_key_ref = $airPriceSolution->key;
            array_push($journeyArrays, $journeyObject);
        }
        if (!isset($rSGetAvailableFaresXML->FLIGHTSIN)) {
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

    private function createJourneyObject(AirPricingSolution $airPriceSolution, $flightXML) {
        loadClass(APPPATH . "/models/fly_search/journey.php");
        $bookingClass = (string) $flightXML->CLASSOFFLIGHT;
        $cabinClass = "Economy";
        $journeyObject = new Journey();
        $journeyObject->key = substr(md5(microtime()), 0, 10);
        $journeyObject->identifier = (string) $flightXML->FLIGHTIDENTIFIER;
        $journeyObject->airPriceSolutionKeyRef = $airPriceSolution->key;
        $journeyObject->viaAirport = (string)$flightXML->VIAINFO;
        loadClass(APPPATH . "/models/fly_search/air_segment.php");
        loadClass(APPPATH . "/models/fly_search/book_info.php");
        foreach ($flightXML->SEGMENTS->FSEGMENT_STRC as $flightSegmentXML) {
            $airSegmentObject = new AirSegment();
            $airSegmentObject->key = substr(md5($journeyObject->identifier), 0, 10);
            $airSegmentObject->carrier = (string) $flightSegmentXML->CARRIERCODE;
            if (!isset($this->airlineArray[$airSegmentObject->carrier])) {
                $airCompanyObject = AirlineService::getAirlineByIATACode($airSegmentObject->carrier);
                $this->airlineArray[$airSegmentObject->carrier] = $airCompanyObject;
            }
            $airSegmentObject->flightNumber = (string) $flightSegmentXML->FLIGHTNUMBER;
            $airSegmentObject->origin = (string) $flightSegmentXML->DEPARTURECODE;
            $airSegmentObject->destination = (string) $flightSegmentXML->ARRIVALCODE;
            $avaibleSeatCount = (string) $flightSegmentXML->AVAILSEATS;

            $airSegmentObject->bookingCounts = $bookingClass . $avaibleSeatCount;
            if (!isset($this->airportArray[$airSegmentObject->origin])) {
                $airportService = AirportService::getInstance();
                $originAirportObject = $airportService->getAirportDetail($airSegmentObject->origin);
                $this->airportArray[$airSegmentObject->origin] = $originAirportObject;
            } else {
                $originAirportObject = $this->airportArray[$airSegmentObject->origin];
            }

            $destinationAirportObject = null;
            if (!isset($this->airportArray[$airSegmentObject->destination])) {
                $airportService = AirportService::getInstance();
                $destinationAirportObject = $airportService->getAirportDetail($airSegmentObject->destination);
                $this->airportArray[$airSegmentObject->destination] = $destinationAirportObject;
            } else {
                $destinationAirportObject = $this->airportArray[$airSegmentObject->destination];
            }

            $departureDate = (string) $flightSegmentXML->DEPARTUREDATE;
            $departureTime = (string) $flightSegmentXML->DEPARTURETIME;
            $arrivalDate = (string) $flightSegmentXML->ARRIVALDATE;
            $arrivalTime = (string) $flightSegmentXML->ARRIVALTIME;

            $departureDateTime = DateTime::createFromFormat("Ymd Hi", $departureDate . ' ' . $departureTime);
            $depatureDateTimezoneName = timezone_name_from_abbr("", intval($originAirportObject->utcOffset) * 3600, null);
            // 
            $airSegmentObject->departureTime = $departureDateTime->format(DateTime::ISO8601);
            $airSegmentObject->departureDate = $departureDateTime->format("d.m.Y");
            $airSegmentObject->departureHours = $departureDateTime->format('H:i');
            $departureDateTime->setTimezone(new DateTimeZone($depatureDateTimezoneName));
            $arrivalDateTime = DateTime::createFromFormat("Ymd Hi", $arrivalDate . ' ' . $arrivalTime);
            $arrivalDateTimezoneName = timezone_name_from_abbr("", intval($destinationAirportObject->utcOffset) * 3600, null);
            //
            $airSegmentObject->arrivalTime = $arrivalDateTime->format(DateTime::ISO8601);
            $airSegmentObject->arrivalDate = $arrivalDateTime->format("d.m.Y");
            $airSegmentObject->arrivalHours = $arrivalDateTime->format("H:i");
            $arrivalDateTime->setTimezone(new DateTimeZone($arrivalDateTimezoneName));
            $diffTimestamp = $arrivalDateTime->getTimestamp() - $departureDateTime->getTimestamp();
            $airSegmentObject->flightTime = $diffTimestamp / 60;
            $this->airSegmentArray[$airSegmentObject->key] = $airSegmentObject;
            $journeyObject->addAirSegment($airSegmentObject, TRUE);

            $fareBase = (string) $flightSegmentXML->FAREBASE;


            //fareInfo create ;
            $fareInfoObject = new FareInfo();
            $fareInfoObject->key = substr(md5(microtime()), 0, 10);
            $fareInfoObject->origin = $airSegmentObject->origin;
            $fareInfoObject->destination = $airSegmentObject->destination;
            $fareInfoObject->fareRuleKey = $journeyObject->identifier;
            $fareInfoObject->maxWeightOfAllowedBaggage = 20; // Burası değişecek;
            $fareInfoObject->weightUnit = "Kg";
            $fareInfoObject->departureDate = $airSegmentObject->departureTime;
            $fareInfoObject->fareBasis = $fareBase;
            $this->fareInfoArray[$fareInfoObject->key] = $fareInfoObject;

            $bookingInfoObject = new BookingInfo($bookingClass, $cabinClass, $fareInfoObject->key, $airSegmentObject->key);
            foreach ($airPriceSolution->airPricingInfoArray as $airPriceInfoObject) {
                $journeyObject->addBookingInfo($bookingInfoObject, $airPriceInfoObject->passengerType);
            }
        }
        return $journeyObject;
    }

    private function combineAirPriceSolutions(LowFareSearchResult $lowFareSearchResult) {
        if ($lowFareSearchResult->errorCode != TravelPortErrorCodes::SUCCESS) {
            return $lowFareSearchResult;
        }
        $samePricedSolutionArray = array(); // aynı fiyata sahip solutionları tutar;
        $airPriceSolutionArray = $lowFareSearchResult->airPriceSolutionArray;
        foreach ($airPriceSolutionArray as $airPriceSolution) {
            if (isset($samePricedSolutionArray[$airPriceSolution->apprixomate_total_price])) {
                array_push($samePricedSolutionArray[$airPriceSolution->apprixomate_total_price], $airPriceSolution);
            } else {
                $samePricedSolutionArray[$airPriceSolution->apprixomate_total_price] = array();
                array_push($samePricedSolutionArray[$airPriceSolution->apprixomate_total_price], $airPriceSolution);
            }
        }

        loadClass(APPPATH . '/models/fly_search/combined_air_price_solution.php');
        $lowFareSearchResult->combinedAirPriceSolutionArray = array();
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
                    $combinedAirPriceSolutionObject->apiCode = Fly_Constant::CORENDON_API_CODE;
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
            $lowFareSearchResult->combinedAirPriceSolutionArray[$combinedAirPriceSolutionObject->combinedKey] = $combinedAirPriceSolutionObject;
        }
        //unset($lowSearchResult->airPriceSolutionArray);
        return $lowFareSearchResult;
    }
    
    
    private function buildAirPortXML(SimpleXMLElement $airpotrsXML , SearchLocation $searchLocation){
        
         if($searchLocation->isAll == true){
            foreach(explode(",", $searchLocation->associatedAirports) as $airportCode){
                 $airPortCodeSrcXML = $airpotrsXML->addChild("AIRPORTCODE_STRC");
                 $airPortCodeSrcXML->addChild("CODE", $airportCode);
            }     
         }else{
              $airPortCodeSrcXML = $airpotrsXML->addChild("AIRPORTCODE_STRC");
              $airPortCodeSrcXML->addChild("CODE", $searchLocation->airport);
         }
    } 
  

}
?>

