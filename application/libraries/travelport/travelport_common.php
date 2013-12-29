<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of travelport_common
 *
 * @author pasa
 */
include_once 'travelport_account.php';
include_once 'travelport_error.php';
include_once APPPATH . '/models/fly_search/air_segment.php';
include_once APPPATH . '/models/fly_search/fare_info.php';
include_once APPPATH . '/models/fly_booking/fly_apply_book_passanger.php';
include_once APPPATH . '/models/fly_booking/fly_apply_book_universal_record.php';
include_once APPPATH . '/models/fly_booking/fly_apply_book_air_reservation.php';

class TravelportCommon {

    const APICODE = "TRVPT";
    const DOB_FORMAT = "Y-m-d";

    public static function buildAirPricingCommandXML($airSegments) {
        $airPricingCommandXML = new SimpleXMLElement("<myxml></myxml>");
        $airPricingCommandXML = $airPricingCommandXML->addChild("AirPricingCommand");
        foreach ($airSegments as $airsegment) {
            $airSegmentPricingModifiersXML = $airPricingCommandXML->addChild("AirSegmentPricingModifiers");
            $airSegmentPricingModifiersXML->addAttribute("AirSegmentRef", $airsegment->key);
            $airSegmentPricingModifiersXML->addAttribute("CabinClass", $airsegment->bookingCabinClass);
            /*
              $permittedBookingCodesXML = $airSegmentPricingModifiersXML->addChild("PermittedBookingCodes");
              $bookingCodeXML = $permittedBookingCodesXML->addChild("BookingCode");
              $bookingCodeXML->addAttribute("Code", $airsegment->booking_code);
             * 
             */
        }
        return $airPricingCommandXML->asXML();
    }

    //put your code here
    /*
     * <AirSegment Key="11e13f27-ae0f-4bcf-9ab4-3d88d2e2d9dc" Status="NN" ClassOfService="L" Equipment="752" AvailabilitySource="StatusOverlaid" Origin="ORD" Destination="DEN" DepartureTime="2012-03-19T12:03:00" FlightNumber="606" Group="0" Carrier="UA" ArrivalTime="2012-03-19T13:37:00" ProviderCode="1G" FlightTime="154" TravelTime="154"/>
     */
    /*
      class FlyApplyBookPassanger{
      public $type;
      public $name;
      public $lastName;
      public $gender;
      public $birthday;
      public $birthmonth;
      public $birthyear;
      public $DOB; // datetime object for birthdate
      public $frequentFlyCardNumber;
      public $frequentFlyCardCarrier;
      public $prefix;
      public $suffix;
      public $middlename;

      }
     */
    public static function bookingTravelerToXML($passangers, $userContact) {
        $bookingTravellerXMLMessage = "";
        $bookingTravellerXML = new SimpleXMLElement("<Mydata></Mydata>");
        $passangerCount = 0;
        foreach ($passangers as $passanger) {
            $oneBookingTravellerXML = $bookingTravellerXML->addChild("BookingTraveler", NULL, TravelportAccount::$common_scheme_version);
            $oneBookingTravellerXML->addAttribute("Key", $passanger->key);
            $oneBookingTravellerXML->addAttribute("TravelerType", $passanger->type);
            $oneBookingTravellerXML->addAttribute("Gender", $passanger->gender);
            $oneBookingTravellerXML->addAttribute("DOB", $passanger->DOB->format(self::DOB_FORMAT));
            $bookingTravelerNameXML = $oneBookingTravellerXML->addChild("BookingTravelerName");
            $bookingTravelerNameXML->addAttribute("First", $passanger->name);
            $bookingTravelerNameXML->addAttribute("Last", $passanger->lastName);
            if (isset($passanger->prefix)) {
                $bookingTravelerNameXML->addAttribute("Prefix", $passanger->prefix);
            }
            if (isset($passanger->suffix)) {
                $bookingTravelerNameXML->addAttribute("Suffix", $passanger->suffix);
            }
            if (isset($passanger->middlename)) {
                $bookingTravelerNameXML->addAttribute("Middle", $passanger->middlename);
            }
            if ($passangerCount == 0) {
                $phoneNumberXML = $oneBookingTravellerXML->addChild("PhoneNumber");
                $phoneNumberXML->addAttribute("Type", "Mobile");
                $phoneNumberXML->addAttribute("CountryCode", "90"); // Burada kullanıcını  seçtiğin countyty gore gelecek;
                $phoneNumberXML->addAttribute("Number", $userContact->ceptel);

                $emailXML = $oneBookingTravellerXML->addChild("Email");
                $emailXML->addAttribute("Type", "Personel");
                $emailXML->addAttribute("EmailID", $userContact->email);
            }

            if (isset($passanger->frequentFlyCardNumber)) {
                $loyaltyCardXML = $oneBookingTravellerXML->addChild("LoyaltyCard");
                $loyaltyCardXML->addAttribute("SupplierCode", $passanger->frequentFlyCardCarrier);
                $loyaltyCardXML->addAttribute("CardNumber", $passanger->frequentFlyCardNumber);
            }
            if (isset($passanger->ssr) && count($passanger->ssr) > 0) {
                foreach ($passanger->ssr as $flyApplyBookSsr) {
                    $ssrXML = $oneBookingTravellerXML->addChild("SSR");
                    $ssrXML->addAttribute("Type", $flyApplyBookSsr->code);
                    if ($flyApplyBookSsr->isRequiredFreeText == TRUE && isset($flyApplyBookSsr->freeText)) {
                        $ssrXML->addAttribute("FreeText", $flyApplyBookSsr->freeText);
                    }
                }
            }
            $passangerCount++;
            $bookingTravellerXMLMessage = $bookingTravellerXMLMessage . $oneBookingTravellerXML->asXML();
        }
        return $bookingTravellerXMLMessage;
    }

