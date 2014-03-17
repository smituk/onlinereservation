<?php

 class MarmaraReservation extends CI_Model{
     private $id;
     public $clientId;
     public $direciton;
     public $recordType;
     public $recordNo;
     public $pnr;
     public $apiXmlPnr; // tur operator pnr;
     public $airReservationPnr;
     public $agencyId;
     public $statu;
     public $optionDate;
     public $pts = "N";
     public $commission;
     public $paymentId = 0;
     public $recordDate;
     public $recordUser; // kaydeden kullanıcı
     public $recordLevel;
     public $recordUpdateUser; // duzenleyen kullanıcı
     public $recordUpdateDate;
     public $recordUpdateLevel;
     public $note;
     public $explain2;
     public $lockedUser; //kitleyen
     public $lockedDate;
     public $lockedLevel;
     
  
     public function __construct() {
         $this->recordDate = new DateTime(); 
     }
      public function  getId(){
          return $this->id;
      }
      
      public function setId($id){
          $this->id = $id;
      }
 }


?>
