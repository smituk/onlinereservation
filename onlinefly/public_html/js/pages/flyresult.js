/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
/*
 // book count Tool tip  gosterimi için
 $(".book_count_tooltip").each(function() {
 var book_count_tooltip_elements = $(this);
 book_count_tooltip_elements.tooltip({'data-placement': 'top'});
 });
 */

var PRICE_FILTER_TYPE = 1;
var STOP_COUNT_FILTER_TYPE = 2;
var GO_DEPARTURE_TIME_FILTER_TYPE = 3;
var RETURN_DEPARTURE_TIME_FILTER_TYPE = 4;
var GO_ARRIVAL_TIME_FILTER_TYPE = 5;
var RETURN_ARRIVAL_TIME_FILTER_TYPE = 6;
var AIRLINE_COMPANY_FILTER_TYPE = 7;
var filterCriteriaObject = new Object();
var selectedBookSolution = new Object();
var verifiedBookPriceSolutionApp = {
    Models: {},
    Collections: {},
    Views: {},
    Templates: {}
}
$(document).ready(function() {

    var boardingairportInput = $("#boardingairport");
    boardingairportInput.select2({data: airports, minimumInputLength: 3, placeholder: "Gidiş", allowClear: true, matcher: function(term, text) {
            return text.toUpperCase().indexOf(term.toUpperCase()) == 0;
        }});
    $("#boardingairport").select2("val", session_search_criteria["boardingairpotCode"]);
    var landingairportInput = $("#landingairport");
    landingairportInput.select2({data: airports, minimumInputLength: 3, placeholder: "Dönüş", allowClear: true, matcher: function(term, text) {
            return text.toUpperCase().indexOf(term.toUpperCase()) == 0;
        }});
    landingairportInput.select2("val", session_search_criteria['landingairpotCode'])


    var returnDateInput = $("#return_date");
    var goDateInput = $("#go_date");
    var defaultGoDate = new Date();
    var currentDate = defaultGoDate;
    defaultGoDate.setDate(defaultGoDate.getDate() + 7);
    goDateInput.datepicker({
        showOn: "button",
        buttonImage: "/onlinefly/public_html/css/ico/calendar_small.jpg",
        buttonImageOnly: true,
        numberOfMonths: 2,
        minDate: new Date(),
        dateFormat: "dd/mm/yy",
        onSelect: function(date) {
            returnDateInput.datepicker("option", 'minDate', date);
        }});
    returnDateInput.datepicker({
        showOn: "button",
        buttonImage: "/onlinefly/public_html/css/ico/calendar_small.jpg",
        buttonImageOnly: true,
        numberOfMonths: 2,
        minDate: new Date(),
        dateFormat: "dd/mm/yy"
    });
    $(".return-departure-time-filter-container").hide();
    $(".return-arrival-time-filter-container").hide();
    if (session_search_criteria['directionOption'] != null) {
        if (session_search_criteria['directionOption'] == "2") {
            $("input:radio[name='flightdirection'][value='2']").attr("checked", "checked");
            goDateInput.datepicker("setDate", convertStringToDate("yy-mm-dd", session_search_criteria['goDate']));

            returnDateInput.datepicker("setDate", convertStringToDate("yy-mm-dd", session_search_criteria['returnDate']));
            returnDateInput.datepicker("option", 'minDate', convertStringToDate("yy-mm-dd", session_search_criteria['goDate']));

            $(".return-departure-time-filter-container").show();
            $(".return-arrival-time-filter-container").show();

        } else if (session_search_criteria['directionOption'] == "1") {
            $("input:radio[name='flightdirection'][value='1']").attr("checked", "checked");
            goDateInput.datepicker("setDate", convertStringToDate("yy-mm-dd", session_search_criteria['goDate']));
            returnDateInput.datepicker("setDate", convertStringToDate("yy-mm-dd", session_search_criteria['returnDate']));
            returnDateInput.datepicker("option", 'minDate', convertStringToDate("yy-mm-dd", session_search_criteria['goDate']));
            $(".return-date_row").hide();
        } else if (session_search_criteria['directionOption'] == "0") {
            $("input:radio[name='flightdirection'][value='0']").attr("checked", "checked");
            goDateInput.datepicker("setDate", convertStringToDate("yy-mm-dd", session_search_criteria['goDate']));
        }
    }

    $("#yetiskinNumber").find("option[value='" + session_search_criteria['yetiskinNumber'] + "']").attr("selected", "selected")
    $("#yetiskinNumber").selectBoxIt();
    $("#cocukNumber").find("option[value='" + session_search_criteria['cocukNumber'] + "']").attr("selected", "selected")
    $("select[name = cocukNumber]").selectBoxIt();

    $("#bebekNumber").find("option[value='" + session_search_criteria['bebekNumber'] + "']").attr("selected", "selected")
    $("select[name = bebekNumber]").selectBoxIt();
    var flightClassInput = $("#flight-class-select");
    var flightTypeInput = $("#flight-type-select");
    flightClassInput.select2();
    flightClassInput.select2("val", session_search_criteria['cabinClass']);
    flightTypeInput.select2();

    if (session_search_criteria['flightType'] == "1") {
        $("input[name=flight-type-checkbox]").attr("checked", "checked");
    }

    if (session_search_criteria['dateOption'] == "2") {//+/-3 gıun için
        $("input[name=flight-date-option-checkbox]").attr("checked", "checked");

    }
    $("input:radio[name='flightdirection']").click(function() {
        onflyDirecitonOptionClick($(this).val());
    });

    $(".fly-search-container").css("display", "block");
    setSearchFilterUI();
    setOtherDepartureJourneyEvent();
    //setOtherArrivalJourneyEvent();
    setPriceDetailTriggerEvent();
    setBestPriceFlightTableEvent();
    setSearchDayNavigatorEvents();
    setSearchButtonEvent();
    setSearchNavDayEvent();
    setScrollEvents();
    
    $(".air-solution .flight-summary").each(function(index) {
        var legElement = $(this);
        var firstJourney = legElement.find(".journey").first();
        
        firstJourney.find("input:radio").attr("checked", true);
        
        if(index % 2 === 0){
             legElement.find(".journey").click(function(){
                 var journey = $(this);
                 var journeyRefArray = journey.attr("ref").split(",");
                 //alert(journey.attr("ref"));
                var otherLegElementSiblings = journey.parents(".flight-summary").siblings(".flight-summary");
                
                 otherLegElementSiblings.find(".journey").each(function(){
                     var otherLegJourneyElement = $(this);
                     var otherLegJourneyElementRefArray = otherLegJourneyElement.attr("ref").split(",");
                     var intersectionArray = _.intersection(journeyRefArray,otherLegJourneyElementRefArray);
                     if(intersectionArray !== undefined && intersectionArray.length > 0){
                         otherLegJourneyElement.find("input:radio").prop("checked", true);
                          otherLegJourneyElement.find("input:radio").prop("disabled", false);
                     }else{
                          otherLegJourneyElement.find("input:radio").prop("checked", false);
                          otherLegJourneyElement.find("input:radio").prop("disabled", true);
                     }
                 });
                 
             });
       
        
        var otherLegElementSiblings = legElement.siblings(".flight-summary");
        var firstJourneyRefArray = firstJourney.attr("ref").split(",");
        otherLegElementSiblings.find(".journey").each(function(){
             var otherLegJourneyElement = $(this);
                     var otherLegJourneyElementRefArray = otherLegJourneyElement.attr("ref").split(",");
                     var intersectionArray = _.intersection(firstJourneyRefArray,otherLegJourneyElementRefArray);
                     if(intersectionArray !== undefined && intersectionArray.length > 0){
                         otherLegJourneyElement.find("input:radio").prop("checked", true);
                          otherLegJourneyElement.find("input:radio").prop("disabled", false);
                     }else{
                          otherLegJourneyElement.find("input:radio").prop("checked", false);
                          otherLegJourneyElement.find("input:radio").prop("disabled", true);
                     }
           
         });
       }
    });
    
    
    
    setBookingButtonEvent();
    $(document).ajaxStop($.unblockUI);

    ///BackBone model setting;
    verifiedBookPriceSolutionApp.Models.Journey = Backbone.Model.extend({
        initialize: function() {
        }
    });

    verifiedBookPriceSolutionApp.Models.SummaryInfo = Backbone.Model.extend({});
    verifiedBookPriceSolutionApp.Models.AirSegment = Backbone.Model.extend({});
    verifiedBookPriceSolutionApp.Collections.Journeys = Backbone.Collection.extend({
        model: verifiedBookPriceSolutionApp.Models.Journey
    });

    verifiedBookPriceSolutionApp.Templates.BookVerifySummaryInfoTemp = _.template($("#bookingVerifySummaryInfoTemplate").html());
    verifiedBookPriceSolutionApp.Templates.BookVerifyJourney = _.template($("#bookedJourneyTemplate").html());
    verifiedBookPriceSolutionApp.Templates.BookVerifyJAirSegment = _.template($("#airSegmentTemplate").html());

    verifiedBookPriceSolutionApp.Views.Journey = Backbone.View.extend({
        el: "#bookPriceVerifyModal .journey-list",
        template: verifiedBookPriceSolutionApp.Templates.BookVerifyJourney,
        render: function() {
            var thisView = this;
            var count = 0;
            thisView.$el.empty();
            _.each(thisView.collection.models, function(journeyModel, i) {
                var airsegments = _.values(journeyModel.get("airSegmentItems"));
                var airSegmentHtmls = "";
                _.each(airsegments, function(airsegment) {
                    var airSegmentModel = new verifiedBookPriceSolutionApp.Models.AirSegment();
                    airsegment.flightTime = Math.floor((parseInt(airsegment.flightTime) / 60)) + "h: " + Number(parseInt(airsegment.flightTime) % 60) + 'm';
                    airSegmentModel.set(airsegment);
                    var airSegmentView = new verifiedBookPriceSolutionApp.Views.AirSegment({model: airSegmentModel});
                    airSegmentHtmls = airSegmentHtmls + airSegmentView.render();
                });

                var journeyHtml = thisView.template(journeyModel.toJSON());

                thisView.$el.append(journeyHtml);
                thisView.$el.find(".journey").eq(i).append(airSegmentHtmls);
                count++;

            });

            return this;
        }
    });
    verifiedBookPriceSolutionApp.Views.AirSegment = Backbone.View.extend({
        template: verifiedBookPriceSolutionApp.Templates.BookVerifyJAirSegment,
        render: function() {
            return this.template(this.model.toJSON());
        }
    });

    verifiedBookPriceSolutionApp.Views.Container = Backbone.View.extend({
        el: "#bookPriceVerifyModal .md-content",
        template: verifiedBookPriceSolutionApp.Templates.BookVerifySummaryInfoTemp,
        initialize: function() {
        },
        render: function() {
            var outputHtml = this.template(this.model.toJSON());
            outputHtml = outputHtml + "<div style='clear: both;'></div>";
            this.$el.find(".bookVerifyPriceText").empty();
            this.$el.find(".bookVerifyPriceText").prepend(outputHtml);
            $("#bookPriceVerifyModal .price-detail-trigger").click(function() {
                var thisElement = $(this);
                if (thisElement.hasClass("triggered")) {
                    thisElement.removeClass("triggered");
                    thisElement.removeClass("icon-chevron-up");
                    thisElement.addClass("icon-chevron-down");
                    $("#bookPriceVerifyModal .bookVerifyPriceDetails").hide(200);
                } else {
                    thisElement.addClass("triggered");
                    thisElement.removeClass("icon-chevron-down");
                    thisElement.addClass("icon-chevron-up");
                    $("#bookPriceVerifyModal .bookVerifyPriceDetails").show(200);
                }

            });

            return this;
        }
    });

    $(".md-approve").click(function() {
        document.location.href = "bookingInfo";
    });
});

