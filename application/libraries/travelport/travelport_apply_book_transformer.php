<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
include_once APPPATH . '/interface/xml_transformer.php';
include_once 'travelport_account.php';
include_once 'travelport_common.php';
include_once 'travelport_action_status.php';
include_once 'travelport_error.php';
include_once APPPATH . '/models/fly_booking/fly_apply_book_result.php';
include_once APPPATH . '/models/fly_booking/fly_book_air_solution_changed_info.php';
include_once APPPATH . '/models/fly_booking/fly_book_pnr_status_code.php';
include_once APPPATH . '/models/fly_booking/air_segment_sell_failure_Info.php';

class TravelportApplyBookTransformer implements XmlTransformer {

    public $name = "applyBook";
    public $applyBookInformation;

    public function convertObject($responseXml, $isConverted = FALSE) {
        if ($responseXml == null && $responseXml == FALSE) {
            return FALSE;
        }

        $responseSimpleXmlElement = new SimpleXMLElement($responseXml);
        $flyApplyBookResult = new FlyApplyBookResult();
        $flyApplyBookResult->applyBookInformation = $this->applyBookInformation;
        $flyApplyBookResult->apiCode = TravelportCommon::APICODE;
        $errorStatu = TravelportCommon::getErrorStatu($responseSimpleXmlElement, $this);
        if ($errorStatu->code != TravelPortErrorCodes::SUCCESS) {
            $flyApplyBookResult->errorCode = $errorStatu->code;
            $flyApplyBookResult->errorDesc = $errorStatu->description;
            $flyApplyBookResult->userFriendlyErrorDesc = $errorStatu->description;
            $flyApplyBookResult->serviceName = $errorStatu->serviceName;
            return $flyApplyBookResult;
        }

        $responseSimpleXmlElement->registerXPathNamespace("air", TravelportAccount::$air_scheme_version);
        foreach ($responseSimpleXmlElement->xpath("//air:AirSolutionChangedInfo") as $airSolutionChangedInfoXML) {
            $airSolutionChangedInfoXMLAttributes = $airSolutionChangedInfoXML->attributes();
            $reason = (string) $airSolutionChangedInfoXMLAttributes["Reason"][0];
            if ($reason == AirSolutionChangedReasonCode::PRICE) {
                $flyApplyBookResult->pnrStatusCode = PnrStatusCode::PRICE_CHANGED;
            } else if ($reason == AirSolutionChangedReasonCode::SCHEDULE) {
                $flyApplyBookResult->pnrStatusCode = PnrStatusCode::SCHEDULE_CHANGED;
            } else if ($reason == AirSolutionChangedReasonCode::Both) {
                $flyApplyBookResult->pnrStatusCode = PnrStatusCode::BOTHCHANGED;
            }
            //TODO   price solution cevrilecek
            $flyApplyBookResult->errorCode = ErrorCodes::PRICEORSCHEDULECHANGED;
            $flyApplyBookResult->errorDesc = "Fiyat veya Schedule Değişmiş";
            $flyApplyBookResult->userFriendlyErrorDesc = "Fiyat veya Schedule Değişmiş";
            return $flyApplyBookResult;
        }
        foreach ($responseSimpleXmlElement->xpath("//air:AirSegmentSellFailureInfo") as $airSegmentSellFailureInfoXML) {
            $airSegmentSellFailureInfo = new AirSegmentSellFailureInfo();
            $airSegments = array();
            foreach ($airSegmentSellFailureInfoXML->xpath("//air:AirSegment") as $airSegmentXML) {
                array_push($airSegments, TravelportCommon::airSegmentXMLToObject($airSegmentXML));
            }
            $airSegmentSellFailureInfo->airSegments = $airSegments;
            $flyApplyBookResult->airSegmentSellFailureInfo = $airSegmentSellFailureInfo;
            $responseSimpleXmlElement->registerXPathNamespace("universal", TravelportAccount::$universal_scheme_version);
            foreach ($responseSimpleXmlElement->xpath("//universal:UniversalRecord") as $universalRecordXML) {
                $flyApplyBookResult->universalRecord = TravelportCommon::universalRecordXMLToObject($universalRecordXML);
            }
            $flyApplyBookResult->pnrStatusCode = PnrStatusCode::AIRSEGMENT_SELL_FAILURE;
            $flyApplyBookResult->errorCode = ErrorCodes::AIRSEGMENTSELLFAILURE;
            $flyApplyBookResult->errorDesc = "Segmentlerin birinde ilgili sınıftan pnr alınamadı";
            $flyApplyBookResult->userFriendlyErrorDesc = "Segmentlerin birinde ilgili sınıftan pnr alınamadı";
            return $flyApplyBookResult;
        }
        $responseSimpleXmlElement->registerXPathNamespace("universal", TravelportAccount::$universal_scheme_version);
        foreach ($responseSimpleXmlElement->xpath("//universal:UniversalRecord") as $universalRecordXML) {
            $flyApplyBookResult->universalRecord = TravelportCommon::universalRecordXMLToObject($universalRecordXML);
            $isValidAllAirSegment = TRUE;
            foreach ($flyApplyBookResult->universalRecord->reservationInfo->airSegments as $airSegment) {
                if ($airSegment->statu != "HK") {
                    $isValidAllAirSegment = FALSE;
                }
            }
            if ($isValidAllAirSegment) {
                $flyApplyBookResult->pnrStatusCode = PnrStatusCode::SUCCESS;
                $flyApplyBookResult->errorCode = ErrorCodes::SUCCESS;
            } else {
                $flyApplyBookResult->pnrStatusCode = PnrStatusCode::AIRSEGMENTNOTHK;
                $flyApplyBookResult->errorCode = ErrorCodes::AIRSEGMENTNOTHK;
                $flyApplyBookResult->errorDesc = "Tum segmentler HK statude olmalı";
                $flyApplyBookResult->userFriendlyErrorDesc = "Tum segmentler HK statude olmalı";
            }
        }

        return $flyApplyBookResult;
    }