    /*
     * 
     *  <common_v20_0:BookingTraveler Key="YoR173n5TZS1vhOYDkZZLg==" TravelerType="ADT" DOB="1970-01-01" Gender="M" ElStat="A">
      <common_v20_0:BookingTravelerName First="yunus" Last="kula"/>
     */

    public static function bookingTravelerXMLToObject(SimpleXMLElement $bookingTravelXML) {
        $bookingTraveler = new FlyApplyBookPassanger();
        $bookingTravelXML->registerXPathNamespace("common", TravelportAccount::$common_scheme_version);
        $bookingTravelXMLAttributes = $bookingTravelXML->attributes();
        $bookingTraveler->key = (string) $bookingTravelXMLAttributes["Key"][0];
        $bookingTraveler->type = (string) $bookingTravelXMLAttributes["TravelerType"][0];
        $bookingTraveler->DOB = new DateTime((string) $bookingTravelXMLAttributes["DOB"][0]);
        $bookingTraveler->gender = (string) $bookingTravelXMLAttributes["Gender"][0];

        foreach ($bookingTravelXML->xpath("//common:BookingTravelerName") as $bookingTravelerNameXML) {
            $bookingTravelerNameXMLAttributes = $bookingTravelerNameXML->attributes();
            $bookingTraveler->name = (string) $bookingTravelerNameXMLAttributes["First"][0];
            $bookingTraveler->lastName = (string) $bookingTravelerNameXMLAttributes["Last"][0];
            $bookingTraveler->middlename = (string) $bookingTravelerNameXMLAttributes["Middle"][0];
        }
        /*
         * 
         * <common_v20_0:SSR Key="qReh8j8VTy26sOC37jv7lw==" SegmentRef="vJE18PDxRJ29yPgI/bK/qw==" Status="PN" Type="WCHC" Carrier="UA" ProviderReservationInfoRef="rD7sk6MNSTyZErmy2RkFww==" ElStat="C"/>

         */
        $bookingTravelerSSR = array();
        foreach ($bookingTravelXML->xpath("//common:SSR") as $ssrXML) {
            $ssrXMLAttributes = $ssrXML->attributes();
            $code = (string) $ssrXMLAttributes["Type"][0];
            $ssr = FlyApplyBookSsr::getFlyBookSsr($code);
            $ssr->status = (string) $ssrXMLAttributes["Status"][0];
            $ssr->airsegmentRef = (string) $ssrXMLAttributes["SegmentRef"][0];
            $ssr->carrier = (string) $ssrXMLAttributes["Carrier"][0];
            array_push($bookingTravelerSSR, $ssr);
        }
        if (count($bookingTravelerSSR) > 0) {
            $bookingTraveler->ssr = $bookingTravelerSSR;
        }
        return $bookingTraveler;
    }