function  setOtherDepartureJourneyEvent() {
    $(".other-journey-count-info").each(function() {
        var element = $(this);

        element.click(function() {
            var spanElement = element.find(".span8 > span");
            var legParent = element.parents(".flight-summary");
            var legJourneys = legParent.find(".journey");
            if (spanElement.attr("clicked") === "clicked") {
                spanElement.attr("class", "icon-circle-arrow-down");
                spanElement.attr("clicked", "");
                var checkedRadioButton = legJourneys.find("input:checked");
                var selectedGoJourney = checkedRadioButton.parents(".journey");
                selectedGoJourney.siblings(".journey").hide(200);
            } else {
                spanElement.attr("class", "icon-circle-arrow-up");
                spanElement.attr("clicked", "clicked");
                if (legParent.find(".filtered-journey").size() > 0) {
                    legParent.find(".filtered-journey").show(300);
                } else {
                    legJourneys.show(300);
                }
            }
            
            legJourneys.promise().done(function() {
                legParent.find(".not-filtered-journey").hide();
            });
        });
    });
}

function setPriceDetailTriggerEvent() {

    $(".price-detail-trigger").each(function() {
        var element = $(this);
        var air_price_solution_element = element.parents(".air-solution");
        var price_container_element = air_price_solution_element.find(".price-detail-container");
        element.click(function() {
            price_container_element.toggle('300');
            if (element.attr("triggered") == "triggered") {
                element.attr("class", "price-detail-trigger icon-chevron-down");
                element.attr("triggered", "");
            } else {
                element.attr("class", "price-detail-trigger icon-chevron-up");
                element.attr("triggered", "triggered");
            }
        });
    });
}

