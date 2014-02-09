<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

include_once APPPATH . '/helpers/session_helper.php';
include_once APPPATH . '/helpers/fly_search_helper.php';
include_once APPPATH . '/models/constants/flight_constants.php';
include_once APPPATH . '/helpers/fly_search_helper.php';
require_once APPPATH . '/models/fly_search/air_pricing_solution.php';
require_once APPPATH . '/models/fly_search/air_pricing_info.php';
require_once APPPATH . '/models/fly_search/air_segment.php';
require_once APPPATH . '/models/fly_search/book_info.php';
require_once APPPATH . '/models/fly_search/journey.php';
require_once APPPATH . '/models/fly_search/combined_air_price_solution.php';
require_once APPPATH . '/models/fly_search/low_fare_search_result.php';
require_once APPPATH . '/models/fly_search/air_leg.php';
require_once APPPATH . '/models/fly_search/airport.php';
require_once APPPATH . '/models/fly_search/airline_company.php';
require_once APPPATH . '/models/fly_booking/response_book_verify_data.php';
require_once APPPATH . '/models/fly_search/book_info.php';
require_once APPPATH . '/models/fly_search/journey.php';
require_once APPPATH . '/models/fly_search/combined_air_price_solution.php';
require_once APPPATH . '/models/fly_booking/fly_apply_book_usercontact.php';
require_once APPPATH . '/models/fly_booking/fly_apply_book_passanger.php';
require_once APPPATH . '/models/fly_booking/fly_apply_book_info.php';
include_once APPPATH . '/models/fly_search/fare_info.php';
include_once APPPATH . '/models/fly_search/search_air_leg.php';
include_once APPPATH . '/models/fly_search/air_search_location.php';

class Main extends CI_Controller {

    public function index() {

        $this->main_index();
        /*
          $this->load->view("header");
          $this->load->view("main_search");
          $this->load->view("footer");

         */
    }

    public function main_index() {


        $jsfiles = array();
        array_push($jsfiles, "libs/json2.js");
        array_push($jsfiles, "libs/jquery-validate-1.10.0/jquery.validate.min.js");
        // array_push($jsfiles, "libs/require.js-2.1.4/require.min.js");
        array_push($jsfiles, "libs/select2-3.2/select2.min.js");
        array_push($jsfiles, "libs/jquery.blockUI.js");
        array_push($jsfiles, "airports.js");
        array_push($jsfiles, "libs/cookie-util.js");

        array_push($jsfiles, "pages/flysearch.js");

        //more js file

        $data['js'] = $jsfiles;
        $this->load->view("main_header");
        $this->load->view("fly_search/fly_search");
        $this->load->view("main_footer", $data);
    }

    public function searchFlyRequest() {

        try {
             
             
              ini_set('memory_limit', '-1');
            $this->load->model("fly_search/fly_search_criteria");
            $search_criteria = $this->fly_search_criteria->getInstance();
            $search_criteria->boardingCode = $this->input->post("boardingairpotCode");
            $search_criteria->landingCode = $this->input->post("landingairpotCode");
            $search_criteria->godate = $this->input->post("goDate");
            $search_criteria->returndate = $this->input->post("returnDate");
            $search_criteria->flydirection = $this->input->post("directionOption");
            $search_criteria->yetiskinnumber = $this->input->post("yetiskinNumber");
            $search_criteria->bebeknumber = $this->input->post("bebekNumber");
            $search_criteria->cocuknumber = $this->input->post("cocukNumber");
            $search_criteria->dateoption = $this->input->post("dateOption");
            $search_criteria->cabinclass = $this->input->post("cabinClass");
            $search_criteria->flighttype = $this->input->post("flightType");
            
            $searchAirLegsParams = $this->input->post("airSearchLegs");
           
            
            $searchAirLegs = array();
            foreach($searchAirLegsParams as $searchAirLegsParam){
                $searchAirleg = new  SearchAirLeg();
                $searchAirleg->searchDepartureTime = $searchAirLegsParam["departureTime"];
                $originSearchLocation = new SearchLocation();
                $originSearchLocation->airport = $searchAirLegsParam["origin"]["airport"];
                $destinationSearchLocation  = new SearchLocation();
                $destinationSearchLocation->airport = $searchAirLegsParam["destination"]["airport"];
                $searchAirleg->originSearchLocation = $originSearchLocation;
                $searchAirleg->destinationSearchLocation = $destinationSearchLocation;
                array_push($searchAirLegs, $searchAirleg);
            }
            $search_criteria->searchAirLegs = $searchAirLegs;
            $this->load->helper('url');
            $this->load->model("fly_search/fly_search");
            $lowSearchResult =  $this->fly_search->search($search_criteria);
          
           $this->load->model("common/ajax_response");
           $this->ajax_response->data = count($lowSearchResult->combinedAirPriceSolutionArray);
           //$this->ajax_response->data = count($combinedAirPriceSolutionArray);
            $this->ajax_response->error_type = Fly_Constant::INFO_TYPE;
            $this->ajax_response->error_code = Fly_Constant::SUCCESS_ERROR_CODE;
            $ajaxResponseArray = $this->ajax_response->getResponseArray();
          
            echo json_encode($ajaxResponseArray);
           
        } catch (Exception $e) {
            error_log(get_class($e) . " thrown. Message: " . $e->getMessage() . "  in " . $e->getFile() . " on line " . $e->getLine());
            error_log('Exception trace stack: ' . print_r($e->getTrace(), 1));
            echo $e->getMessage() . "|" . $e->getFile() . "|" . $e->getLine();
        }
        return;

        /*
          public $boardingCode;
          public  $landingCode;
          public  $godate;
          public  $returndate;
          public $dateOption;
          public $flyDirection;
          public $yetiskinNumber;
          public $bebekNumber;
          public $cocukNumber;

          boardingairpotCode:IST
          landingairpotCode:ADA
          goDate:Sat Mar 09 2013 00:00:00 GMT+0200 (GTB Standart Saati)
          returnDate:Tue Apr 30 2013 00:00:00 GMT+0300 (GTB Yaz Saati)
          yetiskinNumber:1
          cocukNumber:0
          bebekNumber:0
          dateOption:1
         * */
        /*
          echo json_encode($search_criteria);
          return;
         *
         */
    }

