<?php
use luya\admin\Module;
use luya\admin\ngrest\aw\CallbackButtonWidget;

?>
<script>
zaa.bootstrap.register('InlineController', ['$scope', '$timeout', 'cfpLoadingBar', function($scope, $timeout, cfpLoadingBar) {

    // data table

    $scope.data = [];
    $scope.page = 1;
    $scope.pageCount = 0;
    $scope.dataCount = 0;

    $scope.$watch('page', function(n, o) {
        if (n !== o) {
            $scope.load(n);
        }
    });

    $scope.queryPromise;
    $scope.query = '';

    $scope.$watch('query', function(n, o) {
        if (n !== o) {
            $timeout.cancel($scope.queryPromise);
            cfpLoadingBar.start();
            $scope.queryPromise = $timeout(function() {
                $scope.load(1, n);
            }, 1000);
        }
    })

    $scope.load = function(page, query) {
        $scope.$parent.sendActiveWindowCallback('data', {page:page, query:query}).then(function(response) {
            $scope.data = response.data;
            $scope.pageCount = response.headers('X-Pagination-Page-Count');
            $scope.dataCount = response.headers('X-Pagination-Total-Count');
        })
    };

    // insighs

    $scope.insights = {};

    $scope.loadInsights = function(page) {
        $scope.query = '';
        $scope.$parent.sendActiveWindowCallback('insight').then(function(response) {
            $scope.insights = response.data;
            $scope.load(page);
        });
    };

    $scope.loadInsights($scope.page);
}]);
</script>
<div ng-controller="InlineController">
    <?php if ($isEnabled): ?>
        <p class="alert alert-danger"><?= Module::t('aw_requestinsight_logger_active'); ?></p>
    <?php else: ?>
        <p class="alert alert-info"><?= Module::t('aw_requestinsight_warning'); ?></p>
    <?php endif; ?>
    <?= CallbackButtonWidget::widget(['callback' => 'toggle', 'label' => $isEnabled ? '<i class="material-icons">clear</i> ' . Module::t('aw_requestinsight_btn_disable') : '<i class="material-icons">check</i> ' . Module::t('aw_requestinsight_btn_enable'), 'options' => ['reloadWindowOnSuccess' => true]]); ?>
    <?= CallbackButtonWidget::widget(['callback' => 'delete', 'label' => '<i class="material-icons">delete</i> ' . Module::t('aw_requestinsight_btn_clear'), 'angularCallbackFunction' => 'function() { $scope.loadInsights(0); };']); ?>
    <button type="button" class="btn" ng-click="loadInsights(0)"><i class="material-icons">refresh</i> <?= Module::t('aw_requestinsight_btn_fetch'); ?></button>
    </p>
    <div class="row">
        <div class="col-7">
        <p class="lead"><?= Module::t('aw_requestinsight_request_label'); ?>
            <span class="small small float-right text-muted">
                <span ng-show="insights.avarage">Ø {{insights.avarage}} ms /</span>
                <span ng-show="insights.min">Min {{insights.min}} ms /</span>
                <span ng-show="insights.max">Max {{insights.max}} ms</span>
            </span>
        </p>
            <div class="input-group mb-2">
                <div class="input-group-prepend">
                    <div class="input-group-text">
                        <i class="material-icons">search</i>
                    </div>
                </div>
                <input class="form-control" ng-model="query" type="text">
            </div>
            <table class="table table-bordered table-hover table-striped small">
                <thead>
                    <tr>
                        <th scope="col"><?= Module::t('aw_requestinsight_col_date'); ?></th>
                        <th scope="col"><?= Module::t('aw_requestinsight_col_type'); ?></th>
                        <th scope="col"><?= Module::t('aw_requestinsight_col_url'); ?></th>
                        <th scope="col"><?= Module::t('aw_requestinsight_col_time'); ?></th>
                    </tr>
                </thead>
                <tr ng-repeat="item in data" title="{{item.request_url}}">
                    <td>{{ item.timestamp * 1000 | date:'short'}}</td>
                    <td>{{ item.request_method }}</td>
                    <td>{{ item.request_url | truncate: 100: '...'}}</small></td>
                    <td class="text-nowrap" ng-class="{'text-success': item.response_time <= insights.avarage, 'text-danger': item.response_time > insights.avarage}">
                        {{ item.response_time }} ms
                    </td>
                </tr>
            </table>
            <p class="text-muted small"><?= Module::t('aw_requestinsight_data_pagination'); ?></p>
            <pagination current-page="page" page-count="pageCount"></pagination>
        </div>
        <div class="col-5">
            <p class="lead"><?= Module::t('aw_requestinsight_top_request_label'); ?></p>
            <table class="table table-bordered table-hover table-striped small">
                <thead>
                    <tr>
                        <th scope="col"><?= Module::t('aw_requestinsight_col_url'); ?></th>
                        <th scope="col"><?= Module::t('aw_requestinsight_col_count'); ?></th>
                    </tr>
                </thead>
                <tr ng-repeat="item in insights.counted" title="{{item.request_url}}">
                    <td>{{item.request_url | truncate: 100: '...'}}</td>
                    <td class="text-nowrap">{{item.count }}</td>
                </tr>
            </table>
            <p class="lead"><?= Module::t('aw_requestinsight_longest_response_label'); ?></p>
            <table class="table table-bordered table-hover table-striped small">
                <thead>
                    <tr>
                        <th scope="col"><?= Module::t('aw_requestinsight_col_url'); ?></th>
                        <th scope="col"><?= Module::t('aw_requestinsight_col_time'); ?></th>
                    </tr>
                </thead>
                <tr ng-repeat="item in insights.slowest" title="{{item.request_url}}">
                    <td>{{item.request_url | truncate: 100: '...'}}</td>
                    <td class="text-nowrap">{{ item.response_time }} ms</td>
                </tr>
            </table>
        </div>
    </div>
</div>
