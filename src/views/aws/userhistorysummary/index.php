<?php

use yii\helpers\VarDumper;
use luya\helpers\Json;

/**
 * UserHistorySummaryActiveWindow Index View.
 *
 * @var $this \luya\admin\ngrest\base\ActiveWindowView
 * @var $model \luya\admin\models\User
 */
?>
<script>
zaa.bootstrap.register('UserHistorySummaryController', function($scope, $rootScope, $controller) {

    $scope.loadList = function() {
        $scope.$parent.sendActiveWindowCallback('pie').then(function(response) {
            $scope.pie = response.data;
        });
    };

    $scope.pie = false;
    
    $scope.loadList();
});
</script>
<h1><?= $model->firstname; ?> <?= $model->lastname;?></h1>
<div class="row" ng-controller="UserHistorySummaryController">
	<div class="col-lg-3">
		<div class="card mb-4">
			<div class="card-header">
				Profile
		 	 </div>
			  	<div class="table-responsive-wrapper p-2">
					<table class="table table-sm">
						<tr>
							<td>Title</td>
							<td><?= $model->getTitleNamed(); ?></td>
						</tr>
						<tr>
							<td>Name</td>
							<td><?= $model->firstname; ?> <?= $model->lastname; ?></td>
						</tr>
						<tr>
							<td>E-Mail</td>
							<td><a href="mailto:<?= $model->email; ?>"><?= $model->email; ?></a></td>
						</tr>
					</table>
				</div>
		</div>
		<div class="card mb-4">
			<div class="card-header">
				Stats
		 	 </div>
		  	<div class="card-body" ng-if="pie">
			  	<echarts id="userEchart" legend="legend" item="item" data="pie"></echarts>
		 	 </div>
		</div>
		<div class="card">
			<div class="card-header">
				Customized Settings
			</div>
			<div class="card-body">
				<div class="table-responsive-wrapper">
					<table class="table table-sm table-bordered">
						<thead>
							<tr>
								<th>Key</th>
								<th>Value</th>
							</tr>
						</thead>
						<?php foreach ($model->setting->data as $key => $value): ?>
						<tr>
							<td><small><?= $key; ?></small></td>
							<td><?= VarDumper::dumpAsString($value, 100, false); ?></td>
						</tr>
						<?php endforeach; ?>
					</table>
				</div>
			</div>
		</div>
	</div>
	<div class="col-lg-3">
		<div class="card">
		  <div class="card-header">
		    Latest sessions
		  </div>
		  <ul class="list-group list-group-flush">
		  	<?php foreach ($userLogins as $login): ?>
		    <li class="list-group-item d-flex justify-content-between align-items-center">
		    <span>
		    <?php if ($login->is_destroyed): ?>
		    <small class="badge badge-danger">destroyed</small>
		    <?php else: ?>
		    <small class="badge badge-success">active</small>
		    <?php endif; ?>
		    <?= Yii::$app->formatter->asRelativeTime($login->timestamp_create); ?>
		    </span>
		    <small><?= $login->ip?></small>
		    <?php ?>
		    </li>
		    <?php endforeach; ?>
		  </ul>
		</div>
	</div>
	<div class="col-lg-6">
		<div class="card">
		  <div class="card-header">
		    NgRest Logger
		  </div>
		  <ul class="list-group list-group-flush">
		  	<?php foreach ($ngrestLogs as $log): ?>
		    <li class="list-group-item d-flex justify-content-between align-items-center">
		    <span>
		    	<?php if ($log->is_insert): ?>
		    		<i class="material-icons">note_add</i> 
		    	<?php elseif ($log->is_update): ?>
		    		<i class="material-icons">create</i>
		    	<?php else: ?>
		    		Unknown ID <?= $log->pk_value; ?> in <?= $log->table_name; ?>
		    	<?php endif; ?>
		    	<span class="badge badge-secondary"><?= $log->table_name; ?></span>
		    	<span class="badge badge-info">ID #<?= $log->pk_value; ?></span>
		    	<?= Yii::$app->formatter->asRelativeTime($log->timestamp_create); ?>
		    </span>
		    <span class="badge badge-primary badge-pill" ng-click="hiddenElement<?= $log->id; ?>=!hiddenElement<?= $log->id; ?>">Details</span>
		    </li>
		    <li class="list-group-item p-2" style="background-color:#f1f1f1" ng-show="hiddenElement<?= $log->id; ?>">
				<div class="table-responsive-wrapper">
		    	<table class="table table-bordered">
		    		<thead>
		    			<tr>
		    				<th class="w-25">Attribute</th>
		    				<th class="w-25">Original</th>
		    				<th class="w-50">New</th>
		    			</tr>
		    		</thead>
		    	<?php if ($log->is_insert): ?>
		    		<?php foreach ($log->getAttributesJsonArray() as $key => $value): if (empty($value)) { continue; } ?>
		    		<tr>
		    			<td><?= $key; ?></td>
		    			<td>-</td>
		    			<td><small><?= $value; ?></small></td>
		    		</tr>
		    		<?php endforeach; ?>
			    <?php elseif ($log->is_update): ?>
			    	<?php foreach ($log->getAttributesJsonArray() as $key => $value): $oldValue = $log->getAttributeFromJsonDiffArray($key); if (empty($value) && empty($oldValue)) { continue; }?>
		    		<tr>
		    			<td><?= $key; ?></td>
		    			<td><small><?= $oldValue; ?></small></td>
		    			<td><small><?= $value; ?></small></td>
		    		</tr>
		    		<?php endforeach; ?>
			    	<?php else: ?>
			    	<?php endif; ?>
		    	</table>
		    	</div>
		    </li>
		    <?php endforeach; ?>
		  </ul>
		</div>
	</div>
</div>