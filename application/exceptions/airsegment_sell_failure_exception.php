<?php
   include_once APPPATH.'models/common/error_dto.php';
  class AirSegmentSellFailureException extends Exception{
      public  function __construct($message = null) {
          parent::__construct($message, ErrorCodes::AIRSEGMENTSELLFAILURE);
      }
  }
?>