    public function searchResults() {
        $jsfiles = array();
        $cssfiles = array();
        
        $this->load->model("fly_search/fly_search_criteria");
        $session = new Session(300);

        $lowFareSearchResult = $session->get(Fly_Constant::SESSION_COMBINED_AIR_PRICE_SOLUTIONS_PARAMETER);
        if($lowFareSearchResult == FALSE){
            redirect();
            
        }

        array_push($cssfiles, "pages/fly_result.css");
        array_push($jsfiles, "libs/jquery-validate-1.10.0/jquery.validate.min.js");
        //array_push($jsfiles, "libs/require.js-2.1.4/require.min.js");
        array_push($jsfiles, "libs/select2-3.2/select2.min.js");
        array_push($jsfiles, "libs/jquery.blockUI.js");
        array_push($jsfiles, "airports.js");
        array_push($jsfiles, "pages/flyresult.js");
        //array_push($jsfiles, "pages/flysearch.js");
        //more js file
       
        //echo json_encode($lowFareSearchResult);
        $this->load->model("fly_search/fly_search_result_filter_values");
         $carrierFlightTypePriceArray = Fly_seach_helper::getCarrierFlightTypePrices($lowFareSearchResult);
        $this->fly_search_result_filter_values->setFilterResultValues($lowFareSearchResult, $carrierFlightTypePriceArray);
        $search_criteria = $session->get(Fly_Constant::SESSION_SEARCH_CRITERIA_PARAMETER);
        $data["searchCriteria"] = $search_criteria;
       
        $data["carrierFlightTypePricesArray"] = $carrierFlightTypePriceArray;
        $data['price_data_table'] = Fly_seach_helper::createFlightSummayTableData($carrierFlightTypePriceArray);
        $data[Fly_Constant::FLY_SEARCH_RESULT_FILTER_VALUES_PARAMAMETER] = $this->fly_search_result_filter_values;
        $data["lowFareSearchResult"] = $lowFareSearchResult;
        //file_put_contents("parse2.json", json_encode($data));
        
        $data['js'] = $jsfiles;
        $data['css'] = $cssfiles;
        $this->load->view("main_header", $data);
        $this->load->view("fly_result/fly_result3", $data);
        $this->load->view("main_footer", $data);
         
        
    }

    public function searchNavDay() {

        $action = $this->input->post("action");
        $this->load->model("fly_search/fly_search");
        $combinedAirPriceSolutionArray =  $this->fly_search->searchNavDay($action);
        $this->load->model("common/ajax_response");
        $this->ajax_response->data = count($combinedAirPriceSolutionArray);
        $this->ajax_response->error_type = Fly_Constant::INFO_TYPE;
        $this->ajax_response->error_code = Fly_Constant::SUCCESS_ERROR_CODE;
        $ajaxResponseArray = $this->ajax_response->getResponseArray();
        header('Content-type: application/json');
        echo json_encode($ajaxResponseArray);
        return;
    }

