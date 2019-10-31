<?php
/** @var array $relations */
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