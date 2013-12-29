<?php
include_once 'corendon_common.php';
class CorendonGetPaymentMethodsTransformer implements XmlTransformer {
    private $verifiedAirPriceSolution;
    private $searchCriteria;
    public  $name ="CorendonGetPaymentMethods"; 
    
    public function __construct($verifiedAirPriceSolution  , $searchCriteria) {
        $this->verifiedAirPriceSolution = $verifiedAirPriceSolution;
        $this->searchCriteria = $searchCriteria;
    }


    
    public function convertObject($responseXml, $isConverted = FALSE) {
        $responseXML = new SimpleXMLElement($responseXml);
        $responseXML->registerXPathNamespace("ns", CorendonAccount::getDefaultNameSpace());
        foreach ($responseXML->xpath("//ns:RSGetPaymentMethods") as $getPaymentMethodXML) {
             return $getPaymentMethodXML->PAYMENTIDENTIFIER;
        }
        return null;
    }
    
    

    public function prepareXml() {
        $getPaymetMethodsRequestXML = new SimpleXMLElement("<myxml></myxml>");
        $getPaymetMethodsRequestXML = $getPaymetMethodsRequestXML->addChild("GetPaymentMethods", NULL, CorendonAccount::getDefaultNameSpace());
        $requestXML = $getPaymetMethodsRequestXML->addChild("request");
        $goJourney = $this->verifiedAirPriceSolution->allJourneys[0];
        //$requestXML->addChild("CURRENCY",  $this->searchCriteria->currency);
      
       
        $requestXML->addChild("CURRENCY",  "EUR");
        $requestXML->addChild("FLIGHTIDENTIFIER",  $goJourney->identifier);
        CorendonCommon::buildAgentXML($requestXML);
        $getPaymetMethodsRequestXML = $getPaymetMethodsRequestXML->asXML();
       $message = <<<EOM
        <s:Envelope xmlns:s = "http://schemas.xmlsoap.org/soap/envelope/">
        <s:Body xmlns:xsi = "http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd = "http://www.w3.org/2001/XMLSchema">
         $getPaymetMethodsRequestXML
        </s:Body>
        </s:Envelope>
EOM;
        return $message;
                
        
    }

}
?>