    // isRaw eger true ise yaratılan xml string olarak doner , diger taktirde  SimpleXMLElement olarak döner.
    public static function airsegmentObjectToXML(AirSegment $airsegment, $isRawXML = TRUE, $airsegmentSimpleXML = null) {

        $child = null;
        if (!isset($airsegmentSimpleXML)) {
            $airSegmentXML = new SimpleXMLElement("<MyTag></MyTag>");
            $child = $airSegmentXML->addChild("AirSegment");
        } else {
            $child = $airsegmentSimpleXML;
        }
        $child->addAttribute("Key", $airsegment->key);
        $child->addAttribute("Carrier", $airsegment->carrier);
        $child->addAttribute("FlightNumber", $airsegment->flightNumber);
        $child->addAttribute("Origin", $airsegment->origin);
        $child->addAttribute("Destination", $airsegment->destination);
        $child->addAttribute("DepartureTime", $airsegment->departureTime);
        $child->addAttribute("ArrivalTime", $airsegment->arrivalTime);
        $child->addAttribute("ClassOfService", $airsegment->bookingCode);
        $child->addAttribute("ProviderCode", $airsegment->providerCode);
        if (isset($airsegment->operatingCarrier)) {
            $codeShareInfoXML = $child->addChild("CodeshareInfo");
            $codeShareInfoXML->addAttribute("OperatingCarrier", $airsegment->operatingCarrier);
            $codeShareInfoXML->addAttribute("OperatingFlightNumber", $airsegment->operatingFlightNumber);
        }
        $airAvaibleInfoXML = $child->addChild("AirAvailInfo");
        $airAvaibleInfoXML->addAttribute("ProviderCode", $airsegment->providerCode);
        $bookingCodeInfoXML = $airAvaibleInfoXML->addChild("BookingCodeInfo");
        $bookingCodeInfoXML->addAttribute("BookingCounts", $airsegment->bookingCounts);
        $child->addAttribute("Group", $airsegment->group);
        if ($isRawXML == FALSE) {
            return $child;
        }
        return $child->asXML();
    }

    /*
      //arrival_time: "2013-08-20T00:20:00.000+03:00"
      avaible_booking_count: null
      booking_cabin_class: null
      booking_code: null
      booking_counts: null
      carrier: "PS"
      carrierName: "Ukraine International Airlines"
      departure_time: "2013-08-19T20:35:00.000+02:00"
      destination: "KBP"
      distance: "1119"
      equipment: ""
      eticket_avability: ""
      flight_number: "9383"
      flight_ref: null
      flight_time: "165"
      group: "0"
      key: "0T"
      origin: "AMS"
      provider_code: null
     * 
     */

