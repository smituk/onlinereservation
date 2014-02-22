<?php
include_once APPPATH.'models/common/error_dto.php';
class PnrNotCreatedException extends Exception{
    public function __construct($message = null, $code = null) {
        parent::__construct($message, $code);
    }
}
  
?>