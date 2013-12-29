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
 
  class Corendon   implements AirServiceProvider{
    private $actionMethod;
    private function  sendMessageCorendonApi($message){
       $soap_do = curl_init(CorendonAccount::getEndPoint());
        $header = array(
            "Content-Type: text/xml;charset=UTF-8", "Accept: gzip,deflate",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction:http://tempuri.org/".$this->actionMethod,
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
    
  




    private function  sendRequestWebService(XmlTransformer $transformer){
        $requestXml = $transformer->prepareXML();
        file_put_contents($transformer->name."Request.xml", $requestXml);
        $responseXml = $this->sendMessageCorendonApi($requestXml);
        //$responseXml = file_get_contents($transformer->name."Response.xml");
        file_put_contents($transformer->name."Response.xml", $responseXml);
        return $transformer->convertObject($responseXml);
    }

    
    public function applyBook($applyBookInformation) {
       $verifiedCombinedAirPriceSolution = $applyBookInformation->verifiedCombinedAirPriceSolution;
       $paymmentMethodIdentifier = $this->getPaymentMethod($verifiedCombinedAirPriceSolution, NULL);
       loadClass($this->getLibraryDirectory()."/corendon_apply_book_transformer.php");
       $transformer  = new CorendonApplyBookTransformer($applyBookInformation, $paymmentMethodIdentifier);
       $this->actionMethod ="BookFlight";
       $flyApplyBookResult = $this->sendRequestWebService($transformer);
       return $paymmentMethodIdentifier;
    }

    public function bookPriceVerify($combinedAirPriceSolution, $selectedJourneys,$airSegmentArray ,$searchCriteria) {
        $responseBookPriceVerifyData = new ResponseBookPriceVerifyData();
        $responseBookPriceVerifyData->combinedAirPriceSolution = $combinedAirPriceSolution;
        $responseBookPriceVerifyData->journeyKeys = $journeyKeys;
        
        $verifiedAirPriceSolution = $combinedAirPriceSolution;
        $verifiedAirPriceSolution->allJourneys = array();
        foreach($journeyKeys as $journeyKey){
            foreach($combinedAirPriceSolution->departure_journeys  as $departureJourney){
                if($journeyKey == $departureJourney->key){
                    array_push($verifiedAirPriceSolution->allJourneys, $departureJourney);
                    break;
                }
            }
            foreach($combinedAirPriceSolution->return_journeys as $returnJourney){
                if($journeyKey == $returnJourney->key){
                    array_push($verifiedAirPriceSolution->allJourneys, $returnJourney);
                    break;
                }
            }
        }
       
        $responseBookPriceVerifyData->verifiedAirPriceSolution = $verifiedAirPriceSolution;
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
         $transformer =  new CorendonGetAvaibleFareTransformer($search_criteria);
         $combinedAirPriceSolutions =  $transformer->convertObject($xmlData, TRUE);
         $lowFareSearchResult = new LowFareSearchResult();
         $lowFareSearchResult->apiCode = Fly_Constant::CORENDON_API_CODE;
         if($combinedAirPriceSolutions == null || count($combinedAirPriceSolutions) < 1){
             $lowFareSearchResult->errorCode = ErrorCodes::NOTFOUNDAIRPRICESOLUTION;
             return $lowFareSearchResult;
         }
           $lowFareSearchResult->errorCode = ErrorCodes::SUCCESS;
           $lowFareSearchResult->combinedAirPriceSolutionArray = $combinedAirPriceSolutions;
           return $lowFareSearchResult;
         
     }
     
     
     private function  getPaymentMethod($verifiedCombinedAirPriceSolution , $searchCriteria){
           loadClass($this->getLibraryDirectory()."/corendon_get_payment_methods_transformer.php");
           $transformer = new CorendonGetPaymentMethodsTransformer($verifiedCombinedAirPriceSolution,$searchCriteria);
           $this->actionMethod = "GetPaymentMethods";
           return $this->sendRequestWebService($transformer);
     }
     
     
     private function  getLibraryDirectory(){
         return APPPATH."/libraries/corendon";
     }
     
      

}
?>