<?php
use yii\widgets\DetailView;

?>
<?php if (!empty($intro)): ?>
<div class="mb-3"><?= $intro; ?></div>
<?php endif; ?>

<div class="table-responsive">
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => $attributes,
        'options' => ['class' => 'table table-bordered table-striped'],
    ]); ?>
</div>

<?php if (!empty($outro)): ?>
<div class="mt-3"><?= $outro; ?></div>
<?php endif; ?>