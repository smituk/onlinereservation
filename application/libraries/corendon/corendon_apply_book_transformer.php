<?php

include_once 'corendon_common.php';
include_once APPPATH . '/interface/xml_transformer.php';
include_once APPPATH . '/models/constants/flight_constants.php';

class CorendonApplyBookTransformer implements XmlTransformer {

    public $name = "CorendonApplyook";
    private $paymentMethodIdentifier;
    private $applyBookInformation;

    public function __construct(FlyApplyBookInformation $applyBookInformation, $paymentMethodIdentifier) {
        $this->applyBookInformation = $applyBookInformation;
        $this->paymentMethodIdentifier = $paymentMethodIdentifier;
    }

    /*
     * <BookFlightResponse xmlns="http://tempuri.org/">
      <BookFlightResult>
      <CONFIRMATIONKEY>6241406</CONFIRMATIONKEY>
      <NOTESANDINFORMATIONS/>
      <TOTALPRICE>319</TOTALPRICE>
      <CURRENCY>EUR</CURRENCY>
      <PAYMENTSURCHAGE/>
      <EQUIVPAYMENTSURCHAGE/>
      <PASSENGER>
      <PASSENGERRESPONSE>
      <LASTNAME>testi</LASTNAME>
      <FIRSTNAME>testi</FIRSTNAME>
      <PASSTYPE>ADT</PASSTYPE>
      <FARE>223</FARE>
      <TAX>90</TAX>
      <CURRENCY>EUR</CURRENCY>
      </PASSENGERRESPONSE>
      </PASSENGER>
      <ERRDETAILCODE>0</ERRDETAILCODE>
      </BookFlightResult>
      </BookFlightResponse>
     */

    public function convertObject($responseXml, $isConverted = FALSE) {
       /*
        if (!$isConverted) {
            return $responseXml;
        }
        * *
        */
        loadClass(APPPATH . '/models/fly_booking/fly_apply_book_result.php');
        loadClass(APPPATH . '/models/fly_booking/fly_apply_book_universal_record.php');
        loadClass(APPPATH . '/models/fly_booking/fly_apply_book_air_reservation.php');
        loadClass(APPPATH . '/models/fly_booking/fly_apply_book_passanger.php');

        $responseXML = new SimpleXMLElement($responseXml);
        $responseXML->registerXPathNamespace("ns", CorendonAccount::getDefaultNameSpace());
        $flyApplyBookResult = new FlyApplyBookResult();
        $flyApplyBookResult->applyBookInformation = $this->applyBookInformation;
        $flyApplyBookResult->apiCode = CorendonCommon::APICODE;



        foreach ($responseXML->xpath("//ns:BookFlightResponse") as $bookFlightResponseXML) {
            $bookFlightResultXML = $bookFlightResponseXML->BookFlightResult;
            $confirmationKey = (string) $bookFlightResultXML->CONFIRMATIONKEY;
            //$confirmationKey = (int) $confirmationKey;
            if (intval($confirmationKey) <= 0) {
                $flyApplyBookResult->errorCode = ErrorCodes::BOOKCREATEUNSUCCESSFULL;
                return $flyApplyBookResult;
            }
            $universalRecord = new UniversalRecord();
            $universalRecord->status = UniversalRecordStatus::ACTIVE;
            $universalRecord->locatorCode = (string) $confirmationKey;
            $universalRecord->bookingTravelers = $this->applyBookInformation->passangers;

            $airReservationInfo = new AirReservation();
            $airReservationInfo->locatorCode = (string) $confirmationKey;
            $airReservationInfo->airSegments = array();
            $airReservationInfo->createDateTime = new DateTime();
            $airReservationInfo->modifiedDateTime = new DateTime();
            $airReservationInfo->supplierLocators = array();
            foreach ($this->applyBookInformation->verifiedCombinedAirPriceSolution->legs as $legObject) {
                foreach ($legObject->getJourneys() as $journey) {
                    foreach ($journey->airSegmentItems as $airsegment) {
                        array_push($airReservationInfo->airSegments, $airsegment);
                        array_push( $airReservationInfo->supplierLocators, array("supplierCode" => $airsegment->carrier, "supplierLocatorCode" => $confirmationKey));
                    }
                }
            }
            $airReservationInfo->airPriceInfos  = $this->applyBookInformation->verifiedCombinedAirPriceSolution->airPricingInfoArray;
            $universalRecord->reservationInfo = $airReservationInfo;
            $flyApplyBookResult->universalRecord = $universalRecord;
            $flyApplyBookResult->errorCode = ErrorCodes::SUCCESS;
            $flyApplyBookResult->pnrStatusCode = PnrStatusCode::SUCCESS;
            return $flyApplyBookResult;
        }

        $flyApplyBookResult->errorCode = ErrorCodes::BOOKCALLINGUNSUCCESSFULL;
        $flyApplyBookResult->errorDesc = "Corendon Servis Request Failed";
        $flyApplyBookResult->userFriendlyErrorDesc = "Pnr Yaratmada Hata ile Karşılaşıldı";
        return $flyApplyBookResult;
    }

