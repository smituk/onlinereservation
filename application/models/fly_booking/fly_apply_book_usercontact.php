<?php
   class FlyApplyBookUserContact {
       public $name;
       public $lastname;
       public $gender;
       public $email;
       public $tel;
       public $ceptel;
       public $city;
       public $country;
       public $zipcode;
       public $address;
       
       
       
       public function getAddress(){
           if($this->address == null){
               return $this->city."-".$this->country."-".$this->zipcode;
           }
           return $this->address;
       }
       
   }
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
