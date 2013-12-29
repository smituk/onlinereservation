


<?php
include_once APPPATH . '/helpers/fly_search_helper.php';

function create_journey_uniqe_key($journey_air_segments_keys) {
    $unique_key = "";
    foreach ($journey_air_segments_keys as $air_segment_key) {
        $unique_key = $unique_key . $air_segment_key . ":";
    }
    return $unique_key;
}

function isExsistAirsegmentKey($air_segment_keys, $key) {
    foreach ($air_segment_keys as $air_segment_key) {
        if ($air_segment_key == $key) {
            return TRUE;
        }
    }
    return FALSE;
}

function build_flight_summary_table_template($results) {
    $template_data = $results->best_price_table_data;
    $airline_company_array_data = $template_data["airline_company_array"];
    $no_stop_flight_array_data = $template_data["no_stop_flight_array"];
    $stop_flight_array_data = $template_data["stop_flight_array"];
    $moreone_stop_flight_array_data = $template_data["moreone_stop_flight"];

    $template = "<td class='rowhead'>Butun uçuşlar</td>";
    for ($k = 0; $k < count($airline_company_array_data); $k++) {
        if (isset($airline_company_array_data[$k])) {
            $template.="<td align='center' valign='middle'  class='header'><img width='25' height='25'src='" . base_url("onlinefly/public_html/img/hava_sirket_icon_logo/hava_sirket_icon_logo/" . $airline_company_array_data[$k] . ".png") . "'/> </td>";
        } else {
            $template.="<td align='center' valign='middle'  class='header'></td>";
        }
    }
    $template = "<tr>" . $template . "</tr><tr><td class='rowhead'>Direk</td>";
    for ($k = 0; $k < count($airline_company_array_data); $k++) {
        if (isset($no_stop_flight_array_data[$k])) {
            $template.="<td align='center' valign='middle'  class='header2' airlinecompany='$airline_company_array_data[$k]'>" . $no_stop_flight_array_data[$k] . "</td>";
        } else {
            $template.="<td align='center' valign='middle'  class='header'></td>";
        }
    }
    $template = "<tr>" . $template . "</tr><tr><td class='rowhead'> 1 Aktarma '</td>";
    for ($k = 0; $k < count($airline_company_array_data); $k++) {
        if (isset($stop_flight_array_data[$k])) {
            $template.="<td align='center' valign='middle'  class='header2' airlinecompany='$airline_company_array_data[$k]'>" . $stop_flight_array_data[$k] . "</td>";
        } else {
            $template.="<td align='center' valign='middle'  class='header'></td>";
        }
    }
    $template = "<tr>" . $template . "</tr><tr><td class='rowhead'> 2+ Aktarma</td>";
    for ($k = 0; $k < count($airline_company_array_data); $k++) {
        if (isset($moreone_stop_flight_array_data[$k])) {
            $template.="<td align='center' valign='middle'  class='header2' airlinecompany='$airline_company_array_data[$k]'>" . $moreone_stop_flight_array_data[$k] . "</td>";
        } else {
            $template.="<td align='center' valign='middle' class='header' ></td>";
        }
    }
    $template.="</tr>";
    return $template;
}

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
/*
  <div class="span8 column-header">
  <div class="row">
  <div class="span1 select-column header-column"><span>Seç</span></div>
  <div class="span1 date-column  header-column"><span>Tarih</span></div>
  <div class="span1 airline-company-column header-column"><span>Havayolu</span></div>
  <div class="span1 flight-no-column header-column"><span>Uçuş No</span></div>
  <div class="span1 origin-column header-column"><span>Kalkış</span></div>
  <div class="span1 destination-column header-column"><span>Varış</span></div>
  <div class="span1 flight-time-column header-column"><span>Uçuş süresi</span></div>
  <div class="span1 class-column header-column"><span>Sınıf</span></div>
  </div>
 *
 */

function build_flight_price_detail_template($air_price_solution_item) {
    $message = <<<EOM

    <div class="row">
     <div class="span2 header-column"><span>Yolcular</span></div>
     <div  class="span2 header-column"><span>Yolcu başına tarife</span></div>
     <div  class="span2 header-column"><span>Yolcu başına vergiler ve harçlar</span> </div>
     <div  class="span2 header-column"><span>Toplam fiyat</span></div>
    </div>

EOM;
    $all_total_price = 0;
    foreach ($air_price_solution_item->air_pricing_info as $air_price_info_item) {
        $passenger_count = $air_price_info_item->passenger_count;
        $one_base_price = floatval($air_price_info_item->approximate_base_price_amount);
        $one_tax_price = floatval($air_price_info_item->taxes_amount);
        $total_price = (float) ($passenger_count * ( $one_base_price + $one_tax_price));
        $all_total_price += $total_price;
        $passenger_text = $air_price_info_item->passenger_type_desc . " x " . $passenger_count;
        $message .= <<<EOM

    <div class="row">
     <div class="span2"><span>$passenger_text</span></div>
     <div  class="span2"><span>$one_base_price EUR</span></div>
     <div  class="span2"><span>$one_tax_price EUR</span> </div>
     <div  class="span2"><span>$total_price EUR</span></div>
    </div>

EOM;
    }
    $message .= <<<EOM

    <div class="row">
     <div class="span2"><span></span></div>
     <div  class="span2"><span></span></div>
     <div  class="span2"><span></span> </div>
     <div  class="span2"><span class="all-total-price"> $all_total_price EUR</span></div>
    </div>

EOM;

    $message = "<div class='span8'>" . $message . "</div>";
    return $message;
}

function build_flight_detail_template($airsegment_object, $air_price_solution_item, $with_option, $flight_direction) {
    $message_option = "";
    if ($with_option == TRUE) {
        $message_option = "<input type='radio' name='" . $flight_direction . "_price_" . $air_price_solution_item->total_price . " value='" . $air_price_solution_item->total_price . "' airsegment_key='" . $airsegment_object->key . "' />";
    }

    $air_price_info_array = $air_price_solution_item->air_pricing_info;
    $air_price_info_object = $air_price_info_array[0];
    $class = "";
    $remain_book_count_warning_message = "";
    $selected_booking_info_object = null;
    foreach ($air_price_info_object->booking_short_info_array as $booking_info_object) {
        if ($booking_info_object->air_segment_ref === $airsegment_object->key) {
            $booking_counts = $airsegment_object->booking_counts;
            $booking_counts_array = explode("|", $booking_counts);

            $found = FALSE;
            foreach ($booking_counts_array as $book_code_info) {
                if ($booking_info_object->booking_code == substr($book_code_info, 0, 1)) {
                    $class = $book_code_info;
                    $avaible_booking_count = intval(substr($book_code_info, 1, 1));
                    if ($avaible_booking_count < 9) {
                        $remain_book_count_warning_message = "(" . $avaible_booking_count . ")";
                    }
                    $found = TRUE;
                    $selected_booking_info_object = $booking_info_object;
                    break;
                }
            }
            if ($found == TRUE) {
                break;
            }
        }
    }
    $airsegment_departure_time = new DateTime($airsegment_object->departure_time);
    $airsegment_arrival_time = new DateTime($airsegment_object->arrival_time);
    $date = $airsegment_departure_time->format('d.m.Y');

    $departure_time = $airsegment_departure_time->format("H:i");
    $arrival_time = $airsegment_arrival_time->format("H:i");
    $airline_company_logo_url = base_url("onlinefly/public_html/img/hava_sirket_icon_logo/hava_sirket_icon_logo/" . $airsegment_object->carrier . ".png");
    $flight_time = sprintf("%02dh %02dm", floor($airsegment_object->flight_time / 60), $airsegment_object->flight_time % 60);
    $message = <<<EOM
    <div class="row" segment="$airsegment_object->key">
          <div class="span1 select-column header-column"><span>$message_option</span></div>
         <div class="span1 date-column  header-column-value"><span>$date</span></div>
         <div class="span1 airline-company-column header-column-value"><span><img width="25" height="25" src="$airline_company_logo_url" alt="$airsegment_object->carrier"/></span></div>
         <div class="span1 flight-no-column header-column-value"><span>$airsegment_object->carrier </span> <span>$airsegment_object->flight_number</span></div>
        <div class="span1 origin-column header-column-value"><span>$airsegment_object->origin  </span><span>$departure_time</span></div>
        <div class="span1 destination-column header-column-value"><span>$airsegment_object->destination  </span> <span>$arrival_time</span><span></span></div>
        <div class="span1 flight-time-column header-column-value"><span>$flight_time</span></div>
        <div class="span1 class-column header-column-value"><span><a href="#" class="book_count_tooltip"  data-placement="top" data-toggle="tooltip" title="$airsegment_object->booking_counts">$selected_booking_info_object->booking_code</a></span><span class="remain-book-count"> $remain_book_count_warning_message</span><span class="cabin-class">  $selected_booking_info_object->cabin_class</span></div>

   </div>
EOM;
    return $message;
}

