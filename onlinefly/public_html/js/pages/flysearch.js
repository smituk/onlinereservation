/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


var goDateInput = $("#go_date");
var returnDateInput = $("#return_date");
var boardingairportInput = $("#boardingairport");
var landingairportInput = $("#landingairport");
var flyDirectionoptionInput = $("input:radio[name=flightdirection]");
var searchButton = $("#searchButton");

var flightClassInput = $("#flight-class-select");
var flightTypeInput = $("#flight-type-select");

var flightTypeInput;


var oneDirectioValue = 1;
var doubleDirection = 2;
var cokluDirection = 0;



function initialize() {
    $(document).ready(function() {

	$("#yetiskinNumber").selectBoxIt();
	$("select[name = cocukNumber]").selectBoxIt();
	$("select[name = bebekNumber]").selectBoxIt();
	var currentDate = new Date();

	var defaultGoDate = new Date();
	defaultGoDate.setDate(defaultGoDate.getDate() + 1);
	goDateInput.datepicker({
	    showOn: "button",
	    buttonImage: "../beyhan_fly/onlinefly/public_html/css/ico/calendar_small.jpg",
	    buttonImageOnly: true,
	    numberOfMonths: 2,
	    minDate: currentDate,
	    defaultDate: $.datepicker.formatDate("dd/mm/yy", currentDate),
	    dateFormat: "dd/mm/yy",
             onSelect: function(date) {
                returnDateInput.datepicker("option",'minDate',date);
         }
	});

	goDateInput.val(convertDateToString(defaultGoDate));
	var defaultReturnDate = defaultGoDate;
	defaultReturnDate.setDate(defaultReturnDate.getDate() + 7);
	returnDateInput.datepicker({
	    showOn: "button",
	    buttonImage: "../beyhan_fly/onlinefly/public_html/css/ico/calendar_small.jpg",
	    buttonImageOnly: true,
	    numberOfMonths: 2,
	    minDate: currentDate,
	    defaultDate: +14,
	    dateFormat: "dd/mm/yy"


	});
	returnDateInput.val(convertDateToString(defaultReturnDate));
	boardingairportInput.select2({data: airports, minimumInputLength: 3, placeholder: "Gidiş", allowClear: true, matcher: function(term, text) {
		return text.toUpperCase().indexOf(term.toUpperCase()) == 0;
	    }});
	landingairportInput.select2({data: airports, minimumInputLength: 3, placeholder: "Dönüş", allowClear: true, matcher: function(term, text) {
		return text.toUpperCase().indexOf(term.toUpperCase()) == 0;
	    }});
	flightClassInput.select2();
	flightTypeInput.select2();
        
        var  previousSearchCriteria = getPreviousSearchCriteria();
        if(previousSearchCriteria != null){
            boardingairportInput.select2("val", previousSearchCriteria["boardingairportCode"]);
            landingairportInput.select2("val",  previousSearchCriteria["landingairportCode"]);
        }
	flyDirectionoptionInput.click(function() {
	    onflyDirecitonOptionClick($(this).val());
	});

	searchButton.click(function() {
	    onflySearchButtonClick();
	});

    });
    
     $(document).ajaxStop($.unblockUI);
}

function onflyDirecitonOptionClick(value) {
    if (value == oneDirectioValue) {
	//returnDateInput.datepicker("option", "disabled", false);
        $(".return-date-container").hide(100);
    } else if (value == doubleDirection) {
	//returnDateInput.datepicker("option", "disabled", true);
        $(".return-date-container").show(200);
    } else if (value == cokluDirection) {
	openCokluUcusOption();
    }
}
function openCokluUcusOption() {


}


function   onflySearchButtonClick() {
    var valid = validflySearchRequest();
    if (!valid) {
	return;
    }
    
    $.blockUI({message: $('#domMessage'), backgroundColor: "transparent"});
    var succesResponse = doflySearch(responseFlySearch);
}

function  validflySearchRequest() {
    var valid = true;
    var boardingportCode = boardingairportInput.select2("val");

    if (boardingportCode == null || boardingportCode.length < 1) {
	valid = false;
	$(".boardingairpot-alert").show();
    }
    var landingairportCode = landingairportInput.select2("val");
    if (landingairportCode == null || landingairportCode.length < 1) {
	valid = false;
	$(".landingairpot-alert").show();
    }


    var goDate = goDateInput.datepicker("getDate");
    if ($("input:radio[name=flightdirection]:checked").val() == 2) {
	if (goDate == null || goDate.length < 1) {
	    valid = false;
	    $(".godate-alert").show();
	} else {
	    goDate = $.datepicker.formatDate("yy-mm-dd", goDate);
	}
    }
    var returnDate = returnDateInput.datepicker("getDate");
    if (returnDate == null || returnDate.length < 1) {
	valid = false;
	$(".returndate-alert").show();
    } else {
	returnDate = $.datepicker.formatDate("yy-mm-dd", returnDate);
    }


    yetiskinSelectBox = $("#yetiskinNumber").selectBoxIt().val();
    cocukSelectBox = $("select[name = cocukNumber]").selectBoxIt().val();
    bebekSelectBox = $("select[name = bebekNumber]").selectBoxIt().val();

    var dateOptionValue = $("input:radio[name = dateOption]:checked").val();




    return valid;
}

