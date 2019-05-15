<?php
use yii\widgets\DetailView;

?>
<div class="table-responsive">
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => $attributes,
        'options' => ['class' => 'table table-bordered table-striped'],
    ]); ?>
</div>