    public static function airSegmentXMLToObject(SimpleXMLElement $air_segment_item) {
        $air_segment_item_attributes = $air_segment_item->attributes();
        $airSegmentObject = new AirSegment();
        $airSegmentObject->key = (string) $air_segment_item_attributes["Key"][0];
        $airSegmentObject->carrier = (string) $air_segment_item_attributes["Carrier"][0];
        $airCompanyObject = AirlineService::getAirlineByIATACode($airSegmentObject->carrier);

        if (isset($airCompanyObject)) {
            $airSegmentObject->carrierName = $airCompanyObject->name;
        } else {
            $airSegmentObject->carrierName = $airSegmentObject->carrier;
        }
        $airSegmentObject->flightNumber = (string) $air_segment_item_attributes["FlightNumber"][0];
        $airSegmentObject->origin = (string) $air_segment_item_attributes["Origin"][0];
        $airSegmentObject->destination = (string) $air_segment_item_attributes["Destination"][0];
        $airSegmentObject->departureTime = (string) $air_segment_item_attributes["DepartureTime"][0];
        $airSegmentObject->arrivalTime= (string) $air_segment_item_attributes["ArrivalTime"][0];
        $airSegmentObject->flightTime = (string) $air_segment_item_attributes["FlightTime"][0];
        $airSegmentObject->distance = (string) $air_segment_item_attributes["Distance"][0];
        $airSegmentObject->equipment = (string) $air_segment_item_attributes["Equipment"][0];
        $airSegmentObject->eticketAvability= (string) $air_segment_item_attributes["ETicketability"][0];
        $airSegmentObject->group = (string) $air_segment_item_attributes["Group"][0];
        $airSegmentObject->providerCode = (string) $air_segment_item_attributes["ProviderCode"][0];
        $airSegmentObject->bookingCode = (string) $air_segment_item_attributes["ClassOfService"][0];
        $airSegmentObject->statu = (string) $air_segment_item_attributes["Status"][0];
        foreach ($air_segment_item->children('air', TRUE) as $air_segment_child_item) {
            $air_segment_child_item_attributes = $air_segment_child_item->attributes();
            if ($air_segment_child_item->getName() === "FlightDetailsRef") {
                $airSegmentObject->flightRef = (string) $air_segment_child_item_attributes["Key"][0];
            } else if ($air_segment_child_item->getName() == "AirAvailInfo") {
                $airSegmentObject->providerCode = (string) $air_segment_child_item_attributes["ProviderCode"][0];
                foreach ($air_segment_child_item->children('air', TRUE) as $child) {
                    if ($child->getName() === "BookingCodeInfo") {
                        $child_attributes = $child->attributes();
                        $airSegmentObject->bookingCounts = (string) $child_attributes["BookingCounts"][0];
                    }
                }
            } else if ($air_segment_child_item->getName() == "CodeshareInfo") {
                $airSegmentObject->operatingCarrier = (string) $air_segment_child_item_attributes["OperatingCarrier"];
                $airSegmentObject->operatingFlightNumber = (string) $air_segment_child_item_attributes["OperatingFlightNumber"];
            }
        }
        $sellMessages = array();
        foreach ($air_segment_item->xpath("//air:SellMessage") as $sellMessageXML) {
            array_push($sellMessages, (string) $sellMessageXML);
        }
        $airsegment_departure_time = new DateTime($airSegmentObject->departureTime);
        $airsegment_arrival_time = new DateTime($airSegmentObject->arrivalTime);
        $airSegmentObject->departureDate = $airsegment_departure_time->format('d.m.Y');

        $airSegmentObject->departureHours = $airsegment_departure_time->format("H:i");
        
        $airSegmentObject->arrivalDate = $airsegment_arrival_time->format("d.m.Y");
        $airSegmentObject->arrivalHours= $airsegment_arrival_time->format("H:i");

        return $airSegmentObject;
    }