    public function test() {
        $data = array();
        $this->load->view("main_header", $data);
        $this->load->view("test");
        $this->load->view("main_footer", $data);
    }

    public function applyFilter() {
        $fiter_type = $this->input->post(Fly_Constant::FILTER_TYPE_PARAMETER);
        $filterCriteria = $this->input->post(Fly_Constant::FILTER_CRITERIA_PARAMETER);

        $filterCriteriaObject = json_decode($filterCriteria);
        $this->load->model("fly_search/fly_search");
        $filtered_combined_solutions = $this->fly_search->applyFilter($filterCriteriaObject);
        $this->load->model("common/ajax_response");
        $this->ajax_response->data = $filtered_combined_solutions;
        $this->ajax_response->error_type = Fly_Constant::INFO_TYPE;
        $this->ajax_response->error_code = Fly_Constant::SUCCESS_ERROR_CODE;
        $ajaxResponseArray = $this->ajax_response->getResponseArray();
        header('Content-type: application/json');
        //echo json_encode($filterCriteriaObject);
        echo json_encode($ajaxResponseArray);
        return;
    }

    public function bookPriceVerify() {
        $combinedAirSolutionKey = $this->input->post(Fly_Constant::AIR_SOLUTION_PRICE_KEY_PARAMETER);
        $journeys = array();
        
        $this->load->model("fly_search/fly_search_criteria");
        $session = new Session(300);
        $lowFareSearchResult = $session->get(Fly_Constant::SESSION_COMBINED_AIR_PRICE_SOLUTIONS_PARAMETER);
        $selectedCombinedAirPriceSolution  = $lowFareSearchResult->combinedAirPriceSolutionArray[$combinedAirSolutionKey];
        $selectedJourneyKeys = $this->input->post(Fly_Constant::SELECTED_JOURNEY_PARAMETER);
        
        foreach($selectedJourneyKeys as $selectedJourneyKey){
            $legObjectKey = $selectedJourneyKey["key"]; // baglı olan leg key;
            $legObject  =  $selectedCombinedAirPriceSolution->getLeg($legObjectKey);
            $selectedJourney = $legObject->getJourney($selectedJourneyKey["selectedJourneyKey"]);
            array_push($journeys , $selectedJourney);
        }

        $this->load->model('fly_booking/fly_book');
        $search_criteria = $session->get(Fly_Constant::SESSION_SEARCH_CRITERIA_PARAMETER);
        $bookPriceVerifyResponse = $this->fly_book->bookPriceVerify($selectedCombinedAirPriceSolution, $journeys, $lowFareSearchResult->airSegmentArray,$search_criteria);
        $session_data = array();
        $session_data["xxxx"] = "dsdsd";
        $session->set(Fly_Constant::SESSION_BOOK_PRICE_VERIFIED_SOLUTION_PARAMETER, $bookPriceVerifyResponse);
        // $session_data[Fly_Constant::SESSION_BOOK_PRICE_VERIFIED_SOLUTION_PARAMETER] = $bookPriceVerifyResponse;
        $this->session->set_userdata($session_data);
        $this->load->model("common/ajax_response");
        $this->ajax_response->data = $bookPriceVerifyResponse;
        $this->ajax_response->error_type = Fly_Constant::INFO_TYPE;
        $this->ajax_response->error_code = Fly_Constant::SUCCESS_ERROR_CODE;
        $ajaxResponseArray = $this->ajax_response->getResponseArray();
        header('Content-type: application/json');
        echo json_encode($ajaxResponseArray);
        return;
    }

    function bookInformationEnterView() {


        $session = new Session(300);

        $bookingPriceVerifiedSolution = $session->get(Fly_Constant::SESSION_BOOK_PRICE_VERIFIED_SOLUTION_PARAMETER);
        //redirect("/index.php/searchresults" ,"refresh");


        if ($bookingPriceVerifiedSolution) {
            $jsfiles = array();
            $cssfiles = array();
            array_push($cssfiles, "pages/fly_book.css");
            array_push($jsfiles, 'pages/flybook.js');

            $data["css"] = $cssfiles;
            $data["js"] = $jsfiles;
            $data["bookingPriceVerifiedSolution"] = $bookingPriceVerifiedSolution;
            $this->load->view("main_header", $data);
            $this->load->view("fly_book/fly_booking", $data);
            $this->load->view("main_footer", $data);

            return;
        }
        header("Location:" . base_url("index.php/searchresults"));
    }