function  buildflySearchRequest() {
    var requestFlySearchJsonObject = {};
    requestFlySearchJsonObject['boardingairpotCode'] = boardingairportInput.select2("val");
    requestFlySearchJsonObject['landingairpotCode'] = landingairportInput.select2("val");
    requestFlySearchJsonObject['goDate'] = $.datepicker.formatDate("yy-mm-dd", goDateInput.datepicker("getDate"));
    requestFlySearchJsonObject['returnDate'] = $.datepicker.formatDate("yy-mm-dd", returnDateInput.datepicker("getDate"));
    requestFlySearchJsonObject['yetiskinNumber'] = $("select[name=yetiskinNumber]").selectBoxIt().val();
    requestFlySearchJsonObject['cocukNumber'] = $("select[name = cocukNumber]").selectBoxIt().val();
    requestFlySearchJsonObject['bebekNumber'] = $("select[name = bebekNumber]").selectBoxIt().val();
    requestFlySearchJsonObject['dateOption'] = $("input:radio[name = dateOption]:checked").val();
    requestFlySearchJsonObject['directionOption'] = $("input:radio[name=flightdirection]:checked").val();
    requestFlySearchJsonObject['cabinClass'] = flightClassInput.select2("val");
    requestFlySearchJsonObject['flightType'] = flightTypeInput.select2("val");
    
    var airSearchLegs = new Array();
    var firstAirSearchLeg  = {};
    firstAirSearchLeg.origin = {};
    firstAirSearchLeg.destination = {};
    firstAirSearchLeg.origin.airport = boardingairportInput.select2("val");
    firstAirSearchLeg.destination.airport = landingairportInput.select2("val");
    firstAirSearchLeg.departureTime = $.datepicker.formatDate("yy-mm-dd", goDateInput.datepicker("getDate"));
    airSearchLegs.push(firstAirSearchLeg);
    var secondAirSearchLeg = {};
    secondAirSearchLeg.origin = {};
    secondAirSearchLeg.destination = {};
    secondAirSearchLeg.origin.airport = landingairportInput.select2("val");
    secondAirSearchLeg.destination.airport = boardingairportInput.select2("val");
    secondAirSearchLeg.departureTime  = $.datepicker.formatDate("yy-mm-dd", returnDateInput.datepicker("getDate"));
    airSearchLegs.push(secondAirSearchLeg);
    requestFlySearchJsonObject['airSearchLegs'] = airSearchLegs;
    return requestFlySearchJsonObject;
}

function  doflySearch(_callback) {
    var requestFlySearchJsonObject = buildflySearchRequest();
    setSearchCriteriaToCookie(requestFlySearchJsonObject);
    var url = "index.php/searchflyrequest";
    $.post(url, requestFlySearchJsonObject, function(data) {
	if (_callback != null) {
	    _callback(data);
	}
    },'json').fail( function(xhr, textStatus, errorThrown) {
        alert(errorThrown+"-"+xhr.responseText+"-"+textStatus);
    });

}

function responseFlySearch(data) {
    if (data !== null && data.data > 0) {

	document.location.href = "index.php/searchresults";

    }else{
        alert("Uçus Bulunamadı");
    }
}

function setLoaderModal() {
    $(function() {
	$("#dialog").dialog({
	    dialogClass: 'transparent',
	    resizable: false,
	    draggable: false,
	    modal: true,
	    height: 0,
	    width: 0,
	    autoOpen: false,
	    overlay: {
		opacity: 0
	    }

	});
    });
}


function setSearchCriteriaToCookie(searchCriteria){
   // createCookie foksiyonu cookie-util.js dosyasından gelmektedir createCookie("author", "aurelio", 30);
  createCookie("boardingairpotCode",searchCriteria["boardingairpotCode"],100);  
  createCookie("landingairpotCode",searchCriteria["landingairpotCode"],100);
}

function getPreviousSearchCriteria(){
   
    var searchCriteria = {};
    if( readCookie("boardingairpotCode") == null || readCookie("landingairpotCode") == null){
        return  null;
    }
    searchCriteria["boardingairportCode"] = readCookie("boardingairpotCode");
    searchCriteria["landingairportCode"] = readCookie("landingairpotCode");
    return searchCriteria;
}


function convertDateToString(date){
    var day = date.getDate();
    if(date.getDate() < 10){
        day = "0"+ day;
    }
    var month = date.getMonth() +  1;
    if(month  < 10){
        month = "0" +  month;
    }
    return day+"/"+month + "/" + date.getFullYear();
}

initialize();
