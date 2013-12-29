<?php
  include_once 'travelport_common.php';
  include_once  APPPATH.'helpers/soap_helper.php';
  include_once  APPPATH.'/models/common/error_dto.php';
  class TravelportUniversalRecordCancelTransformer implements XmlTransformer{
    private $universalRecord;
    public $name = "uiversalRecordCancel";
    public function  __construct($universalRecord){
      $this->universalRecord = $universalRecord;     
     }
    
    public function convertObject($responseXml , $isConverted = FALSE) {
      $responseSimpleXmlElement = new SimpleXMLElement($responseXml);  
      $errorDto = TravelportCommon::getErrorStatu($responseSimpleXmlElement, $this);
      if($errorDto->code != TravelPortErrorCodes::SUCCESS){
          return $errorDto;
      }
      $responseSimpleXmlElement->registerXPathNamespace("universal",  TravelportAccount::$universalRecordEndPoint);
      foreach($responseSimpleXmlElement->xpath("//universal:ProviderReservationStatus") as $providerReservationStatusXML){
          $providerReservationStatusXMLAttributes = $providerReservationStatusXML->attributes();
          $canceled = (string)$providerReservationStatusXMLAttributes["Cancelled"][0];
          if($canceled != "true"){
              $errorDto->code = ErrorCodes::BOOKNOTCANCELED;
              $errorDto->serviceName = $this->name;
          }
      }
      return $errorDto;
    }
      

    public function prepareXml() {
        $messageXML = new SimpleXMLElement("<message></message>");
        $universalRecordCancelReqXML = $messageXML->addChild("UniversalRecordCancelReq",null,  TravelportAccount::$universal_scheme_version);
        $universalRecordCancelReqXML->addAttribute("TargetBranch", TravelportAccount::$branch);
        $universalRecordCancelReqXML->addAttribute("UniversalRecordLocatorCode",  $this->universalRecord->locatorCode);
        $universalRecordCancelReqXML->addAttribute("Version",1);
        return SoapHelper::buildSoapXML($universalRecordCancelReqXML->asXML());
    }

}

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

?>