    function applyBook() {
        $bookRequest = $this->input->post(Fly_Constant::BOOK_REQUEST_PARAMETER);
        $bookRequestJSONObject = json_decode($bookRequest);
        $userInfoJSONObject = $bookRequestJSONObject->{Fly_Constant::BOOK_APPLY_REQUEST_USER_PARAMETER};
        $userContactInformation = new FlyApplyBookUserContact();
        $userContactInformation->name = $userInfoJSONObject->{Fly_Constant::BOOK_APPLY_REQUEST_USER_NAME_PARAMETER};
        $userContactInformation->lastname = $userInfoJSONObject->{Fly_Constant::BOOK_APPLY_REQUEST_USER_LASTNAME_PARAMETER};
        $userContactInformation->gender = $userInfoJSONObject->{Fly_Constant::BOOK_APPLY_REQUEST_USER_GENDER_PARAMETER};
        $userContactInformation->email = $userInfoJSONObject->{Fly_Constant::BOOK_APPLY_REQUEST_USER_EMAIL_PARAMETER};
        $userContactInformation->tel = $userInfoJSONObject->{Fly_Constant::BOOK_APPLY_REQUEST_USER_TEL_PARAMETER};
        $userContactInformation->ceptel = $userInfoJSONObject->{Fly_Constant::BOOK_APPLY_REQUEST_USER_CEPTEL_PARAMETER};
        $userContactInformation->city = $userInfoJSONObject->{Fly_Constant::BOOK_APPLY_REQUEST_USER_CITY_PARAMETER};
        $userContactInformation->country = $userInfoJSONObject->{Fly_Constant::BOOK_APPLY_REQUEST_USER_COUNTRY_PARAMETER};
        $userContactInformation->zipcode = $userInfoJSONObject->{Fly_Constant::BOOK_APPLY_REQUEST_USER_ZIPCODE_PARAMETER};

        $passangersJSONObject = $bookRequestJSONObject->{Fly_Constant::BOOK_APPLY_REQUEST_PASSANGERS_PARAMETER};
        $passangers = array();
        $passangerCount = 0;
        foreach ($passangersJSONObject as $passangerJSONObject) {
            $passanger = new FlyApplyBookPassanger();
            $passanger->type = $passangerJSONObject->{Fly_Constant::BOOK_APPLY_REQUEST_PASSANGER_TYPE_PARAMETER};
            $passanger->birthday = $passangerJSONObject->{Fly_Constant::BOOK_APPLY_REQUEST_PASSANGER_BIRTHDAY_PARAMETER};
            $passanger->birthmonth = $passangerJSONObject->{Fly_Constant::BOOK_APPLY_REQUEST_PASSANGER_BIRTHMONTH_PARAMETER};
            $passanger->birthyear = $passangerJSONObject->{Fly_Constant::BOOK_APPLY_REQUEST_PASSANGER_BIRTHYEAR_PARAMETER};
            $passanger->lastName = $passangerJSONObject->{Fly_Constant::BOOK_APPLY_REQUEST_PASSANGER_LASTNAME_PARAMETER};
            $passanger->name = $passangerJSONObject->{Fly_Constant::BOOK_APPLY_REQUEST_PASSANGER_NAME_PARAMETER};
            $passanger->gender = $passangerJSONObject->{Fly_Constant::BOOK_APPLY_REQUEST_PASSANGER_GENDER_PARAMETER};
            $passanger->DOB = Fly_seach_helper::getDOBFormat($passanger->birthyear,$passanger->birthmonth, $passanger->birthday);
            //$passanger->frequentFlyCardNumber= ?
            $passanger->key = $passangerCount;
            $passangerCount++;
            array_push($passangers, $passanger);
        }
        $session = new Session(300);
        $bookingPriceVerifiedSolution = $session->get(Fly_Constant::SESSION_BOOK_PRICE_VERIFIED_SOLUTION_PARAMETER);
        $flyApplyBookInfo = new FlyApplyBookInformation();
        $flyApplyBookInfo->passangers = $passangers;
        $flyApplyBookInfo->userContact = $userContactInformation;
        $flyApplyBookInfo->verifiedCombinedAirPriceSolution = $bookingPriceVerifiedSolution->verifiedAirPriceSolution;
        
        $this->load->model('fly_booking/fly_book');
        $fly_apply_book_result = $this->fly_book->applyBook($flyApplyBookInfo);
      
        $this->load->model("common/ajax_response");
        $this->ajax_response->data = $fly_apply_book_result;
        $this->ajax_response->error_type = Fly_Constant::INFO_TYPE;
        $this->ajax_response->error_code = Fly_Constant::SUCCESS_ERROR_CODE;
        $ajaxResponseArray = $this->ajax_response->getResponseArray();

        echo json_encode($ajaxResponseArray);
    }

}

?>
 