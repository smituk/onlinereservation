<?php

include_once APPPATH . '/helpers/session_helper.php';
include_once APPPATH . '/helpers/fly_search_helper.php';
include_once APPPATH . '/models/constants/flight_constants.php';
include_once APPPATH . '/models/fly_search/filtered_combined_air_price_solution.php';
include_once APPPATH . '/models/common/service_locator.php';
include_once APPPATH . '/models/fly_search/search_air_leg.php';
include_once APPPATH . '/models/fly_search/air_search_location.php';
include_once APPPATH . '/services/param_service.php';

class Fly_search extends CI_Model {

    public function search($search_criteria = null) {
        if ($search_criteria == null) {
            return null;
        }
        $session = new Session(300);
        $session->set(Fly_Constant::SESSION_SEARCH_CRITERIA_PARAMETER, $search_criteria);
        return $this->sendMessageAPIs($search_criteria);
    }

    private function sendMessageAPIs($search_criteria) {
        $activeApiArray = ParamService::getActiveApi();
        loadClass(APPPATH . '/models/fly_search/low_fare_search_result.php');
        $totalLowFareSearchResult = new LowFareSearchResult();
        $totalLowFareSearchResult->airSegmentArray = array();
        $totalLowFareSearchResult->fareInfoArray = array();
        $totalLowFareSearchResult->combinedAirPriceSolutionArray = array();
        $totalLowFareSearchResult->airportArray = array();
        $totalLowFareSearchResult->airlineArray = array();
        foreach ($activeApiArray as $apiCode) {

            //$service = ServiceLocator::getInstance()->getAirServiceProvider(Fly_Constant::TRAVELPORT_API_CODE);
            //$service = ServiceLocator::getInstance()->getAirServiceProvider(Fly_Constant::CORENDON_API_CODE);
            $service = ServiceLocator::getInstance()->getAirServiceProvider($apiCode);
            $responseXMLData = $service->searchFlight($search_criteria);
            $lowFareSearchResult = $service->convertXMLToCombinedAirPriceSolutions($responseXMLData, $search_criteria);
            //file_put_contents("ccccc.json", json_encode($lowFareSearchResult));
            if ($lowFareSearchResult->errorCode == ErrorCodes::SUCCESS) {
                $totalLowFareSearchResult->airSegmentArray += $lowFareSearchResult->airSegmentArray;
                $totalLowFareSearchResult->fareInfoArray += $lowFareSearchResult->fareInfoArray;
                $totalLowFareSearchResult->combinedAirPriceSolutionArray += $lowFareSearchResult->combinedAirPriceSolutionArray;
                $totalLowFareSearchResult->airportArray += $lowFareSearchResult->airportArray;
                $totalLowFareSearchResult->airlineArray += $lowFareSearchResult->airlineArray;
            }
            unset($lowFareSearchResult);
        }

        uasort($totalLowFareSearchResult->combinedAirPriceSolutionArray, "combinedAirPriceSolutionSorter");
        $session = new Session(300);
        $session->set(Fly_Constant::SESSION_COMBINED_AIR_PRICE_SOLUTIONS_PARAMETER, $totalLowFareSearchResult);
        return $totalLowFareSearchResult;
    }

    public function getFlightSearchResult() {
        $session = new Session(300);
        return $session->get(Fly_Constant::SESSION_COMBINED_AIR_PRICE_SOLUTIONS_PARAMETER);
    }

    public function getSearchCriteria() {
        $session = new Session(300);
        return $session->get(Fly_Constant::SESSION_SEARCH_CRITERIA_PARAMETER);
    }

