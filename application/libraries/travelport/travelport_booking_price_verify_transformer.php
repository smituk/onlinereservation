<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of travelport_booking_price_verify_transformer
 *
 * @author pasa
 */
include_once APPPATH . '/interface/xml_transformer.php';
include_once 'travelport_account.php';
include_once 'travelport_common.php';

class TravelportBookingPriceVerifyTransformer implements XmlTransformer {

    public $combinedAirPriceSolution;
    public $selectedJourneys;
    public $name = "bookingVerify";
    public $searchCriteria;
    public $airSegmentArray;
    public $rawResponseXML;

    public function prepareXML() {
        $airSegments = array();
        foreach ($this->selectedJourneys as $selectedJourney) {
            $selectedJourneyAirSegments = $selectedJourney->getAirSegments($this->airSegmentArray);
            foreach ($selectedJourneyAirSegments as $selectedJourneyAirSegment) {
                $airSegment = clone $selectedJourneyAirSegment;
                $segmentBookingInfo = $selectedJourney->getSegmentBookingInfo($airSegment->key);
                $airSegment->bookingCode = $segmentBookingInfo->bookingCode;
                $airSegment->bookingCabinClass = $segmentBookingInfo->cabinClass;
                array_push($airSegments, $airSegment);
            }
        }


        $airPriceRequestXML = new SimpleXMLElement("<myxml></myxml>");
        $airPriceRequestXML = $airPriceRequestXML->addChild("AirPriceReq", null, TravelportAccount::$air_scheme_version);
        $airPriceRequestXML->addAttribute("TargetBranch", TravelportAccount::$branch);
        $billingPointOfSaleInfoXML = $airPriceRequestXML->addChild("BillingPointOfSaleInfo", NULL, TravelportAccount::$common_scheme_version);
        $billingPointOfSaleInfoXML->addAttribute("OriginApplication", "UAPI");
        $airItineraryXML = $airPriceRequestXML->addChild("AirItinerary");
        TravelportCommon::airItineraryObjectToXml($airSegments, $airItineraryXML);
        $airPricingModifier = $airPriceRequestXML->addChild("AirPricingModifiers");
        $airPricingModifier->addAttribute("CurrencyType", $this->searchCriteria->currency);
        $count = 0;
        for ($i = 0; $i < (int) $this->searchCriteria->yetiskinnumber; $i++) {
            $searchPassengerXML = TravelportCommon::addSearchPassengerXML($airPriceRequestXML, "ADT");
             $searchPassengerXML->addAttribute("Key", $count);
            $count++;
        }

        for ($i = 0; $i < (int) $this->searchCriteria->cocuknumber; $i++) {
            TravelportCommon::addSearchPassengerXML($airPriceRequestXML, "CNN", 7);
             $searchPassengerXML = $searchPassengerXML->addAttribute("Key", $count);
            $count++;
        }

        for ($i = 0; $i < (int) $this->searchCriteria->bebeknumber; $i++) {
            $searchPassengerXML = TravelportCommon::addSearchPassengerXML($airPriceRequestXML, "INF");
             $searchPassengerXML->addAttribute("Key", $count);
             $count++;
        }
        TravelportCommon::addAirPricingCommandXML($airPriceRequestXML, $airSegments);
        $xmlString = $airPriceRequestXML->asXML();
        $message = <<<EOM
          <s:Envelope xmlns:s = "http://schemas.xmlsoap.org/soap/envelope/">
            <s:Body xmlns:xsi = "http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd = "http://www.w3.org/2001/XMLSchema">
            $xmlString
            </s:Body>
            </s:Envelope>
EOM;
        unset($airPriceRequestXML);
        return $message;
    }