    public function prepareXml() {

        $passangers = $this->applyBookInformation->passangers;
        $verifiedCombinedAirPriceSolution = $this->applyBookInformation->verifiedCombinedAirPriceSolution;
        $userContact = $this->applyBookInformation->userContact;
        //$rawVerifyBookPriceXML = $this->applyBookInformation->rawVerifyBookPriceXML; // Bu değer , fiyat dogrulama servisinden gelen responsu içermekte. cunku burdaki tum datalar  , booking için gereklidir.
        $TARGETBRANCH = TravelportAccount::$branch;
        $commonVersion = TravelportAccount::$common_scheme_version;
        $airVersion = TravelportAccount::$air_scheme_version;
        $universalVersion = TravelportAccount::$universal_scheme_version;


        $bookingTravellerXMLMessage = TravelportCommon::bookingTravelerToXML($passangers, $userContact);
        $actionStatuXML = $this->actionStatuToXML(); //Duruma gore yazılacak.
        $airPriceSolutionXML = $this->airPriceSolutionToXML($verifiedCombinedAirPriceSolution);

        $message = <<<EOM
           <s:Envelope xmlns:s = "http://schemas.xmlsoap.org/soap/envelope/">
            <s:Body xmlns:xsi = "http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd = "http://www.w3.org/2001/XMLSchema">
             <AirCreateReservationReq  xmlns="$universalVersion" TargetBranch="$TARGETBRANCH">
               <BillingPointOfSaleInfo OriginApplication = "UAPI" xmlns = "$commonVersion" ></BillingPointOfSaleInfo>
                 $bookingTravellerXMLMessage
                 $airPriceSolutionXML
                 $actionStatuXML
               </AirCreateReservationReq>
             </s:Body>
          </s:Envelope>
EOM;

        return $message;
    }