function build_all_flight_detail_template($air_price_solution_item_array, $results) {
    $departure_message = "";
    $arrival_message = null;
    $departure_airsegment_keys = array();
    $arrival_airsegment_keys = array();
    $origin_air_solutions_journey_map_array = array();
    $arrival_air_solutions_journey_map_array = array();
    $total_departure_journey_count = 0;
    $total_arrival_journey_count = 0;
    foreach ($air_price_solution_item_array as $air_price_solution_item) {
        $journeys = $air_price_solution_item->journeys;
        $origin_journey = $journeys[0];
        $arrival_journey = null;
        if (count($journeys) > 1) {
            $arrival_journey = $journeys[1];
        }


        $unique_journey_key = create_journey_uniqe_key($origin_journey->air_segment_keys);
        if (!isExsistAirsegmentKey($departure_airsegment_keys, $unique_journey_key)) {
            $air_solution_journey_map = array("travel_time" => $origin_journey->total_travel_time, "journey" => $origin_journey, "air_price_solution" => $air_price_solution_item);
            array_push($origin_air_solutions_journey_map_array, $air_solution_journey_map);
            array_push($departure_airsegment_keys, $unique_journey_key);
        }

        if ($arrival_journey !== null) {
            $unique_journey_key = create_journey_uniqe_key($arrival_journey->air_segment_keys);
            if (!isExsistAirsegmentKey($arrival_airsegment_keys, $unique_journey_key)) {
                $air_solution_journey_map2 = array("travel_time" => $arrival_journey->total_travel_time, "journey" => $arrival_journey, "air_price_solution" => $air_price_solution_item);
                array_push($arrival_air_solutions_journey_map_array, $air_solution_journey_map2);
                array_push($arrival_airsegment_keys, $unique_journey_key);
            }
        }
    }

    if (count($origin_air_solutions_journey_map_array) > 0) {
        usort($origin_air_solutions_journey_map_array, 'Fly_seach_helper::journey_travel_time_compare');
        $journey_count = 0;
        foreach ($origin_air_solutions_journey_map_array as $air_solutions_journey_map) {
            $display_property = "display:block;";
            $class = "departure";
            if ($journey_count > 0) {
                $display_property = "display:none;'";
                $class = "other-departure";
            }
            $journey = $air_solutions_journey_map["journey"];
            $air_price_solution_item = $air_solutions_journey_map["air_price_solution"];
            $origin_founded_airsegment_objects = Fly_seach_helper::gets_airsegment_by_keys($journey->air_segment_keys, $results);
            $air_company = Fly_seach_helper::get_air_line_company_of_air_solution($air_price_solution_item, $results);
            $departure_message = $departure_message . "<div class='journey row $class' airline-company='$air_company'  air-price-solution-key='$journey->air_price_solution_key_ref' style='$display_property'><div class='span8'>";
            $i = 0;
            foreach ($origin_founded_airsegment_objects as $airsegment_object) {
                if ($i == 0) {
                    $departure_message = $departure_message . build_flight_detail_template($airsegment_object, $air_price_solution_item, TRUE, 1);
                } else {
                    $departure_message = $departure_message . build_flight_detail_template($airsegment_object, $air_price_solution_item, FALSE, 1);
                }
                $i++;
            }
            $departure_message = $departure_message . "</div></div>";

            $journey_count++;
        }
        $total_departure_journey_count = $journey_count;
    }

    if (count($arrival_air_solutions_journey_map_array) > 0) {
        usort($arrival_air_solutions_journey_map_array, 'Fly_seach_helper::journey_travel_time_compare');
        $journey_count = 0;
        foreach ($arrival_air_solutions_journey_map_array as $air_solutions_journey_map) {
            $class = "return";
            $display_property = "display:block;";
            if ($journey_count > 0) {
                $display_property = "display:none;";
                $class = "other-return";
            }
            $journey = $air_solutions_journey_map["journey"];
            $air_price_solution_item = $air_solutions_journey_map["air_price_solution"];
            $arrival_founded_airsegment_objects = Fly_seach_helper::gets_airsegment_by_keys($journey->air_segment_keys, $results);
            $air_company = Fly_seach_helper::get_air_line_company_of_air_solution($air_price_solution_item, $results);
            $arrival_message = $arrival_message . "<div class='journey row $class' airline-company='$air_company'  air-price-solution-key='$journey->air_price_solution_key_ref'style='$display_property'><div class='span8'>";
            $i = 0;
            foreach ($arrival_founded_airsegment_objects as $airsegment_object) {
                if ($i == 0) {
                    $arrival_message = $arrival_message . build_flight_detail_template($airsegment_object, $air_price_solution_item, TRUE, 2);
                } else {
                    $arrival_message = $arrival_message . build_flight_detail_template($airsegment_object, $air_price_solution_item, FALSE, 2);
                }
                $i++;
            }
            $arrival_message = $arrival_message . "</div></div>";
            $journey_count++;
        }
        $total_arrival_journey_count = $journey_count;
    }

    $flight_deatils_message = array("origins" => $departure_message,
        "arrivals" => $arrival_message,
        "total_origin_count" => $total_departure_journey_count,
        "total_arrival_count" => $total_arrival_journey_count);
    return $flight_deatils_message;
}