    public static function airPriceInfoXMLToObject($airPriceInfoXML, $fareInfoArray = null, $airSegmentArray = null , $legArray = null) {
       
        
        $air_price_info_item = $airPriceInfoXML;
        $air_price_info_item_attributes = $air_price_info_item->attributes();
        $airPricingInfoObject = new AirPricingInfo();
        $airPricingInfoObject->key = (string) $air_price_info_item_attributes["Key"][0];
        $airPricingInfoObject->approximateBasePrice = (string) $air_price_info_item_attributes["ApproximateBasePrice"];
        $airPricingInfoObject->approximateTotalPrice = (string) $air_price_info_item_attributes["ApproximateTotalPrice"][0];
        $airPricingInfoObject->taxes = (string) $air_price_info_item_attributes["Taxes"][0];
        $airPricingInfoObject->totalPrice = (string) $air_price_info_item["TotalPrice"][0];
        $airPricingInfoObject->lastestTicketingTime = (string) $air_price_info_item_attributes["LatestTicketingTime"][0];
        $airPricingInfoObject->platinCarrier = (string) $air_price_info_item_attributes["PlatingCarrier"][0];
        $airPricingInfoObject->refundable = (string) $air_price_info_item_attributes["Refundable"][0];
        preg_match('/([^a-zA-Z]+)/', $airPricingInfoObject->approximateBasePrice, $base_price_match);
        $airPricingInfoObject->approximateBasePriceAmount = $base_price_match[0];
        preg_match('/([^a-zA-Z]+)/', $airPricingInfoObject->approximateBasePrice, $total_price_match);
        $airPricingInfoObject->approximateTotalPriceAmout = $total_price_match[0];
        preg_match('/([^a-zA-Z]+)/', $airPricingInfoObject->taxes, $tax_price_match);
        $airPricingInfoObject->taxesAmount = $tax_price_match[0];
        $airPricingInfoObject->pricingMethod = (string) $air_price_info_item_attributes["PricingMethod"][0];
        $airPricingInfoObject->trueLastDateToTicket = (string) $air_price_info_item_attributes["TrueLastDateToTicket"][0];
        $airPricingInfoObject->bookingShortInfoArray = array();

        $passenger_count = 0;
        foreach ($airPriceInfoXML->children('air', TRUE) as $air_price_solution_child_node) {
            $air_price_solution_child_node_attributes = $air_price_solution_child_node->attributes();
            if ($air_price_solution_child_node->getName() === "PassengerType") {
                $airPricingInfoObject->passengerType = (string) $air_price_solution_child_node_attributes["Code"][0];
                if ($airPricingInfoObject->passengerType == "ADT") {
                    $airPricingInfoObject->passengerTypeDesc = "Yetişkin";
                } else if ($airPricingInfoObject->passengerType == "CNN") {
                    $airPricingInfoObject->passengerTypeDesc = "Çocuk";
                } else if ($airPricingInfoObject->passengerType == "INF") {
                    $airPricingInfoObject->passengerTypeDesc = "Bebek";
                } else {
                    $airPricingInfoObject->passengerTypeDesc = $airPricingInfoObject->passengerType;
                }

                $passenger_count++;
            } else if ($air_price_solution_child_node->getName() === "BookingInfo") {
                $booking_info_object = new BookingInfo();
                $booking_info_object->airSegmentRef = (string) $air_price_solution_child_node_attributes["SegmentRef"][0];
                $booking_info_object->bookingCode = (string) $air_price_solution_child_node_attributes["BookingCode"][0];
                $booking_info_object->cabinClass = (string) $air_price_solution_child_node_attributes["CabinClass"][0];
                $booking_info_object->fareInfoRef = (string) $air_price_solution_child_node_attributes["FareInfoRef"][0];
                $airPricingInfoObject->bookingShortInfoArray[$booking_info_object->airSegmentRef] = $booking_info_object;
            }
        }
        
        $fareInfoList = array();
        if (isset($fareInfoArray) && count($fareInfoArray) > 0) {
            foreach ( $airPricingInfoObject->bookingShortInfoArray as $bookInfoObject){
               
                $fareInfoList[$bookInfoObject->fareInfoRef]  = $fareInfoArray[$bookInfoObject->fareInfoRef];
               // array_push($fareInfoList, $fareInfoArray[$bookInfoObject->fare_info_ref]);
            }
            
        } else {
            foreach ($airPriceInfoXML->xpath("//air:FareInfo") as $fareInfoXML) {
                $fareInfoObject = self::fareInfoXMLToXML($fareInfoXML);
                 array_push($fareInfoList, $fareInfoObject);
            }
        }
        $airPricingInfoObject->passengerCount = $passenger_count;
        $airPricingInfoObject->fareInfoList = $fareInfoList;
        unset($air_price_info_item_attributes);
        return $airPricingInfoObject;
    }

