<?php
class ErrorCodes{
    const SUCCESS = "00000";
    
    const PRICEORSCHEDULECHANGED = "10001";
    const AIRSEGMENTSELLFAILURE  = "10002";
    const AIRSEGMENTNOTHK        =  "10003";
    const NOTFOUNDAIRPRICESOLUTION = "10004";
    
    const BOOKNOTCANCELED        ="30001";
}
class ErrorDTO {
    public $errorCode;
    public $errorDesc;
    public $userFriendlyErrorDesc;
    public $serviceName;
}

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

