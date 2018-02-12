<div class="luya-content">
    <div class="row">
    <?php foreach ($items as $dashboard): /* @var $dashboard \luya\admin\base\DashboardObjectInterface */ ?>
    <div class="col-md-4 mb-2">
    	<?= $dashboard->getTemplate(); ?>
    </div>
    <?php endforeach; ?>
    </div>
</div>