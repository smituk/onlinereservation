<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

//<air:AirSegment Key="1T" Group="0" Carrier="TK" FlightNumber="1958" Origin="AMS" Destination="IST" DepartureTime="2013-03-30T14:50:00.000+01:00" ArrivalTime="2013-03-30T19:20:00.000+02:00" FlightTime="210" Distance="1384" ETicketability="Yes" Equipment="738" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail exists" OptionalServicesIndicator="false" AvailabilitySource="AvailStatusTTY">

class AirSegment {

    public $key;
    public $carrier;
    public $carrierName;
    public $flightNumber;
    public $origin;
    public $originAirPort;
    public $destination;
    public $destinationAirPort;
    public $departureTime;
    public $arrivalTime;
    public $flightTime;
    public $distance;
    public $equipment;
    public $eticketAvability;
    public $flightRef;
    public $bookingCounts;
    public $bookingCode;
    public $providerCode;
    public $bookingCabinClass;
    public $avaibleBookingCount;
    public $group;
    public $departureDate;
    public $departureHours;
    public $arrivalDate;
    public $arrivalHours;
    public $operatingCarrier;
    public $operatingFlightNumber;
    public $statu;//HK gibi
    public $sellMessages;
    
    
    
    
}

?>