function setBestPriceFlightTableEvent() {
    var bestPriceElements = $(".header2");
    var airsoultionElements = $(".air-solution");
    var all_journeys = $(".journey");
    bestPriceElements.each(function() {
        var element = $(this);
        element.click(function() {
            var stopCount = 0;
            if (element.hasClass("no-stop")) {
                stopCount = 0;
            } else if (element.hasClass("one-stop")) {
                stopCount = 1;
            } else if (element.hasClass("more-stop")) {
                stopCount = 2;
            }

            var priceString = element.html();
            var matches = priceString.match(/\d+/);
            var minPrice = parseInt(matches[0]);

            var airline_company = element.attr("airlinecompany");
            //airsoultionElements.css("display", "block");
            if (element.hasClass("header2-clicked")) {
                filterCriteriaObject.airlineCompany = undefined;
                filterCriteriaObject.stopCount = undefined;

                element.removeClass("header2-clicked");
                $(".price-filter-input").slider("values", 0, parseInt(filter["min_price"]));
                /*
                 var thisJourney = $(".journey[airline-company!='" + airline_company + "']");
                 thisJourney.each(function() {
                 var theJourney = $(this);
                 theJourney.parents(".air-solution").css("display", "block");
                 });
                 */

            } else {
                filterCriteriaObject.airlineCompany = airline_company;
                filterCriteriaObject.stopCount = stopCount;
                $(".price-filter-input").slider("values", 0, minPrice);

                bestPriceElements.removeClass("header2-clicked");
                element.addClass("header2-clicked");
            }
        });

    });


}