    public static function airPriceInfoObjectToXML(AirPricingInfo $airPriceInfoObject, $isRaw = TRUE, $airPriceInfoSimpleXML = NULL) {
        if (isset($airPriceInfoSimpleXML)) {
            
        } else {
            $airPriceInfoSimpleXML = new SimpleXMLElement("<MyData></MyData>");
            $airPriceInfoSimpleXML = $airPriceInfoSimpleXML->addChild("AirPricingInfo");
        }
        //22AirPricingSolution Key="LEW4LgBoSfCpPgCoMgZG2g==" TotalPrice="EUR1751.61" BasePrice="EUR696.00" ApproximateTotalPrice="EUR1751.61" ApproximateBasePrice="EUR696.00" Taxes="EUR1055.61">
        $airPriceInfoSimpleXML->addAttribute("Key", $airPriceInfoObject->key);
        $airPriceInfoSimpleXML->addAttribute("TotalPrice", $airPriceInfoObject->totalPrice);
        $airPriceInfoSimpleXML->addAttribute("BasePrice", $airPriceInfoObject->approximateBasePrice);
        $airPriceInfoSimpleXML->addAttribute("ApproximateTotalPrice", $airPriceInfoObject->approximateTotalPrice);
        $airPriceInfoSimpleXML->addAttribute("ApproximateBasePrice", $airPriceInfoObject->approximateBasePrice);
        $airPriceInfoSimpleXML->addAttribute("Taxes", $airPriceInfoObject->taxes);
        $airPriceInfoSimpleXML->addAttribute("PricingMethod", $airPriceInfoObject->pricingMethod);
        foreach ($airPriceInfoObject->fareInfoList as $fareInfoObject) {
            $fareInfoXML = $airPriceInfoSimpleXML->addChild("FareInfo");
            $fareInfoXML->addAttribute("Key", $fareInfoObject->key);
            $fareInfoXML->addAttribute("FareBasis", $fareInfoObject->fareBasis);
            $fareInfoXML->addAttribute("PassengerTypeCode", $fareInfoObject->passengerTypeCode);
            $fareInfoXML->addAttribute("Origin", $fareInfoObject->origin);
            $fareInfoXML->addAttribute("Destination", $fareInfoObject->destination);
            $fareInfoXML->addAttribute("EffectiveDate", $fareInfoObject->efectiveDate);
            $fareInfoXML->addAttribute("DepartureDate", $fareInfoObject->departureDate);
            if (isset($fareInfoObject->notValidAfter) && $fareInfoObject->notValidAfter != "") {
                $fareInfoXML->addAttribute("NotValidAfter", $fareInfoObject->notValidAfter);
            }
            if (isset($fareInfoObject->notValidBefore) && $fareInfoObject->notValidBefore != "") {
                $fareInfoXML->addAttribute("NotValidBefore", $fareInfoObject->notValidBefore);
            }
            $fareRuleKeyXML = $fareInfoXML->addChild("FareRuleKey", $fareInfoObject->fareRuleKey);
            $fareRuleKeyXML->addAttribute("FareInfoRef", $fareInfoObject->key);
            $fareRuleKeyXML->addAttribute("ProviderCode", $fareInfoObject->providerCode);
        }



        /**
         *   public $booking_code;
          public $cabin_class;
          public $fare_info_ref;
          public $air_segment_ref;
         */
        foreach ($airPriceInfoObject->booking_short_info_array as $bookInfoObject) {
            $bookingInfoXML = $airPriceInfoSimpleXML->addChild("BookingInfo");
            $bookingInfoXML->addAttribute("BookingCode", $bookInfoObject->booking_code);
            $bookingInfoXML->addAttribute("CabinClass", $bookInfoObject->cabin_class);
            $bookingInfoXML->addAttribute("FareInfoRef", $bookInfoObject->fare_info_ref);
            $bookingInfoXML->addAttribute("SegmentRef", $bookInfoObject->air_segment_ref);
        }
        if ($isRaw == FALSE) {
            return $airPriceInfoSimpleXML;
        }
        return $airPriceInfoSimpleXML->asXML();
    }

