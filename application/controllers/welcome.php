<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Welcome extends CI_Controller {
    
    public function demo() {

        $TARGETBRANCH = 'P106768';
        $CREDENTIALS = 'Universal API/uAPI-817584913:Z!rc0n7ade27dd-05d2-4383-89d9-4c65c8';
        $message = <<<EOM
        <s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
            <s:Body xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
                <LowFareSearchReq xmlns="http://www.travelport.com/schema/air_v18_0" TargetBranch="$TARGETBRANCH">
                    <BillingPointOfSaleInfo xmlns="http://www.travelport.com/schema/common_v15_0" OriginApplication="UAPI"/>
                    <SearchAirLeg>
                        <SearchOrigin>
                            <Airport xmlns="http://www.travelport.com/schema/common_v15_0" Code="IST"/>
                        </SearchOrigin>
                        <SearchDestination>
                            <Airport xmlns="http://www.travelport.com/schema/common_v15_0" Code="AMS"/>
                        </SearchDestination>
                        <SearchDepTime PreferredTime="2013-01-28"/>
                        <AirLegModifiers/>
                    </SearchAirLeg>
                    <SearchAirLeg>
                        <SearchOrigin>
                            <Airport xmlns="http://www.travelport.com/schema/common_v15_0" Code="AMS"/>
                        </SearchOrigin>
                        <SearchDestination>
                            <Airport xmlns="http://www.travelport.com/schema/common_v15_0" Code="IST"/>
                        </SearchDestination>
                        <SearchDepTime PreferredTime="2013-01-30"/>
                        <AirLegModifiers/>
                    </SearchAirLeg>
                    
                    <AirSearchModifiers/>
                    <SearchPassenger xmlns="http://www.travelport.com/schema/common_v15_0" PricePTCOnly="false" Code="ADT"/>
                </LowFareSearchReq>
            </s:Body>
        </s:Envelope>
EOM;
        
        $message = <<<MSG
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
<s:Body xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
<LowFareSearchReq xmlns="http://www.travelport.com/schema/air_v16_0" TargetBranch="$TARGETBRANCH">
<BillingPointOfSaleInfo xmlns="http://www.travelport.com/schema/common_v13_0" OriginApplication="UAPI"/>
<SearchAirLeg>
<SearchOrigin>
<Airport xmlns="http://www.travelport.com/schema/common_v13_0" Code="IST"/>
</SearchOrigin>
<SearchDestination>
<Airport xmlns="http://www.travelport.com/schema/common_v13_0" Code="AMS"/>
</SearchDestination>
<SearchDepTime PreferredTime="2013-02-07">
<SearchExtraDays xmlns="http://www.travelport.com/schema/common_v13_0" DaysBefore="2" DaysAfter="2"/>
</SearchDepTime>
</SearchAirLeg>
<SearchAirLeg>
<SearchOrigin>
<Airport xmlns="http://www.travelport.com/schema/common_v13_0" Code="AMS"/>
</SearchOrigin>
<SearchDestination>
<Airport xmlns="http://www.travelport.com/schema/common_v13_0" Code="IST"/>
</SearchDestination>
<SearchDepTime PreferredTime="2013-02-23">
<SearchExtraDays xmlns="http://www.travelport.com/schema/common_v13_0" DaysBefore="2" DaysAfter="2"/>
</SearchDepTime>
</SearchAirLeg>
<AirSearchModifiers>
</AirSearchModifiers>
<SearchPassenger xmlns="http://www.travelport.com/schema/common_v13_0" PricePTCOnly="false" Code="ADT"/>
<AirPricingModifiers FaresIndicator="PublicAndPrivateFares">
<ExemptTaxes/>
</AirPricingModifiers>
</LowFareSearchReq>
</s:Body>
</s:Envelope>
MSG;
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



        $xmlDoc = new DOMDocument ();
        $xmlDoc->preserveWhiteSpace = false;
        $xmlDoc->formatOutput = true;

        $xmlDoc->loadXML($message);
        $message_beautiful = $xmlDoc->saveXML();

        $xmlDoc->loadXML(curl_exec($soap_do));
        $result = $xmlDoc->saveXML();

        $data = array();
        $data['message'] = $message_beautiful;
        $data['result'] = $result;
        $data['error'] = curl_error($soap_do);

        $this->load->view('welcome_message', $data);
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */