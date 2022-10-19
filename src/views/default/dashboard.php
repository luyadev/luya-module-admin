<div class="luya-content">
    <div class="card-columns">
    <?php foreach ($items as $dashboard): /* @var $dashboard \luya\admin\base\DashboardObjectInterface */ ?>
    	<?= $dashboard->getTemplate(); ?>
<?php endforeach; ?>
    </div>
</div>
