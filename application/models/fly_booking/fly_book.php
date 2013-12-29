<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of fly_book
 *
 * @author pasa
 */
include_once APPPATH . '/models/common/service_locator.php';
include_once APPPATH . 'models/fly_search/combined_air_price_solution.php';
include_once APPPATH . '/models/fly_search/fly_search_criteria.php';
class Fly_book extends CI_Model {
    
    public function bookPriceVerify($combinedAirPriceSolution , $selectedJourneyArray, $airSegmentArray,$searchCriteria){
       $service  = ServiceLocator::getInstance()->getAirServiceProvider($combinedAirPriceSolution->apiCode);
       return $service->bookPriceVerify($combinedAirPriceSolution,$selectedJourneyArray,$airSegmentArray,$searchCriteria);
    }
    public function applyBook($applyBookInformation){
        
        $service  = ServiceLocator::getInstance()->getAirServiceProvider($applyBookInformation->verifiedCombinedAirPriceSolution->apiCode);
        $flyApplyBookResult = $service->applyBook($applyBookInformation);
        //TODO  buraya ilgili kayıtlar atılacak;
        return $flyApplyBookResult;
    }
    
   
}

?>