function setSearchDayNavigatorEvents() {
    var prevDayLink = $(".prev-day-link");
    prevDayLink.click(function() {
        alert("prev");
    });
    var nextDayLink = $(".next-day-link");
    nextDayLink.click(function() {
        alert("next");
    });

}

function setSearchButtonEvent() {
    $("#searchButton").click(function() {
        var succesResponse = doflySearch(responseFlySearch);
    });
}

function  setSearchNavDayEvent() {
    $(".nav-icon").click(function() {
        var thisElement = $(this);
        var action = null;
        if (thisElement.hasClass("go-nav-prev-icon")) {
            action = "goprev";

        } else if (thisElement.hasClass("go-nav-next-icon")) {
            action = "gonext";

        } else if (thisElement.hasClass("return-nav-prev-icon")) {
            action = "returnprev";

        } else if (thisElement.hasClass("return-nav-next-icon")) {
            action = "returnnext";
        }
        if (action != null) {
            $.blockUI({message: $('#domMessage'), backgroundColor: "transparent"});
            var url = "searchnavday"
            var requestParam = {'action': action};
            $.post(url, requestParam, function(data) {
                responseFlySearch(data);
            }, "json");
        }

    });
}

function setSearchFilterUI() {
    setPriceFilterUI();
    setDepartureTimeFilterUI();
    setStopCountFilterUI();
    setAirlineCompanyFilterUI();
}

function setAirlineCompanyFilterUI() {
    $("input[name=airlineCompanyCodeCheckbox]").click(function() {
        var selectedAirlineCompanies = {};
        $("input[name=airlineCompanyCodeCheckbox]:checked").each(function() {
            var checkedElement = $(this);

            selectedAirlineCompanies[checkedElement.val()] = checkedElement.val();
        });
        filterCriteriaObject.selectedAirlineCompanies = selectedAirlineCompanies;
        onApplyFilter(AIRLINE_COMPANY_FILTER_TYPE, filterCriteriaObject);
    })
}
function setStopCountFilterUI() {
    $("input[name=stopCount]").click(function() {
        filterCriteriaObject.isNoStopExist = 0;
        filterCriteriaObject.isOneStopExist = 0;
        filterCriteriaObject.isTwoOrMoreExist = 0;
        $("input[name=stopCount]:checked").each(
                function() {
                    var checkedElement = $(this);
                    if (checkedElement.val() == 0) {
                        filterCriteriaObject.isNoStopExist = 1;
                    } else if (checkedElement.val() == 1) {
                        filterCriteriaObject.isOneStopExist = 1;
                    } else if (checkedElement.val() == 2) {
                        filterCriteriaObject.isTwoOrMoreExist = 1;
                    }
                }
        );
        onApplyFilter(STOP_COUNT_FILTER_TYPE, filterCriteriaObject);
    })
}


