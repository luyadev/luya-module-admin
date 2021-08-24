 /**
  * LUYA admin scheduler directive.
  * 
  * The scheduler directive will turn any field into an interactive scheduling system.
  * 
  * ```
  * <luya-schedule
  *     value="{{currentValueOfTheEntity}}"
  *     primary-key-value="{{primaryKeyModelValue}}"
  *     model-class="luya\admin\models\User"
  *     attribute-name="is_deleted"
  *     title="Deleted Title"
  *     attribute-values="[{"label":"Draft","value":0},{"label":"Archived","value":2},{"label":"Published","value":1}]"
  * />
  * ```
  * 
  * + value: Its a two-way binding name of field which contains the value
  * + attribute-values: Its a two-way binding name of field which contains the current values to schedule and display based on an array with label and value key.
  * + primary-key-value: Its a two-way binding mame of field which contains the current primary key. Composite keys must be seperated by commans like `1,3`.
  * + model-class: The path to the model class which must impelement NgRestModelInterface
  * + attribute-name: The name of the attribute which should be scheduled.
  * + title: The title of the attribute, like the label.
  * 
  * > Keep in mind to enable the queue fake cronjob or enable a cronjob which runs the queue command.
  * 
  * @since 2.0
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
            onlyIcon: "@",
            title: "@"
        },
        controller: ['$scope', '$http', '$timeout', 'AdminToastService', function($scope, $http, $timeout, AdminToastService) {

            // toggle window

            $scope.getFirstAttributeKeyAsDefaultValue = function() {
                return $scope.attributeValues[0]['value'];
            };

            $scope.newvalue = $scope.getFirstAttributeKeyAsDefaultValue();
            $scope.isVisible = false;
            $scope.upcomingAccordionOpen = true;
            $scope.archiveAccordionOpen = false;
            $scope.showDatepicker = false;
            $scope.modalPositionClass = "";

            $scope.$watch('showDatepicker', function(newValue) {
                if (newValue === 0) {
                    // disable the schedule checkbox, ensure to reset the timestamp.
                    var date = new Date();
                    $scope.timestamp = date.getTime() / 1000;
                }
            });

            $scope.toggleWindow = function() {
                $scope.isVisible = !$scope.isVisible;

                if ($scope.isVisible) {
                    $scope.getLogTable();
                } else {
                    $scope.hideInlineModal();
                }
            };

            $scope.escModal = function() {
                if($scope.isVisible) {
                    $scope.isVisible = false;
                    $scope.hideInlineModal();
                }
            };

            $scope.getUniqueFormId = function(prefix) {
                return prefix + $scope.primaryKeyValue + '_' + $scope.attributeName;
            };

            // get existing job data
            $scope.logs = {
                'upcoming': [],
                'archived': []
            };

            $scope.getLogTable = function(callback) {
                $http.get('admin/api-admin-common/scheduler-log?model='+$scope.modelClass+'&pk=' + $scope.primaryKeyValue + '&target=' + $scope.attributeName).then(function(response) {
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
                }, function(error) {
                    AdminToastService.errorArray(error.data);
                });
            };

            $scope.deleteJob = function(job) {
                $http.delete('admin/api-admin-common/scheduler-delete?id=' + job.id).then(function(response) {
                    $scope.getLogTable();
                });
            };
        }],
        link: function (scope, element, attr) {
            var inlineModal = element.find('.inlinemodal');
            var inlineModalArrow = element.find('.inlinemodal-arrow');
            var button = element.find('.scheduler-btn');

            // The spacing the modal has to the window border
            // and button
            var modalMargin = 15;

            // If the space right to the button is smaller than minSpaceRight
            // and the space to the left is bigger than to the right
            // the modal will be aligned to the left of the button
            var minSpaceRight = 500;

            // If the space left or right of the button is smaller than minSpace
            // the modal will be display in full width
            var minSpace = 300;

            // The max width of the modal, defined in the scss component inlinemodal
            var maxWidth = 1000;

            // Get the button position and align the modal to the right if
            // it hast at least "minSpaceRight" spacing to the right
            // If not, align left
            scope.alignModal = function() {
                var documentSize = {width: $(document).width(), height: element.parents('.luya-content')[0].scrollHeight || $(document).height()};
                var buttonBcr = button[0].getBoundingClientRect();

                var buttonSpaceRight = documentSize.width - (buttonBcr.left + buttonBcr.width + (modalMargin * 2));
                var buttonSpaceLeft = buttonBcr.left - (modalMargin * 2);
                var alignRight = buttonSpaceRight >= minSpaceRight || buttonSpaceRight >= buttonSpaceLeft;
                var notEnoughSpace = buttonSpaceLeft < minSpace && buttonSpaceRight < minSpace;

                inlineModal.removeClass('inlinemodal--left inlinemodal--right inlinemodal--full');
                if(notEnoughSpace) {
                    inlineModal.addClass('inlinemodal--full');
                    inlineModal.css({
                        display: 'block',
                        left: modalMargin,
                        right: modalMargin,
                        top: modalMargin,
                        bottom: modalMargin
                    });
                } else if (alignRight) {
                    inlineModal.addClass('inlinemodal--right');
                    inlineModal.css({
                        display: 'block',
                        left: buttonBcr.left + buttonBcr.width + modalMargin,
                        right: modalMargin,
                        width: 'auto'
                    });
                } else {
                    inlineModal.addClass('inlinemodal--left');
                    inlineModal.css({
                        display: 'block',
                        left: buttonBcr.left > maxWidth ? 'auto' : modalMargin,
                        right: (documentSize.width + modalMargin) - buttonBcr.left,
                        width: buttonBcr.left > maxWidth ? '100%' : 'auto'
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
                var arrowHeight = inlineModalArrow.outerHeight();

                var newTop = buttonBcr.top - modalMargin - (arrowHeight / 2); // 7.5 equals the height of the arrow / 2

                var topMin = 10;
                var topMax = modalBcr.height - modalMargin - (arrowHeight / 2) - 10;

                if (newTop <= topMin) {
                    newTop = topMin;
                } else if (newTop >= (topMax > 0 ? topMax : 5000)) { // Top max might be below 0 if the modal bcr is 0
                    newTop = topMax;
                }

                inlineModalArrow.css({
                    top: newTop
                });
            };

            scope.showInlineModal = function() {
                scope.alignModal();
            };

            scope.hideInlineModal = function() {
                inlineModal.css({display: 'none'});
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
                            '<i class="material-icons">schedule</i><span ng-hide="onlyIcon">{{valueToLabel(value)}}</span>' +
                        '</button>' +
                        '<div class="inlinemodal" style="display: none;" ng-class="modalPositionClass" zaa-esc="escModal()">' +
                            '<div class="inlinemodal-inner">' +
                                '<div class="inlinemodal-head clearfix">' +
                                    '<div class="modal-header">' +
                                        '<h5 class="modal-title">{{title}}</h5>' +
                                        '<div class="modal-close">' +
                                            '<button type="button" class="close" aria-label="Close" ng-click="toggleWindow()">' +
                                                '<span aria-hidden="true"><span class="modal-esc">ESC</span> &times;</span>' +
                                            '</button>' +
                                        '</div>' +
                                    '</div>' +
                                '</div>' +
                                '<div class="inlinemodal-content">' +
                                    '<div class="clearfix">' +
                                        '<zaa-select model="newvalue" options="attributeValues" label="' + i18n['js_scheduler_new_value'] + '"></zaa-select>' +
                                        '<zaa-checkbox model="showDatepicker" fieldid="{{getUniqueFormId(\'datepicker\')}}" label="' + i18n['js_scheduler_show_datepicker'] + '"></zaa-checkbox>'+
                                        '<zaa-datetime ng-show="showDatepicker" model="timestamp" label="' + i18n['js_scheduler_time'] + '"></zaa-datetime>' +
                                        '<button type="button" class="btn btn-save btn-icon float-right" ng-click="saveNewJob()">' + i18n['js_scheduler_save'] + '</button>' +
                                    '</div>' +
                                    
                                    '<div class="card mt-4" ng-class="{\'card-closed\': !upcomingAccordionOpen}" ng-hide="logs.upcoming.length <= 0">' +
                                        '<div class="card-header" ng-click="upcomingAccordionOpen=!upcomingAccordionOpen">' +
                                            '<span class="material-icons card-toggle-indicator">keyboard_arrow_down</span>' +
                                            '<i class="material-icons">alarm</i>&nbsp;<span> ' + i18n['js_scheduler_title_upcoming'] + '</span><span class="badge badge-secondary float-right">{{logs.upcoming.length}}</span>' +
                                        '</div>'  +
                                        '<div class="card-body p-2">' +
                                            '<div class="table-responsive">' +
                                                '<table class="table table-hover table-align-middle">' +
                                                    '<thead>' +
                                                        '<tr>' +
                                                            '<th>' + i18n['js_scheduler_table_newvalue'] + '</th>' +
                                                            '<th>' + i18n['js_scheduler_table_timestamp'] + '</th>' +
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
                                            '<i class="material-icons">alarm_on</i>&nbsp;<span> ' + i18n['js_scheduler_title_completed'] + '</span><span class="badge badge-secondary float-right">{{logs.archived.length}}</span>' +
                                        '</div>'  +
                                        '<div class="card-body p-2">' +
                                            '<div class="table-responsive">' +
                                                '<table class="table table-hover table-align-middle">' +
                                                    '<thead>' +
                                                        '<tr>' +
                                                            '<th>' + i18n['js_scheduler_table_newvalue'] + '</th>' +
                                                            '<th>' + i18n['js_scheduler_table_timestamp'] + '</th>' +
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
