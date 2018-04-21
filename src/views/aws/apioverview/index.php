<?php

use luya\helpers\Url;
use luya\admin\Module;

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
    <div class="col-md-2">
        <div class="card">
            <div class="card-header"><?= Module::t('aw_apioverview_accesstokentitle'); ?></div>
            <div class="card-body">
                <p class="card-text"><small><?= empty($model->auth_token) ? '' : '<kbd>' . $model->auth_token . '</kbd>'; ?></small></p>
                <p class="card-text"><?= Module::t('aw_apioverview_accesstokeninfo'); ?></p>
                <a ng-click="generateNewToken()" class="btn btn-danger"><?= Module::t('aw_apioverview_accesstokenbtnlabel'); ?></a>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card">
            <div class="card-header"><?= Module::t('aw_apioverview_endpointstitle'); ?></div>

            <?php if (empty($groupsCount)): ?>
            <div class="alert alert-danger m-3"><?= Module::t('aw_apioverview_no_perm_groups'); ?></div>
            <?php elseif (empty($model->auth_token)): ?>
            <div class="alert alert-danger m-3"><?= Module::t('aw_apioverview_no_access_token'); ?></div>
            <?php else: ?>
            <table class="table table-hover">
            	<thead>
            		<tr>
            			<th><?= Module::t('aw_apioverview_endpoint'); ?></th>
            			<th><i class="material-icons" tooltip tooltip-text="<?= Module::t('aw_apioverview_permadd'); ?>" tooltip-position="bottom">note_add</i></th>
            			<th><i class="material-icons" tooltip tooltip-text="<?= Module::t('aw_apioverview_permedit'); ?>" tooltip-position="bottom">create</i></th>
            			<th><i class="material-icons" tooltip tooltip-text="<?= Module::t('aw_apioverview_permdelete'); ?>" tooltip-position="bottom">delete</i></th>
            			<th></th>
            		</tr>
            	</thead>
            	<?php foreach ($endpoints as $data): ?>
            	<tr ng-init="actions['<?= $data['api']; ?>'] = false">
            		<td><small><code>admin/<?= $data['api']; ?></code></small></td>
                    <?php if ($data['permission']): ?>
            		<td><?php if ($data['crud_create']): ?><i class="material-icons text-success">check</i><?php else: ?><i class="material-icons text-danger">clear</i><?php endif; ?></td>
            		<td><?php if ($data['crud_update']): ?><i class="material-icons text-success">check</i><?php else: ?><i class="material-icons text-danger">clear</i><?php endif; ?></td>
            		<td><?php if ($data['crud_delete']): ?><i class="material-icons text-success">check</i><?php else: ?><i class="material-icons text-danger">clear</i><?php endif; ?></td>
            		<td><button type="button" class="btn btn-sm py-0 btn-secondary float-right" ng-click="testUrl('<?= Url::base(true); ?>/admin/<?= $data['api']; ?>')"><i class="material-icons">play_circle_filled</i></button></td>
                    <?php else: ?>
                    <td colspan="3">&nbsp;</td>
                    <td><button type="button" class="btn btn-sm py-0 btn-secondary float-right" ng-click="actions['<?= $data['api']; ?>'] = !actions['<?= $data['api']; ?>']">
                        <i class="material-icons" ng-show="!actions['<?= $data['api']; ?>']">keyboard_arrow_left</i>
                        <i class="material-icons" ng-show="actions['<?= $data['api']; ?>']">keyboard_arrow_down</i>
                    </button></td>
                    <?php endif; ?>
            	</tr>
                <?php foreach ($data['actions'] as $name): ?>
                <tr ng-show="actions['<?= $data['api']; ?>']" class="bg-light">
                    <td><small><code>admin/<?= $data['api']; ?>/<?= $name; ?></code></small></td>
                    <td colspan="3"></td>
                    <td><button type="button" class="btn btn-sm py-0 btn-secondary float-right" ng-click="testUrl('<?= Url::base(true); ?>/admin/<?= $data['api']; ?>/<?= $name; ?>')"><i class="material-icons">play_circle_filled</i></button></td>
                </tr>
                <?php endforeach; ?>
            	<?php endforeach; ?>
            </table>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card">
            <div class="card-header"><?= Module::t('aw_apioverview_responsetestertitle'); ?></div>
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted"><?= Module::t('aw_apioverview_responsetesterrequest'); ?></h6>
                <form ng-submit="runRequest()">
                  <div class="form-row">
                    <div class="col-10">
                      <input type="text" class="form-control" ng-model="requestUrl" />
                    </div>
                    <div class="col">
                      <button type="submit" class="btn btn-primary float-right" ng-click="runRequest()"><i class="material-icons">play_circle_filled</i></button>
                    </div>
                  </div>
                </form>
            </div>
            <div class="card-body" ng-show="response">
          	    <h6 class="card-subtitle mb-2 text-muted"><?= Module::t('aw_apioverview_responsetesterresponse'); ?> 
                    <span class="badge badge-success" ng-show="response.status == 200">{{ response.status }}</span>
                    <span class="badge badge-danger" ng-show="response.status != 200">{{ response.status }}</span>
                </h6>
                <pre class="mb-0"><small><code>{{ response.data | json }}</code></small></pre>
            </div>
        </div>
    </div>
</div>