    private function actionStatuToXML() {
        $actionStatuXML = new SimpleXMLElement("<mydata></mydata>");
        $actionStatuXML = $actionStatuXML->addChild("ActionStatus", NULL, TravelportAccount::$common_scheme_version);
        $actionStatuXML->addAttribute("ProviderCode", "1G");
        if ($this->applyBookInformation->reservationType == "T") {
            $nowTime = new DateTime();
            $nowTime = $nowTime->add(new DateInterval("PT23H"));
            $actionStatuXML->addAttribute("Type", TravelportActionStatus::TAW);
            $actionStatuXML->addAttribute("TicketDate", $nowTime->format(DateTime::ISO8601));
        } else if ($this->applyBookInformation->reservationType == "O") {
            $actionStatuXML->addAttribute("Type", TravelportActionStatus::TTL);
            $actionStatuXML->addAttribute("TicketDate", $this->applyBookInformation->reservationDate->format(DateTime::ISO8601));
        }
        return $actionStatuXML->asXML();
    }

    private function airPriceSolutionToXML(CombinedAirPriceSolution $verifiedCombinedAirPriceSolution) {

        //AirPricingSolution Key="LEW4LgBoSfCpPgCoMgZG2g==" TotalPrice="EUR1751.61" BasePrice="EUR696.00" ApproximateTotalPrice="EUR1751.61" ApproximateBasePrice="EUR696.00" Taxes="EUR1055.61">
        $airPriceSolutionXML = new SimpleXMLElement("<MyData></MyData>");
        $airPriceSolutionXML = $airPriceSolutionXML->addChild("AirPricingSolution", null, TravelportAccount::$air_scheme_version);
        $airPriceSolutionXML->addAttribute("Key", $verifiedCombinedAirPriceSolution->combinedKey);
        $airPriceSolutionXML->addAttribute("TotalPrice", $verifiedCombinedAirPriceSolution->totalPrice);
        $airPriceSolutionXML->addAttribute("BasePrice", $verifiedCombinedAirPriceSolution->basePrice);
        $airPriceSolutionXML->addAttribute("ApproximateTotalPrice", $verifiedCombinedAirPriceSolution->apprixomateTotalPrice);
        $airPriceSolutionXML->addAttribute("ApproximateBasePrice", $verifiedCombinedAirPriceSolution->approximateBasePrice);
        $airPriceSolutionXML->addAttribute("Taxes", $verifiedCombinedAirPriceSolution->taxes);

        foreach ($verifiedCombinedAirPriceSolution->legs as $legObject) {
            foreach ($legObject->getJourneys() as $journey) {
                foreach ($journey->airSegmentItems as $airsegment) {
                    $airSegmentXML = $airPriceSolutionXML->addChild("AirSegment");
                    TravelportCommon::airsegmentObjectToXML($airsegment, FALSE, $airSegmentXML);
                }
            }
        }

        foreach ($verifiedCombinedAirPriceSolution->airPricingInfoArray as $airPriceInfoArray) {
            foreach ($airPriceInfoArray as $airPriceInfoObject) {
                $airPriceInfoXML = $airPriceSolutionXML->addChild("AirPricingInfo");
                TravelportCommon::airPriceInfoObjectToXML($airPriceInfoObject, FALSE, $airPriceInfoXML);
                foreach ($this->applyBookInformation->passangers as $passenger) {
                    if ($airPriceInfoObject->passengerType == $passenger->type) {
                        $passangerTypeXML = $airPriceInfoXML->addChild("PassengerType");
                        $passangerTypeXML->addAttribute("Code", $passenger->type);
                        $passangerTypeXML->addAttribute("PricePTCOnly", "false");
                        $passangerTypeXML->addAttribute("BookingTravelerRef", $passenger->key);
                        if ($passenger->type != "ADT") {
                            $today = new DateTime();
                            $diff = $today->diff($passenger->DOB);
                            $passangerTypeXML->addAttribute("Age", $diff->y);
                        }
                    }
                }
            }
            break;
        }

        return $airPriceSolutionXML->asXML();
    }

}

?>
