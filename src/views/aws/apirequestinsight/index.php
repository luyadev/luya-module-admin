<?php
use luya\admin\ngrest\aw\CallbackButtonWidget;
?>
<script>
zaa.bootstrap.register('InlineController', ['$scope', function($scope,) {

    // data table

    $scope.data = [];
    $scope.page = 0;
    $scope.pageCount = 0;
    $scope.dataCount = 0;

    $scope.$watch('page', function(n, o) {
        if (n !== o) {
            $scope.load(n);
        }
    });

    $scope.load = function(page) {
        $scope.$parent.sendActiveWindowCallback('data', {page:page}).then(function(response) {
            $scope.data = response.data;
            $scope.pageCount = response.headers('X-Pagination-Page-Count');
            $scope.dataCount = response.headers('X-Pagination-Total-Count');
        })
    };

    // insighs

    $scope.insights = {};

    $scope.loadInsights = function(page) {
        $scope.$parent.sendActiveWindowCallback('insight').then(function(response) {
            $scope.insights = response.data;
            $scope.load(page);
        });
    };

    $scope.loadInsights($scope.page);
}]);
</script>
<div ng-controller="InlineController">
    <p class="alert alert-warning">
        This feature is mainly used to get insights for a given API. Its not recommend to enable this function over a long time as it can collect a lot of data which can slow down your application.
    </p>
    <?php if($isEnabled): ?>
    <p class="alert alert-danger">The request logger is currently active!</p>
    <?php endif; ?>
    <?= CallbackButtonWidget::widget(['callback' => 'toggle', 'label' => $isEnabled ? '<i class="material-icons">clear</i> Disable Logger' : '<i class="material-icons">check</i> Enable Logger', 'options' => ['reloadWindowOnSuccess' => true]]); ?>
    <?= CallbackButtonWidget::widget(['callback' => 'delete', 'label' => '<i class="material-icons">delete</i> Delete Data', 'angularCallbackFunction' => 'function() { $scope.load(1); };']); ?>
    <button type="button" class="btn" ng-click="loadInsights(0)"><i class="material-icons">refresh</i> Fetch data</button>
    </p>
    <div class="row">
        <div class="col-7">
        <p class="lead">Requests
        <span class="small small float-right text-muted">
        <span>Ø {{insights.avarage}} ms /</span>
        <span>Min {{insights.min}} ms /</span>
        <span>Max {{insights.max}} ms</span>
</span></p>
            <table class="table table-bordered table-hover table-striped table-sm small">
                <thead>
                    <tr>
                        <th scope="col">Date</th>
                        <th scope="col">Type</th>
                        <th scope="col">Url</th>
                        <th scope="col">Time</th>
                    </tr>
                </thead>
                <tr ng-repeat="item in data" title="{{item.request_url}}">
                    <td>{{ item.timestamp * 1000 | date:'short'}}</td>
                    <td>{{ item.request_method }}</td>
                    <td>{{ item.request_url | truncate: 100: '...'}}</small></td>
                    <td ng-class="{'text-success': item.response_time <= insights.avarage, 'text-danger': item.response_time > insights.avarage}">
                        {{ item.response_time }} ms
                    </td>
                </tr>
            </table>
            <p class="text-muted small">{{data.length}} of {{dataCount}} Requests</p>
            <pagination current-page="page" page-count="pageCount"></pagination>
        </div>
        <div class="col-5">
            <p class="lead">Top requested Url</p>
            <table class="table table-bordered table-hover table-striped table-sm small">
                <thead>
                    <tr>
                        <th scope="col">Url</th>
                        <th scope="col">Count</th>
                    </tr>
                </thead>
                <tr ng-repeat="item in insights.counted" title="{{item.request_url}}">
                    <td>{{item.request_url | truncate: 100: '...'}}</td>
                    <td>{{item.count }}</td>
                </tr>
            </table>
            <p class="lead">Longest response time</p>
            <table class="table table-bordered table-hover table-striped table-sm small">
                <thead>
                    <tr>
                        <th scope="col">Url</th>
                        <th scope="col">Time</th>
                    </tr>
                </thead>
                <tr ng-repeat="item in insights.slowest" title="{{item.request_url}}">
                    <td>{{item.request_url | truncate: 100: '...'}}</td>
                    <td>{{ item.response_time }} ms</td>
                </tr>
            </table>
        </div>
    </div>
</div>