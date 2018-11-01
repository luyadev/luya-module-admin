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
        controller: ['$scope', '$http', function($scope, $http) {

            // toggle window

            $scope.isVisible = false;

            $scope.toggleWindow = function() {
                $scope.isVisible = !$scope.isVisible;

                if ($scope.isVisible) {
                    $scope.getLogTable();
                }
            };

            // get existing job data

            $scope.logs = [];

            $scope.getLogTable = function() {
                $http.get('admin/api-admin-common/scheduler-log?model='+$scope.modelClass+'&pk=' + $scope.primaryKeyValue).then(function(response) {
                    $scope.logs = response.data;

                    // check if latestId is done, if yes, maybe directly change the value for a given field.
                    angular.forEach($scope.logs, function(value, key) {
                        if (value.id == $scope.latestId && value.is_done) {
                            $scope.value = value.new_attribute_value;
                        }
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
        template: function () {
            return '<div style="position: relative;" ng-class="{\'temp-z-index-fix\' : isVisible}"><button ng-click="toggleWindow()" type="button" class="btn btn-link"><i class="material-icons">timelapse</i><span ng-hide="onlyIcon">{{valueToLabel(value)}}</span></button>' + 
            '<div ng-show="isVisible" style="position: absolute; left: 50%; transform: translate(-50%, 0); min-width: 1300px;"><div class="card card-body mb-3" style="box-shadow: 3px 0px 1px 3px #ccc; ">'+
            
            '<div class="row">'+
            '<div class="col">'+
                '<p class="lead">Log</p>'+
                '<table class="table table-bordered">'+
                '<thead><tr><th>New Value</th><th>Schedule Time</th><th>Is Done</th></tr></thead>'+
                '<tr ng-repeat="log in logs">'+
                '<td>{{valueToLabel(log.new_attribute_value)}}</td><td>{{log.schedule_timestamp*1000 | date:\'short\'}}</td><td>{{log.is_done}}</td>'+
                '</tr>' + 
                '</table>'+
            '</div><div class="col">'+
            '<span class="btn btn-cancel btn-icon float-right" ng-click="toggleWindow()"></span>' +
                '<p class="lead">Schedule Event</p>'+
                '<zaa-datetime model="timestamp" label="Zeitpunkt" />'+
                '<zaa-select model="newvalue" options="attributeValues" label="Neuer Wert" />'+
                '<button type="button" class="btn btn-save btn-icon" ng-click="saveNewJob()"></button>'+
            '</div></div></div></div>'+
            '<style>.temp-z-index-fix { z-index:100 }</style>' +
            '</div>';
        }
    };
});