<?php

use luya\admin\models\StorageFile;
use luya\admin\Module;
use luya\helpers\Html;
use yii\helpers\VarDumper;

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
			  	<div class="table-responsive">
					<table class="table table-sm table-borderless table-striped small">
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
							<td><?= Yii::$app->formatter->asDatetime($model->api_last_activity, 'short'); ?></td>
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
			<div class="table-responsive">
				<table class="table table-sm table-borderless table-striped small">
					<?php foreach ($model->setting->data as $key => $value): ?>
					<tr>
						<td><?= $key; ?></td>
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
				<table class="table table-sm table-striped table-borderless table-hoverable small mb-0">
				<?php foreach ($userLogins as $login): ?>
					<tr>
						<td>
						<?php if ($login->is_destroyed): ?>
							<small class="badge badge-danger"><?= Module::t('aw_userhistorysummary_lastsessions_destroyed'); ?></small>
						<?php else: ?>
							<small class="badge badge-success"><?= Module::t('aw_userhistorysummary_lastsessions_active'); ?></small>
						<?php endif; ?>
						</td>
			   	 	<td><?= Yii::$app->formatter->asRelativeTime($login->timestamp_create); ?></td>
					<td class="text-right">
						<?= $login->ip; ?>
					</td>
			    <?php ?>
					</tr>
		    <?php endforeach; ?>
				</table>
		</div>
	</div>
	<div class="col-lg-6">
		<div class="card">
		 	<div class="card-header">
		    <?= Module::t('aw_userhistorysummary_ngrestlogs'); ?>
		  	</div>
		  	<ul class="list-group list-group-flush">
			  	<?php foreach ($ngrestLogs as $log): ?>
			    <li class="list-group-item d-flex justify-content-between align-items-center p-2">
			    <span>
			    	<?php if ($log->is_insert): ?>
			    		<i class="material-icons" alt="Added">add_box</i> 
			    	<?php elseif ($log->is_update): ?>
			    		<i class="material-icons" alt="Updated">create</i>
		    		<?php elseif ($log->is_delete): ?>
			    		<i class="material-icons" alt="Deleted">delete</i>
			    	<?php endif; ?>
			    	<span class="badge badge-secondary"><?= StorageFile::cleanBaseTableName($log->table_name); ?></span>
			    	<span class="badge badge-info">ID #<?= $log->pk_value; ?></span>
			    	<?= Yii::$app->formatter->asRelativeTime($log->timestamp_create); ?>
			    </span>
			    <?php if (!$log->is_delete): ?>
			    <span class="badge badge-primary badge-pill" ng-click="hiddenElement<?= $log->id; ?>=!hiddenElement<?= $log->id; ?>"><?= Module::t('aw_userhistorysummary_ngrestlogs_detailbtn'); ?></span>
			    <?php endif; ?>
			    </li>
			    <li class="list-group-item p-0" ng-show="hiddenElement<?= $log->id; ?>">
					<div class="table-responsive">
			    	<table class="table table-sm table-striped table-borderless table-hoverable small">
			    		<thead>
			    			<tr>
			    				<th class="w-25"><?= Module::t('aw_userhistorysummary_ngrestlogs_detailattribute'); ?></th>
			    				<th class="w-25"><?= Module::t('aw_userhistorysummary_ngrestlogs_detailold'); ?></th>
			    				<th class="w-50"><?= Module::t('aw_userhistorysummary_ngrestlogs_detailnew'); ?></th>
			    			</tr>
			    		</thead>
			    	<?php if ($log->is_insert): ?>
			    		<?php foreach ($log->attributes_json as $key => $value): if (empty($value)): continue; endif; ?>
			    		<tr>
			    			<td><?= $key; ?></td>
			    			<td>-</td>
			    			<td><?= is_scalar($value) ? Html::encode($value) : VarDumper::dumpAsString($value); /* format data with formatter based on variable type */ ?></td>
			    		</tr>
<?php endforeach; ?>
				    <?php elseif ($log->is_update): ?>
				    	<?php $changes = false;
				        foreach ($log->attributes_json as $key => $value): $oldValue = $log->attributesAttributeDiff($key);
				            if (empty($oldValue)): continue; endif;
				            $changes = true; ?>
			    		<tr>
			    			<td><?= $key; ?></td>
			    			<td><?= is_scalar($oldValue) ? Html::encode($oldValue) : VarDumper::dumpAsString($oldValue); ?></td>
			    			<td><?= is_scalar($value) ? Html::encode($value) : VarDumper::dumpAsString($value); ?></td>
			    		</tr>
						<?php endforeach; ?>
						<?php if (!$changes): ?>
							<tr>
								<td colspan="3">No changes</td>
							</tr>
						<?php endif; ?>
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
