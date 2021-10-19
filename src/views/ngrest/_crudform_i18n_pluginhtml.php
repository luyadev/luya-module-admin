<?php
use luya\helpers\Inflector;

?>
<div class="form-i18n" ng-class="{'has-field-help': getFieldHelp('<?= $element['name']; ?>')}">
    <?= $helpButtonHtml; ?>
    <label class="form-i18n-label <?php if ($isRequired): ?>font-weight-bold<?php endif; ?>">
        <?php if ($this->context->getIcon($element)): ?>
        <i class="material-icons"><?= $this->context->getIcon($element); ?></i>
        <?php endif; ?>
        <span><?= $element['alias']; ?></span>
    </label>
    <div class="row">
    <?php foreach ($languages as $lang): $ngModel = $this->context->i18nNgModelString($configContext, $element['name'], $lang['short_code']); ?>
        <div class="col" ng-show="AdminLangService.isInSelection('<?=$lang['short_code']; ?>')">
            <?= $this->context->renderElementPlugins($configContext, $element['type'], Inflector::slug($ngModel . " " . $lang['short_code']), $element['name'], $ngModel, $element['alias'], true); ?>
            <span class="flag flag-<?= $lang['short_code']; ?> form-col-flag">
                <span class="flag-fallback"><?= $lang['short_code']; ?></span>
            </span>
        </div>
    <?php endforeach;?>
    </div>
</div>