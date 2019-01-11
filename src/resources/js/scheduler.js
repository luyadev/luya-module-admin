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
            $scope.upcomingAccordionOpen = true;
            $scope.archiveAccordionOpen = false;

            $scope.toggleWindow = function() {
                $scope.isVisible = !$scope.isVisible;

                if ($scope.isVisible) {
                    $scope.getLogTable();
                } else {
                    $scope.hideInlineModal();
                }
            };

            // get existing job data
            $scope.logs = {
                'upcoming': [],
                'archived': []
            };

            $scope.getLogTable = function(callback) {
                $http.get('admin/api-admin-common/scheduler-log?model='+$scope.modelClass+'&pk=' + $scope.primaryKeyValue).then(function(response) {
                    $scope.logs.archived = [];
                    $scope.logs.upcoming = [];

                    response.data.forEach(function(entry) {
                        if(entry.is_done) {
                            $scope.logs.archived.push(entry);
                        } else {
                            $scope.logs.upcoming.push(entry);
                        }
                    });

                    // check if latestId is done, if yes, maybe directly change the value for a given field.
                    angular.forEach($scope.logs.archived, function(value, key) {
                        if (value.id == $scope.latestId) {
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

            $scope.deleteJob = function(job) {
                // todo: Delete
            };
        }],
        link: function (scope, element, attr) {
            var inlineModal = element.find('.inlinemodal');
            var inlineModalArrow = element.find('.inlinemodal-arrow');
            var button = element.find('.scheduler-btn');
            var modalMargin = 25;
            var minSpaceRight = 700;

            // Get the button position and align the modal to the right if
            // it hast at least "minSpaceRight" spacing to the right
            // If not, align left
            scope.alignModal = function() {
                var documentSize = {width: $(document).width(), height: element.parents('.luya-content')[0].scrollHeight || $(document).height()};
                var buttonBcr = button[0].getBoundingClientRect();

                var buttonSpaceRight = documentSize.width - (buttonBcr.left + buttonBcr.width);
                var alignRight = buttonSpaceRight >= minSpaceRight;

                inlineModal.removeClass('inlinemodal--left inlinemodal--right');
                if (alignRight) {
                    inlineModal.addClass('inlinemodal--right');
                    inlineModal.css({
                        'left': buttonBcr.left + buttonBcr.width + modalMargin,
                        'right': modalMargin,
                        'width': 'auto'
                    });
                } else {
                    inlineModal.addClass('inlinemodal--left');
                    inlineModal.css({
                        'left': buttonBcr.left > 1100 ? 'auto' : modalMargin + 'px',
                        'right': (buttonSpaceRight + buttonBcr.width + modalMargin) + 'px',
                        'width': buttonBcr.left > 1100 ? '100%' : 'auto'
                    });
                }

                scope.alignModalArrow();
            };

            // Calculate the new top value for the arrow inside the inline modal
            // We also check if the arrow is "inside" the modal and if not
            // set a min or max top value
            scope.alignModalArrow = function() {
                var modalBcr = inlineModal[0].getBoundingClientRect();
                var buttonBcr = button[0].getBoundingClientRect();

                var newTop = buttonBcr.top - modalMargin;

                var topMin = 5;
                var topMax = (((modalBcr.top + modalBcr.height) - modalMargin) - $(inlineModalArrow).outerHeight()) - 5;

                if (newTop <= topMin) {
                    newTop = topMin;
                } else if (newTop >= (topMax > 0 ? topMax : 5000)) { // Top max might be below 0 if the modal bcr is 0
                    newTop = topMax;
                }

                inlineModalArrow.css({
                    top: newTop + 'px'
                });
            };

            scope.showInlineModal = function() {
                scope.alignModal();

                inlineModal.css({
                    display: 'block',
                    zIndex: 500
                });
            };

            scope.hideInlineModal = function() {
                element.find('.inlinemodal').css({display: 'none'});
            };

            var w = angular.element(window);
            w.bind('resize', function() {
                if(scope.isVisible) {
                    scope.alignModal();
                }
            });
            $(window).on('scroll', function() {
                if(scope.isVisible) {
                    scope.alignModalArrow();
                }
            });
            element.parents().on('scroll', function() {
                if(scope.isVisible) {
                    scope.alignModalArrow();
                }
            });
        },
        template: function () {
            return '<div class="scheduler" ng-class="{\'inlinemodal--open\' : isVisible}">'+
                        '<button ng-click="toggleWindow()" type="button" class="scheduler-btn btn btn-link">' +
                            '<i class="material-icons">timelapse</i><span ng-hide="onlyIcon">{{valueToLabel(value)}}</span>' +
                        '</button>' +
                        '<div class="inlinemodal" style="display: none;">' +
                            '<div class="inlinemodal-inner">' +
                                '<div class="inlinemodal-head clearfix">' +
                                    '<span class="btn btn-cancel btn-icon float-right" ng-click="toggleWindow()"></span>' +
                                '</div>' +
                                '<div class="inlinemodal-content">' +

                                    '<div class="clearfix">' +
                                        '<zaa-datetime model="timestamp" label="Zeitpunkt" />' +
                                        '<zaa-select model="newvalue" options="attributeValues" label="Neuer Wert" />' +
                                        '<button type="button" class="btn btn-save btn-icon float-right" ng-click="saveNewJob()">New job</button>' +
                                    '</div>' +
                                    
                                    '<div class="card mt-4" ng-class="{\'card-closed\': !upcomingAccordionOpen}" ng-hide="logs.upcoming.length <= 0">' +
                                        '<div class="card-header" ng-click="upcomingAccordionOpen=!upcomingAccordionOpen">' +
                                            '<span class="material-icons card-toggle-indicator">keyboard_arrow_down</span>' +
                                            '<i class="material-icons">alarm</i>&nbsp;<span> Upcoming</span><small class="ml-1"><i>({{logs.upcoming.length}})</i></small>' +
                                        '</div>'  +
                                        '<div class="card-body p-2">' +
                                            '<div class="table-responsive-wrapper">' +
                                                '<table class="table table-hover table-align-middle mb-0">' +
                                                    '<thead>' +
                                                        '<tr>' +
                                                            '<th>New Value</th>' +
                                                            '<th>Scheduled time</th>' +
                                                            '<th></th>' +
                                                        '</tr>' +
                                                    '</thead>' +
                                                    '<tbody>' +
                                                        '<tr ng-repeat="log in logs.upcoming">' +
                                                            '<td>{{valueToLabel(log.new_attribute_value)}}</td>'+
                                                            '<td>{{log.schedule_timestamp*1000 | date:\'short\'}}</td>'+
                                                            '<td style="width: 60px;"><button type="button" class="btn btn-delete btn-icon" ng-click="deleteJob(log)"></button></td>'+
                                                        '</tr>' +
                                                    '</tbody>' +
                                                '</table>' +
                                            '</div>' +
                                        '</div>' +
                                    '</div>' +

                                    '<div class="card mt-3" ng-class="{\'card-closed\': !archiveAccordionOpen}" ng-hide="logs.archived.length <= 0">' +
                                        '<div class="card-header" ng-click="archiveAccordionOpen=!archiveAccordionOpen">' +
                                            '<span class="material-icons card-toggle-indicator">keyboard_arrow_down</span>' +
                                            '<i class="material-icons">alarm_on</i>&nbsp;<span> Completed</span><small class="ml-1"><i>({{logs.archived.length}})</i></small>' +
                                        '</div>'  +
                                        '<div class="card-body p-2">' +
                                            '<div class="table-responsive-wrapper">' +
                                                '<table class="table table-hover table-align-middle mb-0">' +
                                                    '<thead>' +
                                                        '<tr>' +
                                                            '<th>New Value</th>' +
                                                            '<th>Scheduled time</th>' +
                                                        '</tr>' +
                                                    '</thead>' +
                                                    '<tbody>' +
                                                        '<tr ng-repeat="log in logs.archived">' +
                                                            '<td>{{valueToLabel(log.new_attribute_value)}}</td>'+
                                                            '<td>{{log.schedule_timestamp*1000 | date:\'short\'}}</td>' +
                                                        '</tr>' +
                                                    '</tbody>' +
                                                '</table>' +
                                            '</div>' +
                                        '</div>' +
                                    '</div>' +

                                '</div>' +
                            '</div>' +
                            '<div class="inlinemodal-arrow"></div>' +
                        '</div>' +
                    '</div>';
        }
    };
});
