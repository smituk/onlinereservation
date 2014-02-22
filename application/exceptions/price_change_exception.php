<?php
   include_once APPPATH.'models/common/error_dto.php';
class PriceChangedException extends Exception{
    public function __construct($message = null) {
        parent::__construct($message,  ErrorCodes::PRICEORSCHEDULECHANGED,NULL);
    }
}

?>