function buid_search_day_nav_template($search_criteria) {

    $goDateTime = strtotime($search_criteria->godate);
    $departure_nav_temp = "<div class='go-nav' style='padding-left:70px;'><span class='label2'>Gidiş :  </span><span class='nav-icon go-nav-prev-icon icon-caret-left'> </span><span>" . date("d.m.Y", $goDateTime) . " " . date("l", $goDateTime) . " " . "</span><span class ='nav-icon go-nav-next-icon icon-caret-right'> </span></div>";
    $return_nav_temp = "";

    if ($search_criteria->flydirection == "2") {
        $returnDateTime = strtotime($search_criteria->returndate);
        $return_nav_temp = "";
        $return_nav_temp.= "<div class='return-nav'><span class='label2'>Dönüş :  </span><span class='nav-icon return-nav-prev-icon icon-caret-left'> </span><span>" . date("d.m.Y", $returnDateTime) . " " . date("l", $returnDateTime) . " " . "</span><span class ='nav-icon return-nav-next-icon   icon-caret-right'></span></div>";

        $departure_nav_temp = "<div class='span3'>" . $departure_nav_temp . "</div>";
        $seperator = "<div class='span1' style='text-align:center;'> || </div>";
        $return_nav_temp = "<div class='span3'>" . $return_nav_temp . "</div>";
        return $departure_nav_temp . $seperator . $return_nav_temp;
    } else {
        $departure_nav_temp = "<div class='span8'>" . $departure_nav_temp . "</div>";
        return $departure_nav_temp;
    }
    
}

