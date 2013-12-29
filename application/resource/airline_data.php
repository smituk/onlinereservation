<?php

include_once APPPATH . '/models/fly_search/airline_company.php';

class AirlineData {

    public $airlineDataArray;
    public static $instance = NULL;

    private function __construct() {
         $ci=& get_instance();
        $query  = $ci->db->query("SELECT * FROM airlines");
        $this->airlineDataArray = array();
        foreach ($query->result() as $airlineJsonObject) {
            if ($airlineJsonObject->iata != null && $airlineJsonObject->iata != "") {
                $airlineData = new AirlineCompany();
                $airlineData->name = $airlineJsonObject->name;
                $airlineData->iataCode = $airlineJsonObject->iata;
                $airlineData->country = $airlineJsonObject->country;
                $airlineData->active = $airlineJsonObject->active;
                $airlineData->code = $airlineJsonObject->iata;
                $this->airlineDataArray[$airlineJsonObject->iata] = $airlineData;
            }
            $combinanationAirline = new AirlineCompany();
            $combinanationAirline->code="XXXX";
            $combinanationAirline->iataCode = "XXXX";
            $combinanationAirline->name = "Kombinasyon";
            $this->airlineDataArray["XXXX"] = $combinanationAirline;      
        }
    }

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new AirlineData();
        }
        return self::$instance;
    }

    public function getAirlineDataArray() {
        return $this->airlineDataArray;
    }

}

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
