<?php
  class MarmaraPasanger extends CI_Model{
      private $id;
      
      public function  getId(){
          return $this->id;
      }
      
      public function setId($id){
          $this->id = $id;
      }
      
      
  }

?>