    public function prepareXml() {
        $bookFlightXML = new SimpleXMLElement("<myxml></myxml>");
        $bookFlightXML = $bookFlightXML->addChild("BookFlight", null, "http://tempuri.org/");
        $requestXML = $bookFlightXML->addChild("request");

        $passengers = $this->applyBookInformation->passangers;
        $verifiedCombinedAirPriceSolution = $this->applyBookInformation->verifiedCombinedAirPriceSolution;
        $userContact = $this->applyBookInformation->userContact;

        $flightInIdentifier = null;
        $flightOutIdentifier = null;

        $legIndexCount = 0;
        foreach ($verifiedCombinedAirPriceSolution->legs as $legObject) {
            $journeys = $legObject->getJourneys();
            $firstJourney = $journeys[0];
            if ($legIndexCount == 0) {
                $flightOutIdentifier = $firstJourney->identifier;
            } else if ($legIndexCount == 1) {
                $flightInIdentifier = $firstJourney->identifier;
            }
            $legIndexCount++;
        }


        $fareIdentifier = $flightOutIdentifier;
        if ($flightInIdentifier != null) {
            $fareIdentifier = $fareIdentifier . "-" . $flightInIdentifier;
        }
        $requestXML->addChild("FAREIDENTIFIER", $fareIdentifier);
        $requestXML->addChild("FLIGHTIDENTIFIEROUT", $flightOutIdentifier);
        $requestXML->addChild("FLIGHTIDENTIFIERIN", $flightInIdentifier);
        $this->buildPassengerXml($requestXML, $passengers);
        $this->buildContactAddressXML($requestXML, $userContact);
        $requestXML->addChild("LANGUAGECODE", "EN");
        $requestXML->addChild("PAYMENTIDENTIFIER", $this->paymentMethodIdentifier);
        CorendonCommon::buildAgentXML($requestXML, TRUE);

        $bookFlightMessage = $bookFlightXML->asXML();
        $message = <<<EOM
        <s:Envelope xmlns:s = "http://schemas.xmlsoap.org/soap/envelope/">
        <s:Body xmlns:xsi = "http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd = "http://www.w3.org/2001/XMLSchema">
         $bookFlightMessage
        </s:Body>
        </s:Envelope>
EOM;
        return $message;
    }

    private function buildPassengerXml(SimpleXMLElement $requestXML, $passengers) {
        $passengersXML = $requestXML->addChild("PASSENGERS");

        foreach ($passengers as $passenger) {
            $passengerXML = $passengersXML->addChild("PASSENGER");
            if ($passenger->gender == "M") {
                $passengerXML->addChild("TITLE", "Mr");
            } else if ($passenger->gender == "F") {
                $passengerXML->addChild("TITLE", "Ms");
            }
            $passengerXML->addChild("LASTNAME", $passenger->lastName);
            $passengerXML->addChild("FIRSTNAME", $passenger->name);
            if ($passenger->type == "CNN") {
                $passengerXML->addChild("PASSTYPE", "CHD");
            } else {
                $passengerXML->addChild("PASSTYPE", $passenger->type);
            }
            $passengerXML->addChild("DOB", $passenger->DOB->format("Ymd"));
            if ($passenger->type == "INF") {
                $passengerXML->addChild("TRAVELSWITH", 1);
            }

            if (isset($passenger->frequentFlyCardNumber)) {
                $passengerXML->addChild("FREQUENTFLYERNUMBER", $passenger->frequentFlyCardNumber);
            }

            //@TODO ID Card ile ilgili  kod  eklenmesi gerekebilir

            $passengerXML->addChild("GENDER", $passenger->type);
        }
    }

    private function buildContactAddressXML(SimpleXMLElement $requestXML, FlyApplyBookUserContact $userContact) {
        $contactAddressXML = $requestXML->addChild("CONTACTADDRESS");
        $contactAddressXML->addChild("FIRSTNAME", $userContact->name);
        $contactAddressXML->addChild("LASTNAME", $userContact->lastname);
        $contactAddressXML->addChild("ADDRESS1", $userContact->getAddress());
        $contactAddressXML->addChild("ADDRESS2");
        $contactAddressXML->addChild("ADDRESS3");
        $contactAddressXML->addChild("ZIP", $userContact->zipcode);
        $contactAddressXML->addChild("CITY", $userContact->city);
        $contactAddressXML->addChild("COUNTRY", $userContact->country);
        $contactAddressXML->addChild("PHONEHOME", $userContact->tel);
        $contactAddressXML->addChild("PHONEWORK", $userContact->tel);
        $contactAddressXML->addChild("PHONEMOBILE", $userContact->ceptel);
        $contactAddressXML->addChild("FAX");
        $contactAddressXML->addChild("EMAIL", $userContact->email);
        $contactAddressXML->addChild("IDCARDNUMBER");
        $contactAddressXML->addChild("PASSWORD");
        $contactAddressXML->addChild("IDCARDTYPE");
    }

}
?>