    public static function fareInfoXMLToXML(SimpleXMLElement $fareInfoXML) {
        
        
        $fareInfoObject = new FareInfo();

        $fareInfoXMLAttributes = $fareInfoXML->attributes();
        $fareInfoObject->key = (string) $fareInfoXMLAttributes["Key"][0];
        $fareInfoObject->fareBasis = (string) $fareInfoXMLAttributes["FareBasis"][0];
        $fareInfoObject->passengerTypeCode = (string) $fareInfoXMLAttributes["PassengerTypeCode"][0];
        $fareInfoObject->origin = (string) $fareInfoXMLAttributes["Origin"][0];
        $fareInfoObject->destination = (string) $fareInfoXMLAttributes["Destination"][0];
        $fareInfoObject->efectiveDate = (string) $fareInfoXMLAttributes["EffectiveDate"][0];
        $fareInfoObject->departureDate = (string) $fareInfoXMLAttributes["DepartureDate"][0];
        $fareInfoObject->notValidAfter = (string) $fareInfoXMLAttributes["NotValidAfter"][0];
        $fareInfoObject->notValidBefore = (string) $fareInfoXMLAttributes["NotValidBefore"][0];
    
        
        $fareInfoXMLChildren   = $fareInfoXML->children("air");
        
       // echo print_r($fareInfoXML);
        if(isset($fareInfoXML->FareRuleKey)){
            
             $fareInfoObject->fareRuleKey = (string)$fareInfoXML->FareRuleKey;
             $fareInfoObject->providerCode = "1G";
        }
      // echo $fareInfoXML->asXml();
        if(isset($fareInfoXML->BaggageAllowance)){
            
            if(isset($fareInfoXML->BaggageAllowance->NumberOfPieces)){
                
                $fareInfoObject->numberOfAllowedBaggagePieces = (string)$fareInfoXML->BaggageAllowance->NumberOfPieces;
            }else if(isset ($fareInfoXML->BaggageAllowance->MaxWeight)){
                $maxWeightAttributes = $fareInfoXML->BaggageAllowance->MaxWeight->attributes();
                $fareInfoObject->maxWeightOfAllowedBaggage = (string)$maxWeightAttributes["Value"][0];
                $fareInfoObject->weightUnit = (string)$maxWeightAttributes["Unit"][0];
            }
        }

        return $fareInfoObject;
    }

    public static function airItineraryObjectToXml($airsegmentArray) {
        $xml = "";
        foreach ($airsegmentArray as $airsegment) {
            $xml = $xml . self::airsegmentObjectToXML($airsegment);
        }
        return "<AirItinerary>" . $xml . "</AirItinerary>";
    }

    public static function buildPassangerOption($yetiskin_number, $cocuk_number, $bebek_number) {
        $message = "";
        for ($i = 0; $i < (int) $yetiskin_number; $i++)
            $message .= '<SearchPassenger xmlns = "http://www.travelport.com/schema/common_v20_0" PricePTCOnly="false" Code="ADT"/>';

        for ($i = 0; $i < (int) $cocuk_number; $i++)
            $message .= '<SearchPassenger xmlns = "http://www.travelport.com/schema/common_v20_0" PricePTCOnly="false"  Age="7" Code="CNN"/>';

        for ($i = 0; $i < (int) $bebek_number; $i++)
            $message .= '<SearchPassenger xmlns = "http://www.travelport.com/schema/common_v20_0" PricePTCOnly="false" Code="INF"/>';

        return $message;
    }

    public static function universalRecordXMLToObject(SimpleXMLElement $universalRecordXML) {
        $universalRecordXML->registerXPathNamespace("universal", TravelportAccount::$universal_scheme_version);
        $universalRecord = new UniversalRecord();
        $universalRecordXMLAttributes = $universalRecordXML->attributes();
        $universalRecord->locatorCode = (string) $universalRecordXMLAttributes["LocatorCode"][0];
        $universalRecord->status = (string) $universalRecordXMLAttributes["Status"][0];
        $bookingTravelers = array();
        $universalRecordXML->registerXPathNamespace("common", TravelportAccount::$common_scheme_version);

        foreach ($universalRecordXML->xpath("//common:BookingTraveler") as $bookingTravelXML) {
            array_push($bookingTravelers, TravelportCommon::bookingTravelerXMLToObject($bookingTravelXML));
        }
        $universalRecord->bookingTravelers = $bookingTravelers;
        $universalRecordXML->registerXPathNamespace("air", TravelportAccount::$air_scheme_version);
        foreach ($universalRecordXML->xpath("//air:AirReservation") as $airReservationXML) {
            $universalRecord->reservationInfo = TravelportCommon::airReservationRecordXMLToObject($airReservationXML);
        }

        return $universalRecord;
    }

