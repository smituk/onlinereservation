<?php

class BookingInfo {

    public $bookingCode;
    public $cabinClass;
    public $fareInfoRef;
    public $airSegmentRef;
    
    public function __construct($bookingCode = null , $cabinClass = null , $fareInfoRef = null , $airSegmentRef = "null") {
        $this->bookingCode = $bookingCode;
        $this->cabinClass = $cabinClass;
        $this->fareInfoRef = $fareInfoRef;
        $this->airSegmentRef = $airSegmentRef;
    }

}

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
