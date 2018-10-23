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

            $scope.log = [];

            $scope.getLogTable = function() {
                $http.get('admin/api-admin-common/scheduler-log?model='+$scope.modelClass).then(function(response) {
                    $scope.log = response.data;
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
                    console.log(response);
                });
            };
            
        }],
        template: function () {
            return '<div><span ng-click="toggleWindow()">Toggler ({{value}})</span>' + 
            '<div ng-show="isVisible" style="position:static;"><div class="card card-body mb-3" style="height:400px;">'+
            '<div class="row">'+
            '<div class="col">'+
            '{{log || json}}'+
            '</div><div class="col"><h2>Add</h2>'+
            '<zaa-datetime model="timestamp" /><zaa-text model="newvalue" /><button type="button" class="btn btn-save btn-icon" ng-click="saveNewJob()"></button>'+
            '</div></div></div></div>'+
            '</div>';
        }
    };
});