function build_air_solution_template($air_price_solution_item_array, $results, $count) {
    $scrolled = "scrolled"; 
    //bu flag ile scroll aşağı indiği zaman goruntülenek elementlerin bulunması sağlanır
    // scrolled ise gorunmekte ,  no-scrolled ise gorunmemeketedir air solution element
      if($count > 5){
      $scrolled="no-scrolled";
      }
     
    $flight_all_detail = build_all_flight_detail_template($air_price_solution_item_array, $results);
    $departure_flights = $flight_all_detail["origins"];
    $arrival_flights = $flight_all_detail["arrivals"];
    $price_detail_message = build_flight_price_detail_template($air_price_solution_item_array[0]);
    $current_price = $air_price_solution_item_array[0]->apprixomate_total_price;
    $arrival_message = "";

    if ($arrival_flights != null) {
        $total_arrival_count_warning_message = "";
        $total_arrival_counts = $flight_all_detail["total_arrival_count"];
        if ($total_arrival_counts > 1) {// birden fazla  journey varsa
            $total_arrival_counts--;
            $total_arrival_count_warning_message = <<<EOM
        <div class="row other-arrival-journey">
             <div class="span2 offset3">
               <span class="icon-circle-arrow-down" clicked="">Diğer $total_arrival_counts  seçenek </span>
              </div>
        </div>
EOM;
        }
        $arrival_message = <<<EOM
    <div class="row">
        <div class="span8 price-direction">
            <strong class="in">Donüş</strong>
        </div>
    </div>

      <div class="row flight-summary-colums">
                    <div class="span8 column-header">
                        <div class="row">
                            <div class="span1 select-column header-column"><span>Seç</span></div>
                            <div class="span1 date-column  header-column"><span>Tarih</span></div>
                            <div class="span1 airline-company-column header-column"><span>Havayolu</span></div>
                            <div class="span1 flight-no-column header-column"><span>Uçuş No</span></div>
                            <div class="span1 origin-column header-column"><span>Kalkış</span></div>
                            <div class="span1 destination-column header-column"><span>Varış</span></div>
                            <div class="span1 flight-time-column header-column"><span>Uçuş süresi</span></div>
                            <div class="span1 class-column header-column"><span>Sınıf</span></div>
                        </div>
                    </div>
                </div>

    <div class="row flight-summary">
        <div class="span8">
            $arrival_flights
        </div>
    </div>
    $total_arrival_count_warning_message
EOM;
    }
    $total_departure_count_warning_message = "";
    $total_origin_counts = $flight_all_detail["total_origin_count"];

    if ($total_origin_counts > 1) {// birden fazla  journey varsa
        $total_origin_counts--;
        $total_departure_count_warning_message = <<<EOM
        <div class="row other-departure-journey">
             <div class="span2 offset3">
               <span class="icon-circle-arrow-down" clicked="">Diğer $total_origin_counts   seçenek </span>
              </div>
        </div>
EOM;
    }

    $message = <<<EOM
        <div class='row air-solution $scrolled' > 
            <div class='span8' >
                <div class='row price-detail'>
                    <div class='span8'>
                        <div class="row">
                           <div class="span8 price-head" >
                             <div class="count">
                               <div>
	                        <span class="price-detail-trigger icon-chevron-down" triggered="" >Ayrıntılar</span>
                                <strong>
                                    $current_price
                                </strong>
                              </div>
	                    </div>
                         </div>
	               </div>
	               <div  class="row price-detail-container" style="display:none">
                            $price_detail_message
                       </div>
                    </div>
                </div>
                <div class="row">
                    <div class="span8 price-direction">

                        <strong class="out">Gidiş</strong>
                    </div>
                </div>

                <div class="row flight-summary-colums">
                    <div class="span8 column-header">
                        <div class="row">
                            <div class="span1 select-column header-column"><span>Seç</span></div>
                            <div class="span1 date-column  header-column"><span>Tarih</span></div>
                            <div class="span1 airline-company-column header-column"><span>Havayolu</span></div>
                            <div class="span1 flight-no-column header-column"><span>Uçuş No</span></div>
                            <div class="span1 origin-column header-column"><span>Kalkış</span></div>
                            <div class="span1 destination-column header-column"><span>Varış</span></div>
                            <div class="span1 flight-time-column header-column"><span>Uçuş süresi</span></div>
                            <div class="span1 class-column header-column"><span>Sınıf</span></div>
                        </div>
                    </div>
                </div>

                <div class="row flight-summary">
                    <div class="span8">
                        $departure_flights
                    </div>
                </div>
                $total_departure_count_warning_message
                $arrival_message
            </div>
        </div>
EOM;
    return $message;
}
?>

