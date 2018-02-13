<?php

use yii\helpers\VarDumper;

/**
 * UserHistorySummaryActiveWindow Index View.
 *
 * @var $this \luya\admin\ngrest\base\ActiveWindowView
 * @var $model \luya\admin\models\User
 */
?>
<h1><?= $model->firstname; ?> <?= $model->lastname;?> <span class="badge badge-primary"><?= $model->email; ?></span></h1>
<div class="row">
	<div class="col-md-4">
		<div class="card">
		  <div class="card-header">
		    Latest Logins
		  </div>
		  <ul class="list-group list-group-flush">
		  	<?php foreach ($userLogins as $login): ?>
		    <li class="list-group-item d-flex justify-content-between align-items-center">
		    <span><?= strftime("%x, %X", $login->timestamp_create); ?>
		    <?php if ($login->is_destroyed): ?>
		    <small class="text-danger">destroyed</small>
		    <?php else: ?>
		    <small class="text-success">active</small>
		    <?php endif; ?>
		    </span>
		    <span class="badge badge-primary badge-pill"><?= $login->ip?></span>
		    <?php ?>
		    </li>
		    <?php endforeach; ?>
		  </ul>
		</div>
	</div>
	<div class="col-md-6">
		<div class="card">
		  <div class="card-header">
		    NgRest Logger
		  </div>
		  <ul class="list-group list-group-flush">
		  	<?php foreach ($ngrestLogs as $log): ?>
		    <li class="list-group-item d-flex justify-content-between align-items-center">
		    <span>
		    	<?= strftime("%x, %X", $log->timestamp_create); ?>
		    	<?php if ($log->is_insert): ?>
		    		Added ID <?= $log->pk_value; ?> to <?= $log->table_name; ?>
		    	<?php elseif ($log->is_update): ?>
		    		Updated ID <?= $log->pk_value; ?> in <?= $log->table_name; ?>
		    	<?php else: ?>
		    		Unknown ID <?= $log->pk_value; ?> in <?= $log->table_name; ?>
		    	<?php endif; ?>
		    </span>
		    <span class="badge badge-primary badge-pill" ng-click="hiddenElement<?= $log->id; ?>=!hiddenElement<?= $log->id; ?>">Details</span>
		    </li>
		    <li class="list-group-item" ng-show="hiddenElement<?= $log->id; ?>">
				<div class="table-responsive-wrapper">
		    	<table class="table table-bordered">
		    		<thead>
		    			<tr>
		    				<th class="w-25">Attribute</th>
		    				<th class="w-25">Original Value</th>
		    				<th class="w-50">New Value</th>
		    			</tr>
		    		</thead>
		    	<?php if ($log->is_insert): ?>
		    		<?php foreach ($log->getAttributesJsonArray() as $key => $value): ?>
		    		<tr>
		    			<td><?= $key; ?></td>
		    			<td>-</td>
		    			<td><?= $value; ?></td>
		    		</tr>
		    		<?php endforeach; ?>
			    <?php elseif ($log->is_update): ?>
			    	<?php foreach ($log->getAttributesJsonArray() as $key => $value): ?>
		    		<tr>
		    			<td><?= $key; ?></td>
		    			<td><?= $log->getAttributeFromJsonDiffArray($key); ?></td>
		    			<td><?= $value; ?></td>
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
	<div class="col-md-2">
		<div class="card">
		  <div class="card-header">
		    Stats
		  </div>
		  <div class="card-body">
		  	Logins:
		  	Updates:
		  	Added:
		  	Deleted:
		  </div>
		</div>
	</div>
</div>