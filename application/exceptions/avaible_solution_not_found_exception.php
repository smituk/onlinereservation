<?php
   include_once APPPATH.'models/common/error_dto.php';
   class AvaibleSolutionNotFoundException extends Exception{
        
        
       public function __construct($message = null) {
           
           parent::__construct($message, ErrorCodes::NOTFOUNDAIRPRICESOLUTION);
       }
       
       public function  setMessage($message){
           $this->message = $message;
       }
   }

?>