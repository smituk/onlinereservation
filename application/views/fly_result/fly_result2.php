


<?php

include_once APPPATH . '/helpers/fly_search_helper.php';
include_once APPPATH . '/services/airline_service.php';

function build_flight_summary_table_template($price_data_table) {

    $airline_company_array_data = $price_data_table["airline_company_array"];
    $no_stop_flight_array_data = $price_data_table["no_stop_flight_array"];
    $stop_flight_array_data = $price_data_table["stop_flight_array"];
    $moreone_stop_flight_array_data = $price_data_table["moreone_stop_flight"];

    $template = "<td class='rowhead'><span>Bütün uçuşlar</span></td>";
    for ($k = 0; $k < count($airline_company_array_data); $k++) {

        if (isset($airline_company_array_data[$k])) {
            $airlineCompanyName = AirlineService::getAirlineByIATACode($airline_company_array_data[$k]);
            if ($airlineCompanyName)
                $template.="<td align='center' valign='middle'  class='header'><img width='25' height='25'src='" . base_url("onlinefly/public_html/img/hava_sirket_icon_logo/hava_sirket_icon_logo/" . $airline_company_array_data[$k] . ".png") . "'/><div class='company-name'>$airlineCompanyName->name</div></td>";
        } else {
            $template.="<td align='center' valign='middle'  class='header'></td>";
        }
    }
    $template = "<tr>" . $template . "</tr><tr><td class='rowhead'>Direk</td>";
    for ($k = 0; $k < count($airline_company_array_data); $k++) {
        if (isset($no_stop_flight_array_data[$k])) {
            $template.="<td align='center' valign='middle'  class='header2 non-stop' airlinecompany='$airline_company_array_data[$k]'>" . $no_stop_flight_array_data[$k] . "</td>";
        } else {
            $template.="<td align='center' valign='middle'  class='header'></td>";
        }
    }
    $template = "<tr>" . $template . "</tr><tr><td class='rowhead'> 1 Aktarma</td>";
    for ($k = 0; $k < count($airline_company_array_data); $k++) {
        if (isset($stop_flight_array_data[$k])) {
            $template.="<td align='center' valign='middle'  class='header2 one-stop' airlinecompany='$airline_company_array_data[$k]'>" . $stop_flight_array_data[$k] . "</td>";
        } else {
            $template.="<td align='center' valign='middle'  class='header'></td>";
        }
    }
    $template = "<tr>" . $template . "</tr><tr><td class='rowhead'> 2+ Aktarma</td>";
    for ($k = 0; $k < count($airline_company_array_data); $k++) {
        if (isset($moreone_stop_flight_array_data[$k])) {
            $template.="<td align='center' valign='middle'  class='header2 more-stop' airlinecompany='$airline_company_array_data[$k]'>" . $moreone_stop_flight_array_data[$k] . "</td>";
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
    foreach ($air_price_solution_item->air_price_info_items as $air_price_info_item) {
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
        $name = $flight_direction . "_price_" . $air_price_solution_item->apprixomate_total_price_amount;
        $message_option = "<input type='radio' name='$name' value='$air_price_solution_item->apprixomate_total_price_amount' airsegment_key='$airsegment_object->key' />";
    }
    $remain_book_count_warning_message = "";
    if ($airsegment_object->avaible_booking_count < 9) {
        $remain_book_count_warning_message = "(" . $airsegment_object->avaible_booking_count . ")";
    }
    //@TODO Diğer apilerde bunlar kontrol edilecek
    $airsegment_departure_time = new DateTime($airsegment_object->departure_time);
    $airsegment_arrival_time = new DateTime($airsegment_object->arrival_time);
    $date = $airsegment_departure_time->format('d.m.Y');

    $departure_time = $airsegment_departure_time->format("H:i");
    $arrival_time = $airsegment_arrival_time->format("H:i");
    $airline_company_logo_url = base_url("onlinefly/public_html/img/hava_sirket_icon_logo/hava_sirket_icon_logo/" . $airsegment_object->carrier . ".png");
    $flight_time = sprintf("%02dh %02dm", floor($airsegment_object->flight_time / 60), $airsegment_object->flight_time % 60);
    $name = $airsegment_object->carrier;
    $airlineCompamyName = AirlineService::getAirlineByIATACode($airsegment_object->carrier);
    if (isset($airlineCompamyName)) {
        $name = $airlineCompamyName->name;
    }
    $message = <<<EOM
    <div class="row" segment="$airsegment_object->key">
         <div class="span1 select-column header-column"><span>$message_option</span></div>
         <div class="span1 date-column  header-column-value"><span>$date</span></div>
         <div class="span1 airline-company-column header-column-value"><span><a href="#" class="carrier_count_tooltip"  data-placement="top" data-toggle="tooltip" title="$name"><img width="25" height="25" src="$airline_company_logo_url" alt="$airsegment_object->carrier"/></a></span></div>
         <div class="span1 flight-no-column header-column-value"><span>$airsegment_object->carrier </span> <span>$airsegment_object->flight_number</span></div>
        <div class="span1 origin-column header-column-value"><span>$airsegment_object->origin  </span><span>$departure_time</span></div>
        <div class="span1 destination-column header-column-value"><span>$airsegment_object->destination  </span> <span>$arrival_time</span><span></span></div>
        <div class="span1 flight-time-column header-column-value"><span>$flight_time</span></div>
        <div class="span1 class-column header-column-value"><span><a href="#" class="book_count_tooltip"  data-placement="top" data-toggle="tooltip" title="$airsegment_object->booking_counts">$airsegment_object->booking_code</a></span><span class="remain-book-count"> $remain_book_count_warning_message</span><span class="cabin-class">  $airsegment_object->booking_cabin_class</span></div>

   </div>
EOM;
    return $message;
}

function build_all_flight_detail_template($combined_air_price_solution) {
    $departure_message = "";
    $arrival_message = null;
    if (count($combined_air_price_solution->departure_journeys) > 0) {
        foreach ($combined_air_price_solution->departure_journeys as $departure_journey) {
            $departure_message = $departure_message . "<div id='$departure_journey->key' class='journey row go-journey $departure_journey->related_journey_id' airline-company='$departure_journey->air_company'  air-price-solution-key='$departure_journey->air_price_solution_key_ref' ><div class='span8'>";
            $i = 0;
            foreach ($departure_journey->air_segment_items as $airsegment_object) {
                if ($i == 0) {
                    $departure_message = $departure_message . build_flight_detail_template($airsegment_object, $combined_air_price_solution, TRUE, 1);
                } else {
                    $departure_message = $departure_message . build_flight_detail_template($airsegment_object, $combined_air_price_solution, FALSE, 1);
                }
                $i++;
            }
            $departure_message = $departure_message . "</div></div>";
        }
    }

    if (count($combined_air_price_solution->return_journeys) > 0) {
        foreach ($combined_air_price_solution->return_journeys as $return_journey) {
            $i = 0;
            $arrival_message = $arrival_message . "<div id='$return_journey->key' class='journey row return-journey $return_journey->related_journey_id' airline-company='$return_journey->air_company'  air-price-solution-key='$return_journey->air_price_solution_key_ref'><div class='span8'>";
            foreach ($return_journey->air_segment_items as $airsegment_object) {
                if ($i == 0) {
                    $arrival_message = $arrival_message . build_flight_detail_template($airsegment_object, $combined_air_price_solution, TRUE, 2);
                } else {
                    $arrival_message = $arrival_message . build_flight_detail_template($airsegment_object, $combined_air_price_solution, FALSE, 2);
                }
                $i++;
            }
            $arrival_message = $arrival_message . "</div></div>";
        }
    }
    $flight_deatils_message = array("origins" => $departure_message, "arrivals" => $arrival_message,);
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

function build_air_solution_template($combined_air_price_solution, $count) {
    $scrolled = "scrolled";
    //bu flag ile scroll aşağı indiği zaman goruntülenek elementlerin bulunması sağlanır
    // scrolled ise gorunmekte ,  no-scrolled ise gorunmemeketedir air solution element
    if ($count > 5) {
        $scrolled = "no-scrolled";
    }

    $flight_all_detail = build_all_flight_detail_template($combined_air_price_solution);
    $departure_flights = $flight_all_detail["origins"];
    $arrival_flights = $flight_all_detail["arrivals"];
    $price_detail_message = build_flight_price_detail_template($combined_air_price_solution);
    $current_price = $combined_air_price_solution->apprixomate_total_price;
    $arrival_message = "";

    if ($arrival_flights != null) {
        $total_arrival_count_warning_message = "";
        $total_arrival_counts = count($combined_air_price_solution->return_journeys);
        if ($total_arrival_counts > 1) {// birden fazla  journey varsa
            $total_arrival_counts--;
            $total_arrival_count_warning_message = <<<EOM
        <div class="row other-arrival-journey">
             <div class="span8">
               <span class="icon-circle-arrow-down" clicked="">Diğer $total_arrival_counts  Dönüş </span>
              </div>
        </div>
EOM;
        }
        $arrival_message = <<<EOM
    <div class="row">
        <div class="span8 price-direction">
            <strong class="in">Dönüş</strong>
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
    $total_origin_counts = count($combined_air_price_solution->departure_journeys);
    if ($total_origin_counts > 1) {// birden fazla  journey varsa
        $total_origin_counts--;
        $total_departure_count_warning_message = <<<EOM
        <div class="row other-departure-journey">
             <div class="span8">
               <span class="icon-circle-arrow-down" clicked="">Diğer $total_origin_counts   Gidiş </span>
              </div>
        </div>
EOM;
    }

    $message = <<<EOM
        <div  id='$combined_air_price_solution->combined_key' class='row air-solution $scrolled' > 
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
                <div class="row ">
                   <div class="span8  booking-process-button-container">
                      <div class="row">
                        <div class="span2 offset6 booking-button">
                               <span class="bookingButton"  href="#">
                                <span class="booking-button-text"> Bilet Al </span>
                                <i class="icon-chevron-right icon-1x "></i>
                              </span>
                        </div> 
                      </div>
                   </div>
                 </div>
            </div>
        </div>
EOM;
    return $message;
}
?>

<div class =" container result-container">

    <div class ="row air-solution-contanier">
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
                                <input type="radio"  name="flightdirection" value="0"/>Ç. Uçus
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
                    <div class="row price-filter-container ">
                        <div class="span3  filter-element">
                            <div class="filter-label">
                                <span class="filter-label-icon icon-filter"></span><span class="filter-label-text">Fiyat Aralığı:</span>
                                <span class="min-value"></span>-<span class="max-value"></span>
                            </div>

                            <div class="filter-input">
                                <div class="price-filter-input" style="margin:3px 6px;"></div>

                            </div>
                        </div>
                    </div>
                    <div class="row flytype-filter-container ">
                        <div class="span3  filter-element">
                            <div class="row"> 
                                <div class="filter-label">  
                                    <div class="span3">
                                        <span class="filter-label-icon icon-filter"></span><span class="filter-label-text">Duraklar</span>
                                    </div>

                                </div>
                            </div>
                            <div class="row"> 
                                <div class="span3"> 

                                    <div class="filter-input-checkbox">
                                        <input type="checkbox" name="stopCount" value="0" <?php echo $flySearchResultFilterValues->isNoStopFlightExist === true ? " checked" : " disabled"; ?>/> 
                                        <span class="checkbox-label">Direk</span>
                                    </div>
                                    <div class="filter-input-checkbox">
                                        <input type="checkbox" name="stopCount" value="1" <?php echo $flySearchResultFilterValues->isOneStopFlightExist === true ? " checked" : " disabled"; ?>/> 
                                        <span class="checkbox-label">1 Aktarma</span>
                                    </div>
                                    <div class="filter-input-checkbox">
                                        <input type="checkbox" name="stopCount" value="2" <?php echo $flySearchResultFilterValues->isTwoMoreStopFlightExist === true ? " checked" : " disabled"; ?>/> 
                                        <span class="checkbox-label">2+ Aktarma</span>
                                    </div>
                                </div>
                            </div>


                        </div>
                    </div>
                    <div class="row filter-departure-time-container">
                        <div class="span3 filter-element">
                            <div class="row"> 
                                <div class="filter-label">  
                                    <div class="span3">
                                        <span class="filter-label-icon icon-filter"></span><span class="filter-label-text">Kalkış Saatleri</span>
                                        <div class="go-departure-time-filter-container filter-input">
                                            <div class="filter-departure-time-info">
                                                <span class="info-label">Gidiş :</span><span class="info-value"></span>
                                            </div>
                                            <div class="go-departure-time-filter-input"></div>
                                        </div>
                                        <div class="return-departure-time-filter-container filter-input">
                                            <div class="filter-departure-time-info">
                                                <span class="info-label">Dönüş :</span><span class="info-value">00:00-23:59</span>
                                            </div>
                                            <div class="return-departure-time-filter-input"></div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row arrival-filter-time-container">
                        <div class="span3 filter-element">
                            <div class="row"> 
                                <div class="filter-label">  
                                    <div class="span3">
                                        <span class="filter-label-icon icon-filter"></span><span class="filter-label-text">Varış Saatleri</span>
                                        <div class="go-arrival-time-filter-container filter-input">
                                            <div class="filter-departure-time-info">
                                                <span class="info-label">Gidiş :</span><span class="info-value">00:00-23:59</span>
                                            </div>
                                            <div class="go-arrival-time-filter-input"></div>
                                        </div>
                                        <div class="return-arrival-time-filter-container filter-input">
                                            <div class="filter-arrival-time-info">
                                                <span class="info-label">Dönüş :</span><span class="info-value">00:00-23:59</span>
                                            </div>
                                            <div class="return-arrival-time-filter-input"></div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row airline-filter-container">
                        <div class="span3  filter-element">
                            <div class="row"> 
                                <div class="filter-label">  
                                    <div class="span3">
                                        <span class="filter-label-icon icon-filter"></span><span class="filter-label-text">Hava Yolları Şirketi </span>
                                    </div>

                                </div>
                            </div>
                            <div class="row"> 
                                <div class="span3"> 

                                    <?php
                                    $existAirlineCompanies = $flySearchResultFilterValues->existAirlineCompanies;
                                    foreach ($existAirlineCompanies as $airlineCompany) {
                                        echo "<div class='filter-input-checkbox'>
                                                      <input type='checkbox' name='airlineCompanyCodeCheckbox' value='$airlineCompany->code' checked/>
                                                      <span class='filter-label-text'> $airlineCompany->code - $airlineCompany->name </span></div><br>";
                                    }
                                    ?>

                                </div>
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
                        echo build_flight_summary_table_template($price_data_table);
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
                    $count = 0;
                    foreach ($combined_air_solutions_array as $combined_air_solution_item) {
                        echo build_air_solution_template($combined_air_solution_item, $count);
                        $count++;
                    }
                    ?>

                </div>
                <div class="row auto-loading-icon " style="display:none;">
                    <div class="span8 pagination-centered">
                        <i class="icon-spinner icon-spin icon-large"></i>
                    </div>
                </div>
                <div class="row auto-filter-loading-icon " style="display:none;">
                    <div class="span8 pagination-centered">
                        <i class="icon-refresh icon-spin icon-large"></i>
                    </div> 
                </div>  
                <div class="row filter-request-message " style="display:none;">
                    <div class="span8 pagination-centered">
                        <span class="icon-warning-sign"> </span> <p>Kriterlere göre uçuş bulunamadı.</p>
                    </div>        
                </div> 

            </div>
        </div>
    </div>

    <script type="tex/tempate" id="bookingVerifySummaryInfoTemplate">
     
      <h3 id="bookPrice">Fiyat : <%= apprixomate_total_price %></h3>
       <span class="price-detail-trigger icon-chevron-down">Ayrıntılar</span>  
    </script>
    <script type="text/template" id="bookPriceDetailTempalte">
     <div class="bookPriceDetail">
       <span><%= passenger_type_desc %> x <%= passenger_count %> </span>
       <span><%= approximate_base_price_amount %></span>
       <span><%= taxes_amount %></span>
       <span><%= all_total_price %></span>
    </div>
    </script>
    <script type="text/template" id="airSegmentTemplate">
       <div class="air-segment">
       
           <span class="header-column"><%=departure_date%></span>
         <span class="header-column"><img src="<?php echo base_url("onlinefly/public_html/img/hava_sirket_icon_logo/hava_sirket_icon_logo/"); ?><%=carrier %>.png" width="25" height="25"/></span>
         <span class="header-column"><%=carrier%> <%=flight_number%></span>
         <span class="header-column"><%=origin%> <%=departure_hours%></span>
         <span class="header-column"><%=destination%> <%=arrival_hours%></span>
         <span class="header-column"><%=flight_time%></span>
         <span class="header-column"><strong><%=booking_code%></strong> <%=booking_cabin_class%></span>
       </div>
    </script>
    
<!--    
    arrival_time: "2013-08-25T18:35:00.000+03:00"
avaible_booking_count: null
booking_cabin_class: "Economy"
booking_code: "V"
booking_counts: null
carrier: "PS"
carrierName: "Ukraine International Airlines"
departure_time: "2013-08-25T14:50:00.000+02:00"
destination: "KBP"
distance: "1119"
equipment: ""
eticket_avability: ""
flight_number: "104"
flight_ref: null
flight_time: "165"
group: "0"
key: "dyNBBeh6S4uoc8fGPxHMdA=="
origin: "AMS"
provider_code: "1G"
-->
    <script type="text/template" id="bookedJourneyTemplate">
      <div class="journey">
       <div class="journey-header">
       <div class="price-direction">
           <strong class="<%= journeyDirectionType %>"><%= journeyDirectionTypeText %></strong>
       </div>
       <div class="colum-header">
         <span class="header-column">Tarih</span>
         <span class="header-column">Havayolu</span>
         <span class="header-column">Uçuş No</span>
         <span class="header-column">Kalkış</span>
         <span class="header-column">Varış</span>
         <span class="header-column">Uçuş Süresi</span>
         <span class="header-column">Sınıf</span>    
       </div>        
       </div>
       </div>
    </script>
    
     
            <div class="md-modal md-effect-1 " id="bookPriceVerifyModal">
                <div class="md-content ">
                    <div class="close-button"><i class="icon-remove-sign"></i></div>
                        <div class="bookVerifyPrice">
                             
                         <div class="bookVerifyPriceText">
                             <div style="float: none"></div>
                         </div>
                        </div>
                        <div class="bookVerifyPriceDetails">
                          
                                <div class="bookPriceDetailHeader">
                                    <span>Yolcular </span>
                                    <span>Yolcu başına tarife</span>
                                    <span>Yolcu başına vergiler ve harçlar</span>
                                    <span>Toplam fiyat</span>
                                </div>   
                        
                        </div>
                        <div class="journey-list"></div>
                        <div class="actionButtons">

                            <button class="md-approve"><span>Bileti Al</span> <i class="icon-chevron-right icon-1x "></i>  </button> 
                            <div style="clear:both"></div>
                           
                        </div>
                    </div>
                

    </div>
    <div class="md-overlay"></div>
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
    echo "filter['max_price'] ='" . $flySearchResultFilterValues->maxPriceAmount . "';";
    echo "filter['min_price'] ='" . $flySearchResultFilterValues->minPriceAmount . "';";
    echo "filter['goDepartureTimeMinValue']='" . $flySearchResultFilterValues->goDepartureTimeMinValue . "';";
    echo "filter['goDepartureTimeMaxValue']='" . $flySearchResultFilterValues->goDepartureTimeMaxValue . "';";
    echo "filter['returnDepartureTimeMaxValue']='" . $flySearchResultFilterValues->returnDepartureTimeMaxValue . "';";
    echo "filter['returnDepartureTimeMinValue']='" . $flySearchResultFilterValues->returnDepartureTimeMinValue . "';";
    echo "filter['goArrivalTimeMinValue']='" . $flySearchResultFilterValues->goArrivalTimeMinValue . "';";
    echo "filter['goArrivalTimeMaxValue']='" . $flySearchResultFilterValues->goArrivalTimeMaxValue . "';";
    echo "filter['returnArrivalTimeMinValue']='" . $flySearchResultFilterValues->returnArrivalTimeMinValue . "';";
    echo "filter['returnArrivalTimeMaxValue']='" . $flySearchResultFilterValues->returnArrivalTimeMaxValue . "';";
    echo "</script>";
    
    
    ?>