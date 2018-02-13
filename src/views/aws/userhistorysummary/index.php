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
		    	<b><?= $log->table_name?></b>
		    <table class="table table-bordered">
		    	<?php foreach ($log->getDiffArray() as $k => $v): ?>
		    	<tr>
			    	<td><?= $k; ?></td>
			    	<td><?= $v; ?></td>	
			    </tr>
		    	<?php endforeach ?>
		    </table>
		    </span>
		    <span class="badge badge-primary badge-pill"><?= strftime("%x, %X", $log->timestamp_create); ?></span>
		    <?php ?>
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