function setDepartureTimeFilterUI() {
    $(".go-departure-time-filter-container .info-value").html(convertMinutesToHoursString(filter['goDepartureTimeMinValue']) + "-" + convertMinutesToHoursString(filter['goDepartureTimeMaxValue']));

    $(".go-departure-time-filter-input").slider({
        range: true,
        min: parseInt(filter['goDepartureTimeMinValue']),
        max: parseInt(filter['goDepartureTimeMaxValue']),
        step: 15,
        values: [0, 1439],
        slide: function(event, ui) {
            var minMinute = ui.values[0];
            var maxMinute = ui.values[1];
            $(".go-departure-time-filter-container .info-value").html(convertMinutesToHoursString(minMinute) + "-" + convertMinutesToHoursString(maxMinute));
        },
        change: function(event, ui) {
            var minMinute = ui.values[0];
            var maxMinute = ui.values[1];
            filterCriteriaObject.goDepartureTimeMinValue = minMinute;
            filterCriteriaObject.goDepartureTimeMaxValue = maxMinute;
            onApplyFilter(GO_DEPARTURE_TIME_FILTER_TYPE, filterCriteriaObject);
        }

    });

    var returnDepartureTimeMinValue = session_search_criteria['directionOption'] == "2" ? parseInt(filter['returnDepartureTimeMinValue']) : 0;
    var returnDepaetureTimeMaxValue = session_search_criteria['directionOption'] == "2" ? parseInt(filter['returnDepartureTimeMaxValue']) : 1439;
    $(".return-departure-time-filter-container .info-value").html(convertMinutesToHoursString(returnDepartureTimeMinValue) + "-" + convertMinutesToHoursString(returnDepaetureTimeMaxValue));
    //filter['returnDepartureTimeMinValue']
    $(".return-departure-time-filter-input").slider({
        range: true,
        min: returnDepartureTimeMinValue,
        max: returnDepaetureTimeMaxValue,
        step: 15,
        values: [0, 1439],
        slide: function(event, ui) {
            var minMinute = ui.values[0];
            var maxMinute = ui.values[1];
            $(".return-departure-time-filter-container .info-value").html(convertMinutesToHoursString(minMinute) + "-" + convertMinutesToHoursString(maxMinute));
        },
        change: function(event, ui) {
            var minMinute = ui.values[0];
            var maxMinute = ui.values[1];
            filterCriteriaObject.returnDepartureTimeMinValue = minMinute;
            filterCriteriaObject.returnDepartureTimeMaxValue = maxMinute;
            onApplyFilter(RETURN_DEPARTURE_TIME_FILTER_TYPE, filterCriteriaObject);
        }

    });
    $(".go-arrival-time-filter-container .info-value").html(convertMinutesToHoursString(filter["goArrivalTimeMinValue"]) + "-" + convertMinutesToHoursString(filter["goArrivalTimeMaxValue"]));
    $(".go-arrival-time-filter-input").slider({
        range: true,
        min: parseInt(filter["goArrivalTimeMinValue"]),
        max: parseInt(filter["goArrivalTimeMaxValue"]),
        step: 15,
        values: [0, 1439],
        slide: function(event, ui) {
            var minMinute = ui.values[0];
            var maxMinute = ui.values[1];
            $(".go-arrival-time-filter-container .info-value").html(convertMinutesToHoursString(minMinute) + "-" + convertMinutesToHoursString(maxMinute));
        },
        change: function(event, ui) {
            var minMinute = ui.values[0];
            var maxMinute = ui.values[1];
            filterCriteriaObject.goArrivalTimeMinValue = minMinute;
            filterCriteriaObject.goArrivalTimeMaxValue = maxMinute;
            onApplyFilter(GO_ARRIVAL_TIME_FILTER_TYPE, filterCriteriaObject);

        }


    });
    var returnArrivalTimeMinValue = session_search_criteria['directionOption'] == "2" ? parseInt(filter['returnArrivalTimeMinValue']) : 0;
    var returnArrivalTimeMaxValue = session_search_criteria['directionOption'] == "2" ? parseInt(filter['returnArrivalTimeMaxValue']) : 1439;
    $(".return-arrival-time-filter-container .info-value").html(convertMinutesToHoursString(returnArrivalTimeMinValue) + "-" + convertMinutesToHoursString(returnArrivalTimeMaxValue));
    $(".return-arrival-time-filter-input").slider({
        range: true,
        min: returnArrivalTimeMinValue,
        max: returnArrivalTimeMaxValue,
        step: 15,
        values: [0, 1439],
        slide: function(event, ui) {
            var minMinute = ui.values[0];
            var maxMinute = ui.values[1];
            $(".return-arrival-time-filter-container .info-value").html(convertMinutesToHoursString(minMinute) + "-" + convertMinutesToHoursString(maxMinute));
        },
        change: function(event, ui) {
            var minMinute = ui.values[0];
            var maxMinute = ui.values[1];
            filterCriteriaObject.returnArrivalTimeMinValue = minMinute;
            filterCriteriaObject.returnArrivalTimeMaxValue = maxMinute;
            onApplyFilter(RETURN_ARRIVAL_TIME_FILTER_TYPE, filterCriteriaObject);
        }

    });
}
function setPriceFilterUI() {
    var min = parseInt(filter["min_price"]); // filter global bir objedir  
    $(".price-filter-input").slider({
        range: true,
        min: min,
        max: parseInt(filter["max_price"]),
        values: [parseInt(filter["min_price"]), parseInt(filter["max_price"])],
        slide: function(event, ui) {
            $(".min-value").html(ui.values[ 0 ]);
            $(".max-value").html(ui.values[ 1 ]);

            // $( "#price_range_slider" ).val( "$" + ui.values[ 0 ] + " - $" + ui.values[ 1 ] );     
        },
        change: function(event, ui) {
            filterCriteriaObject.minValue = ui.values[ 0 ];
            filterCriteriaObject.maxValue = ui.values[1];
            $(".min-value").html(ui.values[ 0 ]);
            $(".max-value").html(ui.values[ 1 ]);
            onApplyFilter(PRICE_FILTER_TYPE, filterCriteriaObject);
        }
    });

    $(".min-value").html($(".price-filter-input").slider("values", 0));
    $(".max-value").html($(".price-filter-input").slider("values", 1));
    //$( "#price_range_slider" ).val( "$" + $( "#price-filter-input" ).slider( "values", 0 ) +      " - $" + $( "#price-filter-input" ).slider( "values", 1 ) ); 

}

