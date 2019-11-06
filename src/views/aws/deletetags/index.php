<?php
/** @var array $relations */

use luya\admin\Module;
use luya\admin\ngrest\aw\ActiveWindowFormWidget;

?>
<?php if (count($relations) > 0): ?>
<p class="lead"><?= Module::t('aws_delete_relations_info', ['count' => count($relations), 'name' => $tagName]); ?></p>
<table class="table table-striped mt-5 mb-5">
<thead>
    <tr>
        <th><?= Module::t('aws_delete_relations_table_name'); ?></th>
        <th><?= Module::t('aws_delete_relations_table_count'); ?></th>
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
    'buttonValue' => Module::t('ngrest_button_delete'),
    'options' => [
        'closeOnSuccess' => true,
        'reloadListOnSuccess' => true,
    ]
]); ?>
<?= $form->field('name', Module::t('model_tag_name'))->textInput()->hint(Module::t('aws_delete_relations_form_hint')); ?>
<?php $form::end(); ?>