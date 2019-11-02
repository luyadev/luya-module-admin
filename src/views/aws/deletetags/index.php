<?php
/** @var array $relations */

use luya\admin\ngrest\aw\ActiveWindowFormWidget;

?>
<p class="lead">The tag has been used XYZ times. Also relations are made to the following tables:</p>
<table class="table table-bordered">
<?php foreach ($relations as $relation): ?>
<tr>
    <td><?= $relation['table_name']; ?></td>
    <td><?= $relation['count']; ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php $form = ActiveWindowFormWidget::begin([
    'callback' => 'remove',
    'buttonValue' => 'Delete',
    'options' => [
        'closeOnSuccess' => true,
        'reloadListOnSuccess' => true,
    ]
]); ?>
<?= $form->field('name', 'Enter the tag name to delete')->textInput()->hint('dfdf'); ?>
<?php $form::end(); ?>