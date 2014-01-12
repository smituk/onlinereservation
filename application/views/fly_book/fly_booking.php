<?php
include_once APPPATH . '/services/airline_service.php';

function build_price_detail_template($airPriceInfoItems) {
    $priceDetailHtml = "";
}

function buidOptionHtmlTemplate($minValue, $maxValue) {
    $optionHtml = "";
    for ($i = $minValue; $i <= $maxValue; $i++) {
        if ($i == 1970) {
            $optionHtml = $optionHtml . "<option  selected='selected' value='$i'>$i</option>";
        } else {
            $optionHtml = $optionHtml . "<option value='$i'>$i</option>";
        }
    }
    return $optionHtml;
}

function build_passanger_input_template($passangerType, $passangerTypeText) {

    $thisYear = (int) date('Y');
    $maxYear = 0;
    $minYear = 0;
    if ($passangerType == "ADT") {
        $maxYear = $thisYear - 13;
        $minYear = $thisYear - 100;
    } else if ($passangerType == "CNN") {
        $maxYear = $thisYear - 3;
        $minYear = $thisYear - 12;
    } else if ($passangerType == "INF") {
        $maxYear = $thisYear;
        $minYear = $thisYear - 2;
    }
    $passangerInputHtml = "";
    $passangerInputHtml = $passangerInputHtml . "<div class='passanger-headers'>";
    $passangerInputHtml = $passangerInputHtml . "<span class='passanger-header passanger-type-code' style='display:none'>$passangerType</span>";
    $passangerInputHtml = $passangerInputHtml . "<span class='passanger-header passanger-type'>$passangerTypeText</span>";
    $passangerInputHtml = $passangerInputHtml . "<span class='passanger-header passanger-name'>İsim <span class='required-sign'>*</span></span>";
    $passangerInputHtml = $passangerInputHtml . "<span class='passanger-header passanger-last-name'>Soy İsim<span class='required-sign'>*</span></span>";
    $passangerInputHtml = $passangerInputHtml . "<span class='passanger-header passanger-birthday'>Dogum tarihi</span>";
    $passangerInputHtml = $passangerInputHtml . "</div>";

    $passangerInputHtml = $passangerInputHtml . "<div class='passanger-inputs'>";
    $passangerInputHtml = $passangerInputHtml . "<span class='passanger-input passanger-gender'>";
    $passangerInputHtml = $passangerInputHtml . "<select class='passanger-gender-input'>";
    $passangerInputHtml = $passangerInputHtml . "<option value='M'>Erkek</option>";
    $passangerInputHtml = $passangerInputHtml . "<option value='F'>Kız</option>";
    $passangerInputHtml = $passangerInputHtml . "</select></span>";
    $passangerInputHtml = $passangerInputHtml . "<span class='passanger-input required'>";
    $passangerInputHtml = $passangerInputHtml . "<input type='text' name='passanger-name-input'/>";
    $passangerInputHtml = $passangerInputHtml . "</span>";
    $passangerInputHtml = $passangerInputHtml . "<span class='passanger-input required'>";
    $passangerInputHtml = $passangerInputHtml . "<input type='text' name='passanger-lastname-input'/></span>";
    $passangerInputHtml = $passangerInputHtml . "<span class='passanger-input passanger-birthdate-inputs'>";
    $passangerInputHtml = $passangerInputHtml . "<select class='passenger-birthday-input'>";
    $passangerInputHtml = $passangerInputHtml . buidOptionHtmlTemplate(1, 31);
    $passangerInputHtml = $passangerInputHtml . "</select>";
    $passangerInputHtml = $passangerInputHtml . "<select class='passenger-birtmonth-input'>";
    $passangerInputHtml = $passangerInputHtml . buidOptionHtmlTemplate(1, 12);
    $passangerInputHtml = $passangerInputHtml . "</select>";
    $passangerInputHtml = $passangerInputHtml . "<select class='passenger-birthyear-input'>";
    $passangerInputHtml = $passangerInputHtml . buidOptionHtmlTemplate($minYear, $maxYear);
    $passangerInputHtml = $passangerInputHtml . "</select>";
    $passangerInputHtml = $passangerInputHtml . "</span>";
    $passangerInputHtml = $passangerInputHtml . "</div>";
    $passangerInputHtml = "<div class='passanger'>" . $passangerInputHtml . "</div>";
    return $passangerInputHtml;
}

