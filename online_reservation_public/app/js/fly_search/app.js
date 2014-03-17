'use strict';


// Declare app level module which depends on filters, and services
angular.module(APP_NAME, [
  'ngRoute',
   APP_NAME+'.filters',
   APP_NAME+'.services',
   APP_NAME+'.directives',
   APP_NAME+'.controllers'
  
]).
config(['$routeProvider', function($routeProvider) {
  $routeProvider.when('/',{templateUrl:PUBLIC_DIRECTORY+"/partials/"+APP_PAGE_NAME+'/index.html',controller:APP_NAME+'Controller'});
  $routeProvider.when('/view1', {templateUrl: PUBLIC_DIRECTORY+'/partials/partial1.html', controller: 'MyCtrl1'});
  $routeProvider.when('/view2', {templateUrl: PUBLIC_DIRECTORY+'/partials/partial2.html', controller: 'MyCtrl2'});
  $routeProvider.otherwise({redirectTo: '/view1'});
}]);




$(document).ready(function(){
    
    $('.navbar li').click(function(e) {
    $('.navbar li.active').removeClass('active');
    var $this = $(this);
    if (!$this.hasClass('active')) {
        $this.addClass('active');
    }
    e.preventDefault();
    
    });
 
    
});

