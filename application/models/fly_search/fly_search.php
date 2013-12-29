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
            if ($lowFareSearchResult->errorCode == ErrorCodes::SUCCESS) {
                $totalLowFareSearchResult->airSegmentArray +=  $lowFareSearchResult->airSegmentArray;
                $totalLowFareSearchResult->fareInfoArray += $lowFareSearchResult->fareInfoArray;
                $totalLowFareSearchResult->combinedAirPriceSolutionArray +=  $lowFareSearchResult->combinedAirPriceSolutionArray;
                $totalLowFareSearchResult->airportArray += $lowFareSearchResult->airportArray;
                $totalLowFareSearchResult->airlineArray += $lowFareSearchResult->airlineArray;
            }
            unset($lowFareSearchResult);
        }

        //uasort($combined_air_solutions_array, "combinedAirPriceSolutionSorter");
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

        $all_combined_air_price_solutions = $this->getFlightSearchResult();
        $filtered_combined_air_price_solutions = array();
        foreach ($all_combined_air_price_solutions as $combined_air_price_solution) {
            $filtered = true;
            $filtered_departure_journey_keys = array();
            $filtered_return_journey_keys = array();
            $is_all_departure_journeys_filtered = false;
            $is_all_return_journeys_filtered = false;

            //fiyat filtresi için 
            if (isset($filterCriteriaObject->{Fly_Constant::FILTER_MIN_PRICE_PARAMETER}) && isset($filterCriteriaObject->{Fly_Constant::FILTER_MAX_PRICE_PARAMETER})) {
                if ($filtered && !((int) $combined_air_price_solution->apprixomate_total_price_amount >= (int) $filterCriteriaObject->{Fly_Constant::FILTER_MIN_PRICE_PARAMETER} && (int) $combined_air_price_solution->apprixomate_total_price_amount <= (int) $filterCriteriaObject->{Fly_Constant::FILTER_MAX_PRICE_PARAMETER})) {
                    $filtered = false;
                }
            }
            // stopCount için 
            if ($filtered) {

                if (isset($filterCriteriaObject->{Fly_Constant::FILTER_STOP_COUNT_PARAMTER}) || isset($filterCriteriaObject->{Fly_Constant::FILTER_IS_NO_STOP_PARAMETER})) {
                    $departure_journeys = $combined_air_price_solution->departure_journeys;
                    $return_journeys = $combined_air_price_solution->return_journeys;
                    $stop_count_filter_departure_journey_keys = array();
                    $stop_count_filter_return_journey_keys = array();

                    foreach ($departure_journeys as $departure_journey) {
                        $stopCount = $departure_journey->getStopCount();
                        $found_return_journey = null;
                        if ($return_journeys != null) {
                            foreach ($return_journeys as $return_journey) {
                                if ($return_journey->related_journey_id == $departure_journey->related_journey_id) {
                                    $found_return_journey = $return_journey;
                                    if ($stopCount < $return_journey->getStopCount()) {
                                        $stopCount = $return_journey->getStopCount();
                                        break;
                                    }
                                }
                            }
                        }
                        if (isset($filterCriteriaObject->{Fly_Constant::FILTER_STOP_COUNT_PARAMTER})) {
                            if (((int) $filterCriteriaObject->{Fly_Constant::FILTER_STOP_COUNT_PARAMTER} == $stopCount)) {
                                array_push($stop_count_filter_departure_journey_keys, $departure_journey);
                                if ($found_return_journey != null) {
                                    array_push($stop_count_filter_return_journey_keys, $found_return_journey);
                                }
                            }
                        } else {
                            if (isset($filterCriteriaObject->{Fly_Constant::FILTER_IS_NO_STOP_PARAMETER}) && (int) $filterCriteriaObject->{Fly_Constant::FILTER_IS_NO_STOP_PARAMETER} == 1) {
                                if ($stopCount == 0) {
                                    array_push($stop_count_filter_departure_journey_keys, $departure_journey);
                                    if ($found_return_journey != null) {
                                        array_push($stop_count_filter_return_journey_keys, $found_return_journey);
                                    }
                                }
                            } if (isset($filterCriteriaObject->{Fly_Constant::FILTER_ONE_STOP_PARAMETER}) && (int) $filterCriteriaObject->{Fly_Constant::FILTER_ONE_STOP_PARAMETER} == 1) {
                                if ($stopCount == 1) {
                                    array_push($stop_count_filter_departure_journey_keys, $departure_journey);
                                    if ($found_return_journey != null) {
                                        array_push($stop_count_filter_return_journey_keys, $found_return_journey);
                                    }
                                }
                            } if (isset($filterCriteriaObject->{Fly_Constant::FILTER_TWO_MORE_STOP_PARAMETER}) && (int) $filterCriteriaObject->{Fly_Constant::FILTER_TWO_MORE_STOP_PARAMETER} == 1) {
                                if ($stopCount > 1) {
                                    array_push($stop_count_filter_departure_journey_keys, $departure_journey);
                                    if ($found_return_journey != null) {
                                        array_push($stop_count_filter_return_journey_keys, $found_return_journey);
                                    }
                                }
                            }
                        }
                    }
                    if (count($stop_count_filter_departure_journey_keys) < 1) {
                        $filtered = false;
                    }
                    if ($return_journeys != null && count($stop_count_filter_return_journey_keys) < 1) {
                        $filtered = false;
                    }
                }
            }
            if ($filtered) {
                $departure_journeys = $combined_air_price_solution->departure_journeys;
                $return_journeys = $combined_air_price_solution->return_journeys;

                foreach ($departure_journeys as $departure_journey) {
                    $is_departure_journey_added = true;
                    if (isset($filterCriteriaObject->{Fly_Constant::FILTER_AIRLINE_COMPANY})) {
                        $airlineCompany = $filterCriteriaObject->{Fly_Constant::FILTER_AIRLINE_COMPANY};
                        if (!($departure_journey->getAirlineCompany() == $airlineCompany)) {
                            $is_departure_journey_added = false;
                        }
                    } else if (isset($filterCriteriaObject->{Fly_Constant::FILTER_SELECTED_AIRLINE_COMPANIES_PARAMETER})) {
                        $selectedAirlineCompanies = $filterCriteriaObject->{Fly_Constant::FILTER_SELECTED_AIRLINE_COMPANIES_PARAMETER};
                        $airlineCompany = $departure_journey->getAirlineCompany();
                        if (!isset($selectedAirlineCompanies->{$airlineCompany})) {
                            $is_departure_journey_added = false;
                        }
                    }


                    if (isset($filterCriteriaObject->{Fly_Constant::FILTER_GO_DEPARTURE_TIME_MINVALUE_PARAMETER}) && isset($filterCriteriaObject->{Fly_Constant::FILTER_GO_DEPARTURE_TIME_MAXVALUE_PARAMETER})) {
                        $journey_departure_time = $departure_journey->getDepartureTime();
                        $total_minute = Fly_seach_helper::getDepartureTimeAsTotalMinute($journey_departure_time);
                        if (!((int) $filterCriteriaObject->{Fly_Constant::FILTER_GO_DEPARTURE_TIME_MINVALUE_PARAMETER} <= $total_minute && $total_minute <= (int) $filterCriteriaObject->{Fly_Constant::FILTER_GO_DEPARTURE_TIME_MAXVALUE_PARAMETER})) {
                            $is_departure_journey_added = false;
                        }
                    }
                    if (isset($filterCriteriaObject->{Fly_Constant::FILTER_GO_ARRIVAL_TIME_MAXVALUE_PARAMETER}) && isset($filterCriteriaObject->{Fly_Constant::FILTER_GO_ARRIVAL_TIME_MINVALUE_PARAMETER})) {
                        $journey_arrival_time = $departure_journey->getArrivalTime();
                        $total_minute = Fly_seach_helper::getDepartureTimeAsTotalMinute($journey_arrival_time);
                        if (!((int) $filterCriteriaObject->{Fly_Constant::FILTER_GO_ARRIVAL_TIME_MINVALUE_PARAMETER} <= $total_minute && $total_minute <= (int) $filterCriteriaObject->{Fly_Constant::FILTER_GO_ARRIVAL_TIME_MAXVALUE_PARAMETER})) {
                            $is_departure_journey_added = false;
                        }
                    }


                    if ($is_departure_journey_added) {
                        array_push($filtered_departure_journey_keys, $departure_journey->key);
                    }
                }
                if (!count($filtered_departure_journey_keys) > 0) {
                    $filtered = false;
                }

                if ($return_journeys != null) {
                    foreach ($return_journeys as $return_journey) {
                        $is_return_journey_added = true;
                        if (isset($filterCriteriaObject->{Fly_Constant::FILTER_AIRLINE_COMPANY})) {
                            $airlineCompany = $filterCriteriaObject->{Fly_Constant::FILTER_AIRLINE_COMPANY};
                            if (!($return_journey->getAirlineCompany() == $airlineCompany)) {
                                $is_return_journey_added = false;
                            }
                        } else if (isset($filterCriteriaObject->{Fly_Constant::FILTER_SELECTED_AIRLINE_COMPANIES_PARAMETER})) {
                            $selectedAirlineCompanies = $filterCriteriaObject->{Fly_Constant::FILTER_SELECTED_AIRLINE_COMPANIES_PARAMETER};
                            $airlineCompany = $return_journey->getAirlineCompany();
                            if (!isset($selectedAirlineCompanies->{$airlineCompany})) {
                                $is_return_journey_added = false;
                            }
                        }


                        if (isset($filterCriteriaObject->{Fly_Constant::FILTER_RETURN_DEPARTURE_TIME_MINVALUE_PARAMETER}) && isset($filterCriteriaObject->{Fly_Constant::FILTER_RETURN_DEPARTURE_TIME_MAXVALUE_PARAMETER})) {
                            $journey_departure_time = $return_journey->getDepartureTime();
                            $total_minute = Fly_seach_helper::getDepartureTimeAsTotalMinute($journey_departure_time);
                            if (!((int) $filterCriteriaObject->{Fly_Constant::FILTER_RETURN_DEPARTURE_TIME_MINVALUE_PARAMETER} <= $total_minute && $total_minute <= (int) $filterCriteriaObject->{Fly_Constant::FILTER_RETURN_DEPARTURE_TIME_MAXVALUE_PARAMETER} )) {
                                $is_return_journey_added = false;
                            }
                        }

                        if (isset($filterCriteriaObject->{Fly_Constant::FILTER_RETURN_ARRIVAL_TIME_MAXVALUE_PARAMETER}) && isset($filterCriteriaObject->{Fly_Constant::FILTER_RETURN_ARRIVAL_TIME_MINVALUE_PARAMETER})) {
                            $journey_departure_time = $return_journey->getArrivalTime();
                            $total_minute = Fly_seach_helper::getDepartureTimeAsTotalMinute($journey_departure_time);
                            if (!((int) $filterCriteriaObject->{Fly_Constant::FILTER_RETURN_ARRIVAL_TIME_MINVALUE_PARAMETER} <= $total_minute && $total_minute <= (int) $filterCriteriaObject->{Fly_Constant::FILTER_RETURN_ARRIVAL_TIME_MAXVALUE_PARAMETER} )) {
                                $is_return_journey_added = false;
                            }
                        }


                        if ($is_return_journey_added) {
                            array_push($filtered_return_journey_keys, $return_journey->key);
                        }
                    }
                    if (!count($filtered_return_journey_keys) > 0) {
                        $filtered = false;
                    }
                }
            }


            if ($filtered == true) {

                $filtered_combined_air_price_object = new FilteredCombinedAirPriceSolution();
                $filtered_combined_air_price_object->combined_key = $combined_air_price_solution->combined_key;
                $filtered_combined_air_price_object->is_all_departures_journeys_filtered = $is_all_departure_journeys_filtered;
                $filtered_combined_air_price_object->is_all_return_journeys_filtered = $is_all_return_journeys_filtered;
                if (count($combined_air_price_solution->departure_journeys) == count($filtered_departure_journey_keys)) {
                    $filtered_combined_air_price_object->is_all_departures_journeys_filtered = TRUE;
                }
                if (count($combined_air_price_solution->return_journeys) == count($filtered_return_journey_keys)) {
                    $filtered_combined_air_price_object->is_all_return_journeys_filtered = TRUE;
                }
                $filtered_combined_air_price_object->filtered_departure_journeys_keys = $filtered_departure_journey_keys;
                $filtered_combined_air_price_object->filtered_return_journeys_keys = $filtered_return_journey_keys;
                array_push($filtered_combined_air_price_solutions, $filtered_combined_air_price_object);
            }
        }
        return $filtered_combined_air_price_solutions;
    }
}

function combinedAirPriceSolutionSorter(CombinedAirPriceSolution $combinedAirPriceSolution, CombinedAirPriceSolution $ohterCombinedAirPriceSolution) {
    if ($combinedAirPriceSolution->apprixomate_total_price_amount == $ohterCombinedAirPriceSolution->apprixomate_total_price_amount) {
        return 0;
    }
    return $combinedAirPriceSolution->apprixomate_total_price_amount < $ohterCombinedAirPriceSolution->apprixomate_total_price_amount ? -1 : 1;
}

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
