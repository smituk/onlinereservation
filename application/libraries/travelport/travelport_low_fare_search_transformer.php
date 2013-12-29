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
        $searchOriginLocationXML = $searchOriginXML->addChild("Airport", NULL, TravelportAccount::$common_scheme_version);
        $searchOriginLocationXML->addAttribute("Code", $searchAirLeg->originSearchLocation->airport);
        $searchDestinationXML = $searchAirLegXML->addChild("SearchDestination");
        $searchDestinationLocationXML = $searchDestinationXML->addChild("Airport", NULL, TravelportAccount::$common_scheme_version);
        $searchDestinationLocationXML->addAttribute("Code", $searchAirLeg->destinationSearchLocation->airport);
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
            $cabinClassXML = $preferredCabinsXML->addChild("CabinClass");
            $cabinClassXML->addAttribute("Type", $this->searchCriteria->cabinclass);
        }
        
        if (isset($this->searchCriteria->flighttype) && $this->searchCriteria->flighttype != "all") {
            if($this->searchCriteria->flighttype == "1"){
                $flightTypeXML = $airLegModifierXML->addChild("FlightType");
                $flightTypeXML->addAttribute("RequireSingleCarrier","true");
                $flightTypeXML->addAttribute("MaxConnections","0");
                $flightTypeXML->addAttribute("MaxStops","0");
            }
        }
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
//        $providerXML = $preferredProvidersXML->addChild("Provider",NULL, TravelportAccount::$common_scheme_version);
//        $providerXML->addAttribute("Code","ACH");
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
        
        if($this->searchCriteria->flighttype == "1"){
                $flightTypeXML = $airSearchModifiersXML->addChild("FlightType");
                $flightTypeXML->addAttribute("RequireSingleCarrier","true");
                $flightTypeXML->addAttribute("MaxConnections","0");
                $flightTypeXML->addAttribute("MaxStops","0");
            }
    }
    
    private function buildSearchPassengerXMLs(SimpleXMLElement $lowFareSearchRequestXML){
        for ($i = 0; $i < (int) $this->searchCriteria->yetiskinnumber; $i++){
            $searchPassengerXML = $lowFareSearchRequestXML->addChild("SearchPassenger",NULL,  TravelportAccount::$common_scheme_version);
            $searchPassengerXML->addAttribute("Code" , "ADT");
            $searchPassengerXML->addAttribute("PricePTCOnly", "false");
        }
        
        for ($i = 0; $i < (int) $this->searchCriteria->cocuknumber; $i++){
            $searchPassengerXML = $lowFareSearchRequestXML->addChild("SearchPassenger",NULL,  TravelportAccount::$common_scheme_version);
            $searchPassengerXML->addAttribute("Code" , "CNN");
            $searchPassengerXML->addAttribute("PricePTCOnly", "false");
            $searchPassengerXML->addAttribute("Age" ,"7");
        }
        
          for ($i = 0; $i < (int) $this->searchCriteria->bebeknumber; $i++){
            $searchPassengerXML = $lowFareSearchRequestXML->addChild("SearchPassenger",NULL,  TravelportAccount::$common_scheme_version);
            $searchPassengerXML->addAttribute("Code" , "INF");
            $searchPassengerXML->addAttribute("PricePTCOnly", "false");
        }
    }
    
    private function buildAirPriceModifiersXML(SimpleXMLElement $lowFareSearchRequestXML){
         $airPricingModifiersXML = $lowFareSearchRequestXML->addChild("AirPricingModifiers");
         $airPricingModifiersXML->addAttribute("CurrencyType" , $this->searchCriteria->currency);
    }
}

?>
