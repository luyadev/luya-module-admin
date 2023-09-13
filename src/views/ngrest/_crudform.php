<?php
use luya\admin\Module;

/* @var integer $renderer */
/* @var integer $type 1 = create, 2 = update*/
/* @var boolean $isInline */
/* @var boolean $relationCall */
?>
<?php if ($relationCall): ?>
<div class="ml-3 mr-3">
<?php endif; ?>
<div class="tab-pane tab-padded" role="tabpanel" ng-if="crudSwitchType==<?= $type; ?>" ng-class="{'active' : crudSwitchType==<?= $type; ?>}" <?php if (!$isInline): ?>zaa-esc="closeUpdate()"<?php endif; ?>>
    <form name="formCreate" class="js-form-side-by-side" ng-submit="<?php if ($type == 2):?>submitUpdate(true)<?php else: ?>submitCreate(true)<?php endif; ?>">
        <?php foreach ($this->context->forEachGroups($renderer) as $key => $group): ?>
            <?php if (!$group['is_default'] && !empty($group['fields'])): ?>
                <div class="card crud-card" ng-init="groupToggler[<?= $key; ?>] = <?= (int) !$group['collapsed']; ?>" ng-class="{'card-closed': !groupToggler[<?= $key; ?>]}">
                    <div class="card-header" ng-click="groupToggler[<?= $key; ?>] = !groupToggler[<?= $key; ?>]">
                        <span class="material-icons card-toggle-indicator">keyboard_arrow_down</span>
                        <?= $group['name']; ?>
                    </div>
                    <div class="card-body">
            <?php endif; ?>

                <?php foreach ($group['fields'] as $field => $fieldItem): ?>
                    <div ng-if="!checkIfFieldExistsInPopulateCondition('<?= $field; ?>')">
                        <?= $this->context->generatePluginHtml($fieldItem, $renderer); ?>
                    </div>
                    <div ng-if="checkIfFieldExistsInPopulateCondition('<?= $field; ?>')" ng-init="<?= $this->context->ngModelString($renderer, $field); ?>=checkIfFieldExistsInPopulateCondition('<?= $field; ?>')"></div>
                <?php endforeach; ?>

            <?php if (!$group['is_default'] && !empty($group['fields'])): ?>
                    </div> <!-- /.card-body -->
                </div> <!-- /.card -->
            <?php endif; ?>
        <?php endforeach; ?>
        <button type="submit" class="btn btn-save btn-icon"><?= Module::t($type == 2 ? 'button_save_and_close' : 'button_create_and_close'); ?></button>

        <?php if ($type == 1): ?>
            <button type="button" class="btn btn-save btn-icon" ng-click="submitCreate(false)"><?= Module::t('button_create_and_new'); ?></button>
            <button type="button" class="btn btn-save btn-icon" ng-click="submitCreate(false,true)"><?= Module::t('button_create'); ?></button>
            <button type="button" class="btn btn-link float-right" ng-click="closeCreate()"><?= Module::t('button_cancel'); ?></button>
        <?php else: ?>
            <button type="button" class="btn btn-save btn-icon" ng-click="submitUpdate(false)"><?= Module::t('button_save'); ?></button>
            <button type="button" class="btn btn-link float-right" ng-click="closeUpdate()"><?= Module::t('button_cancel'); ?></button>
        <?php endif;?>
    </form>
</div>
<?php if ($relationCall): ?>
</div>
<?php endif; ?>
