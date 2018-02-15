<?php

use luya\helpers\Url;

/**
 * ApiOverviewActiveWindow Index View.
 *
 * @var $this \luya\admin\ngrest\base\ActiveWindowView
 * @var $model \luya\admin\models\ApiUser
 */
?>
<script>
zaa.bootstrap.register('ApiOverviewController', function($scope, $http, AdminToastService) {
    $scope.generateNewToken = function() {
    	AdminToastService.confirm('Are you sure to replace the current token with a new one? This can not be undone!', 'Set new token', function() {
    		this.close();
    		$scope.$parent.sendActiveWindowCallback('replaceToken').then(function(response) {
    			$scope.$parent.reloadActiveWindow();
            });
    	});
    };

    $scope.response;
    
    $scope.runRequest = function() {
        // auth: config.headers.Authorization = "Bearer " + $rootScope.luyacfg.authToken;
        $http.get($scope.requestUrl).then(function(response) {
            $scope.response = response;
        }, function(error) {
            $scope.response = error;
        });
    };

    $scope.testUrl = function(url) {
        $scope.requestUrl = url;
        $scope.runRequest();
    };
});
</script>
<div class="row" ng-controller="ApiOverviewController">
    <div class="col-md-2">
        <div class="card">
            <div class="card-header">Access Token</div>
            <div class="card-body">
                <p class="card-text"><small><?= empty($model->auth_token) ? '<i>none</i>' : '<kbd>' . $model->auth_token . '</kbd>'; ?></small></p>
                <p class="card-text">If you set a new access token, ensure the old token is not used! Otherwise your application wont work anymore.</p>
                <a ng-click="generateNewToken()" class="btn btn-danger">Generate new token</a>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">Available endpoints</div>
            <ul class="list-group list-group-flush">
                <?php foreach (Yii::$app->auth->getPermissionTableDistinct($model->id) as $data): if (empty($data['api'])) { continue; }?>
                    <li class="list-group-item">
                        <small>
                            <code><?= Url::base(true); ?>/admin/<?= $data['api']; ?></code>
                        </small>
                        <button type="button" class="btn btn-secondary" ng-click="testUrl('<?= Url::base(true); ?>/admin/<?= $data['api']; ?>')">Test</button>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">Endpoint Tester</div>
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Request</h6>
                <form ng-submit="runRequest()">
                  <div class="form-row">
                    <div class="col-11">
                      <input type="text" class="form-control" ng-model="requestUrl" />
                    </div>
                    <div class="col">
                      <button type="submit" class="btn btn-primary" ng-click="runRequest()">Run</button>
                    </div>
                  </div>
                </form>
            </div>
            <div class="card-body" ng-show="response">
          	    <h6 class="card-subtitle mb-2 text-muted">Response 
                    <span class="badge badge-success" ng-show="response.status == 200">{{ response.status }}</span>
                    <span class="badge badge-danger" ng-show="response.status != 200">{{ response.status }}</span>
                </h6>
                <pre class="mb-0"><code>{{ response.data | json }}</code></pre>
            </div>
        </div>
    </div>
</div>