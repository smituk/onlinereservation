<?php

class Fly_search_criteria extends CI_Model {
    public $searchAirLegs;
    public $boardingCode;
    public $landingCode;
    public $godate;
    public $returndate;
    public $dateoption;
    public $flydirection;
    public $yetiskinnumber = 0;
    public $bebeknumber = 0;
    public $cocuknumber = 0;
    public $currency = "EUR";
    public $cabinclass;
    public $flighttype;
    public $preferredCarriers;
    public $disfavoredCarriers;
    public $permitedCarriers;
    public $exludedCarriers;
    public $prohibitedCarriers;
    public $affiliteId;
    public $aheadDateInterval  = 0;
    public $backDateInterval  = 0;

    public function getInstance() {
        return $this;
    }

    /*
     * To change this template, choose Tools | Templates
     * and open the template in the editor.
     */
}

?>