function build_passanger_template($airPriceInfoItem) {
    
    $passangerHtml = "";
    for ($i = 0; $i < (int) $airPriceInfoItem->passengerCount; $i++) {
        $passangerHtml = $passangerHtml . build_passanger_input_template($airPriceInfoItem->passengerType, $airPriceInfoItem->passengerTypeDesc);
    }
    return $passangerHtml;
}

function build_airsegment_template($airsegmentObject) {
    $airsegment_departure_time = new DateTime($airsegmentObject->departureTime);
    $airsegment_arrival_time = new DateTime($airsegmentObject->arrivalTime);
    $date = $airsegment_departure_time->format('d.m.Y');

    $departure_time = $airsegment_departure_time->format("H:i");
    $arrival_time = $airsegment_arrival_time->format("H:i");
    $airline_company_logo_url = base_url("onlinefly/public_html/img/hava_sirket_icon_logo/hava_sirket_icon_logo/" . $airsegmentObject->carrier . ".png");
    $flight_time = sprintf("%02dh %02dm", floor($airsegmentObject->flightTime / 60), $airsegmentObject->flightTime % 60);
    $name = $airsegmentObject->carrier;
    $airlineCompamyName = AirlineService::getAirlineByIATACode($airsegmentObject->carrier);
    if (isset($airlineCompamyName)) {
        $name = $airlineCompamyName->name;
    }
    $airsegmentHtml = "";
    $airsegmentHtml = $airsegmentHtml . "<span>$date</span>";
    $airsegmentHtml = $airsegmentHtml . "<span><a href='#' class='carrier_count_tooltip'  data-placement='top' data-toggle='tooltip' title='$name'><img src='$airline_company_logo_url' width='26' height='28'/></a></span>";
    $airsegmentHtml = $airsegmentHtml . "<span>$airsegmentObject->carrier $airsegmentObject->flightNumber</span>";
    $airsegmentHtml = $airsegmentHtml . "<span>$airsegmentObject->origin $departure_time</span>";
    $airsegmentHtml = $airsegmentHtml . "<span>$airsegmentObject->destination $arrival_time</span>";
    $airsegmentHtml = $airsegmentHtml . "<span>$flight_time</span>";
    $airsegmentHtml = $airsegmentHtml . "<span><strong>$airsegmentObject->bookingCode  $airsegmentObject->bookingCabinClass</strong></span>";


    return "<div class='airsegment'>" . $airsegmentHtml . "</div>";
}

function build_journey_template($bookPriceVerifyResponse, $legObject, $journey) {
    $journeyHeader = "";
    $airsegmentHeaderHtml = "";
    $airsegmentHeaderHtml = $airsegmentHeaderHtml . "<span>Tarih</span>";
    $airsegmentHeaderHtml = $airsegmentHeaderHtml . "<span>Havayolu</span>";
    $airsegmentHeaderHtml = $airsegmentHeaderHtml . "<span>Uçus No</span>";
    $airsegmentHeaderHtml = $airsegmentHeaderHtml . "<span>Kalkış</span>";
    $airsegmentHeaderHtml = $airsegmentHeaderHtml . "<span>Varış</span>";
    $airsegmentHeaderHtml = $airsegmentHeaderHtml . "<span>Uçuş Süresi</span>";
    $airsegmentHeaderHtml = $airsegmentHeaderHtml . "<span>Sınıf</span>";
    if ($legObject->direction == "G") {
        $journeyHeader = "<div class='span8  journey-header departure'>";
        $journeyHeader = $journeyHeader . "<span class='header-text'>Gidiş</span><span class='departure-icon'></span>";
        $journeyHeader = $journeyHeader . "</div>";
        $airsegmentHeaderHtml = "<div class='column-header departure'>" . $airsegmentHeaderHtml . "</div>";
    } else {
        $journeyHeader = "<div class='span8  journey-header return'>";
        $journeyHeader = $journeyHeader . "<span class='header-text'>Dönüş</span><span class='return-icon'></span>";
        $journeyHeader = $journeyHeader . "</div>";
        $airsegmentHeaderHtml = "<div class='column-header return'>" . $airsegmentHeaderHtml . "</div>";
    }


    $airsegmentsHTML = "";
    foreach ($journey->airSegmentItems as $airsegmentObject) {
        $airsegmentsHTML = $airsegmentsHTML . build_airsegment_template($airsegmentObject);
    }

    $journeyHeader = "<div class='row'>" . $journeyHeader . "</div>";
    return $journeyHeader . $airsegmentHeaderHtml . $airsegmentsHTML;
}

