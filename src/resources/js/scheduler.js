/**
 * LUYA Admin scheduler
 * 
 * @since 1.3.0
 */

 /**
  * 
  * ```
  * <luya-schedule value="{{currentValueOfTheEntity}}" model-class="luya\admin\models\User" attribute-name="is_deleted" attribute-values="{0:'Not Deleted',1:'Deleted'}" />
  * ```
  */
zaa.directive("luyaSchedule", function() {
    return {
        restrict: 'E',
        relace: true,
        scope: {
            value: "=",
            modelClass: "@",
            attributeName: "@",
            attributeValues: "@"
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
                $http.get('admin/api-admin-common/scheduler-log?model='+$scope.modelClass).then(function(response) {
                    $scope.logs = response.data;
                });
            };

            // submit new job

            $scope.timestamp;
            $scope.newvalue;

            $scope.saveNewJob = function() {
                console.log($scope.attributeName);
                $http.post('admin/api-admin-common/scheduler-add', {
                    model_class: $scope.modelClass,
                    primary_key: $scope.value,
                    target_attribute_name: $scope.attributeName,
                    new_attribute_value: $scope.newvalue,
                    schedule_timestamp: $scope.timestamp
                }).then(function(response) {
                    $scope.getLogTable();
                    // post success message with admin toast
                });
            };
            
        }],
        template: function () {
            return '<div><span ng-click="toggleWindow()"><i class="material-icons">timelapse</i> {{value}}</span>' + 
            '<div ng-show="isVisible"><div class="card card-body mb-3">'+
            '<div class="row">'+
            '<div class="col">'+
                '<p class="lead">Log</p>'+
                '<table class="table table-bordered">'+
                '<tr ng-repeat="log in logs">'+
                '<td>{{log.new_attribute_value}}</td><td>{{log.schedule_timestamp*1000 | date:\'short\'}}</td><td>{{log.is_done}}</td>'+
                '</tr>' + 
                '</table>'+
            '</div><div class="col">'+
                '<p class="lead">Schedule Event</p>'+
                '<zaa-datetime model="timestamp" label="Zeitpunkt" />'+
                '<zaa-text model="newvalue" label="Neuer Wert" />'+
                '<button type="button" class="btn btn-save btn-icon" ng-click="saveNewJob()"></button>'+
            '</div></div></div></div>'+
            '</div>';
        }
    };
});