<?php
   class Ajax_response extends CI_Model{
       public $data;
       public $error_type;
       public $error_code;
       public $short_info;
       
       
       public function getResponseArray(){
           $response_array = array("data"=>  $this->data,
                                   "error_code" => $this->error_code,
                                   "error_type" => $this->error_type,
                                   "short_info" => $this->short_info);
           return $response_array;
       }
   }
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
