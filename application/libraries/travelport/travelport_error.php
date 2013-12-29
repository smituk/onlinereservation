<?php  
class TravelPortErrorCodes{
    const SUCCESS = "00000";
}



class TravelportError{
   public $code = TravelPortErrorCodes::SUCCESS;
   public $service;  
   public $serviceName;
   public $type; // Businiess veya webservisten kaynaklanan bir hata
   public $description;
}


?>

