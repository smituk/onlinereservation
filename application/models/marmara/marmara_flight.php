<?php
 class MarmaraFlight extends CI_Model{
     private $id;
     public $xmlApiId;
     public $airlineCompanyId; // database de code diye alana karsılık gelmekte.
     public $flightNumber;
     public $year;
     public $date;
     public $origin;//parkur1;
     public $destination; //parkur2;
     public $intermetiadeOrigin; //ara parkur
     public $originFlightTime;//
     public $destinationFlightTime;
     public $infatCost;
     public $infantSellPrice;
     public $infantBaggage;
     public $totalSeat;
     public $backupSeat;//yedek koltuk;
     public $warningSeat;//uyarı koltuk;
     
      public function  getId(){
          return $this->id;
      }
      
      public function setId($id){
          $this->id = $id;
      }
 }


?>