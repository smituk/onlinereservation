<?php

include_once APPPATH . '/interface/xml_transformer.php';
include_once 'travelport_account.php';
include_once 'travelport_common.php';

class TravelportLowFareSearchTransformer implements XmlTransformer {

    public $name = "low_fare_search";
    private $searchCriteria;

    public function __construct($searchCriteria) {
        $this->searchCriteria = $searchCriteria;
    }

    public function convertObject($responseXml , $isConverted = FALSE ) {
        return $responseXml;
    }

    public function prepareXml() {

        $lowFareSearchRequestXML = new SimpleXMLElement("<myxml></myxml>");
        $lowFareSearchRequestXML = $lowFareSearchRequestXML->addChild("LowFareSearchReq", null, TravelportAccount::$air_scheme_version);
        $lowFareSearchRequestXML->addAttribute("TargetBranch", TravelportAccount::$branch);
        $lowFareSearchRequestXML->addAttribute("SolutionResult","false");
        $billingPointOfSaleInfoXML = $lowFareSearchRequestXML->addChild("BillingPointOfSaleInfo", NULL, TravelportAccount::$common_scheme_version);
        $billingPointOfSaleInfoXML->addAttribute("OriginApplication", "UAPI");
        if ($this->searchCriteria->flydirection == 1) {// Tek yönler için.
            $this->buildSearchAirLegXML($lowFareSearchRequestXML, $this->searchCriteria->searchAirLegs[0]);
        } else {
            foreach ($this->searchCriteria->searchAirLegs as $searchAirLeg) {
                $this->buildSearchAirLegXML($lowFareSearchRequestXML, $searchAirLeg);
            }
        }
        $this->buildAirSearchModifierXML($lowFareSearchRequestXML);
        $this->buildSearchPassengerXMLs($lowFareSearchRequestXML);
        $this->buildAirPriceModifiersXML($lowFareSearchRequestXML);
        
        $lowFareSearchRequestXMLMessage = $lowFareSearchRequestXML->asXML();
        
        $message = <<<EOM
        <s:Envelope xmlns:s = "http://schemas.xmlsoap.org/soap/envelope/">
        <s:Body xmlns:xsi = "http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd = "http://www.w3.org/2001/XMLSchema">
          $lowFareSearchRequestXMLMessage
        </s:Body>
        </s:Envelope>
EOM;
        return $message;
    }

    private function buildSearchAirLegXML(SimpleXMLElement $lowFareSearchRequestXML, SearchAirLeg $searchAirLeg) {
        $searchAirLegXML = $lowFareSearchRequestXML->addChild("SearchAirLeg");
        $searchOriginXML = $searchAirLegXML->addChild("SearchOrigin");
        $originSearchLocation =  $searchAirLeg->originSearchLocation;
        if($originSearchLocation->isAll){
            $searchOriginLocationXML = $searchOriginXML->addChild("City", NULL, TravelportAccount::$common_scheme_version);
            $searchOriginLocationXML->addAttribute("Code", $searchAirLeg->originSearchLocation->city);
        }else{
            $searchOriginLocationXML = $searchOriginXML->addChild("Airport", NULL, TravelportAccount::$common_scheme_version);
            $searchOriginLocationXML->addAttribute("Code", $searchAirLeg->originSearchLocation->airport);
        }
        $searchDestinationXML = $searchAirLegXML->addChild("SearchDestination");
        $destinationSearchLocation = $searchAirLeg->destinationSearchLocation;
        if($destinationSearchLocation->isAll){
            $searchDestinationLocationXML = $searchDestinationXML->addChild("City", NULL, TravelportAccount::$common_scheme_version);
            $searchDestinationLocationXML->addAttribute("Code", $searchAirLeg->destinationSearchLocation->city);
        }else{
            $searchDestinationLocationXML = $searchDestinationXML->addChild("Airport", NULL, TravelportAccount::$common_scheme_version);
            $searchDestinationLocationXML->addAttribute("Code", $searchAirLeg->destinationSearchLocation->airport);
        }
       
        
        $searchDepartureTimeXML = $searchAirLegXML->addChild("SearchDepTime");
        $searchDepartureTimeXML->addAttribute("PreferredTime", $searchAirLeg->searchDepartureTime . "T00:00:00");
        if (isset($searchAirLeg->searchArrvalTime)) {
            $searchArrivalTimeXML = $searchAirLegXML->addChild("SearchArvTime");
            $searchArrivalTimeXML->addAttribute("PreferredTime", $searchAirLeg->searchArrivalTime . "T00:00:00");
        }
        $this->buildSearchAirLegModifierXML($searchAirLegXML);
    }