<div class =" container result-container">

    <div class ="row air-solution-contaier">
        <div class="span3 left-container">
            <div class="row fly-search-container" style="display:none;">
                <div class="span3">
                    <div class="row fly-direction-container">

                        <div class="span1">
                            <label class="radio">
                                <input type="radio"  name="flightdirection" value="2"/> Gidiş/Dönüş
                            </label>
                        </div>
                        <div class="span1">
                            <label class="radio">
                                <input type="radio"  name="flightdirection" value="1"/>Tek Yön
                            </label>
                        </div>
                        <div class="span1">
                            <label class="radio">
                                <input type="radio"  name="flightdirection" value="0"/>Çoklu Uçus
                            </label>
                        </div>

                    </div>
                    <div class="row">
                        <div class="span3 depature-airport">
                            <div class=" label departure-airport-label"><span>Nereden</span></div> 
                            <input  id ="boardingairport" type="hidden" name="boardingairport" placeholder="Nereden"/>
                            <div class="alert-error boardingairpot-alert" style ="display:none">
                                <span class="icon-warning-sign"> </span>Kalkış yeri seçiniz
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="span3 return-airport">
                            <div class=" label landing-airport-label"><span>Nereye</span></div>  
                            <input  id="landingairport" type="hidden" name="landingairport" placeholder="Nereye">
                                <div class="alert-error landingairpot-alert " style ="display:none">
                                    <span class="icon-warning-sign"> </span>İniş  Yeri seçiniz
                                </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class ="span3">
                            <div class="label go-date-label"><span>Gidiş Tarihi</span></div>  
                            <input type="text" id="go_date" placeholder="Gidiş Tarihi"></input>
                            <div class="alert-error godate-alert" style ="display:none">
                                <span class="icon-warning-sign"> </span>Gidiş tarihi giriniz
                            </div>
                        </div>
                    </div>
                    <div class="row return-date_row">
                        <div class ="span3">
                            <div class="label return-date-label"><span>Dönüş Tarihi</span></div>  
                            <input type="text" id="return_date" placeholder ="Dönüş Tarihi"></input>
                            <div class="alert-error returndate-alert" style ="display:none">
                                <span class="icon-warning-sign"> </span>Dönüş tarihi giriniz.
                            </div>
                        </div>
                    </div>
                    <div class="row passenger-type-container" >
                        <div class="span1">
                            <span class="label">Yetişkinler</span>
                            <div class="selectBox">
                                <select name="yetiskinNumber" id="yetiskinNumber">
                                    <option value="0" >0</option>
                                    <option value="1" selected="selected">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                    <option value="9">9</option>
                                    <option value="10">10</option>
                                </select>
                            </div>
                        </div>
                        <div class="span1">
                            <span class="label">Çocuklar</span>
                            <select name="cocukNumber" id="cocukNumber">
                                <option value="0" selected="selected" >0</option>
                                <option value="1" >1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                                <option value="9">9</option>
                                <option value="10">10</option>
                            </select>

                        </div>
                        <div class="span1">
                            <span class="label">Bebekler</span>
                            <select name="bebekNumber" id="bebekNumber">
                                <option value="0" selected="selected"  >0</option>
                                <option value="1" >1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                                <option value="9">9</option>
                                <option value="10">10</option>
                            </select>

                        </div>
                    </div>
                    <div class=" row fly-search-detail">
                        <div class="span2 flight-class-container">

                            <span class="flight-class-label  label1"> Sınıf</span><span class="flight-class-input"> <select id="flight-class-select">

                                    <option value="Economy">Economy</option>
                                    <option value="Premium Economy">Premium Economy</option>
                                    <option value="Business">Business</option>
                                    <option value="First">FirstClass</option>
                                    <option value="all" selected="selected">Hepsi</option>
                                </select></span>

                        </div>
                        <div class="span1 flight-type-container">
                            <span class="flight-type-input">
                                <input type="checkbox" name="flight-type-checkbox"/>
                            </span>
                            <span class="flight-type-label label1" style=" "> Direk Uçuş</span>

                        </div>
                    </div>
                    <div class="row">
                        <div class="span2">

                        </div>
                        <div class="span1 flight-date-option-container">
                            <span class="flight-date-option-input">
                                <input type="checkbox" name="flight-date-option-checkbox"/>
                            </span>
                            <span class="flight-date-option-label label1" style=" "> +/- 3 gün</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="span1"></div>
                        <div class="span1 flight-search-button-container">
                            <span id="searchButton"  href="#">
                                <i class="icon-search icon-1x ">Uçus Ara</i></span>
                        </div>
                        <div class="span1"></div>
                    </div>
                </div>
            </div>

            <div class="row fly-search-filter-container">
                <div class="span3">
                    <div class="row price-filter-container">
                        <div class="span3">
                            <div class="filter-label">
                                <span class="filter-label-icon icon-filter"></span><span class="filter-label-text">Fiyat Aralığı:</span>
                                <span class="min-value"></span>-<span class="max-value"></span>
                            </div>

                            <div class="filter-input">
                                <div class="price-filter-input" style="margin:3px 6px;"></div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
        <div class="span8">
            <div id="domMessage" style="display:none;">
                <h1><img width="70" height="70" src="<?php echo base_url("onlinefly/public_html/img/loading.gif"); ?>"/> Uçuşlar getiriliyor</h1>
            </div>  
            <div class="row best-flight-options-container">

                <div class="span8">
                    <table class="table" cellspacing ="0"  cellpadding="0" style="margin:0 auto;">
                        <?php
                        echo build_flight_summary_table_template($results);
                        ?>
                    </table>
                </div>
            </div>
            <div class="row search-day-nav-container">
                <?php echo buid_search_day_nav_template($search_criteria); ?>	

            </div>
            <div class="row">
                <div class="span8 air-solution-contaier-list ">
                    <?php
                    if (count($results->air_price_solutions) > 0) {
                        $offset = 0;
                        $length = 0;
                        $current_total_price = "";
                        $count = 0;
                        foreach ($results->air_price_solutions as $air_price_solution_item) {
                            if ($current_total_price == "" || $air_price_solution_item->apprixomate_total_price == $current_total_price) {
                                
                            } else {
                                //$xx = array_slice($results->air_price_soltions, $offset, $length);
                                // echo $xx[0]->apprixomate_total_price;
                                //echo "<br>current-$current_total_price-ofseet-$offset</br>";
                                echo build_air_solution_template(array_slice($results->air_price_solutions, $offset, $length), $results, $count);
                                $offset = $offset + $length;
                                $length = 0;
                                $count++;
                            }

                            $length++;
                            $current_total_price = $air_price_solution_item->apprixomate_total_price;
                        }
                    }
                    ?>
                    
                </div>
                <div class="row auto-loading-icon " style="display:none;">
                    <div class="span8 pagination-centered">
                          <i class="icon-spinner icon-spin icon-large"></i>
                    </div>
                </div> 
            </div>
        </div>
    </div>