function onflyDirecitonOptionClick(value) {
    if (value == 2) {
        $(".return-date_row").show("300");
    } else if (value == 1) {
        $(".return-date_row").hide("300");
    } else if (value == 0) {
        //TODO openCokluUcusOption();
    }
}

//when Page is scrolled 
function setScrollEvents() {
    $(window).scroll(function() {
        /*
         var  newScrollTop = $(this).scrollTop();
         $(".left-container").stop();
         $(".left-container").animate({"marginTop":newScrollTop+10});
         */
        if ($(window).scrollTop() + $(window).height() > $(document).height() - 200) {

            var noScrolledAirPriceSolutions = $(".no-scrolled");
            if (noScrolledAirPriceSolutions.size() > 0) {
                $(".auto-loading-icon").css("display", "block");
                var count = 3;
                for (var i = 0; i < count; i++) {
                    var scrolledElement = noScrolledAirPriceSolutions.eq(i);
                    scrolledElement.removeClass("no-scrolled");
                    scrolledElement.addClass("scrolled");
                }
                $(".auto-loading-icon").css("display", "none");
                $(this).animate({scrollTop: newScrollTop - 200}, 200);
            }
        }
    });

}
function buidSearchFlightRequest() {
    var requestFlySearchJsonObject = {};
    requestFlySearchJsonObject['boardingairpotCode'] = $("#boardingairport").select2("val");
    requestFlySearchJsonObject['landingairpotCode'] = $("#landingairport").select2("val");
    requestFlySearchJsonObject['goDate'] = $.datepicker.formatDate("yy-mm-dd", $("#go_date").datepicker("getDate"));
    requestFlySearchJsonObject['returnDate'] = $.datepicker.formatDate("yy-mm-dd", $("#return_date").datepicker("getDate"));
    requestFlySearchJsonObject['yetiskinNumber'] = $("select[name=yetiskinNumber]").selectBoxIt().val();
    requestFlySearchJsonObject['cocukNumber'] = $("select[name = cocukNumber]").selectBoxIt().val();
    requestFlySearchJsonObject['bebekNumber'] = $("select[name = bebekNumber]").selectBoxIt().val();
    requestFlySearchJsonObject['dateOption'] = "1";
    if ($("input:checkbox[name = flight-date-option-checkbox]").attr("checked") == "checked") {
        requestFlySearchJsonObject['dateOption'] = "2";
    }


    requestFlySearchJsonObject['directionOption'] = $("input:radio[name=flightdirection]:checked").val();
    requestFlySearchJsonObject['cabinClass'] = $("#flight-class-select").select2("val");
    requestFlySearchJsonObject['flightType'] = "all";
    if ($("input:checkbox[name='flight-type-checkbox']") == "1") {
        requestFlySearchJsonObject['flightType'] = "1";
    }
    var airSearchLegs = new Array();
    var firstAirSearchLeg = {};
    firstAirSearchLeg.origin = {};
    firstAirSearchLeg.destination = {};
    firstAirSearchLeg.origin.airport = $("#boardingairport").select2("val");
    firstAirSearchLeg.destination.airport = $("#landingairport").select2("val");
    firstAirSearchLeg.departureTime = $.datepicker.formatDate("yy-mm-dd", $("#go_date").datepicker("getDate"));
    airSearchLegs.push(firstAirSearchLeg);
    var secondAirSearchLeg = {};
    secondAirSearchLeg.origin = {};
    secondAirSearchLeg.destination = {};
    secondAirSearchLeg.origin.airport = $("#landingairport").select2("val");
    secondAirSearchLeg.destination.airport = $("#boardingairport").select2("val");
    secondAirSearchLeg.departureTime = $.datepicker.formatDate("yy-mm-dd", $("#return_date").datepicker("getDate"));
    airSearchLegs.push(secondAirSearchLeg);
    requestFlySearchJsonObject['airSearchLegs'] = airSearchLegs;
    return requestFlySearchJsonObject;

    return requestFlySearchJsonObject;
}


function  doflySearch(_callback) {
    var requestFlySearchJsonObject = buidSearchFlightRequest();
    if (!validateSearchRequest(requestFlySearchJsonObject)) {
        return;
    }
    $.blockUI({message: $('#domMessage'), backgroundColor: "transparent"});
    var url = "searchflyrequest"
    $.post(url, requestFlySearchJsonObject, function(data) {

        responseFlySearch(data);

    }, "json");

}