    public function searchNavDay($action = null) {
        if ($action != null) {

            $currentSearchCriteria = $this->getSearchCriteria();
            $searchAirlegs = array_values($currentSearchCriteria->searchAirLegs);
            $firstSearchAirleg = $searchAirlegs[0];
            $lastSearchAirLeg = $searchAirlegs[count($searchAirlegs) - 1];
            if ($action == "goprev") { // kalkış gununden 1 gun onceki
                $goDateTime = strtotime($currentSearchCriteria->godate);
                $goDateTime = strtotime('-1 day', $goDateTime);
                $currentSearchCriteria->godate = date("Y-m-d", $goDateTime);
                $firstSearchAirleg->searchDepartureTime = date("Y-m-d", $goDateTime);
                $currentSearchCriteria->searchAirLegs[0]->searchDepartureTime = date("Y-m-d", $goDateTime);
            } else if ($action == "gonext") { // kalkısgununden 1 gun sonra
                $goDateTime = strtotime($currentSearchCriteria->godate);
                $goDateTime = strtotime('+1 day', $goDateTime);
                $currentSearchCriteria->godate = date("Y-m-d", $goDateTime);
                $firstSearchAirleg->searchDepartureTime = date("Y-m-d", $goDateTime);
            } else if ($action == "returnprev") { // donus gununden 1 gun once
                $returnDateTime = strtotime($currentSearchCriteria->returndate);
                $returnDateTime = strtotime("-1 day", $returnDateTime);
                $currentSearchCriteria->returndate = date("Y-m-d", $returnDateTime);
                $lastSearchAirLeg->searchDepartureTime = date("Y-m-d", $returnDateTime);
            } else if ($action == "returnnext") { // donus gununden 1 gun sonra
                $returnDateTime = strtotime($currentSearchCriteria->returndate);
                $returnDateTime = strtotime("+1 day", $returnDateTime);
                $currentSearchCriteria->returndate = date("Y-m-d", $returnDateTime);
                $lastSearchAirLeg->searchDepartureTime = date("Y-m-d", $returnDateTime);
            }
            $searchAirlegs[0] = $firstSearchAirleg;
            $searchAirlegs[count($searchAirlegs) - 1] = $lastSearchAirLeg;
            $currentSearchCriteria->searchAirLegs = $searchAirlegs;
            return $this->search($currentSearchCriteria);
        }
        return null;
    }

