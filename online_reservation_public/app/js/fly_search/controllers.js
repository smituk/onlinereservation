'use strict';

/* Controllers */
var appModule = angular.module(APP_NAME + '.controllers', [APP_NAME + '.directives', 'ui.bootstrap', 'ngCookies']);

appModule.controller('MyCtrl1', [function() {

    }]);


appModule.controller('MyCtrl2', [function() {

    }]);

appModule.controller(APP_NAME + 'Controller', ['$scope', function($scope) {

    }]);

appModule.controller("topHeaderController", ['$scope', function($scope) {


    }]);

appModule.controller("navBarController", ['$scope', function($scope) {
        $scope.focusReservation = function() {
            alert("focus rezervation");
        };
    }]);



appModule.controller("searchFormController", ['$scope', '$window', '$cookieStore', '$modal', 'FlightSearchService', 'AirportService', 'ErrorCodes', '$q', function($scope, $window, $cookieStore, $modal, FlightSearchService, AirportService, ErrorCodes, $q) {

        $scope.errorMessage = "";

        $scope.checked = true;
        $scope.journeyType = "2";
        $scope.isSecondSearchLocationOrigin = false;
        $scope.isSecondSearchLocationDestination = false;
        $scope.goDate = new Date();
        $scope.goDate.setDate($scope.goDate.getDate()+1);
        $scope.goDateMin = new Date();
        $scope.goDateMax = new Date();
        $scope.goDateMax.setDate($scope.goDateMax.getDate() + 365);
        //$scope.goDateMax = new Date();

        $scope.goDateOptions = {};

        $scope.returnDate = new Date($scope.goDate.getTime() + 7 * 24 * 3600000);
        $scope.returnDateMin = new Date($scope.goDate.getTime());
        $scope.returnDateMax = new Date($scope.goDateMax.getTime() + 365 * 24 * 3600000);
        $scope.returnDateDisabled = false;
        //$scope.goDateMax = new Date();

        //passanger countinfo

        $scope.searchAdultCount = 1;
        $scope.searchChildCount = 0;
        $scope.searchInfantCount = 0;

        $scope.flexThirdDateOption = true;
        $scope.nonstopFlightsOption = false;
        $scope.lowCostFlightsOption = true;
        $scope.searchClass = "all";





        $scope.firstSearchLocationOriginClass = false;
        $scope.secondSearchLocationOriginClass = false;
        $scope.firstSearchLocationDestinationClass = false;
        $scope.secondSearchLocationDestinationClass = false;

        $scope.onJourneyTypeChange = function() {
            var journeyType = $scope.journeyType;
            if (journeyType === "2") {
                $scope.isSecondSearchLocationOrigin = false;
                $scope.isSecondSearchLocationDestination = false;
                $scope.returnDateDisabled = false;
                //$scope.returnDate = $scope.tempReturnDate;

                $scope.secondSearchLocationOrigin = $scope.firstSearchLocationDestination;
                $scope.secondSearchLocationDestination = $scope.firstSearchLocationOrigin;
                $("#secondSearchLocationDestination").select2("val", $scope.firstSearchLocationOrigin);
                $("#secondSearchLocationOrigin").select2("val", $scope.firstSearchLocationDestination);
            } else if (journeyType === "1") {
                $scope.isSecondSearchLocationOrigin = false;
                $scope.isSecondSearchLocationDestination = false;
                $scope.returnDateDisabled = true;
                // $scope.tempReturnDate = $scope.returnDate;
                //$scope.returnDate = null;

            } else if (journeyType === "3") { // kombinasyon

                $scope.secondSearchLocationOrigin = $scope.firstSearchLocationDestination;
                $scope.secondSearchLocationDestination = $scope.firstSearchLocationOrigin;
                $scope.isSecondSearchLocationOrigin = true;
                $scope.isSecondSearchLocationDestination = true;
                $scope.returnDateDisabled = false;
                //$scope.tempReturnDate = $scope.returnDate;
            }
        };



        $scope.goDateOpen = function($event) {
            $event.preventDefault();
            $event.stopPropagation();
            $scope.goDateOpened = true;
        };

        $scope.returnDateOpen = function($event) {
            $event.preventDefault();
            $event.stopPropagation();

            $scope.returnDateOpened = true;
        };


        $scope.increasePassengerCount = function(passengerCount) {
            if ($scope[passengerCount] < 9) {
                $scope[passengerCount]++;
            }
        };

        $scope.decrasePassengerCount = function(passengerCount) {
            if ($scope[passengerCount] > 0) {
                $scope[passengerCount]--;
            }
        };

        $scope.passengerInfoCountFocus = function(passengerCount) {
            $scope[passengerCount + "temp"] = $scope[passengerCount];
        };

        $scope.passengerInfoCountBlur = function(passengerCount) {
            if ($scope[passengerCount] === undefined || $scope[passengerCount] === "") {
                $scope[passengerCount] = $scope[passengerCount + "temp"];
            }
        };


        $scope.onSearchPerformed = function() {
            //hide Error text
            $(".error-desc-container").hide();

            var isValid = true;
            var errorDescritions = new Array();
            //alert($scope.goDate);
            $scope.errorMessage = "";
            $scope.firstSearchLocationOriginClass = false;
            $scope.secondSearchLocationOriginClass = false;
            $scope.firstSearchLocationDestinationClass = false;
            $scope.secondSearchLocationDestinationClass = false;
            if ($scope.firstSearchLocationOrigin === undefined || $scope.firstSearchLocationOrigin === '') {
                $scope.firstSearchLocationOriginClass = true;
                $("#firstSearchLocationOrigin").parent(".search-location-autocomplete").addClass("invalid-input");
                isValid = false;
            }

            if ($scope.journeyType === "3" && ($scope.secondSearchLocationOrigin === undefined || $scope.secondSearchLocationOrigin === '')) {
                $scope.secondSearchLocationOriginClass = true;
                $("#secondSearchLocationOrigin").parent(".search-location-autocomplete").addClass("invalid-input");
                isValid = false;
            }

            if ($scope.firstSearchLocationDestination === undefined || $scope.firstSearchLocationDestination === '') {
                $scope.firstSearchLocationDestinationClass = true;
                $("#firstSearchLocationDestination").parent(".search-location-autocomplete").addClass("invalid-input");
                isValid = false;
            }

            if ($scope.journeyType === "3" && ($scope.secondSearchLocationDestination === undefined || $scope.secondSearchLocationDestination === '')) {
                $("#secondSearchLocationDestination").parent(".search-location-autocomplete").addClass("invalid-input");
                $scope.secondSearchLocationDestinationClass = true;
                isValid = false;
            }

            if (!isValid) {
                return;
            }


            if ($scope.firstSearchLocationOrigin === $scope.firstSearchLocationDestination) {
                $scope.errorMessage = "Kalkış ve varış havaalanı farklı  olmalıdır";
                $scope.firstSearchLocationOriginClass = true;
                $scope.firstSearchLocationDestinationClass = true;
            }

            if ($scope.secondSearchLocationOrigin === $scope.secondSearchLocationDestination) {
                $scope.errorMessage = "Kalkış ve varış havaalanı farklı olmalıdır";
                $scope.secondSearchLocationOriginClass = true;
                $scope.secondSearchLocationDestinationClass = true;
            }

            if ($scope.errorMessage !== "") {
                $(".error-desc-container").slideDown(400);
                return;
            }



            FlightSearchService.clearSearhAirLocations();
            var journeyType = $scope.journeyType;
            if (journeyType === "3") {
                journeyType = "2";
            }
            FlightSearchService.setDirection(journeyType);
            FlightSearchService.addSearchAirLocation($scope.firstSearchLocationOrigin, $scope.firstSearchLocationDestination, $scope.goDate);
            FlightSearchService.addSearchAirLocation($scope.secondSearchLocationOrigin, $scope.secondSearchLocationDestination, $scope.returnDate);
            FlightSearchService.setAdultPassengerCount($scope.searchAdultCount);
            FlightSearchService.setChildPassengerCount($scope.searchChildCount);
            FlightSearchService.setInfantPassengerCount($scope.searchInfantCount);
            FlightSearchService.setNonStopFlight($scope.nonstopFlightsOption);
            FlightSearchService.setFlexibleThirdDay($scope.flexThirdDateOption);
            FlightSearchService.setLowCostFlights($scope.lowCostFlightsOption);
            FlightSearchService.setCabinClass($scope.searchClass);

            var differentAirportSummaryIds = [];
            var getAirportSummaryPromises = [];
            angular.forEach(FlightSearchService.getSearchAirLocations(), function(searchAirlocation) {
                getAirportSummaryPromises.push(AirportService.getAirportSummary(searchAirlocation.getOriginAirportId()));
                getAirportSummaryPromises.push(AirportService.getAirportSummary(searchAirlocation.getDestinationAirportId()));
            });

            $q.all(getAirportSummaryPromises).then(function(results) {
               var airportSummaryTexts = [];
               angular.forEach(results ,function(result){
                   if(!airportSummaryTexts[result.id]){
                       var cityOrAirportCode  = result.iataCode;
                       if(!result.iataCode){
                           cityOrAirportCode = result.cityCode;
                       }
                       airportSummaryTexts[result.id] = result.summary.split(",")[0]+" ("+cityOrAirportCode +")";
                   }
               });
               
                var modalInstance = $modal.open({
                    templateUrl: 'online_reservation_public/app/partials/common/searchingPopup.html',
                    controller: FlightSearchService.getModalControllerInstance(),
                    resolve: {
                        searchCriteria: function() {
                            return FlightSearchService.getSearchCriteria();
                        },
                        airportSummaryTexts: function() {
                            return airportSummaryTexts;
                        }
                    },
                    backdrop: 'static',
                    windowClass: 'searching-flight-popup'
                });
            });



            $cookieStore.put("searchAirLocations", FlightSearchService.getSearchAirLocations());
          
            FlightSearchService.performSearch().then(function(response) {
                if (response.data > 0) {
                    $window.location.href = "index.php/searchresults";
                } else {
                    $modal.close();
                    alert("Uçus bulunamadı");
                }

            }, function() {
                alert("bir hata meydana geldi");
            })['finally'](function() {
 
                 $modal.close();
            });

        };

        $scope.$watch('firstSearchLocationOrigin', function(newVal, oldValue) {
            if (oldValue && oldValue !== null && (newVal === oldValue)) {
                return;
            }

            $("#secondSearchLocationDestination").select2("val", newVal);
            $scope.secondSearchLocationDestinationClass = false;

        });

        $scope.$watch('firstSearchLocationDestination', function(newVal, oldValue) {
            if (oldValue && oldValue !== null && (newVal === oldValue)) {
                return;
            }


            $("#secondSearchLocationOrigin").select2("val", newVal);
            $scope.secondSearchLocationOriginClass = false;

        });

        $scope.$watch('goDate', function(newVal, oldValue) {
            if (!moment(newVal).isValid()) {

            }



            $scope.returnDateMin = newVal;

            if ($scope.returnDate !== null && newVal.getTime() > $scope.returnDate.getTime()) {
                $scope.returnDate = newVal;
            }
        });

        $scope.$watch('searchAdultCount', function(newVal, oldValue) {
            if (newVal !== "") {

                if (newVal === 0) {
                    $scope.searchAdultCount = 1;
                } else {
                    if ($scope.searchAdultCount < $scope.searchInfantCount) {
                        $scope.searchInfantCount = $scope.searchAdultCount;
                    }
                }
            }
        });

        $scope.$watch('searchInfantCount', function(newVal, oldValue) {
            if (newVal !== '' && $scope.searchAdultCount !== '' && !(newVal <= $scope.searchAdultCount)) {
                $scope.searchInfantCount = $scope.searchAdultCount;
            }
        });

        $scope.$watch('searchChildCount', function(newVal, oldValue) {
            if (newVal === undefined || newVal === "") {

            }
        });

        //Initials
        var previousSearchAirLocations = $cookieStore.get("searchAirLocations");
        if (previousSearchAirLocations != null && previousSearchAirLocations.length > 0) {
            $scope.firstSearchLocationOrigin = previousSearchAirLocations[0].originAirportId;
            $scope.firstSearchLocationDestination = previousSearchAirLocations[0].destinationAirportId;

            if (previousSearchAirLocations[1]) {
                $scope.secondSearchLocationOrigin = previousSearchAirLocations[1].originAirportId;
                ;
                $scope.secondSearchLocationDestination = previousSearchAirLocations[1].destinationAirportId;
            }
        }



        $(document).ready(function() {

            $("#firstSearchLocationOrigin").on("select2-focus", function() {
                $(this).parent(".invalid-input").removeClass("invalid-input");
                $scope.firstSearchLocationOriginClass = false;
            });

            $("#secondSearchLocationOrigin").on("select2-focus", function() {
                $(this).parent(".invalid-input").removeClass("invalid-input");
                $scope.secondSearchLocationOriginClass = false;
            });

            $("#firstSearchLocationDestination").on("select2-focus", function() {
                $(this).parent(".invalid-input").removeClass("invalid-input");
                $scope.firstSearchLocationDestinationClass = false;
            });

            $("#secondSearchLocationDestination").on("select2-focus", function() {
                $(this).parent(".invalid-input").removeClass("invalid-input");
                $scope.secondSearchLocationDestinationClass = false;
            });

        });




    }]);