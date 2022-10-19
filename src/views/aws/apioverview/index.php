<?php

use luya\admin\Module;
use luya\helpers\Url;

/**
 * ApiOverviewActiveWindow Index View.
 *
 * @var $this \luya\admin\ngrest\base\ActiveWindowView
 * @var $model \luya\admin\models\ApiUser
 */
?>
<script>
zaa.bootstrap.register('ApiOverviewController', ['$scope', '$http', 'AdminToastService', function($scope, $http, AdminToastService) {
    $scope.generateNewToken = function() {
    	AdminToastService.confirm('<?= Module::t('aw_apioverview_resettokenconfirm'); ?>', '<?= Module::t('aw_apioverview_resettokenconfirm_title'); ?>', function() {
    		this.close();
    		$scope.$parent.sendActiveWindowCallback('replaceToken').then(function(response) {
    			$scope.$parent.reloadActiveWindow();
            });
    	});
    };

    $scope.response;

    $scope.runRequest = function() {
        if (!$scope.requestUrl) {
            AdminToastService.error("Request URL can not be empty.");
            return;
        }

        $http.get($scope.requestUrl, {'authToken': '<?= $model->auth_token; ?>'}).then(function(response) {
            $scope.response = response;
        }, function(error) {
            $scope.response = error;
        });
    };

    $scope.testUrl = function(url) {
        $scope.requestUrl = url;
        $scope.runRequest();
    };
}]);
</script>
<div class="row" ng-controller="ApiOverviewController">
    <div class="col-md-6">
        <collapse-container title="Custom <?= Module::t('aw_apioverview_endpoint'); ?>">
            <table class="table table-hover mb-0 pb-0">
            	<thead>
            		<tr>
            			<th><?= Module::t('aw_apioverview_endpoint'); ?></th>
            			<th><i class="material-icons" tooltip tooltip-text="<?= Module::t('aw_apioverview_permadd'); ?>" tooltip-position="bottom">add_box</i></th>
            			<th><i class="material-icons" tooltip tooltip-text="<?= Module::t('aw_apioverview_permedit'); ?>" tooltip-position="bottom">create</i></th>
            			<th><i class="material-icons" tooltip tooltip-text="<?= Module::t('aw_apioverview_permdelete'); ?>" tooltip-position="bottom">delete</i></th>
            			<th></th>
            		</tr>
            	</thead>
            	<?php foreach ($endpoints['specific'] as $data): ?>
            	<tr ng-init="actions['<?= $data['api']; ?>'] = false">
            		<td><small><code>admin/<?= $data['api']; ?></code></small></td>
            		<td><?php if ($data['crud_create']): ?><i class="material-icons text-success">check</i><?php else: ?><i class="material-icons text-danger">clear</i><?php endif; ?></td>
            		<td><?php if ($data['crud_update']): ?><i class="material-icons text-success">check</i><?php else: ?><i class="material-icons text-danger">clear</i><?php endif; ?></td>
            		<td><?php if ($data['crud_delete']): ?><i class="material-icons text-success">check</i><?php else: ?><i class="material-icons text-danger">clear</i><?php endif; ?></td>
            		<td><button type="button" class="btn btn-sm py-0 btn-secondary float-right" ng-click="testUrl('<?= Url::base(true); ?>/admin/<?= $data['api']; ?>')"><i class="material-icons">play_circle_filled</i></button></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </collapse-container>
        <collapse-container title="Global <?= Module::t('aw_apioverview_endpoint'); ?>" class="mt-3">
            <table class="table table-hover mb-0 pb-0">
            	<?php foreach ($endpoints['generic'] as $data): ?>
                    <?php foreach ($data['actions'] as $name): ?>
                    <tr>
                        <td><small><code>admin/<?= $data['api']; ?>/<?= $name; ?></code></small></td>
                        <td><button type="button" class="btn btn-sm py-0 btn-secondary float-right" ng-click="testUrl('<?= Url::base(true); ?>/admin/<?= $data['api']; ?>/<?= $name; ?>')"><i class="material-icons">play_circle_filled</i></button></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </table>
        </collapse-container>
        <div class="card mt-3">
            <div class="card-header"><?= Module::t('aw_apioverview_accesstokentitle'); ?></div>
            <div class="card-body">
                <p class="card-text"><small><?= empty($model->auth_token) ? '' : '<kbd>' . $model->auth_token . '</kbd>'; ?></small></p>
                <p class="card-text"><?= Module::t('aw_apioverview_accesstokeninfo'); ?></p>
                <a ng-click="generateNewToken()" class="btn btn-danger"><?= Module::t('aw_apioverview_accesstokenbtnlabel'); ?></a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><?= Module::t('aw_apioverview_responsetestertitle'); ?></div>
            <div class="card-body">
                <p class="text-muted"><?= Module::t('aw_apioverview_responsetesterrequest'); ?>:</p>
                <form ng-submit="runRequest()">
                  <div class="form-row">
                    <div class="col">
                      <input type="text" class="form-control small text-small" ng-model="requestUrl" />
                    </div>
                    <div class="col flex-grow-0 flex-shrink-1">
                      <button type="submit" class="btn btn-success h-100"><i class="material-icons">play_circle_filled</i></button>
                    </div>
                  </div>
                </form>
            </div>
            <div class="card-body" ng-show="response">
          	    <p class="text-muted">
                    <span class="badge badge-success" ng-show="response.status == 200">{{ response.status }}</span>
                    <span class="badge badge-danger" ng-show="response.status != 200">{{ response.status }}</span>
                    <?= Module::t('aw_apioverview_responsetesterresponse'); ?>:
                </p>
                <pre class="mb-0"><small><code>{{ response.data | json }}</code></small></pre>
            </div>
        </div>
    </div>
</div>
