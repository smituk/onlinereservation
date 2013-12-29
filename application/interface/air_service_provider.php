<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author pasa
 */
interface AirServiceProvider {
     public function searchFlight($searchCriteria);
     public function convertXMLToCombinedAirPriceSolutions($xmlData , $search_criteria);
     public function bookPriceVerify($combinedAirPriceSolution,$selectedJourneys,$airSegmentArray,$searchCriteria);
     public function applyBook($applyBookInformation);
     public function cancelUniversalRecord(UniversalRecord $universalRecord);
     
}

?>
