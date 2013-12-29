<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of fly_apply_book_ssr
 *
 * @author pasa
 * 
 * 
 */

class FlyApplyBookSsr {
    
    const DOCS_STATUS = "DOCS";
    const WCHC_STATUS = "WCHC"; // tekerlekli sandelye
    const INFT_STATUS = "INFT";

    
    public $code;
    public $desc;
    public $freetext;
    public $isRequiredFreeText = FALSE;
    public $status;
    public $airsegmentRef;
    public $carrier;
    
    public   function __construct($code , $desc , $freetext , $isRequşredFreeText = FALSE) {
        $this->code = $code;
        $this->desc = $desc;
        $this->freeText = $freetext;
        $this->isRequiredFreeText = $isRequşredFreeText;
    }
    
    
    public static function getFlyBookSsr($code){
        if($code === FlyApplyBookSsr::DOCS_STATUS){
            return new FlyApplyBookSsr($code, "Passaport information essention ",null, TRUE);
        
        }else if($code == FlyApplyBookSsr::WCHC_STATUS){
            return new FlyApplyBookSsr($code,"Wheelchair is needed",null);
        }else if($code == FlyApplyBookSsr::INFT_STATUS){
            return new FlyApplyBookSsr($code , "Infant traveling in lab" ,null);
        }    
        else{
            return null;
        }
    }
    
    
    
    //put your code here
}

?>
