<?php
use luya\admin\ngrest\aw\CallbackButtonWidget;

/* @var $model \luya\admin\models\StorageFilter */
?>
<?php if ((is_countable($images) ? count($images) : 0) == 0): ?>
    <div class="alert alert-warning">No images has been generated for this filter yet.</div>
<?php else: ?>
<?= CallbackButtonWidget::widget(['label' => '<i class="material-icons">delete</i><span>Remove '.(is_countable($images) ? count($images) : 0).' Images</span>', 'callback' => 'remove', 'options' => ['class' => 'btn btn-delete']]); ?>
    <table class="table table-striped table-bordered mt-3">
        <thead>
            <tr>
                <th>Image</th>
                <th>Path</th>
                <th>Caption</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($images as $image): /* @var $image \luya\admin\image\Item */?>
                <tr>
                    <td><img style="max-width: 200px;" src="<?= $image->source; ?>" alt="" class="img-fluid"></td>
                    <td><a target="_blank" href="<?= $image->source; ?>"><?= $image->source; ?></a></td>
                    <td><?= $image->caption; ?></td>
                </tr>
<?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
