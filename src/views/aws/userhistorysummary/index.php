<?php

use luya\helpers\Html;
use yii\helpers\VarDumper;
use luya\admin\Module;

/**
 * UserHistorySummaryActiveWindow Index View.
 *
 * @var $this \luya\admin\ngrest\base\ActiveWindowView
 * @var $model \luya\admin\models\User
 */
?>
<script>
zaa.bootstrap.register('UserHistorySummaryController', ['$scope', function($scope) {

	$scope.pie = false;
	
    $scope.loadPieCharts = function() {
        $scope.$parent.sendActiveWindowCallback('pie').then(function(response) {
            $scope.pie = response.data;
        });
    };    
    
    $scope.loadPieCharts();
}]);
</script>
<div class="row" ng-controller="UserHistorySummaryController">
	<div class="col-lg-3">
		<div class="card mb-4">
			<div class="card-header">
				<?= Module::t('aw_userhistorysummary_userdata'); ?>
		 	 </div>
			  	<div class="table-responsive-wrapper p-2">
					<table class="table table-sm pb-0 mb-0">
						<tr>
							<td><?= Module::t('mode_user_title'); ?></td>
							<td><?= $model->getTitleNamed(); ?></td>
						</tr>
						<tr>
							<td><?= Module::t('model_user_name'); ?></td>
							<td><?= $model->firstname; ?> <?= $model->lastname; ?></td>
						</tr>
						<tr>
							<td><?= Module::t('mode_user_email'); ?></td>
							<td><a href="mailto:<?= $model->email; ?>"><?= $model->email; ?></a></td>
						</tr>
						<tr>
							<td><?= Module::t('model_user_is_deleted'); ?></td>
							<td><?= Yii::$app->formatter->asBoolean($model->is_deleted); ?></td>
						</tr>
						<tr>
							<td><?= Module::t('model_user_groups'); ?></td>
							<td><?= implode(", ", $groups); ?></td>
						</tr>
						<tr>
							<td><?= Module::t('model_user_api_last_activity'); ?></td>
							<td><?= strftime("%x %X", $model->api_last_activity); ?></td>
						</tr>
					</table>
				</div>
		</div>
		<div class="card mb-4">
			<div class="card-header">
				<?= Module::t('aw_userhistorysummary_contribcount'); ?>
		 	 </div>
		  	<div class="card-body" ng-if="pie">
			  	<echarts id="userEchart" data="pie"></echarts>
		 	 </div>
		</div>
		<div class="card">
			<div class="card-header">
				<?= Module::t('aw_userhistorysummary_customsettings'); ?>
			</div>
			<div class="table-responsive-wrapper p-2">
				<table class="table table-sm pb-0 mb-0">
					<thead>
						<tr>
							<th><?= Module::t('aw_userhistorysummary_customsettings_key'); ?></th>
							<th><?= Module::t('aw_userhistorysummary_customsettings_value'); ?></th>
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
	<div class="col-lg-3">
		<div class="card">
		 	<div class="card-header">
		    	<?= Module::t('aw_userhistorysummary_lastsessions'); ?>
		  	</div>
		  	<ul class="list-group list-group-flush">
		  	<?php foreach ($userLogins as $login): ?>
			    <li class="list-group-item d-flex justify-content-between align-items-center">
			    <span>
			    <?php if ($login->is_destroyed): ?>
			    <small class="badge badge-danger"><?= Module::t('aw_userhistorysummary_lastsessions_destroyed'); ?></small>
			    <?php else: ?>
			    <small class="badge badge-success"><?= Module::t('aw_userhistorysummary_lastsessions_active'); ?></small>
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
		    <?= Module::t('aw_userhistorysummary_ngrestlogs'); ?>
		  	</div>
		  	<ul class="list-group list-group-flush">
			  	<?php foreach ($ngrestLogs as $log): ?>
			    <li class="list-group-item d-flex justify-content-between align-items-center">
			    <span>
			    	<?php if ($log->is_insert): ?>
			    		<i class="material-icons" alt="Added">note_add</i> 
			    	<?php elseif ($log->is_update): ?>
			    		<i class="material-icons" alt="Updated">create</i>
		    		<?php elseif ($log->is_delete): ?>
			    		<i class="material-icons" alt="Deleted">delete</i>
			    	<?php endif; ?>
			    	<span class="badge badge-secondary"><?= $log->table_name; ?></span>
			    	<span class="badge badge-info">ID #<?= $log->pk_value; ?></span>
			    	<?= Yii::$app->formatter->asRelativeTime($log->timestamp_create); ?>
			    </span>
			    <?php if (!$log->is_delete): ?>
			    <span class="badge badge-primary badge-pill" ng-click="hiddenElement<?= $log->id; ?>=!hiddenElement<?= $log->id; ?>"><?= Module::t('aw_userhistorysummary_ngrestlogs_detailbtn'); ?></span>
			    <?php endif; ?>
			    </li>
			    <li class="list-group-item p-2" style="background-color:#f1f1f1" ng-show="hiddenElement<?= $log->id; ?>">
					<div class="table-responsive-wrapper">
			    	<table class="table table-bordered">
			    		<thead>
			    			<tr>
			    				<th class="w-25"><?= Module::t('aw_userhistorysummary_ngrestlogs_detailattribute'); ?></th>
			    				<th class="w-25"><?= Module::t('aw_userhistorysummary_ngrestlogs_detailold'); ?></th>
			    				<th class="w-50"><?= Module::t('aw_userhistorysummary_ngrestlogs_detailnew'); ?></th>
			    			</tr>
			    		</thead>
			    	<?php if ($log->is_insert): ?>
			    		<?php foreach ($log->getAttributesJsonArray() as $key => $value): if (empty($value)) {
    continue;
} ?>
			    		<tr>
			    			<td><?= $key; ?></td>
			    			<td>-</td>
			    			<td><small><?= Html::encode($value); /* format data with formatter based on variable type */ ?></small></td>
			    		</tr>
			    		<?php endforeach; ?>
				    <?php elseif ($log->is_update): ?>
				    	<?php foreach ($log->getAttributesJsonArray() as $key => $value): $oldValue = $log->getAttributeFromJsonDiffArray($key); if (empty($value) && empty($oldValue)) {
    continue;
}?>
			    		<tr>
			    			<td><?= $key; ?></td>
			    			<td><small><?= Html::encode($oldValue); ?></small></td>
			    			<td><small><?= Html::encode($value); ?></small></td>
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