    /*
      <air:AirReservation LocatorCode="ZKWL3M" CreateDate="2013-10-27T18:47:17.634+00:00" ModifiedDate="2013-10-27T18:47:18.673+00:00">

     */

    public static function airReservationRecordXMLToObject(SimpleXMLElement $airReservationXML) {
        $airReservationXML->registerXPathNamespace("air", TravelportAccount::$air_scheme_version);
        $airReservationXMLAttributes = $airReservationXML->attributes();
        $airReservation = new AirReservation();
        $airReservation->locatorCode = (string) $airReservationXMLAttributes["LocatorCode"][0];
        $airReservation->createDateTime = new DateTime((string) $airReservationXMLAttributes["CreateDate"][0]);
        $airReservation->modifiedDateTime = new DateTime((string) $airReservationXMLAttributes["ModifiedDate"][0]);
        $supplierLocators = array();
        $airReservationXML->registerXPathNamespace("common", TravelportAccount::$common_scheme_version);
        foreach ($airReservationXML->xpath("//common:SupplierLocator") as $supplierLocatorXML) {
            $supplierLocatorXMLAttributes = $supplierLocatorXML->attributes();
            $supplierCode = (string) $supplierLocatorXMLAttributes["SupplierCode"][0];
            $supplierLocatorCode = (string) $supplierLocatorXMLAttributes["SupplierLocatorCode"][0];
            array_push($supplierLocators, array("supplierCode" => $supplierCode, "supplierLocatorCode" => $supplierLocatorCode));
        }
        $airReservationXML->registerXPathNamespace("air", TravelportAccount::$air_scheme_version);
        $airReservation->supplierLocators = $supplierLocators;
        $airSegments = array();
        foreach ($airReservationXML->xpath("//air:AirSegment") as $airSegmentXML) {
            array_push($airSegments, TravelportCommon::airSegmentXMLToObject($airSegmentXML));
        }
        $airReservation->airSegments = $airSegments;
        $airPricingInfos = array();
        foreach ($airReservationXML->xpath("//air:AirPricingInfo") as $airPricingInfoXML) {
            array_push($airPricingInfos, TravelportCommon::airPriceInfoXMLToObject($airPricingInfoXML));
        }
        $airReservation->airPriceInfos = $airPricingInfos;
        return $airReservation;
    }

    public static function getErrorStatu(SimpleXMLElement $responseSimpleXMLElement, $transformer) {
        $error = new TravelportError();
        $responseSimpleXMLElement->registerXPathNamespace("SOAP", "http://schemas.xmlsoap.org/soap/envelope");
        foreach ($responseSimpleXMLElement->xpath("//SOAP:Fault") as $faultXML) {
            $faultString = NULL;
            foreach ($faultXML->children() as $faultXMLChild) {
                if ($faultXMLChild->getName() == "faultstring") {
                    $faultString = (string) $faultXMLChild;
                    break;
                }
            }
            $faultXML->registerXPathNamespace("common", TravelportAccount::$common_scheme_version);
            foreach ($faultXML->xpath("//common:Code") as $errorCodeXML) {
                $error->code = (string) $errorCodeXML;
                break;
            }
            foreach ($faultXML->xpath("//common:Service") as $serviceXML) {
                $error->service = (string) $serviceXML;
                break;
            }
            foreach ($faultXML->xpath("//common:Type") as $typeXML) {
                $error->type = (string) $typeXML;
                break;
            }
            foreach ($faultXML->xpath("//common:Description") as $descriptionXML) {
                $error->description = $faultString . "-" . (string) $descriptionXML;
                break;
            }
            break;
        }
        if ($transformer != NULL) {
            $error->serviceName = $transformer->name;
        }
        return $error;
    }

}

?>
