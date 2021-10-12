<?php
use luya\admin\Module;

?>
<div ng-controller="ActiveWindowGroupAuth">
            
    <button type="button" ng-click="toggleAll()" class="btn btn-secondary btn-icon"><i class="material-icons">done_all</i> <span><?= Module::t('aws_groupauth_select_all'); ?></span></button>
    <button type="button" ng-click="untoggleAll()" class="btn btn-secondary btn-icon"><i class="material-icons">clear_all</i> <span><?= Module::t('aws_groupauth_deselect_all'); ?></span></button>
    
    <form id="updateSubscription" ng-submit="save(rights)">
        <div class="card mt-3" ng-repeat="(name, items) in auths" ng-class="{'card-closed': !groupVisibility}" ng-init="groupVisibility=1">
            <div class="card-header text-uppercase" ng-click="groupVisibility=!groupVisibility">
                <span class="material-icons card-toggle-indicator">keyboard_arrow_down</span>
                {{ name }}
            </div>
            <div class="table-responsive" ng-show="groupVisibility">
                <table class="table p-0">
                    <thead>
                        <tr>
                            <th class="w-25">
                                <span class="btn btn-icon btn-link" ng-click="toggleModule(items)"><i class="material-icons">done_all</i></span>
                                <span class="btn btn-icon btn-link" ng-click="clearModule(items)"><i class="material-icons">clear_all</i></span>
                            </th>
                            <th class="w-25"><i class="material-icons" tooltip tooltip-text="<?= Module::t('aws_groupauth_th_add'); ?>" tooltip-position="bottom">add_box</i></th>
                            <th class="w-25"><i class="material-icons" tooltip tooltip-text="<?= Module::t('aws_groupauth_th_edit'); ?>" tooltip-position="bottom">create</i></th>
                            <th class="w-25"><i class="material-icons" tooltip tooltip-text="<?= Module::t('aws_groupauth_th_remove'); ?>" tooltip-position="bottom">delete</i></th>
                        </tr>
                    </thead>
                    <tr ng-repeat="a in items">
                        <td>
                            <input id="{{a.id}}_base" type="checkbox" ng-model="rights[a.id].base" ng-true-value="1" ng-false-value="0" ng-click="toggleGroup(a.id)" />
                            <label for="{{a.id}}_base">{{ a.alias_name}}</label>
                        </td>
                        <td ng-show="a.is_crud==1">
                            <input id="{{a.id}}_create" type="checkbox" ng-model="rights[a.id].create" ng-true-value="1" ng-false-value="0" />
                            <label for="{{a.id}}_create"></label>
                        </td>
                        <td ng-show="a.is_crud==1">
                            <input id="{{a.id}}_update" type="checkbox" ng-model="rights[a.id].update" ng-true-value="1" ng-false-value="0" />
                            <label for="{{a.id}}_update"></label>
                        </td>
                        <td ng-show="a.is_crud==1">
                            <input id="{{a.id}}_delete" type="checkbox" ng-model="rights[a.id].delete" ng-true-value="1" ng-false-value="0" />
                            <label for="{{a.id}}_delete"></label>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="mt-3">
            <button type="submit" class="btn btn-save btn-icon"><?= Module::t('button_save'); ?></button>
        </div>
    </form>
</div>