function responseFlySearch(data) {
    if (data !== null && data.data > 0) {

        document.location.href = "searchresults";

    } else {
        alert("Uçuş bulunamadı");
    }
}
function validateSearchRequest(requestFlySearchJsonObject) {
    var valid = true;
    $(".boardingairpot-alert").hide();
    if (requestFlySearchJsonObject['boardingairpotCode'] == null || requestFlySearchJsonObject['boardingairpotCode'].length < 1) {
        valid = false;
        $(".boardingairpot-alert").show(100);
    }

    $(".landingairpot-alert").hide();
    if (requestFlySearchJsonObject['landingairpotCode'] == null || requestFlySearchJsonObject['landingairpotCode'].length < 1) {
        valid = false;
        $(".landingairpot-alert").show(100);
    }

    $(".godate-alert").hide();
    if (requestFlySearchJsonObject['goDate'] == null || requestFlySearchJsonObject['goDate'].length < 1) {
        valid = false;
        $(".godate-alert").show(100);
    }

    $(".returndate-alert").hide();
    if (requestFlySearchJsonObject['returnDate'] == null || requestFlySearchJsonObject['returnDate'].length < 1) {
        valid = false;
        $('.returndate-alert').show(100);
    }
    return valid;
}

function convertStringToDate(format, stringDate) {
    if (format == "yy-mm-dd") {
        var parsedDate = stringDate.split("-");
        var year = parsedDate[0];
        var month = parsedDate[1];
        month = month - 1
        var day = parsedDate[2];
        return new Date(year, month, day);
    }
}

function onApplyFilter(filterType, filterCriteriaObject) {
    var params = {};
    params.filterType = filterType;
    params.fiterCriteria = JSON.stringify(filterCriteriaObject);
    var url = "applyfilter";
    $(".auto-filter-loading-icon").show();
    $(".filter-request-message").hide();
    $.post(url, params, function(data) {
        if (data != null && data.error_code == "0000000") {
            applyFilterCallback(data.data);
        }
    }, 'json');

}

function applyFilterCallback(filteredAirPriceSolutionObjects) {
    var allAirSolutions = $(".air-solution");
    allAirSolutions.hide();

    if (filteredAirPriceSolutionObjects == null || !jQuery.isArray(filteredAirPriceSolutionObjects) || filteredAirPriceSolutionObjects.length < 1) {
        $(".filter-request-message").show();
        $(".auto-filter-loading-icon").show().delay(50).hide();
        return;
    }

    for (var i = 0; i < filteredAirPriceSolutionObjects.length; i++) {
        var filteredAirPriceSolutionObject = filteredAirPriceSolutionObjects[i];
        var combinedKey = filteredAirPriceSolutionObject.combinedKey;
        var combinedAirPriceSolutionContainer = $("#" + jqSelector(combinedKey));
        var legElements = combinedAirPriceSolutionContainer.find(".flight-summary");
        var legCountIndex = 0;
        for (var legKey in filteredAirPriceSolutionObject.filteredLegs) {
            var legElement = legElements.eq(legCountIndex);
            var journeyElements = legElement.find(".journey");
            journeyElements.hide();
            journeyElements.removeClass("not-filtered-journey");
            journeyElements.removeClass("filtered-journey");
            journeyElements.addClass("not-filtered-journey");
            for (var j = 0; j < filteredAirPriceSolutionObject.filteredLegs[legKey].length; j++) {
                var journeyKey = filteredAirPriceSolutionObject.filteredLegs[legKey][j];
                
                journeyElements.each(function() {
                    var thisJourneyElement = $(this);
                    if (thisJourneyElement.attr("id") === journeyKey) {
                        thisJourneyElement.removeClass("not-filtered-journey");
                        thisJourneyElement.addClass("filtered-journey");
                    } 
                });
                
               var filteredJourneyElements = legElement.find(".filtered-journey");
               filteredJourneyElements.first().find("input:radio").prop("checked", true);
               filteredJourneyElements.first().show();
               
               var filteredJourneyElementsSize = filteredJourneyElements.size();
               var otherJourneyCountInfoElement = legElement.find(".other-journey-count-info");
               if(filteredJourneyElementsSize  === 1){
                   otherJourneyCountInfoElement.hide();
               }else{
                   otherJourneyCountInfoElement.find(".count-value").html(filteredJourneyElementsSize-1);
                   otherJourneyCountInfoElement.show();
               }
            }
            legCountIndex++;
        }
        combinedAirPriceSolutionContainer.show();
    }
    $(".auto-filter-loading-icon").show().delay(10).hide();
}