    public function applyFilter($filterCriteriaObject = null) {

        if ($filterCriteriaObject == null) {
            return null;
        }


        $lowFareSearchResult = $this->getFlightSearchResult();

        $filteredCombinedAirPriceSolutionArray = array();

        foreach ($lowFareSearchResult->combinedAirPriceSolutionArray as $combinedAirPriceSolution) {
            $filtered = true;
            $clonedCombinedAirPriceSolution = clone $combinedAirPriceSolution;
            //fiyat filtresi için 
            if (isset($filterCriteriaObject->{Fly_Constant::FILTER_MIN_PRICE_PARAMETER}) && isset($filterCriteriaObject->{Fly_Constant::FILTER_MAX_PRICE_PARAMETER})) {
                if ($filtered && !((int) $clonedCombinedAirPriceSolution->apprixomateTotalPriceAmount >= (int) $filterCriteriaObject->{Fly_Constant::FILTER_MIN_PRICE_PARAMETER} && (int) $clonedCombinedAirPriceSolution->apprixomateTotalPriceAmount <= (int) $filterCriteriaObject->{Fly_Constant::FILTER_MAX_PRICE_PARAMETER})) {
                    $filtered = false;
                    continue;
                }
            }

            $oneByOneAirPriceSolutions = $this->convertCombinedToOneAirSolutionRecursively(array_values($clonedCombinedAirPriceSolution->legs), NULL, 0);

            if (isset($filterCriteriaObject->{Fly_Constant::FILTER_STOP_COUNT_PARAMTER}) || isset($filterCriteriaObject->{Fly_Constant::FILTER_IS_NO_STOP_PARAMETER}) || isset($filterCriteriaObject->{Fly_Constant::FILTER_SELECTED_AIRLINE_COMPANIES_PARAMETER})) {
                $legToJourneysMappingArray = array();
                $stopCountArray = array();
                $carrierCountArray = array();
                foreach ($oneByOneAirPriceSolutions as $oneAirPriceSolution) {
                    $stopCount = 0;
                    $carrrier = null;
                    foreach ($oneAirPriceSolution["journeys"] as $journey) {
                        if ($stopCount < $journey->getStopCount()) {
                            $stopCount = $journey->getStopCount();
                        }
                        $carriers = $journey->getCarriers($lowFareSearchResult->airSegmentArray);
                        $currentCarrier = $carriers[0];
                        if ($carrrier == NULL) {
                            $carrrier = $carriers[0];
                            if (count($carriers) > 1) {
                                $currentCarrier = Fly_Constant::COMBINATION_AIR_COMPANY;
                            }
                        } else {

                            if (count($carriers) > 1) {
                                $currentCarrier = Fly_Constant::COMBINATION_AIR_COMPANY;
                            }
                            if ($currentCarrier != $carrrier) {
                                $currentCarrier = Fly_Constant::COMBINATION_AIR_COMPANY;
                            }
                        }
                        $carrrier = $currentCarrier;
                    }
                    if ($stopCount > 2) {
                        $stopCount = 2;
                    }
                    $stopCountArray[$stopCount][(int) $oneAirPriceSolution["key"]] = $oneAirPriceSolution;
                    $carrierCountArray[$carrrier][(int) $oneAirPriceSolution["key"]] = $oneAirPriceSolution;
                }

                $noStopAirSolutions = isset($stopCountArray[0]) ? $stopCountArray[0] : null;
                $oneStopAirSolutions = isset($stopCountArray[1]) ? $stopCountArray[1] : null;
                $moreThanOneStopAirSolutions = isset($stopCountArray[2]) ? $stopCountArray[2] : null;

                if (isset($filterCriteriaObject->{Fly_Constant::FILTER_STOP_COUNT_PARAMTER})) {

                    if ($filterCriteriaObject->{Fly_Constant::FILTER_STOP_COUNT_PARAMTER} == "0") {
                        if ($noStopAirSolutions == null) {
                            $filtered = false;
                            continue;
                        }

                        foreach ($noStopAirSolutions as $noStopAirSolution) {
                            $legToJourneysMappingArray = Fly_seach_helper::addAirSolutionToLegJourneysMappingArray($noStopAirSolution, $legToJourneysMappingArray);
                        }
                        // diger stopları cıkar;
                    } else if ($filterCriteriaObject->{Fly_Constant::FILTER_STOP_COUNT_PARAMTER} == "1") {
                        if ($oneStopAirSolutions == null) {
                            $filtered = false;
                            continue;
                        }
                        foreach ($oneStopAirSolutions as $oneStopAirSolution) {
                            $legToJourneysMappingArray = Fly_seach_helper::addAirSolutionToLegJourneysMappingArray($oneStopAirSolution, $legToJourneysMappingArray);
                        }
                        // diger stopları cıkar
                    } else if ($filterCriteriaObject->{Fly_Constant::FILTER_STOP_COUNT_PARAMTER} == "2") {
                        if ($moreThanOneStopAirSolutions == null) {
                            $filtered = false;
                            continue;
                        }
                        foreach ($moreThanOneStopAirSolutions as $moreThanOneStopAirSolution) {
                            $legToJourneysMappingArray = Fly_seach_helper::addAirSolutionToLegJourneysMappingArray($moreThanOneStopAirSolution, $legToJourneysMappingArray);
                        }
                        // diger stopları cıkar journey
                    }
                    //One Airline Company için
                    if (isset($filterCriteriaObject->{Fly_Constant::FILTER_AIRLINE_COMPANY})) {
                        $airlineCompany = $filterCriteriaObject->{Fly_Constant::FILTER_AIRLINE_COMPANY};
                        if (!isset($carrierCountArray[$airlineCompany])) {
                            $filtered = false;
                            continue;
                        }
                        $tempLegToJourneysMappingArray = array();
                        foreach ($carrierCountArray[$airlineCompany] as $filteredCarrierAirSolution) {
                            $tempLegToJourneysMappingArray = Fly_seach_helper::addAirSolutionToLegJourneysMappingArray($filteredCarrierAirSolution, $tempLegToJourneysMappingArray);
                        }
                      
                        $legToJourneysMappingArray = Fly_seach_helper::intersectLegJourneysMappingArray($legToJourneysMappingArray, $tempLegToJourneysMappingArray);
                         
                        
                        }
                } else {
                    $isStopCountFilterEntered = false;
                    if (isset($filterCriteriaObject->{Fly_Constant::FILTER_IS_NO_STOP_PARAMETER}) && (int) $filterCriteriaObject->{Fly_Constant::FILTER_IS_NO_STOP_PARAMETER} == 1) {
                        if (isset($noStopAirSolutions)) {
                            foreach ($noStopAirSolutions as $noStopAirSolution) {
                                $legToJourneysMappingArray = Fly_seach_helper::addAirSolutionToLegJourneysMappingArray($noStopAirSolution, $legToJourneysMappingArray);
                            }
                        }
                        $isStopCountFilterEntered = true;
                    }
                    if (isset($filterCriteriaObject->{Fly_Constant::FILTER_ONE_STOP_PARAMETER}) && (int) $filterCriteriaObject->{Fly_Constant::FILTER_ONE_STOP_PARAMETER} == 1) {
                        if (isset($oneStopAirSolutions)) {
                            foreach ($oneStopAirSolutions as $oneStopAirSolution) {
                                $legToJourneysMappingArray = Fly_seach_helper::addAirSolutionToLegJourneysMappingArray($oneStopAirSolution, $legToJourneysMappingArray);
                            }
                        }
                        $isStopCountFilterEntered = true;
                    }
                    if (isset($filterCriteriaObject->{Fly_Constant::FILTER_TWO_MORE_STOP_PARAMETER}) && (int) $filterCriteriaObject->{Fly_Constant::FILTER_TWO_MORE_STOP_PARAMETER} == 1) {
                        if (isset($moreThanOneStopAirSolutions)) {
                            foreach ($moreThanOneStopAirSolutions as $moreThanOneStopAirSolution) {
                                $legToJourneysMappingArray = Fly_seach_helper::addAirSolutionToLegJourneysMappingArray($moreThanOneStopAirSolution, $legToJourneysMappingArray);
                            }
                        }
                        $isStopCountFilterEntered = true;
                    }


                    if (isset($filterCriteriaObject->{Fly_Constant::FILTER_SELECTED_AIRLINE_COMPANIES_PARAMETER})) {
                        $selectedAirlineCompanies = $filterCriteriaObject->{Fly_Constant::FILTER_SELECTED_AIRLINE_COMPANIES_PARAMETER};
                        $tempLegToJourneysMappingArray = array();
                        foreach ($carrierCountArray as $carrier => $carrierAirPriceSolutions) {
                            if (isset($selectedAirlineCompanies->{$carrier})) {
                                foreach ($carrierAirPriceSolutions as $carrierAirPriceSolution) {
                                    $tempLegToJourneysMappingArray = Fly_seach_helper::addAirSolutionToLegJourneysMappingArray($carrierAirPriceSolution, $tempLegToJourneysMappingArray);
                                }
                            }
                        }
                        if ($isStopCountFilterEntered) {
                            $legToJourneysMappingArray = array_intersect($legToJourneysMappingArray, $tempLegToJourneysMappingArray);
                        } else {
                            $legToJourneysMappingArray = $tempLegToJourneysMappingArray;
                        }
                    }
                }

                if (count($legToJourneysMappingArray) == 0) {
                    $filtered = false;
                    continue;
                }
                $clonedCombinedAirPriceSolution = Fly_seach_helper::removeNotAllowedJourneys($clonedCombinedAirPriceSolution, $legToJourneysMappingArray);
            }
            if (isset($filterCriteriaObject->{Fly_Constant::FILTER_GO_DEPARTURE_TIME_MINVALUE_PARAMETER}) && isset($filterCriteriaObject->{Fly_Constant::FILTER_GO_DEPARTURE_TIME_MAXVALUE_PARAMETER})) {
                $legToJourneysMappingArray = array();
                $legCount = count($clonedCombinedAirPriceSolution->legs);
                foreach ($oneByOneAirPriceSolutions as $oneAirPriceSolution) {
                    $legCountIndex = 0;
                    $isJourneyAdded = true;
                    foreach ($oneAirPriceSolution["journeys"] as $journey) {
                        $journey_departure_time = $journey->getDepartureTime($lowFareSearchResult->airSegmentArray);
                        $total_minute = Fly_seach_helper::getDepartureTimeAsTotalMinute($journey_departure_time);
                        if ($legCount == 2 && $legCountIndex == 1) {
                            $legCountIndex++;
                            continue; // iki bacak varsa  ikinci bacak donuş olarak dusunuluyor.
                        } else if (!((int) $filterCriteriaObject->{Fly_Constant::FILTER_GO_DEPARTURE_TIME_MINVALUE_PARAMETER} <= $total_minute && $total_minute <= (int) $filterCriteriaObject->{Fly_Constant::FILTER_GO_DEPARTURE_TIME_MAXVALUE_PARAMETER})) {
                            $isJourneyAdded = false;
                        }
                        $legCountIndex++;
                    }
                    if ($isJourneyAdded) {
                        $legToJourneysMappingArray = Fly_seach_helper::addAirSolutionToLegJourneysMappingArray($oneAirPriceSolution, $legToJourneysMappingArray);
                    }
                }
                $clonedCombinedAirPriceSolution = Fly_seach_helper::removeNotAllowedJourneys($clonedCombinedAirPriceSolution, $legToJourneysMappingArray);
                if ($clonedCombinedAirPriceSolution == null) {
                    $filtered = false;
                    continue;
                }
            }

            if (isset($filterCriteriaObject->{Fly_Constant::FILTER_GO_ARRIVAL_TIME_MAXVALUE_PARAMETER}) && isset($filterCriteriaObject->{Fly_Constant::FILTER_GO_ARRIVAL_TIME_MINVALUE_PARAMETER})) {
                $legToJourneysMappingArray = array();
                $legCount = count($clonedCombinedAirPriceSolution->legs);
                foreach ($oneByOneAirPriceSolutions as $oneAirPriceSolution) {
                    $legCountIndex = 0;
                    $isJourneyAdded = true;
                    foreach ($oneAirPriceSolution["journeys"] as $journey) {
                        $arrivalTime = $journey->getArrivalTime($lowFareSearchResult->airSegmentArray);
                        $totalMinute = Fly_seach_helper::getDepartureTimeAsTotalMinute($arrivalTime);
                        if ($legCount == 2 && $legCountIndex == 1) {
                            $legCountIndex++;
                            continue; // iki bacak varsa  ikinci bacak donuş olarak dusunuluyor.
                        } else if (!((int) $filterCriteriaObject->{Fly_Constant::FILTER_GO_ARRIVAL_TIME_MINVALUE_PARAMETER} <= $totalMinute && $totalMinute <= (int) $filterCriteriaObject->{Fly_Constant::FILTER_GO_ARRIVAL_TIME_MAXVALUE_PARAMETER})) {

                            $isJourneyAdded = false;
                        }
                        $legCountIndex++;
                    }
                    if ($isJourneyAdded) {
                        $legToJourneysMappingArray = Fly_seach_helper::addAirSolutionToLegJourneysMappingArray($oneAirPriceSolution, $legToJourneysMappingArray);
                    }
                }
                $clonedCombinedAirPriceSolution = Fly_seach_helper::removeNotAllowedJourneys($clonedCombinedAirPriceSolution, $legToJourneysMappingArray);
                if ($clonedCombinedAirPriceSolution == null) {
                    $filtered = false;
                    continue;
                }
            }

            if (isset($filterCriteriaObject->{Fly_Constant::FILTER_RETURN_DEPARTURE_TIME_MINVALUE_PARAMETER}) && isset($filterCriteriaObject->{Fly_Constant::FILTER_RETURN_DEPARTURE_TIME_MAXVALUE_PARAMETER})) {
                $legToJourneysMappingArray = array();
                $legCount = count($clonedCombinedAirPriceSolution->legs);
                foreach ($oneByOneAirPriceSolutions as $oneAirPriceSolution) {
                    $legCountIndex = 0;
                    $isJourneyAdded = true;
                    foreach ($oneAirPriceSolution["journeys"] as $journey) {
                        if ($legCount == 2 && $legCountIndex == 1) {
                            $departureTime = $journey->getDepartureTime($lowFareSearchResult->airSegmentArray);
                            $totalMinute = Fly_seach_helper::getDepartureTimeAsTotalMinute($departureTime);
                            if (!((int) $filterCriteriaObject->{Fly_Constant::FILTER_RETURN_DEPARTURE_TIME_MINVALUE_PARAMETER} <= $totalMinute && $totalMinute <= (int) $filterCriteriaObject->{Fly_Constant::FILTER_RETURN_DEPARTURE_TIME_MAXVALUE_PARAMETER} )) {
                                $isJourneyAdded = FALSE;
                            }
                        } else {
                            $legCountIndex++;
                            continue;
                        }
                        $legCountIndex++;
                    }
                    if ($isJourneyAdded) {
                        $legToJourneysMappingArray = Fly_seach_helper::addAirSolutionToLegJourneysMappingArray($oneAirPriceSolution, $legToJourneysMappingArray);
                    }
                }
                $clonedCombinedAirPriceSolution = Fly_seach_helper::removeNotAllowedJourneys($clonedCombinedAirPriceSolution, $legToJourneysMappingArray);
                if ($clonedCombinedAirPriceSolution == null) {
                    $filtered = false;
                    continue;
                }
            }

            if (isset($filterCriteriaObject->{Fly_Constant::FILTER_RETURN_ARRIVAL_TIME_MAXVALUE_PARAMETER}) && isset($filterCriteriaObject->{Fly_Constant::FILTER_RETURN_ARRIVAL_TIME_MINVALUE_PARAMETER})) {
                $legToJourneysMappingArray = array();
                $legCount = count($clonedCombinedAirPriceSolution->legs);
                foreach ($oneByOneAirPriceSolutions as $oneAirPriceSolution) {
                    $legCountIndex = 0;
                    $isJourneyAdded = true;
                    foreach ($oneAirPriceSolution["journeys"] as $journey) {
                        if ($legCount == 2 && $legCountIndex == 1) {
                            $arrivalTime = $journey->getArrivalTime($lowFareSearchResult->airSegmentArray);
                            $totalMinute = Fly_seach_helper::getDepartureTimeAsTotalMinute($arrivalTime);
                            if (!((int) $filterCriteriaObject->{Fly_Constant::FILTER_RETURN_ARRIVAL_TIME_MINVALUE_PARAMETER} <= $totalMinute && $totalMinute <= (int) $filterCriteriaObject->{Fly_Constant::FILTER_RETURN_ARRIVAL_TIME_MAXVALUE_PARAMETER} )) {
                                $isJourneyAdded = FALSE;
                            }
                        } else {
                            $legCountIndex++;
                            continue;
                        }
                        $legCountIndex++;
                    }
                    if ($isJourneyAdded) {
                        $legToJourneysMappingArray = Fly_seach_helper::addAirSolutionToLegJourneysMappingArray($oneAirPriceSolution, $legToJourneysMappingArray);
                    }
                }
                $clonedCombinedAirPriceSolution = Fly_seach_helper::removeNotAllowedJourneys($clonedCombinedAirPriceSolution, $legToJourneysMappingArray);
                if ($clonedCombinedAirPriceSolution == null) {
                    $filtered = false;
                    continue;
                }
            }











            // Kalan Journeyler arasındaki bağlantı kontrol edilir.
            if ($filtered) {
                $legObjects = array_values($clonedCombinedAirPriceSolution->legs);
                $firstLegObjects = $legObjects[0];
                if (count($legObjects) != 1) {
                    foreach ($firstLegObjects->getJourneys() as $firstLegJourney) {
                        $isHaveAllAirPriceSolutionRef = false;
                        for ($i = 1; $i < count($legObjects); $i++) {
                            foreach ($legObjects[$i]->getJourneys() as $otherLegJourney) {
                                if (Fly_seach_helper::isJourneysHaveSameAirPriceSolution($firstLegJourney, $otherLegJourney)) {
                                    $isHaveAllAirPriceSolutionRef = true;
                                    break;
                                }
                            }
                            unset($otherLegJourney);
                            if (!$isHaveAllAirPriceSolutionRef) {
                                break;
                            }
                        }

                        if (!$isHaveAllAirPriceSolutionRef) {
                            for ($i = 1; $i < count($legObjects); $i++) {
                                foreach ($legObjects[$i]->getJourneys() as $otherLegJourney) {
                                    foreach ($firstLegJourney->getAirSolutionKeyRefArray() as $airPriceSolutionKey) {
                                        $otherLegJourney->removeAirPriceSolutionKeyRef($airPriceSolutionKey);
                                    }

                                    if (count($otherLegJourney->getAirSolutionKeyRefArray()) == 0) {
                                        $legObjects[$i]->removeJourney($otherLegJourney);
                                    }
                                }
                            }
                            $firstLegObjects->removeJourney($firstLegJourney);
                        }
                    }
                }

                foreach ($legObjects as $finalLegObject) {
                    if (count($finalLegObject->getJourneys()) == 0) {
                        $filtered = false;
                        break;
                    }
                }
            }
            
            
            if ($filtered == true) {

                $filteredCombinedAirPriceSolution = new FilteredCombinedAirPriceSolution();
                $filteredCombinedAirPriceSolution->combinedKey = $clonedCombinedAirPriceSolution->combinedKey;
                $filteredCombinedAirPriceSolution->isAllLegsFiltereed = true;
                $filteredCombinedAirPriceSolution->filteredLegs = array();
                unset($filteredLegObject);
                foreach ($clonedCombinedAirPriceSolution->legs as $filteredLegObject) {
                    $filteredJourneyKeyArray = array();
                    foreach ($filteredLegObject->getJourneys() as $filteredJourney) {
                        array_push($filteredJourneyKeyArray, $filteredJourney->key);
                    }
                    $filteredCombinedAirPriceSolution->filteredLegs[$filteredLegObject->key] = $filteredJourneyKeyArray;
                }
                array_push($filteredCombinedAirPriceSolutionArray, $filteredCombinedAirPriceSolution);
            }
        }
        return $filteredCombinedAirPriceSolutionArray;
    }

