<?php
  include_once APPPATH . '/models/common/error_dto.php';
  class FlyApplyBookResult extends ErrorDTO{
      public $applyBookInformation; // request;
      public $airChangedSolution;//
      public $universalRecord;
      public $airSegmentSellFailureInfo;
      public $pnrStatusCode;// Bu kod pnrin biletlenebilirliğini gösteris
      public $apiCode;
  }
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
