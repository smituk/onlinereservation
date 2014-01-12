<?php

class Fly_search_criteria extends CI_Model {
    public $searchAirLegs;
    public $boardingCode;
    public $landingCode;
    public $godate;
    public $returndate;
    public $dateoption;
    public $flydirection;
    public $yetiskinnumber;
    public $bebeknumber;
    public $cocuknumber;
    public $currency = "EUR";
    public $cabinclass;
    public $flighttype;
    public $preferredCarriers;
    public $disfavoredCarriers;
    public $permitedCarriers;
    public $exludedCarriers;
    public $prohibitedCarriers;
    public $affiliteId;

    public function getInstance() {
        return $this;
    }

    /*
     * To change this template, choose Tools | Templates
     * and open the template in the editor.
     */
}

?>