    private function convertCombinedToOneAirSolutionRecursively($legObjectArray, $airPriceSolutionArray, $legLevel) {
        if ($legLevel == count($legObjectArray)) {
            return $airPriceSolutionArray;
        }

        if ($legLevel == 0) {
            $airPriceSolutionArray = array();
            foreach ($legObjectArray[$legLevel]->getJourneys() as $journey) {
                $newSolution = array("key" => (count($airPriceSolutionArray) + 1), "journeys" => array());
                array_push($newSolution["journeys"], $journey);
                array_push($airPriceSolutionArray, $newSolution);
            }
        } else {
            $count = 0;
            $prevAirPriceSolutionArray = $airPriceSolutionArray;
            foreach ($legObjectArray[$legLevel]->getJourneys() as $journey) {
                $solutionIndex = 0;
                foreach ($prevAirPriceSolutionArray as $airPriceSolution) {
                    if ($count == 0) {
                        array_push($airPriceSolution["journeys"], $journey);
                        $airPriceSolutionArray[$solutionIndex] = $airPriceSolution;
                    } else {
                        $oldSolution = $prevAirPriceSolutionArray[$solutionIndex];
                        $prevJourneys = $oldSolution["journeys"];
                        $newSolution = array("key" => (count($airPriceSolutionArray) + 1), "journeys" => array());
                        foreach ($prevJourneys as $prevJourney) {
                            array_push($newSolution["journeys"], $prevJourney);
                        }
                        array_push($newSolution["journeys"], $journey);
                        array_push($airPriceSolutionArray, $newSolution);
                    }
                    $solutionIndex++;
                }
                $airPriceSolutionArray = $airPriceSolutionArray;
                $count++;
            }
        }

        return $this->convertCombinedToOneAirSolutionRecursively($legObjectArray, $airPriceSolutionArray, $legLevel + 1);
    }

}

function combinedAirPriceSolutionSorter(CombinedAirPriceSolution $combinedAirPriceSolution, CombinedAirPriceSolution $ohterCombinedAirPriceSolution) {
    if ($combinedAirPriceSolution->apprixomateTotalPriceAmount == $ohterCombinedAirPriceSolution->apprixomateTotalPriceAmount) {
        return 0;
    }
    return $combinedAirPriceSolution->apprixomateTotalPriceAmount < $ohterCombinedAirPriceSolution->apprixomateTotalPriceAmount ? -1 : 1;
}

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