    private function buildSearchAirLegModifierXML(SimpleXMLElement $searchAirLegXML) {
        $airLegModifierXML = $searchAirLegXML->addChild("AirLegModifiers");
        if (isset($this->searchCriteria->cabinclass) && $this->searchCriteria->cabinclass != "all") {
            $preferredCabinsXML = $airLegModifierXML->addChild("PreferredCabins");
            $cabinClassXML = $preferredCabinsXML->addChild("CabinClass" ,NULL, TravelportAccount::$common_scheme_version);
            $cabinClassXML->addAttribute("Type", $this->searchCriteria->cabinclass);
        }
        
        /*
        if (isset($this->searchCriteria->flighttype) && $this->searchCriteria->flighttype == SearchCriteraFlightTypeEnum::NONSTOP) {
            if($this->searchCriteria->flighttype == SearchCriteraFlightTypeEnum::NONSTOP){
                $flightTypeXML = $airLegModifierXML->addChild("FlightType");
                $flightTypeXML->addAttribute("RequireSingleCarrier","true");
                $flightTypeXML->addAttribute("MaxConnections","0");
                $flightTypeXML->addAttribute("MaxStops","1");
            }
        }
         */
         
    }
   
    /*
     * 
     *  public $preferredCarriers;
    public $disfavoredCarriers;
    public $permitedCarriers;
    public $exludedCarriers;
    public $prohibitedCarriers;
     */
    private function buildAirSearchModifierXML(SimpleXMLElement $lowFareSearchRequestXML){
        $airSearchModifiersXML = $lowFareSearchRequestXML->addChild("AirSearchModifiers");
        
        $preferredProvidersXML = $airSearchModifiersXML->addChild("PreferredProviders");
        $providerXML = $preferredProvidersXML->addChild("Provider",NULL, TravelportAccount::$common_scheme_version);
        $providerXML->addAttribute("Code","1G");
        /*
        $providerXML = $preferredProvidersXML->addChild("Provider",NULL, TravelportAccount::$common_scheme_version);
        $providerXML->addAttribute("Code","ACH");
         * */
         
        if($this->searchCriteria->preferredCarriers != null){
              $preferredCarrierXML = $airSearchModifiersXML->addChild("PreferredCarriers");
            foreach($this->searchCriteria->preferredCarriers as $preferredCarrier){
               //<Carrier xmlns="http://www.travelport.com/schema/common_v20_0" Code="UA"/>
                $carrierXML = $preferredCarrierXML->addChild("Carrier",NULL, TravelportAccount::$air_scheme_version);
                $carrierXML->addAttribute("Code",$preferredCarrier);
            }
        }
        
        if($this->searchCriteria->prohibitedCarriers != null){
            $prohibitedCarrierXML  = $airSearchModifiersXML->addChild("ProhibitedCarriers");
            foreach($this->searchCriteria->prohibitedCarriers as $prohibitedCarrier){
                $carrierXML = $prohibitedCarrierXML->addChild("Carrier",NULL, TravelportAccount::$air_scheme_version);
                $carrierXML->addAttribute("Code",$prohibitedCarrier);
            }
        }
        
        if($this->searchCriteria->flighttype == SearchCriteraFlightTypeEnum::NONSTOP){
            /*
             * <ns2:FlightType NonStopDirects="true" StopDirects="false" SingleOnlineCon="false" DoubleOnlineCon="false" TripleOnlineCon="false" SingleInterlineCon="false" DoubleInterlineCon="false" TripleInterlineCon="false" />
             */    
            $flightTypeXML = $airSearchModifiersXML->addChild("FlightType");
                $flightTypeXML->addAttribute("RequireSingleCarrier","true");
                $flightTypeXML->addAttribute("MaxConnections","0");
                $flightTypeXML->addAttribute("MaxStops","0");
                $flightTypeXML->addAttribute("NonStopDirects","true");
                $flightTypeXML->addAttribute("StopDirects" ,"false");
                $flightTypeXML->addAttribute("SingleOnlineCon","false");
                $flightTypeXML->addAttribute("DoubleOnlineCon","false");
                $flightTypeXML->addAttribute("TripleOnlineCon","false");
                
            }
    }
    
    private function buildSearchPassengerXMLs(SimpleXMLElement $lowFareSearchRequestXML){
        for ($i = 0; $i < (int) $this->searchCriteria->yetiskinnumber; $i++){
            TravelportCommon::addSearchPassengerXML($lowFareSearchRequestXML, "ADT");
        }
        
        for ($i = 0; $i < (int) $this->searchCriteria->cocuknumber; $i++){
            TravelportCommon::addSearchPassengerXML($lowFareSearchRequestXML, "CNN",7);
        }
        
          for ($i = 0; $i < (int) $this->searchCriteria->bebeknumber; $i++){
           TravelportCommon::addSearchPassengerXML($lowFareSearchRequestXML, "INF");
        }
    }
    
    private function buildAirPriceModifiersXML(SimpleXMLElement $lowFareSearchRequestXML){
         $airPricingModifiersXML = $lowFareSearchRequestXML->addChild("AirPricingModifiers");
         $airPricingModifiersXML->addAttribute("CurrencyType" , $this->searchCriteria->currency);
         $airPricingModifiersXML->addAttribute("ETicketability","Required");
    }
}

?>
