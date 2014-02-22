<?php

include_once APPPATH . '/models/fly_search/low_fare_search_result.php';
include_once APPPATH . '/models/constants/flight_constants.php';
include_once APPPATH . '/services/airline_service.php';
include_once APPPATH . '/models/fly_search/air_pricing_solution.php';
include_once APPPATH . '/models/fly_search/journey.php';
include_once APPPATH . '/models/fly_search/air_pricing_info.php';
include_once APPPATH . '/models/fly_search/air_segment.php';
include_once APPPATH . '/models/fly_search/flight_detail.php';
include_once APPPATH . '/models/fly_search/book_info.php';
include_once APPPATH . '/models/fly_search/fly_search_criteria.php';
include_once APPPATH . '/models/fly_search/combined_air_price_solution.php';
include_once APPPATH . '/models/fly_search/low_fare_search_result.php';
include_once APPPATH . '/models/constants/flight_constants.php';
include_once APPPATH . '/services/airline_service.php';
include_once APPPATH . '/interface/air_service_provider.php';
include_once APPPATH . '/models/fly_booking/response_book_verify_data.php';
include_once APPPATH . '/models/fly_booking/fly_apply_book_result.php';
include_once 'corendon_account.php';
include_once 'corendon_get_avaible_fare_transformer.php';

class Corendon implements AirServiceProvider {

    private $actionMethod;