function build_journey_container_template(ResponseBookPriceVerifyData $bookPriceVerifyResponse) {
    $legObjects = $bookPriceVerifyResponse->verifiedAirPriceSolution->legs;
    $journeyContainerHtml = "";
    foreach ($legObjects as $legObject) {
        foreach ($legObject->getJourneys() as $journey) {
            $journeyContainerHtml = $journeyContainerHtml . build_journey_template($bookPriceVerifyResponse, $legObject, $journey);
        }
    }
    return $journeyContainerHtml;
}
?>
<div class="container">
    <div class="row fly-book-info-container">
        <div class="span8 fly-book-passenger-info-container">
            <div class="row">
                <div class="span8">
                    <fieldset class="fly-book-user-info-fieldset fieldset">
                        <legend>İletişim Bilgileriniz</legend>
                        <div class="row">
                            <div class="span8">
                                <div class="fly-book-user-info-fieldset-alert alert alert-error" style="display:none">
                                    <a class="close" data-dismiss="alert">×</a>  
                                    <strong>!</strong> Lütfen zorunlu alanları giriniz.
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="span4 left-info">
                                <div class="row input-row">
                                    <div class="span2 input-label"><span>Cinsiyet</span></div>
                                    <div class="span2 input-value">
                                        <select id="user-gender">
                                            <option value="M">Bay</option>
                                            <option value="F">Bayan</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row input-row">
                                    <div class="span2 input-label"><span>İsim</span><span class="required-sign">*</span></div>
                                    <div class="span2 input-value required"><input name="user-name" type="text" /></div>
                                </div>
                                <div class="row input-row">
                                    <div class="span2 input-label"><span>Soy İsim</span><span class="required-sign">*</span></div>
                                    <div class="span2 input-value required"><input name="user-lastname" type="text" /></div>
                                </div>
                                <div class="row input-row">
                                    <div class="span2 input-label"><span>Email</span><span class="required-sign">*</span></div>
                                    <div class="span2 input-value required"><input name="user-email" type="text" /></div>
                                </div>
                                <div class="row input-row">
                                    <div class="span2 input-label"><span>Email Tekrar</span><span class="required-sign">*</span></div>
                                    <div class="span2 input-value required"><input name="user-email-repeat" type="text" /></div>
                                </div>
                            </div>
                            <div class="span4 right-info">
                                <div class="row input-row">
                                    <div class="span2 input-label"><span>Telefon</span><span class="required-sign">*</span></div>
                                    <div class="span2 input-value required"><input name="user-tel" type="text" /></div>
                                </div>
                                <div class="row input-row">
                                    <div class="span2 input-label"><span>Cep Telefon</span><span class="required-sign">*</span></div>
                                    <div class="span2 input-value required"><input name="user-ceptel" type="text" /></div>
                                </div>
                                <div class="row input-row">
                                    <div class="span2 input-label"><span>Şehir</span><span class="required-sign">*</span></div>
                                    <div class="span2 input-value required"><input name="user-city" type="text" /></div>
                                </div>
                                <div class="row input-row">
                                    <div class="span2 input-label"><span>Ülke</span><span class="required-sign">*</span></div>
                                    <div class="span2 input-value required"><select name="user-country" ><option value ="TR">Türkiye</option></select></div>
                                </div>
                                <div class="row input-row">
                                    <div class="span2 input-label"><span>Posta Kodu</span><span class="required-sign">*</span></div>
                                    <div class="span2 input-value required"><input name="user-zipcode" type="text" /></div>
                                </div> 
                            </div>

                        </div>
                    </fieldset>

                </div>

            </div>
            <div class="row">
                <div class="span8"> 
                    <fieldset class="fly-book-flight-info-fieldset fieldset">
                        <legend>Uçuş Detayları</legend>
                        <div class="row">
                            <div class="span8 journey-container">
