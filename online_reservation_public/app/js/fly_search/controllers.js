'use strict';

/* Controllers */
var appModule = angular.module(APP_NAME + '.controllers', [APP_NAME + '.directives', 'ui.bootstrap']);

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



appModule.controller("searchFormController", ['$scope', function($scope) {
        $scope.checked = true;
        $scope.isSecondSearchLocationOrigin = true;
        $scope.isSecondSearchLocationDestination = true;
        $scope.goDate = new Date();
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
        
        $scope.searchClass ="all";




        $scope.onJourneyTypeChange = function() {
            var journeyType = $scope.journeyType;
            if (journeyType === "2") {
                $scope.isSecondSearchLocationOrigin = true;
                $scope.isSecondSearchLocationDestination = true;
                $scope.returnDateDisabled = false;
                $scope.returnDate = $scope.tempReturnDate;
            } else if (journeyType === "1") {
                $scope.isSecondSearchLocationOrigin = false;
                $scope.isSecondSearchLocationDestination = false;
                $scope.returnDateDisabled = true;
                $scope.tempReturnDate = $scope.returnDate;
                $scope.returnDate = null;

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
        
        
        $scope.onSearchPerformed  = function(){
            var successFunction = function(){};
            var errorFunction = function(){};
        };

        $scope.$watch('firstSearchLocationOrigin', function(newVal, oldValue) {
            if (newVal === oldValue) {
                return;
            }
            $scope.secondSearchLocationDestination = newVal;
            $("#secondSearchLocationDestination").select2("val", newVal);
        });

        $scope.$watch('firstSearchLocationDestination', function(newVal, oldValue) {
            if (newVal === oldValue) {
                return;
            }
            $scope.secondSearchLocationOrigin = newVal;
            $("#secondSearchLocationOrigin").select2("val", newVal);

        });

        $scope.$watch('goDate', function(newVal, oldValue) {
            $scope.returnDateMin = newVal;
            if (newVal.getTime() > $scope.returnDate.getTime()) {
                $scope.returnDate = newVal;
            }
        });

        $scope.$watch('searchAdultCount', function(newVal, oldValue) {
            if (newVal !== "") {
               
                if(newVal === 0){
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
        
        
        

    }]);