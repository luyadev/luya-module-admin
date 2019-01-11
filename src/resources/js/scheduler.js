/**
 * LUYA Admin scheduler
 *
 * @since 1.3.0
 */

 /**
  *
  * ```
  * <luya-schedule
  *     value="{{currentValueOfTheEntity}}"
  *     primary-key-value="{{primaryKeyModelValue}}"
  *     model-class="luya\admin\models\User"
  *     attribute-name="is_deleted"
  *     attribute-values="[{"label":"Draft","value":0},{"label":"Archived","value":2},{"label":"Published","value":1}]"
  * />
  * ```
  */
zaa.directive("luyaSchedule", function() {
    return {
        restrict: 'E',
        relace: true,
        scope: {
            value: "=",
            attributeValues: "=",
            primaryKeyValue: "=",
            modelClass: "@",
            attributeName: "@",
            onlyIcon: "@"
        },
        controller: ['$scope', '$http', '$timeout', function($scope, $http, $timeout) {

            // toggle window

            $scope.isVisible = false;

            $scope.toggleWindow = function() {
                $scope.isVisible = !$scope.isVisible;

                if ($scope.isVisible) {
                    $scope.getLogTable();
                } else {
                    $scope.hideInlineModal();
                }
            };

            // get existing job data

            $scope.logs = [];

            $scope.getLogTable = function(callback) {
                $http.get('admin/api-admin-common/scheduler-log?model='+$scope.modelClass+'&pk=' + $scope.primaryKeyValue).then(function(response) {
                    $scope.logs = response.data;

                    // check if latestId is done, if yes, maybe directly change the value for a given field.
                    angular.forEach($scope.logs, function(value, key) {
                        if (value.id == $scope.latestId && value.is_done) {
                            $scope.value = value.new_attribute_value;
                        }
                    });

                    $timeout(function() {
                        $scope.showInlineModal();
                    });
                });
            };

            $scope.valueToLabel = function(inputValue) {
                var label;
                angular.forEach($scope.attributeValues, function(value) {
                    if (value.value == inputValue) {
                        label = value.label;
                    }
                });

                return label;
            };

            // submit new job
            var now = new Date().getTime() / 1000;
            $scope.latestId;
            $scope.timestamp = parseInt(now);
            $scope.newvalue = $scope.value;
            $scope.saveNewJob = function() {
                $http.post('admin/api-admin-common/scheduler-add', {
                    model_class: $scope.modelClass,
                    primary_key: $scope.primaryKeyValue,
                    target_attribute_name: $scope.attributeName,
                    new_attribute_value: $scope.newvalue,
                    schedule_timestamp: $scope.timestamp
                }).then(function(response) {
                    $scope.latestId = response.data.id;
                    $scope.getLogTable();
                    // post success message with admin toast
                });
            };
        }],
        link: function (scope, element, attr) {
            var inlineModal = element.find('.inlinemodal');
            var button = element.find('.scheduler-btn');

            scope.getModalBcr = function() {
                var buttonBcr = button[0].getBoundingClientRect();
                var currentStyling = inlineModal.attr('style');

                inlineModal.css({display: 'block', left: buttonBcr.left, top: (buttonBcr.top + buttonBcr.height), height: 'auto', width: '100%', maxWidth: '1000px', overflow: 'hidden'});
                var modalBcr = inlineModal[0].getBoundingClientRect();
                if(currentStyling) {
                    inlineModal.attr('style', currentStyling);
                } else {
                    inlineModal.css('display', 'none');
                }

                return modalBcr;
            };

            scope.updateModalBcr = function() {
                var scrollHeight = element.parents('.luya-content')[0].scrollHeight;

                if(scrollHeight) {
                    var padding = parseInt(element.parents('.luya-content').css('padding-top').replace('px', '')) + parseInt(element.parents('.luya-content').css('padding-bottom').replace('px', ''));
                    scrollHeight = scrollHeight - padding;
                }

                var documentSize = {width: $(document).width(), height: scrollHeight || $(document).height()};
                var inlineModalBcr = scope.getModalBcr();
                var buttonBcr = button[0].getBoundingClientRect();

                // Check if modal overlaps to the right and decrease left value
                if(inlineModalBcr.x + inlineModalBcr.width > documentSize.width) {
                    // +25 for 25px spacing from the border
                    inlineModalBcr.x = inlineModalBcr.x - ((inlineModalBcr.x + inlineModalBcr.width) - documentSize.width + 25);

                    // Minimum left is 25
                    if(inlineModalBcr.x < 25) {
                        inlineModalBcr.x = 25;

                        // The modal now overlaps to the right, set max-width
                        if(inlineModalBcr.x + inlineModalBcr.width > documentSize.width) {
                            // -50 because of the spacing on the left and
                            // the spacing we want on the right
                            inlineModal.css('width', documentSize.width - 50);
                        }
                    }
                }

                // Check if the modal is too high and disappears on the bottom
                // of the window
                // +25 because we want a 25px spacing
                if((inlineModalBcr.y + 25) + inlineModalBcr.height > documentSize.height) {

                    console.log('too high, align top');
                    // Too high, set top position
                    inlineModalBcr.y = buttonBcr.y - inlineModalBcr.height;

                    // Check if modal disappears on top of the window
                    if(inlineModalBcr.y - inlineModalBcr.height < 0) {

                        console.log('will overlap');

                        // Enable scrolling of content
                        inlineModal.find('.inlinemodal-content').css('overflow', 'auto');

                        // In this case the inline modal is too high and
                        // needs to be resized
                        if(buttonBcr.y > documentSize.height - buttonBcr.y) {
                            console.log('above');
                            // Space above the button is bigger than below
                            inlineModalBcr.y = 25; // 25 px from top
                            inlineModal.css('height', buttonBcr.y - inlineModalBcr.y - 5); // -5 for better spacing between modal and clicked button
                        } else {
                            console.log('below');
                            // Space below the button is bigger than above
                            inlineModalBcr.y = buttonBcr.y + buttonBcr.height;
                            // +25 for 25px spacing from the border
                            inlineModal.css('height', inlineModalBcr.height - ((inlineModalBcr.y + inlineModalBcr.height) - (documentSize.height - 25)));
                        }
                    } else {
                        // Set height to auto
                        inlineModal.css('height', 'auto');
                    }
                } else {
                    // Set height to auto
                    inlineModal.css('height', 'auto');
                }

                inlineModal.css({
                    top: inlineModalBcr.top,
                    left: inlineModalBcr.left
                });
            };

            scope.showInlineModal = function() {
                var inlineModalBcr = scope.getModalBcr();
                var documentSize = {width: $(document).width(), height: element.parents('.luya-content')[0].scrollHeight || $(document).height()};

                scope.updateModalBcr();
                inlineModal.css({
                    display: 'block',
                    zIndex: 500
                })
            };

            scope.hideInlineModal = function() {
                element.find('.inlinemodal').css({display: 'none'});
            };

            var w = angular.element(window);
            w.bind('resize', function(){
                if(scope.isVisible) {
                    scope.updateModalBcr();
                }
            });
            element.parents().on('scroll', function() {
                if(scope.isVisible) {
                    scope.updateModalBcr();
                }
            });
        },
        template: function () {
            return '<div class="scheduler" ng-class="{\'inlinemodal--open\' : isVisible}">'+
                        '<button ng-click="toggleWindow()" type="button" class="scheduler-btn btn btn-link">' +
                            '<i class="material-icons">timelapse</i><span ng-hide="onlyIcon">{{valueToLabel(value)}}</span>' +
                        '</button>' +
                        '<div class="inlinemodal" style="width: 100%; max-width: 1000px; display: none;">' +
                            '<div class="inlinemodal-head clearfix">' +
                                '<span class="btn btn-cancel btn-icon float-right" ng-click="toggleWindow()"></span>' +
                            '</div>' +
                            '<div class="inlinemodal-content">' +
                                '<div class="clearfix">' +

                                    '<p class="lead">Change</p>'+
                                    '<zaa-datetime model="timestamp" label="Zeitpunkt" />'+
                                    '<zaa-select model="newvalue" options="attributeValues" label="Neuer Wert" />'+
                                    '<button type="button" class="btn btn-save btn-icon" ng-click="saveNewJob()"></button>'+

                                    '<p class="lead">Upcoming</p>'+
                                    '<table class="table table-bordered">'+
                                        '<thead><tr><th>New Value</th><th>Schedule Time</th><th>Action</th></tr></thead>'+
                                        '<tr ng-repeat="log in logs | filter:{is_done:0}">'+
                                            '<td>{{valueToLabel(log.new_attribute_value)}}</td>'+
                                            '<td>{{log.schedule_timestamp*1000 | date:\'short\'}}</td>'+
                                            '<td>TRASH</td>'+
                                        '</tr>' +
                                    '</table>'+

                                    '<p class="lead">Archive</p>'+
                                    '<table class="table table-bordered">'+
                                        '<thead><tr><th>New Value</th><th>Schedule Time</th></tr></thead>'+
                                        '<tr ng-repeat="log in logs | filter:{is_done:1}">'+
                                            '<td>{{valueToLabel(log.new_attribute_value)}}</td><td>{{log.schedule_timestamp*1000 | date:\'short\'}}</td>'+
                                        '</tr>' +
                                    '</table>'+
                                '</div>' +
                            '</div>' +
                        '</div>' +
                        '<style>.temp-z-index-fix { z-index:100 }</style>' +
                    '</div>';
        }
    };
});
