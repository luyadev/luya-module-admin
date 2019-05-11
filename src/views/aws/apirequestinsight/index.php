<script>
zaa.bootstrap.register('InlineController', ['$scope', function($scope,) {

    $scope.data = [];

    $scope.load = function(page) {
        $scope.$parent.sendActiveWindowCallback('data', {page:page}).then(function(response) {
            $scope.data = response.data;
        })
    };

    $scope.load(1);
}]);
</script>
<div class="row" ng-controller="InlineController">
<p>The request logger is currently activ: <b><?= Yii::$app->formatter->asBoolean($isEnabled); ?></b></p>
<p>Total logged request: <?= $count; ?></p>
<table class="table table-bordered table-hover table-striped table-sm">
    <thead>
        <tr>
            <th scope="col">Date</th>
            <th scope="col">Type</th>
            <th scope="col">Url</th>
            <th scope="col">Time</th>
        </tr>
    </thead>
    <tr ng-repeat="item in data">
        <td>{{ item.timestamp * 1000 | date:'short'}}</td>
        <td>{{ item.request_method }}</td>
        <td>{{ item.request_url | truncate: 100: '...'}}</small></td>
        <td>{{ item.response_time }}</td>
    </tr>
</table>
</div>