<?php
echo build_journey_container_template($bookingPriceVerifiedSolution);
?>
                            </div>
                        </div>
                    </fieldset>  
                </div>     
            </div>
            <div class="row">
                <div class="span8"> 
                    <fieldset class="fly-book-passanger-info-fieldset fieldset">
                        <legend>Yolcu(lar)</legend>
                        <div class="row">
                            <div class="span8">
                                <div class="fly-book-passanger-info-fieldset-alert alert alert-error" style="display:none">
                                    <a class="close" data-dismiss="alert">×</a>  
                                    <strong>!</strong> Lütfen zorunlu alanları giriniz.
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="span8 passanger-container">
<?php
foreach (array_values($bookingPriceVerifiedSolution->verifiedAirPriceSolution->airPricingInfoArray) as $airPriceInfoArray) {
   
   foreach($airPriceInfoArray as $airPriceInfoItem){    
      echo build_passanger_template($airPriceInfoItem);
   }
   break;
}
?>

                            </div>

                                <?php
                                //echo build_journey_container_template($bookingPriceVerifiedSolution);
                                ?>
                        </div>
                        <div class="row">
                            <div class="span8 button-container">
                                <button class="md-approve"><span>İşlemi Tamamla</span> <i class="icon-chevron-right icon-1x "></i>  </button> 
                                <div style="clear:both"></div>
                            </div>
                        </div>

                    </fieldset>  
                </div>
            </div>   

        </div>




        <div class="span3 fly-book-price-info-container">
            <div class="row">
                <div class="span3">
                    <fieldset class="fly-book-price-info-fieldset fieldset">
                        <legend>Fiyat Bilgisi</legend>

                        <div class="row price-detail-container">
                            <div class="span3">
                                <div class="row price-element">
                                    <div class="span3 passanger-price-total">
                                        <div class="price-total-summary">
                                            <span class="info-label"><i class="icon-hand-right"></i>Ücret</span>
                                            <i class="icon-chevron-sign-down"></i>
                                            <span class="info-value"><span><?php echo $bookingPriceVerifiedSolution->verifiedAirPriceSolution->approximateBasePriceAmount ?></span></span>
                                        </div>
                                        <div class="price-total-detail"></div>
                                    </div>

                                </div>
                                <div class="row price-element">
                                    <div class="span3 passanger-tax-price-total">
                                        <div class="price-total-summary">
                                            <span class="info-label"><i class="icon-hand-right"></i>Vergiler</span>
                                            <span class="info-value"><?php echo $bookingPriceVerifiedSolution->verifiedAirPriceSolution->taxesAmount ?></span>
                                        </div>
                                        <div class="price-total-detail"></div>
                                    </div>

                                </div>

                                <div class="row price-element">
                                    <div class="span3 passanger-all-price-total">
                                        <div class="price-total-summary">
                                            <span class="info-label"><i class="icon-hand-right"></i>Toplam bilet tutarı</span>
                                            <span class="info-value"><?php echo $bookingPriceVerifiedSolution->verifiedAirPriceSolution->apprixomateTotalPriceAmount ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                </div>
            </div>
        </div>

    </div>

</div>