    private function sendMessageCorendonApi($message) {
        $soap_do = curl_init(CorendonAccount::getEndPoint());
        $header = array(
            "Content-Type: text/xml;charset=UTF-8", "Accept: gzip,deflate",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction:http://tempuri.org/" . $this->actionMethod,
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

    private function sendRequestWebService(XmlTransformer $transformer) {
        $requestXml = $transformer->prepareXML();
        //file_put_contents($transformer->name . "Request.xml", $requestXml);
        $responseXml = $this->sendMessageCorendonApi($requestXml);
        //$responseXml = file_get_contents($transformer->name."Response.xml");
        //file_put_contents($transformer->name . "Response.xml", $responseXml);
        return $transformer->convertObject($responseXml);
    }

    public function applyBook($applyBookInformation) {

        try {
            if (!$this->bookPriceVerifyFromAppliedBook($applyBookInformation)) {
                return FALSE;
            }
        } catch (AvaibleSolutionNotFoundException $ex) {
            throw new PnrNotCreatedException($ex->getMessage(),$ex->getCode());
        }

        $verifiedCombinedAirPriceSolution = $applyBookInformation->verifiedCombinedAirPriceSolution;
        $paymmentMethodIdentifier = $this->getPaymentMethod($verifiedCombinedAirPriceSolution, NULL);

        loadClass($this->getLibraryDirectory() . "/corendon_apply_book_transformer.php");
        $transformer = new CorendonApplyBookTransformer($applyBookInformation, $paymmentMethodIdentifier);
       
          file_put_contents("cdcd.xml", $transformer->prepareXml());
          $flyApplyBookResult =  $transformer->convertObject(file_get_contents("corendon.xml"), TRUE);
         
        /*
        $this->actionMethod = "BookFlight";
        $flyApplyBookResult = $this->sendRequestWebService($transformer);
         * 
         */
        if ($flyApplyBookResult->errorCode != ErrorCodes::SUCCESS) {
            if ($flyApplyBookResult->errorCode == ErrorCodes::PRICEORSCHEDULECHANGED) {
                throw new PriceChangedException($flyApplyBookResult->errorDesc);
            } else {
                throw new PnrNotCreatedException($flyApplyBookResult->errorDesc);
            }
        }
        return $flyApplyBookResult;
    }

    public function bookPriceVerify($combinedAirPriceSolution, $selectedJourneys, $airSegmentArray, $searchCriteria) {
        //FareInfoList gelmediğinden airPriceInfoObjecteki  fareInfoList dolmaz. Daha sonra bu gelistirmenien yapılması gerekir.

        $newSearchCriteria = clone $searchCriteria;
        $newSearchCriteria->aheadDateInterval = 0; // +- gunleri getirmesin
        $newSearchCriteria->backDateInterval = 0;
        $i = 0;
        foreach ($selectedJourneys as $selectedJourney) {
            $searchAirLeg = $newSearchCriteria->searchAirLegs[$i];
            $searchAirLeg->searchDepartureTime = $selectedJourney->getDepartureTime($airSegmentArray);
            $i++;
        }
        $lowFareSearchResult = $this->convertXMLToCombinedAirPriceSolutions($this->searchFlight($newSearchCriteria), $newSearchCriteria);
        $newCombinedAirPriceSolutions = $lowFareSearchResult->getCombinedAirPriceSolutions();
        if ($newCombinedAirPriceSolutions == FALSE || count($newCombinedAirPriceSolutions) < 1) {
            throw new AvaibleSolutionNotFoundException();
        }
        $selectedNewCombinedAirPriceSolution = null;
        $selectedNewJourneys = array();
        foreach ($newCombinedAirPriceSolutions as $newCombinedAirPriceSolution) {
            $i = 0;
            foreach ($newCombinedAirPriceSolution->getLegs() as $legObject) {
                foreach ($legObject->getJourneys() as $journey) {
                    if ($journey->identifier == $selectedJourneys[$i]->identifier) {
                        array_push($selectedNewJourneys, $journey);
                    }
                }
                $i++;
            }
            if (count($selectedNewJourneys) == count($selectedJourneys)) {
                $selectedNewCombinedAirPriceSolution = $newCombinedAirPriceSolution;
                break;
            }
            $selectedNewJourneys = array(); // herbir combined  solution için
        }

        if (!isset($selectedNewCombinedAirPriceSolution)) {
            throw new AvaibleSolutionNotFoundException();
        }
        $responseBookPriceVerifyData = new ResponseBookPriceVerifyData();
        $responseBookPriceVerifyData->combinedAirPriceSolution = $selectedNewCombinedAirPriceSolution;
        $responseBookPriceVerifyData->searchCriteria = $searchCriteria;
        $verifiedAirPriceSoluton = new CombinedAirPriceSolution();
        $verifiedAirPriceSoluton->combinedKey = $selectedNewCombinedAirPriceSolution->combinedKey;
        $verifiedAirPriceSoluton->apprixomateTotalPriceAmount = $selectedNewCombinedAirPriceSolution->apprixomateTotalPriceAmount;
        $verifiedAirPriceSoluton->approximateBasePriceAmount = $verifiedAirPriceSoluton->apprixomateTotalPriceAmount - $combinedAirPriceSolution->taxesAmount;
        $verifiedAirPriceSoluton->taxesAmount = $selectedNewCombinedAirPriceSolution->taxesAmount;
        $verifiedAirPriceSoluton->totalPrice = $selectedNewCombinedAirPriceSolution->totalPrice;
        $verifiedAirPriceSoluton->basePrice = $selectedNewCombinedAirPriceSolution->basePrice;
        $verifiedAirPriceSoluton->apprixomateTotalPrice = $selectedNewCombinedAirPriceSolution->apprixomateTotalPrice;
        $verifiedAirPriceSoluton->approximateBasePrice = $selectedNewCombinedAirPriceSolution->approximateBasePrice;
        $verifiedAirPriceSoluton->taxes = $selectedNewCombinedAirPriceSolution->taxes;
        $verifiedAirPriceSoluton->apiCode = Fly_Constant::CORENDON_API_CODE;
        $verifiedAirPriceSoluton->airPricingInfoArray = $selectedNewCombinedAirPriceSolution->airPricingInfoArray;
        $legIndexCount = 0;

        foreach ($combinedAirPriceSolution->legs as $legObject) {
            $verifiedAirPriceSolutonLegObject = clone $legObject;
            $verifiedAirPriceSolutonLegObject->resetJourneys();
            $journeyIndexCount = 0;
            foreach ($selectedJourneys as $journey) {
                if ($legIndexCount == $journeyIndexCount) {// burada 1. leg 1. journey eklenir. ikinci lege 2.journey eklenir.
                    $airSegments = $journey->getAirSegments($airSegmentArray);
                    foreach ($verifiedAirPriceSoluton->airPricingInfoArray as $airPriceInfoObjectArray) {
                        foreach ($airPriceInfoObjectArray as $passengerType => $airPriceInfoObject) {
                            foreach ($airSegments as $airSegment) {
                                $bookingInfoObject = $journey->getSegmentBookingInfo($airSegment->key, $passengerType);
                                if ($passengerType == "ADT") {
                                    $airSegment->bookingCabinClass = $bookingInfoObject->cabinClass;
                                    $airSegment->bookingCode = $bookingInfoObject->bookingCode;
                                }
                                $airPriceInfoObject->addBookingInfo($bookingInfoObject);
                            }
                            if ($passengerType == "ADT") {
                                $journey->setAirSegments($airSegments);
                            }
                        }
                    }
                    $verifiedAirPriceSolutonLegObject->addAvaibleJourney($journey);
                }
                $journeyIndexCount++;
            }
            $verifiedAirPriceSoluton->addLeg($verifiedAirPriceSolutonLegObject);
            $legIndexCount++;
        }
        $responseBookPriceVerifyData->verifiedAirPriceSolution = $verifiedAirPriceSoluton;
        $legKeyArray = array();
        foreach ($combinedAirPriceSolution->legs as $leg) {
            array_push($legKeyArray, $leg->key);
        }
        $responseBookPriceVerifyData->legKeyArray = $legKeyArray;
        return $responseBookPriceVerifyData;
    }

    public function cancelUniversalRecord(\UniversalRecord $universalRecord) {
        
    }

    public function searchFlight($searchCriteria) {
        $getAvaibleFareTransformer = new CorendonGetAvaibleFareTransformer($searchCriteria);
        $this->actionMethod = "GetAvailableFares";
        return $this->sendRequestWebService($getAvaibleFareTransformer);
    }

    public function convertXMLToCombinedAirPriceSolutions($xmlData, $search_criteria) {
        $transformer = new CorendonGetAvaibleFareTransformer($search_criteria);
        return $transformer->convertObject($xmlData, TRUE);
    }

    private function getPaymentMethod($verifiedCombinedAirPriceSolution, $searchCriteria) {
        loadClass($this->getLibraryDirectory() . "/corendon_get_payment_methods_transformer.php");
        $transformer = new CorendonGetPaymentMethodsTransformer($verifiedCombinedAirPriceSolution, $searchCriteria);
        $this->actionMethod = "GetPaymentMethods";
        return $this->sendRequestWebService($transformer);
    }

    private function getLibraryDirectory() {
        return APPPATH . "/libraries/corendon";
    }

    private function bookPriceVerifyFromAppliedBook(FlyApplyBookInformation $flyApplyBookInformation) {
        $verifiedCombinedAirPriceSolution = $flyApplyBookInformation->verifiedCombinedAirPriceSolution;
        $bookingTraveler = $flyApplyBookInformation->passangers;
        $flySearchCriteria = new Fly_search_criteria();
        $flySearchCriteria->searchAirLegs = array();
        foreach ($verifiedCombinedAirPriceSolution->getLegs() as $legObject) {
            $searchAirLeg = new SearchAirLeg();

            foreach ($legObject->getJourneys() as $journeyObject) {
                $searchAirLeg->searchDepartureTime = $journeyObject->getDepartureTime();
                $originSearchLocation = new SearchLocation();
                $originSearchLocation->airport = $journeyObject->getOrigin();
                $destinationSearchLoacation = new SearchLocation();
                $destinationSearchLoacation->airport = $journeyObject->getDestination();
                $searchAirLeg->originSearchLocation = $originSearchLocation;
                $searchAirLeg->destinationSearchLocation = $destinationSearchLoacation;
                break;
            }
            array_push($flySearchCriteria->searchAirLegs, $searchAirLeg);
        }
        $flySearchCriteria->flydirection = 2;
        if (count($verifiedCombinedAirPriceSolution->getLegs()) == 2) {
            $flySearchCriteria->flydirection = 2;
        } else if (count($verifiedCombinedAirPriceSolution->getLegs()) == 1) {
            $flySearchCriteria->flydirection = 1;
        }

        foreach ($bookingTraveler as $passanger) {
            if ($passanger->type == "ADT") {
                $flySearchCriteria->yetiskinnumber++;
            } else if ($passanger->type == "CNN") {
                $flySearchCriteria->cocuknumber++;
            } else if ($passanger->type == "INF") {
                $flySearchCriteria->bebeknumber++;
            }
        }
        $verifiedCombinedAirPriceSolutionLegs = array_values($verifiedCombinedAirPriceSolution->getLegs()); //Map to array
        $lowFareSearchResult = $this->convertXMLToCombinedAirPriceSolutions($this->searchFlight($flySearchCriteria),$flySearchCriteria);
        $newCombinedAirPriceSolutions = $lowFareSearchResult->getCombinedAirPriceSolutions();
        if ($newCombinedAirPriceSolutions == FALSE || count($newCombinedAirPriceSolutions) < 1) {
            throw new AvaibleSolutionNotFoundException();
        }

        $selectedNewCombinedAirPriceSolution = null;
        $selectedNewJourney = array();

        $verifiedCombinedAirPriceSolutionTotalJourneyCount = $verifiedCombinedAirPriceSolution->getTotalJourneyCount();
                 foreach ($newCombinedAirPriceSolutions as $newCombinedAirPriceSolution) {
            $i = 0;
            foreach ($newCombinedAirPriceSolution->getLegs() as $newLegObject) {
                $verifiedCombinedAirPriceSolutionLegObject = $verifiedCombinedAirPriceSolutionLegs[$i];
                $verifiedCombinedAirPriceSolutionLegJourneys = $verifiedCombinedAirPriceSolutionLegObject->getJourneys();
                foreach ($newLegObject->getJourneys() as $newJourney) {
                    if ($verifiedCombinedAirPriceSolutionLegJourneys[0]->identifier == $newJourney->identifier) {
                        array_push($selectedNewJourney, $newJourney);
                        break;
                    }
                }
                $i++;
            }
            if (count($selectedNewJourney) == $verifiedCombinedAirPriceSolutionTotalJourneyCount) {
                $selectedNewCombinedAirPriceSolution = $newCombinedAirPriceSolution;
                break;
            }
            $selectedNewJourney = array();
        }

        if (!isset($selectedNewCombinedAirPriceSolution)) {
             throw new AvaibleSolutionNotFoundException();
        }
        if ($selectedNewCombinedAirPriceSolution->apprixomateTotalPrice != $verifiedCombinedAirPriceSolution->apprixomateTotalPrice) {
             throw new PriceChangedException();
        }

        return TRUE;
    }

}

?>