function setBookingButtonEvent() {

    $("#bookPriceVerifyModal").find(".close-button").click(function() {
        $(this).parents("#bookPriceVerifyModal").removeClass("md-show");
        $(".md-overlay").css("visibility", "hidden");

    });
    $("#bookPriceVerifyModal .bookVerifyPriceDetails").hide();
    $("#bookPriceVerifyModal .price-detail-trigger").click(function() {
        var thisElement = $(this);
        if (thisElement.hasClass("triggered")) {
            thisElement.removeClass("triggered");
            thisElement.removeClass("icon-chevron-up");
            thisElement.addClass("icon-chevron-down");
            $("#bookPriceVerifyModal .bookVerifyPriceDetails").hide(200);
        } else {
            thisElement.addClass("triggered");
            thisElement.removeClass("icon-chevron-down");
            thisElement.addClass("icon-chevron-up");
            $("#bookPriceVerifyModal .bookVerifyPriceDetails").show(200);
        }

    });
    $(".bookingButton").click(function() {
        var bookedButtonElement = $(this);
        var airPriceSolutionElement = bookedButtonElement.parents(".air-solution");
        var legElements = airPriceSolutionElement.find(".flight-summary");
        var selectedJourneyElementKeys = new Array();
        if (legElements.size() > 0) {
            for (var i = 0; i < legElements.size(); i++) {
                var legObject = {};
                var legElement = legElements.eq(i);
                legObject.key = legElement.attr("id");
                var journeyElements = legElement.find(".journey");
                for (var j = 0; j < journeyElements.size(); j++) {
                    var journeyElement = journeyElements.eq(j);
                    if (journeyElement.find("input:radio").first().is(":checked")) {
                        legObject.selectedJourneyKey = journeyElement.attr("id");
                        selectedJourneyElementKeys.push(legObject);
                        break;
                    }
                }

            }
        }




        selectedBookSolution.airPriceSolutionKey = undefined;
        selectedBookSolution.airPriceSolutionKey = airPriceSolutionElement.attr("id");
        selectedBookSolution.selectedJourneyElementKeys = selectedJourneyElementKeys;

        bookedButtonElement.find(".booking-button-text").html("Fiyat Doğrulanıyor");
        bookedButtonElement.find("i").attr("class", "icon-spinner icon-spin");
        var url = "bookPriceVerify";
        $.post(url, selectedBookSolution, function(data) {
            bookPriceVrifyCallback(data.data);
            bookedButtonElement.find(".booking-button-text").html("Bilet Al");
            bookedButtonElement.find("i").attr("class", "icon-chevron-right icon-1x");
        });


    });

}

function bookPriceVrifyCallback(data) {
    var bookingPriceDetailTemplate = _.template($("#bookPriceDetailTempalte").html());
    var airPriceInfoItems = data.verifiedAirPriceSolution.airPricingInfoArray;
    var priceDetailContainer = $(".bookVerifyPriceDetails");
    priceDetailContainer.find(".bookPriceDetail").remove();
    var currency = data.searchCriteria.currency;

    airPriceInfoItems = _.first(_.values(airPriceInfoItems));
    airPriceInfoItems = _.values(airPriceInfoItems);
    if (airPriceInfoItems != null && airPriceInfoItems.length > 0) {
        _.each(airPriceInfoItems, function(airPriceInfoItem) {
            airPriceInfoItem.allTotalPrice = parseFloat(airPriceInfoItem.approximateTotalPriceAmout) * parseInt(airPriceInfoItem.passengerCount);
            airPriceInfoItem.currency = currency;
            var bookingPriceDetailHtml = bookingPriceDetailTemplate(airPriceInfoItem);
            priceDetailContainer.append(bookingPriceDetailHtml);
        });
    }
    var legKeyArray = data.legKeyArray;
    var journeysCollection = new verifiedBookPriceSolutionApp.Collections.Journeys();
    var bookPriceVerifySummaryInfoModel = new verifiedBookPriceSolutionApp.Models.SummaryInfo();
    data.verifiedAirPriceSolution.currency = currency;
    bookPriceVerifySummaryInfoModel.set(data.verifiedAirPriceSolution);
    var bookPriceVerifyContainerView = new verifiedBookPriceSolutionApp.Views.Container({model: bookPriceVerifySummaryInfoModel});

    if (legKeyArray !== null && legKeyArray.length > 0) {
        _.each(legKeyArray, function(legKey) {

            var leg = data.verifiedAirPriceSolution.legs[legKey];
            _.each(leg.avaibleJourneyOptions, function(journey) {
                var journeyModel = new verifiedBookPriceSolutionApp.Models.Journey();
                journeyModel.set(journey);
                if (leg.direction === "G") {
                    journeyModel.set("journeyDirectionType", "out");
                    journeyModel.set("journeyDirectionTypeText", "Gidiş");
                } else {
                    journeyModel.set("journeyDirectionType", "in");
                    journeyModel.set("journeyDirectionTypeText", "Dönüş");
                }
                journeysCollection.add(journeyModel);
            });

        });
    }

    var journeyView = new verifiedBookPriceSolutionApp.Views.Journey({collection: journeysCollection});
    bookPriceVerifyContainerView.render();
    journeyView.render();
    $(".md-overlay").css("visibility", "visible");
    $("#bookPriceVerifyModal").addClass("md-show");

    console.log(journeysCollection);


}

function convertMinutesToHoursString(minute) {
    var hours = Math.floor(minute / 60);
    minute = minute % 60;
    if (hours < 10) {

        hours = "0" + hours;
    }
    if (minute < 10) {
        minute = "0" + minute;
    }
    return hours + ":" + minute;
}


function jqSelector(str)
{
   return str.replace(/([;&,\.\+\*\~':"\!\^#$%@\[\]\(\)=>\|])/g, '\\\\$1');
}


