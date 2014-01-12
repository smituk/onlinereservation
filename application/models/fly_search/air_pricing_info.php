<?php

class AirPricingInfo {

    public $key;
    public $lastestTicketingTime;
    public $trueLastDateToTicket;
    public $platinCarrier;
    public $refundable;
    public $passengerType;
    public $passengerTypeDesc;
    public $passengerCount;
    public $bookingShortInfoArray; // book_info.php deki dataları içerir.
    public $totalPrice;
    public $approximateTotalPrice;
    public $approximateBasePrice;
    public $taxes;
    public $approximateTotalPriceAmout; // burda para birimsiz hali yani parse edilmii miktar
    public $taxesAmount;
    public $approximateBasePriceAmount;
    public $fareInfoList;// fare info array;
    public $pricingMethod;
    
    
    public function addBookingInfo(BookingInfo $bookingInfoOject){
        if(!isset($this->bookingShortInfoArray)){
            $this->bookingShortInfoArray = array();
        }
        $this->bookingShortInfoArray[$bookingInfoOject->airSegmentRef] = $bookingInfoOject;
    }
    
   

}

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
