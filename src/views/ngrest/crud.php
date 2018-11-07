<?php
use luya\admin\ngrest\render\RenderCrud;
use luya\admin\Module;
use luya\admin\helpers\Angular;

/** @var $config \luya\admin\ngrest\ConfigInterface */
/** @var $this \luya\admin\ngrest\render\RenderCrudView */
/** @var $isInline boolean Whether current window mode is inline or not. $isInline means you are opening a ngrest crud inside a modal. */
/** @var $relationCall boolean Whether the current request is a relation call, this means you can switch between tabs. */
/** @var $modelSelection string|boolean Whether a model can be selected from isInline call, if yes it contains the value from the previous selected model in order to highlight this id. If false the selection is disabled. */
$this->beginPage();
$this->beginBody();
?>
<?php $this->registerAngularControllerScript(); ?>
<div ng-controller="<?= $config->hash; ?>" class="crud">
    <!-- This fake ui-view is used to render the detail item, which actuals uses the parent scope in the ui router controller. -->
    <div style="display: none;" ui-view></div>
    <?php if (!$relationCall): ?>
        <?php if (!$isInline): ?>
            <div class="crud-header">
                <h1 class="crud-title"><?= $currentMenu['alias']; ?></h1>
                <modal is-modal-hidden="isExportModalHidden" modal-title="<?= Module::t('crud_exportdata_btn'); ?>">
                    <div ng-if="!isExportModalHidden">
                        <?= Angular::radio('exportdata.type', Module::t('crud_exportdata_col_format'), ['xlsx' => Module::t('crud_exportdata_col_format_xlsx'), 'csv' => Module::t('crud_exportdata_col_format_csv')]); ?>
                        <?php // Angular::radio('exportdata.header', Module::t('crud_exportdata_col_header'), [1 => Module::t('button_yes'), 0 => Module::t('button_no')]);?>
                        <?= Angular::checkboxArray('exportdata.attributes', Module::t('crud_exportdata_col_columns'), $downloadAttributes, ['preselect' => true]); ?>
                        <button ng-hide="exportResponse" type="button" class="btn btn-icon btn-secondary" ng-click="generateExport()"><?= Module::t('crud_exportdata_btn_generateexport')?></button>
                        <button ng-show="exportResponse" type="button" class="btn btn-icon btn-download" ng-click="downloadExport()"><?= Module::t('crud_exportdata_btn_downloadexport'); ?></button>
                    </div>
                </modal>
                <div class="crud-toolbar">
                    <div class="btn-group" ng-class="{'show': isSettingsVisible}">
                        <button class="btn btn-toolbar" type="button" ng-click="toggleSettingsMenu()">
                            <i class="material-icons">more_vert</i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right" ng-class="{'show': isSettingsVisible}">
                            <a class="dropdown-item" ng-click="toggleExportModal()">
                                <i class="material-icons">get_app</i><span><?= Module::t('crud_exportdata_btn'); ?></span>
                            </a>
                            <?php foreach ($this->context->getSettingButtonDefinitions() as $button): ?>
                                <?= $button; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <ul class="nav nav-tabs nav-tabs-mobile-icons">
            <li class="nav-item">
                <a class="nav-link" ng-class="{'active':crudSwitchType==0}" ng-click="switchTo(0, true)">
                    <i class="material-icons">list</i>
                    <span><?= Module::t('ngrest_crud_btn_list'); ?></span>
                </a>
            </li>
            <?php if ($canCreate && $config->getPointer('create')): ?>
            <li class="nav-item">
                <a class="nav-link" ng-class="{'active':crudSwitchType==1}" ng-click="switchTo(1)">
                    <i class="material-icons">add_box</i>
                    <span><?= Module::t('ngrest_crud_btn_add'); ?></span>
                </a>
            </li>
            <?php endif; ?>
            <li class="nav-item" ng-show="crudSwitchType==2">
                <a class="nav-link" ng-class="{'active' : crudSwitchType==2}" ng-click="switchTo(0, true)">
                    <i class="material-icons">cancel</i>
                    <span><?= Module::t('ngrest_crud_btn_close'); ?></span>
                </a>
            </li>
            <?php if (!$isInline): ?>
            <li class="nav-item" ng-repeat="(index,btn) in tabService.tabs">
                <a class="nav-link" ng-class="{'active' : btn.active}">
                    <i class="material-icons" ng-click="closeTab(btn, index)">cancel</i>
                    <span ng-click="switchToTab(btn)">{{btn.name}} <small class="badge badge-secondary">#{{btn.id}}</small></span>
                </a>
            </li>
            <?php endif; ?>
            <li class="nav-item nav-item-alternative" ng-repeat="lang in AdminLangService.data" ng-class="{'ml-auto' : $first}" ng-show="AdminLangService.data.length > 1">
                <a class="nav-link" ng-click="AdminLangService.toggleSelection(lang)" ng-class="{'active' : AdminLangService.isInSelection(lang.short_code)}" role="tab">
                    <span class="flag flag-{{lang.short_code}}">
                        <span class="flag-fallback">{{lang.name}}</span>
                    </span>
                </a>
            </li>
        </ul>
    <?php endif; ?>
    <div class="tab-content">
        <?php if (!$relationCall && !$isInline): ?>
        <div class="tab-pane" ng-repeat="btn in tabService.tabs" ng-class="{'active' : btn.active}" ng-if="btn.active">
            <crud-relation-loader api="{{btn.api}}" array-index="{{btn.arrayIndex}}" model-class="{{btn.modelClass}}" id="{{btn.id}}"></crud-relation-loader>
        </div>
        <?php endif; ?>
        <div class="tab-pane" ng-if="crudSwitchType==0" ng-class="{'active' : crudSwitchType==0}">
            <div class="tab-padded">
                <div class="row mt-2">
                    <div class="col-md-4 col-lg-6 col-xl-6 col-xxxl-8">
                        <div class="input-group mb-2 mr-sm-2 mb-sm-0">
                            <div class="input-group-addon">
                                <i class="material-icons">search</i>
                            </div>
                            <input class="form-control" ng-model="config.searchQuery" type="text" placeholder="<?= Module::t('ngrest_crud_search_text'); ?>">
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-3 col-xl-3 col-xxxl-2">
                        <select class="form-control" ng-change="changeGroupByField()" ng-model="config.groupByField">
                            <option value="0"><?= Module::t('ngrest_crud_group_prompt'); ?></option>
                            <?php foreach ($config->getPointer('list') as $item): ?>
                                <option value="<?= $item['name']; ?>"><?= $item['alias']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if (!empty($config->getFilters())): ?>
                    <div class="col-md-4 col-lg-3 col-xl-3 col-xxxl-2">
                        <select class="form-control" ng-model="config.filter" ng-change="changeNgRestFilter()">
                            <option value="0"><?= Module::t('ngrest_crud_filter_prompt'); ?></option>
                            <?php foreach (array_keys($config->getFilters()) as $name): ?>
                                <option value="<?= $name; ?>"><?= $name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($relationCall && $canCreate && $config->getPointer('create')): ?>
            <button type="button" class="btn btn-add ml-3 mt-3" ng-click="switchTo(1)">
                    <i class="material-icons">add_box</i>
                    <span><?= Module::t('ngrest_crud_btn_add'); ?></span>
            </button>
            <?php endif; ?>
            <small class="crud-counter"><?= Module::t('ngrest_crud_total_count'); ?></small>
            <div class="table-responsive-wrapper">
                <table class="table table-hover table-align-middle table-striped mt-0">
                    <thead class="thead-default">
                        <tr>
                            <?php foreach ($config->getPointer('list') as $item): ?>
                            <th class="tab-padding-left">
                                <div class="table-sorter-wrapper" ng-class="{'is-active' : isOrderBy('+<?= $item['name']; ?>') || isOrderBy('-<?= $item['name']; ?>') }">
                                    <?php if ($config->getDefaultOrderField()): ?>
                                        <div class="table-sorter table-sorter-up" ng-click="changeOrder('<?= $item['name']; ?>', '-')" ng-class="{'is-sorting': !isOrderBy('-<?= $item['name']; ?>')}">
                                            <span><?= $item['alias']; ?></span>
                                            <i class="material-icons">keyboard_arrow_up</i>
                                        </div>
                                        <div class="table-sorter table-sorter-down" ng-click="changeOrder('<?= $item['name']; ?>', '+')" ng-class="{'is-sorting': !isOrderBy('+<?= $item['name']; ?>')}">
                                            <span><?= $item['alias']; ?></span>
                                            <i class="material-icons">keyboard_arrow_down</i>
                                        </div>
                                    <?php else: ?>
                                        <span><?= $item['alias']; ?></span>
                                    <?php endif; ?>
                                </div>
                            </th>
                            <?php endforeach; ?>
                            <th class="crud-buttons-column"></th>
                        </tr>
                    </thead>
                    <tbody ng-repeat="(key, items) in data.listArray | groupBy: config.groupByField" ng-init="viewToggler[key]=config.groupByExpanded">
                        <tr ng-if="config.groupBy" class="table-group" ng-click="viewToggler[key]=!viewToggler[key]">
                            <td colspan="<?= count($config->getPointer('list')) + 1 ?>">
                                <strong>{{key}}</strong>
                                <i class="material-icons right" ng-show="!viewToggler[key]">keyboard_arrow_right</i>
                                <i class="material-icons right" ng-show="viewToggler[key]">keyboard_arrow_down</i>
                            </td>
                        </tr>
                        <tr ng-repeat="(k, item) in items track by k" ng-show="viewToggler[key]" <?php if ($isInline && !$relationCall && $modelSelection): ?>ng-class="{'crud-selected-row': getRowPrimaryValue(item) == <?= $modelSelection?>}"class="crud-selectable-row"<?php endif; ?>>
                            <?php $i = 0; foreach ($config->getPointer('list') as $item): $i++; ?>
                                <?php foreach ($this->context->createElements($item, RenderCrud::TYPE_LIST) as $element): ?>
                                     <td <?php if ($isInline && !$relationCall && $modelSelection !== false): ?>ng-click="parentSelectInline(item)" <?php endif; ?>class="<?= $i != 1 ?: 'tab-padding-left'; ?>"><?= $element['html']; ?></td>
                                 <?php endforeach; ?>
                             <?php endforeach; ?>
                            <td class="crud-buttons-column" ng-hide="isLocked(config.tableName, item[config.pk])">
                                <?php if (count($this->context->getButtons()) > 0): ?>
                                    <div class="crud-buttons">
                                        <i class="crud-buttons-toggler material-icons">more_vert</i>
                                        <div class="crud-buttons-pan">
                                            <?php foreach ($this->context->getButtons() as $item): ?>
                                                <button type="button" class="crud-buttons-button" ng-click="<?= $item['ngClick']; ?>">
                                                    <i class="crud-buttons-button-icon material-icons"><?= $item['icon']; ?></i>
                                                    <?php if (!empty($item["label"])): ?>
                                                        <span class="btn-crud-label"><?= $item["label"] ?></span>
                                                    <?php endif; ?>
                                                    <div class="crud-buttons-button-loader">
                                                        <div class="loader">
                                                            <svg class="loader-svg" viewBox="25 25 50 50">
                                                                <circle class="loader-circle" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"/>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                </button>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-right" ng-show="isLocked(config.tableName, item[config.pk])">
                                <small><i class="material-icons btn-symbol">lock_outline</i><?= Module::t('locked_info'); ?></small>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div ng-show="data.list.length == 0" class="alert"><?= Module::t('ngrest_crud_empty_row'); ?></div>

            <div class="crud-pagination-wrapper">
                <div class="crud-pagination">
                    <pagination current-page="pager.currentPage" page-count="pager.pageCount"></pagination>
                </div>
            </div>
        </div>
        <?php if ($canCreate && $config->getPointer('create')): ?>
        	<?= $this->render('_crudform', ['type' => '1', 'renderer' => RenderCrud::TYPE_CREATE, 'isInline' => $isInline, 'relationCall' => $relationCall]); ?>
        <?php endif; ?>
        <?php if ($canUpdate && $config->getPointer('update')): ?>
        	<?= $this->render('_crudform', ['type' => '2', 'renderer' => RenderCrud::TYPE_UPDATE, 'isInline' => $isInline, 'relationCall' => $relationCall]); ?>
        <?php endif; ?>
        <?= $this->render('_awform'); ?>
    </div>
</div>
<?php $this->endBody(); ?>
<?php $this->endPage(); ?>
