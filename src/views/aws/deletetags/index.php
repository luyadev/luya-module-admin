<?php
/** @var array $relations */

use luya\admin\ngrest\aw\ActiveWindowFormWidget;

?>
<?php if (count($relations) > 0): ?>
<p class="lead">The tag has been used <?= count($relations); ?> times. Also relations are made to the following tables:</p>
<table class="table table-striped mt-5 mb-5">
<thead>
    <tr>
        <th>Relation Table Name</th>
        <th>Number of Entries</th>
    </tr>
</thead>
<?php foreach ($relations as $relation): ?>
<tr>
    <td><?= $relation['table_name']; ?></td>
    <td><?= $relation['count']; ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
<?php $form = ActiveWindowFormWidget::begin([
    'callback' => 'remove',
    'buttonValue' => 'Delete',
    'options' => [
        'closeOnSuccess' => true,
        'reloadListOnSuccess' => true,
    ]
]); ?>
<?= $form->field('name', 'Tag Name')->textInput()->hint('In order to confirm the tag deletion, enter the name of the tag and submit.'); ?>
<?php $form::end(); ?>