</div

</div>
<?php
/* requestFlySearchJsonObject['yetiskinNumber'] = $("select[name=yetiskinNumber]").selectBoxIt().val();
  requestFlySearchJsonObject['cocukNumber'] = $("select[name = cocukNumber]").selectBoxIt().val();
  requestFlySearchJsonObject['bebekNumber'] = $("select[name = bebekNumber]").selectBoxIt().val();
  requestFlySearchJsonObject['dateOption'] = $("input:radio[name = dateOption]:checked").val();
  requestFlySearchJsonObject['directionOption'] = $("input:radio[name=flightdirection]:checked").val();
  requestFlySearchJsonObject['cabinClass'] = flightClassInput.select2("val");
  requestFlySearchJsonObject['flightType'] = flightTypeInput.select2("val");
 * 
 * 
 *   public $boardingCode;
  public $landingCode;
  public $godate;
  public $returndate;
  public $dateoption;
  public $flydirection;
  public $yetiskinnumber;
  public $bebeknumber;
  public $cocuknumber;
  public $currency = "EUR";
  public $cabinclass;
  public $flighttype;
 */
echo "<script>";
echo "var session_search_criteria = {};";
echo "var filter = {};";
echo "session_search_criteria['boardingairpotCode'] ='";
echo $search_criteria->boardingCode;
echo "';";
echo "session_search_criteria['landingairpotCode']='" . $search_criteria->landingCode . "';";
echo "session_search_criteria['directionOption']='" . $search_criteria->flydirection . "';";
echo "session_search_criteria['goDate']='" . $search_criteria->godate . "';";
echo "session_search_criteria['returnDate']='" . $search_criteria->returndate . "';";
echo "session_search_criteria['yetiskinNumber']='" . $search_criteria->yetiskinnumber . "';";
echo "session_search_criteria['cocukNumber'] = '" . $search_criteria->cocuknumber . "';";
echo "session_search_criteria['bebekNumber']='" . $search_criteria->bebeknumber . "';";
echo "session_search_criteria['dateOption']='" . $search_criteria->dateoption . "';";
echo "session_search_criteria['cabinClass']='" . $search_criteria->cabinclass . "';";
echo "session_search_criteria['flightType']='" . $search_criteria->flighttype . "';";
echo "filter['max_price'] ='" . $results->max_price . "';";
echo "filter['min_price'] ='" . $results->min_price . "';";

echo "</script>";
?>