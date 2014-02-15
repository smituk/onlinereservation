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
       // file_put_contents($transformer->name . "Request.xml", $requestXml);
        $responseXml = $this->sendMessageCorendonApi($requestXml);
        //$responseXml = file_get_contents($transformer->name."Response.xml");
        //file_put_contents($transformer->name . "Response.xml", $responseXml);
        return $transformer->convertObject($responseXml);
    }

    public function applyBook($applyBookInformation) {
        $verifiedCombinedAirPriceSolution = $applyBookInformation->verifiedCombinedAirPriceSolution;
        $paymmentMethodIdentifier = $this->getPaymentMethod($verifiedCombinedAirPriceSolution, NULL);
        loadClass($this->getLibraryDirectory() . "/corendon_apply_book_transformer.php");
        $transformer = new CorendonApplyBookTransformer($applyBookInformation, $paymmentMethodIdentifier);
        file_put_contents("cdcd.xml", $transformer->prepareXml());
        return $transformer->convertObject(file_get_contents("corendon.xml"), TRUE);
        /*
        $this->actionMethod = "BookFlight";
         $flyApplyBookResult = $this->sendRequestWebService($transformer);
         return $flyApplyBookResult;
        //return $paymmentMethodIdentifier;
         * 
         */
    }

    public function bookPriceVerify($combinedAirPriceSolution, $selectedJourneys, $airSegmentArray, $searchCriteria) {
       //FareInfoList gelmediğinden airPriceInfoObjecteki  fareInfoList dolmaz. Daha sonra bu gelistirmenin yapılması gerekir.
        
        $responseBookPriceVerifyData = new ResponseBookPriceVerifyData();
        $responseBookPriceVerifyData->combinedAirPriceSolution = $combinedAirPriceSolution;
        $responseBookPriceVerifyData->searchCriteria = $searchCriteria;
        $verifiedAirPriceSoluton = new CombinedAirPriceSolution();
        $verifiedAirPriceSoluton->combinedKey = $combinedAirPriceSolution->combinedKey;

        $verifiedAirPriceSoluton->apprixomateTotalPriceAmount = $combinedAirPriceSolution->apprixomateTotalPriceAmount;
        $verifiedAirPriceSoluton->approximateBasePriceAmount =  $verifiedAirPriceSoluton->apprixomateTotalPriceAmount-$combinedAirPriceSolution->taxesAmount;
        $verifiedAirPriceSoluton->taxesAmount = $combinedAirPriceSolution->taxesAmount;
        $verifiedAirPriceSoluton->totalPrice = $combinedAirPriceSolution->totalPrice;
        $verifiedAirPriceSoluton->basePrice = $combinedAirPriceSolution->basePrice;
        $verifiedAirPriceSoluton->apprixomateTotalPrice = $combinedAirPriceSolution->apprixomateTotalPrice;
        $verifiedAirPriceSoluton->approximateBasePrice = $combinedAirPriceSolution->approximateBasePrice;
        $verifiedAirPriceSoluton->taxes = $combinedAirPriceSolution->taxes;
        $verifiedAirPriceSoluton->apiCode = Fly_Constant::CORENDON_API_CODE;
        $verifiedAirPriceSoluton->airPricingInfoArray = $combinedAirPriceSolution->airPricingInfoArray;
        $legIndexCount = 0;
        
        foreach($combinedAirPriceSolution->legs as $legObject){
            $verifiedAirPriceSolutonLegObject  = clone $legObject;
            $verifiedAirPriceSolutonLegObject->resetJourneys();
            $journeyIndexCount = 0;
            foreach($selectedJourneys as $journey){
                if($legIndexCount == $journeyIndexCount){// burada 1. leg 1. journey eklenir. ikinci lege 2.journey eklenir.
                    $airSegments = $journey->getAirSegments($airSegmentArray);
                    foreach($verifiedAirPriceSoluton->airPricingInfoArray as $airPriceInfoObjectArray){
                        foreach($airPriceInfoObjectArray as $passengerType => $airPriceInfoObject){
                            foreach($airSegments as $airSegment){
                              $bookingInfoObject = $journey->getSegmentBookingInfo($airSegment->key,$passengerType);
                               if($passengerType == "ADT"){
                                  $airSegment->bookingCabinClass = $bookingInfoObject->cabinClass;
                                  $airSegment->bookingCode = $bookingInfoObject->bookingCode;
                                  
                               }
                               $airPriceInfoObject->addBookingInfo($bookingInfoObject);
                            }
                            if($passengerType== "ADT"){
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

}

?>