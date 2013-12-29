<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Flight_search extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function basic_flight($from, $to, $departure, $return) {
        
        $TARGETBRANCH = 'P106768';
        $CREDENTIALS = 'Universal API/uAPI-817584913:Z!rc0n7ade27dd-05d2-4383-89d9-4c65c8';
        $message = <<<EOM
        <s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
            <s:Body xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
                <LowFareSearchReq xmlns="http://www.travelport.com/schema/air_v18_0" TargetBranch="$TARGETBRANCH">
                    <BillingPointOfSaleInfo xmlns="http://www.travelport.com/schema/common_v15_0" OriginApplication="UAPI"/>
                    <SearchAirLeg>
                        <SearchOrigin>
                            <Airport xmlns="http://www.travelport.com/schema/common_v15_0" Code="$from"/>
                        </SearchOrigin>
                        <SearchDestination>
                            <Airport xmlns="http://www.travelport.com/schema/common_v15_0" Code="$to"/>
                        </SearchDestination>
                        <SearchDepTime PreferredTime="$departure"/>
                        <AirLegModifiers>
                            <PreferredCabins>
                                <CabinClass Type="Economy" ></CabinClass>
                            </PreferredCabins>
                        </AirLegModifiers>

                    </SearchAirLeg>
                    <SearchAirLeg>
                        <SearchOrigin>
                            <Airport xmlns="http://www.travelport.com/schema/common_v15_0" Code="$to"/>
                        </SearchOrigin>
                        <SearchDestination>
                            <Airport xmlns="http://www.travelport.com/schema/common_v15_0" Code="$from"/>
                        </SearchDestination>
                        <SearchDepTime PreferredTime="$return"/>
                        <AirLegModifiers>
                            <PreferredCabins>
                                <CabinClass Type="Economy" ></CabinClass>
                            </PreferredCabins>
                        </AirLegModifiers>
                    </SearchAirLeg>
                    
                    <AirSearchModifiers/>
EOM;
        for ($i = 0; $i < (int)$this->input->post("adults"); $i++ )
            $message .= '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v15_0" PricePTCOnly="false" Code="ADT"/>';
        
        for ($i = 0; $i < (int)$this->input->post("children"); $i++ )
            $message .= '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v15_0" PricePTCOnly="false" Code="CNN"/>';
        
        for ($i = 0; $i < (int)$this->input->post("infants"); $i++ )
            $message .= '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v15_0" PricePTCOnly="false" Code="INF"/>';
        
        $message .= <<<EOM
                    
                </LowFareSearchReq>
            </s:Body>
        </s:Envelope>
EOM;


        $auth = base64_encode("$CREDENTIALS");
        $soap_do = curl_init("https://emea.copy-webservices.travelport.com/B2BGateway/connect/uAPI/AirService");
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

       $result_xml = curl_exec($soap_do);
        
        $xml = simplexml_load_string($result_xml);
        $xml->formatOutput = true;
        //echo $xml->asXML();
        $xml->registerXPathNamespace('air', 'http://www.travelport.com/schema/air_v18_0');
        //$DistanceUnits = $xml->xpath('//air:AirSegment')->attributes()->DistanceUnits;
        //$CurrencyType = $xml->xpath('//air:AirSegment')->attributes()->CurrencyType;


        /**
         * Uçuşları $flights isimli arraye kaydediyorum.
         */
        $flights = array();
        foreach ($xml->xpath('//air:AirSegment') as $item) {
            $flights[(string) $item->attributes()->Key] = $item->attributes();
        }
        //echo $flights['1T']->FlightTime;


        $results = array();
        $segments = array();
        foreach ($xml->xpath('//air:AirPricingSolution') as $item) {
            $results[(string) $item->attributes()->Key] = $item->attributes();
            foreach ($item->xpath('air:AirSegmentRef') as $segment) {
                $segments[(string) $item->attributes()->Key][] = $segment->attributes()->Key;
            }
        }
        
        $result = array();
        
        $result['results'] = $results;
        $result['segments'] = $segments;
        $result['flights'] = $flights;
        
        return $result;
    }
}
?>