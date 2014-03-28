'use strict';


 var select2options ={
            allowClear:true,
            minimumInputLength: 2,
            ajax: {// instead of writing the function to execute the request we use Select2's convenient helper
                type: 'POST',
                contentType: "application/json; charset=utf-8",
                url: "index.php/getAirPortsWithPrefix",
                dataType: 'json',
                data: function(term, page) {
                    return {'airportPrefix': term};
                    //return "{'airportPrefix':\"' + term + '\"}";
                },
                results: function(data, page) { // parse the results into the format expected by Select2.
                    var combodata = new Array();
                    if (data !== undefined && data.length !== undefined && data.length > 0) {
                        for (var i = 0; i < data.length; i++) {
                            var obj = {id: data[i].id, text: data[i].summary};
                            combodata.push(obj);
                        }
                    }

                    return {results: combodata};
                }
            },
            initSelection: function(element, callback) {
                var id = $(element).val();
                if (id !== "") {
                    $.post("index.php/getAirportSummary", {'airportid': id}).done(function(data) {
                        var summarydata = {};
                        summarydata.id = data.id;
                        summarydata.text = data.summary;
                        callback(summarydata);
                    });
                }
            }

        };  

 var appDirectiveModule = angular.module(APP_NAME+'.directives', []);
 appDirectiveModule.value('uiSelect2Config', select2options);
 appDirectiveModule.directive('uiselect2', ['uiSelect2Config', '$http', function (uiSelect2Config, $http) {
  var options = {};
  if (uiSelect2Config) {
    angular.extend(options, uiSelect2Config);
  }
  return {
    require: 'ngModel',
    compile: function (tElm, tAttrs) {
      var watch,
        repeatOption,
  	repeatAttr,
        isSelect = tElm.is('select'),
        isMultiple = (tAttrs.multiple !== undefined);
 
      // Enable watching of the options dataset if in use
      if (tElm.is('select')) {
        repeatOption = tElm.find('option[ng-repeat], option[data-ng-repeat]');
 
        if (repeatOption.length) {
		  repeatAttr = repeatOption.attr('ng-repeat') || repeatOption.attr('data-ng-repeat');
          watch = jQuery.trim(repeatAttr.split('|')[0]).split(' ').pop();
        }
      }
 
      return function (scope, elm, attrs, controller) {
        // instance-specific options
        var opts = angular.extend({}, options, scope.$eval(attrs.uiSelect2));
 
        if (isSelect) {
          // Use <select multiple> instead
          delete opts.multiple;
          delete opts.initSelection;
        } else if (isMultiple) {
          opts.multiple = true;
        }
 
        if (controller) {
          // Watch the model for programmatic changes
          controller.$render = function () {
            if (isSelect) {
              elm.select2('val', controller.$modelValue);
            } else {
              if (isMultiple && !controller.$modelValue) {
                elm.select2('val', []);
              } else {
                elm.select2('val', controller.$modelValue);
              }
            }
          };
 
       
          // Watch the options dataset for changes
          
          if (watch) {
            scope.$watch(watch, function (newVal, oldVal, scope) {
              if (!newVal) return;
              // Delayed so that the options have time to be rendered
              setTimeout(function () {
                elm.select2('val', controller.$viewValue);
                // Refresh angular to remove the superfluous option
                elm.trigger('change');
              });
            });
          }
          
          if (!isSelect) {
            // Set the view and model value and update the angular template manually for the ajax/multiple select2.
            elm.bind("change", function () {
              scope.$apply(function () {
                controller.$setViewValue(elm.select2('val'));
              });
            });
 
            if (opts.initSelection) {
              var initSelection = opts.initSelection;
              opts.initSelection = function (element, callback) {
                initSelection(element, function (value) {
                  controller.$setViewValue(value.id);
                  callback(value);
                });
              };
            }
          }
        }
 
        attrs.$observe('disabled', function (value) {
          elm.select2(value && 'disable' || 'enable');
        });
 
        scope.$watch(attrs.ngMultiple, function(newVal) {
          elm.select2(opts);
        });
 
        // Set initial value since Angular doesn't
        elm.val(scope.$eval(attrs.ngModel));
 
        // Initialize the plugin late so that the injected DOM does not disrupt the template compiler
        setTimeout(function () {
          elm.select2(opts);
          if (!opts.initSelection && !isSelect)
            controller.$setViewValue(elm.select2('val'));
        });
      };
    }
  };
}]);


appDirectiveModule.directive('numeric', function() {
  return {
    require: 'ngModel',
    link: function (scope, element, attr, ngModelCtrl) {
      function fromUser(text) {
        var transformedInput = text.replace(/[^0-9]/g, '');
        //console.log(transformedInput);
        if(transformedInput !== text) {
            ngModelCtrl.$setViewValue(transformedInput);
            ngModelCtrl.$render();
        }
        return transformedInput;  // or return Number(transformedInput)
      }
      ngModelCtrl.$parsers.push(fromUser);
    }
  }; 
});