    public function convertObject($responseXml, $isConverted = FALSE) {

        $airPriceXML = new SimpleXMLElement($responseXml);
        $airPriceXML->formatOutput = true;
        $airPriceXML->registerXPathNamespace('air', TravelportAccount::$air_scheme_version);
        foreach ($airPriceXML->xpath('//air:AirPricingSolution') as $air_price_solution_item) {
            $combinedAirPriceSolution = new CombinedAirPriceSolution();
            $combinedAirPriceSolution->apiCode = TravelportCommon::APICODE;
            $air_price_solution_item_attributes = $air_price_solution_item->attributes();
            $combinedAirPriceSolution->combinedKey = (string) $air_price_solution_item_attributes["Key"][0];
            $combinedAirPriceSolution->totalPrice = (string) $air_price_solution_item_attributes["TotalPrice"][0];
            $combinedAirPriceSolution->basePrice = (string) $air_price_solution_item_attributes["BasePrice"][0];
            $combinedAirPriceSolution->apprixomateTotalPrice = (string) $air_price_solution_item_attributes["ApproximateTotalPrice"][0];
            $combinedAirPriceSolution->approximateBasePrice = (string) $air_price_solution_item_attributes["ApproximateBasePrice"][0];
            //$combinedAirPriceSolution->eq = (string) $air_price_solution_item_attributes["EquivalentBasePrice"][0];
            $combinedAirPriceSolution->taxes = (string) $air_price_solution_item_attributes["Taxes"][0];
            preg_match('/([^a-zA-Z]+)/', $combinedAirPriceSolution->apprixomateTotalPrice, $total_price_match);
            $combinedAirPriceSolution->apprixomateTotalPriceAmount = $total_price_match[0];

            preg_match('/([^a-zA-Z]+)/', $combinedAirPriceSolution->taxes, $tax_price_match);
            $combinedAirPriceSolution->taxesAmount = $tax_price_match[0];
            $combinedAirPriceSolution->approximateBasePriceAmount = $combinedAirPriceSolution->apprixomateTotalPriceAmount - $combinedAirPriceSolution->taxesAmount;
            $combinedAirPriceSolution->airPricingInfoArray = array();
            $adultAirPriceInfo = null;
            foreach ($airPriceXML->xpath('//air:AirPricingInfo') as $airPriceInfoXML) {
                $currentAirPriceInfo = TravelportCommon::airPriceInfoXMLToObject($airPriceInfoXML);
                $combinedAirPriceSolution->airPricingInfoArray[$combinedAirPriceSolution->combinedKey][$currentAirPriceInfo->passengerType] = $currentAirPriceInfo;
                if ($currentAirPriceInfo->passengerType == "ADT") {
                    $adultAirPriceInfo = $combinedAirPriceSolution->airPricingInfoArray[$combinedAirPriceSolution->combinedKey][$currentAirPriceInfo->passengerType];
                }
            }

            $bookingShortInfoArray = $adultAirPriceInfo->bookingShortInfoArray;
            $airSegmentObjectArray = array();
            foreach ($airPriceXML->xpath('//air:AirSegment') as $airSegmentXml) {
                $airSegmentObject = TravelportCommon::airSegmentXMLToObject($airSegmentXml);
                $bookingShortInfo = $bookingShortInfoArray[$airSegmentObject->key];
                $airSegmentObject->bookingCabinClass = $bookingShortInfo->cabinClass;
                array_push($airSegmentObjectArray, $airSegmentObject);
            }
            $legIndexCount = 0;
            $currentAirSegmentObjectArrayIndex = 0;
            foreach ($this->combinedAirPriceSolution->legs as $legObject) {
                $bookPriceVerifyLegObject = clone $legObject;
                $bookPriceVerifyLegObject->resetJourneys();
                $bookPriceVerifyJourneyObject = clone $this->selectedJourneys[$legIndexCount];
                $airSegmentCount = count($bookPriceVerifyJourneyObject->airSegmentKeys);
                $bookPriceVerifyJourneyObject->clearAirSegments();
                $bookPriceVerifyJourneyObject->clearBookingInfoArray();
                for ($i = 0; $i < $airSegmentCount; $i++) {
                    $airSegmentObject = $airSegmentObjectArray[$currentAirSegmentObjectArrayIndex];
                    $bookPriceVerifyJourneyObject->addAirSegment($airSegmentObject);
                    $currentAirSegmentObjectArrayIndex++;
                }
                $bookPriceVerifyLegObject->addAvaibleJourney($bookPriceVerifyJourneyObject);

                $combinedAirPriceSolution->addLeg($bookPriceVerifyLegObject);
                $legIndexCount++;
            }
            return $combinedAirPriceSolution;
        }
    }

}

?>
