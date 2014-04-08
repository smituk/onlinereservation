



'use strict';

/* Services */


// Demonstrate how to register services
// In this case it is a simple value service.
var appServiceModule = angular.module(APP_NAME + '.services', []);


//constants 
appServiceModule.constant('ErrorCodes', function() {
    return {
        SUCCESS: '00000'
    };
});


//Factories
appServiceModule.factory("FlightSearchService", function($http, $q) {

    function flightSearchService() {
        this.searchAirLocations = new Array();

        function SearchAirLocation(origiAirportId, destinationAirportId, flightDate) {
            this.originAirportId = origiAirportId;
            this.destinationAirportId = destinationAirportId;
            this.flightDate = flightDate;

            this.getOriginAirportId = function() {
                return this.originAirportId;
            };

            this.getDestinationAirportId = function() {
                return this.destinationAirportId;
            };

            this.getFlightDate = function() {
                return this.flightDate;
            };


        }
        ;

        this.clearSearhAirLocations = function() {
            this.searchAirLocations = new Array();
        };

        this.addSearchAirLocation = function(originAirportId, destinationAirportId, flightDate) {
            var searchAirLoctionObject = new SearchAirLocation(originAirportId, destinationAirportId, flightDate);
            this.searchAirLocations.push(searchAirLoctionObject);
        };

        this.getSearchAirLocations = function() {
            return this.searchAirLocations;
        };

        this.getDirection = function() {
            return this.direction;
        };

        this.setDirection = function(direciton) {
            this.direction = direciton;
        };

        this.getAdultPassengerCount = function() {
            return this.adultPassengerCount;
        };

        this.setAdultPassengerCount = function(passengerCount) {
            this.adultPassengerCount = passengerCount;
        };

        this.getChildPassengerCount = function() {
            return this.childPassengerCount;
        };

        this.setChildPassengerCount = function(passengerCount) {
            this.childPassengerCount = passengerCount;
        };

        this.getInfantPassengerCount = function() {
            return this.infantPassengerCount;
        };

        this.setInfantPassengerCount = function(passengerCount) {
            this.infantPassengerCount = passengerCount;
        };

        this.isNonStopFlight = function() {
            return this.nonStopFlight;
        };

        this.setNonStopFlight = function(isNonStopFlight) {
            this.nonStopFlight = isNonStopFlight;
        };


        this.isFlexibleThirdDay = function() {
            return this.flexibleThirdDay;
        };

        this.setFlexibleThirdDay = function(isFlexibleThirdDay) {
            this.flexibleThirdDay = isFlexibleThirdDay;
        };

        this.isLowCostFlights = function() {
            return this.lowCostFlights;
        };

        this.setLowCostFlights = function(isLowCostFlights) {
            this.lowCostFlights = isLowCostFlights;
        };

        this.getCabinClass = function() {
            return this.cabinClass;
        };

        this.setCabinClass = function(cabinClass) {
            this.cabinClass = cabinClass;
        };



        this.serialize = function(obj, prefix) {
            var str = [];
            for (var p in obj) {
                var k = prefix ? prefix + "[" + p + "]" : p, v = obj[p];
                str.push(typeof v == "object" ? this.serialize(v, k) : encodeURIComponent(k) + "=" + encodeURIComponent(v));
            }
            return str.join("&");
        };

        this.performSearch = function() {

            var deferred = $q.defer();
            var requestFlySearchJsonObject = {};
            requestFlySearchJsonObject['yetiskinNumber'] = this.getAdultPassengerCount();
            requestFlySearchJsonObject['cocukNumber'] = this.getChildPassengerCount();
            requestFlySearchJsonObject['bebekNumber'] = this.getInfantPassengerCount();
            requestFlySearchJsonObject['directionOption'] = this.getDirection();
            requestFlySearchJsonObject['cabinClass'] = this.getCabinClass();
            requestFlySearchJsonObject['isNonStopFlight'] = this.isNonStopFlight();
            requestFlySearchJsonObject['isFlexibleThirdDay'] = this.isFlexibleThirdDay();
            requestFlySearchJsonObject['isLowCostFlights'] = this.isLowCostFlights();

            var airSearchLegs = new Array();

            for (var i = 0; i < this.searchAirLocations.length; i++) {

                var searchAirLocationObject = this.searchAirLocations[i];
                var searchAirleg = {};
                searchAirleg.origin = {};
                searchAirleg.destination = {};
                searchAirleg['origin']['airport'] = searchAirLocationObject.getOriginAirportId();
                searchAirleg.destination.airport = searchAirLocationObject.getDestinationAirportId();
                if (searchAirLocationObject.getFlightDate()) {
                    var year = searchAirLocationObject.getFlightDate().getFullYear();
                    var month = ('0' + (searchAirLocationObject.getFlightDate().getMonth() + 1)).slice(-2);
                    var day = ('0' + (searchAirLocationObject.getFlightDate().getDate())).slice(-2);
                    searchAirleg.departureTime = year + "-" + month + '-' + day;
                }
                if (this.getDirection() === "1" && i > 0) {  // tek yonler için 
                    continue;
                }

                airSearchLegs.push(searchAirleg);
            }

            requestFlySearchJsonObject['airSearchLegs'] = airSearchLegs;
            $http.post('index.php/searchflyrequest', this.serialize(requestFlySearchJsonObject), {
                responseType: 'json',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}}).success(function(data, status, headers, config) {
                if (data != null && data.error_code) {
                    deferred.resolve(data);
                } else {
                    deferred.reject(data);
                }
            }).error(function(data, status, headers, config) {
                deferred.reject();
            });

            return deferred.promise;

        };

        this.getSearchCriteria = function() {
            var searchCriteria = {};
            searchCriteria.searchAirLocations = this.getSearchAirLocations();
            searchCriteria.adultPassengerCount = this.getAdultPassengerCount();
            searchCriteria.childPassengerCount = this.getChildPassengerCount();
            searchCriteria.infantPassengerCount = this.getInfantPassengerCount();
            searchCriteria.cabinClass = this.getCabinClass();
            return searchCriteria;
        };

        this.getModalControllerInstance = function() {
            var searchingFlightControllerInstance = function($scope, $modalInstance, searchCriteria, AirportService) {
                $scope.searchCriteria = searchCriteria;
                
                 

                $scope.ok = function() {
                    $modalInstance.close(searchCriteria);
                };

                $scope.cancel = function() {
                    $modalInstance.dismiss('cancel');
                };

            };
            return searchingFlightControllerInstance;
        };





    }
    ;

    return new flightSearchService();

});

appServiceModule.factory("AirportService", function($http, $q) {
     
    function AirportService(){
        this.getAirportSummary = function (airportCode){
          var param = "airportid="+airportCode;  
          var deferred = $q.defer();
            $http.post('index.php/getAirportSummary',param, {
                responseType: 'json',
                headers: {'Content-Type':'application/x-www-form-urlencoded'}}).success(function(data, status, headers, config) {
                if (data != null && data.id) {
                    deferred.resolve(data);
                } else {
                    deferred.reject(data);
                }
            }).error(function(data, status, headers, config) {
                deferred.reject();
            });

            return deferred.promise;
        };
     }
     return new AirportService();
    
});
