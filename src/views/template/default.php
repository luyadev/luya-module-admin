<?php
use luya\admin\Module;

?>
<div class="luya-main luya-main-crud" ng-controller="DefaultController">
    <div class="luya-subnav">
        <div class="modulenav-mobile">
            <div class="modulenav-mobile-title" ng-show="currentItem"><i class="material-icons">{{currentItem.icon}}</i> {{ currentItem.alias }}</div>
            <div class="modulenav-mobile-title" ng-show="!currentItem"><i class="material-icons">dashboard</i> <?= Module::t('menu_dashboard'); ?></div>
            <label for="modulenav-toggler" class="modulenav-toggler-icon" ng-click="isOpenModulenav = !isOpenModulenav">
                <i class="material-icons" ng-show="!isOpenModulenav">menu</i>
                <i class="material-icons" ng-show="isOpenModulenav">close</i>
            </label>
        </div>
        <div class="modulenav" ng-class="{'modulenav-mobile-hidden': !isOpenModulenav}">
            <div class="modulenav-group">
                <ul class="modulenav-list">
                    <li class="modulenav-item">
                        <span class="modulenav-link" ng-class="{'modulenav-link-active' :currentItem == null }" ng-click="loadDashboard(); isOpenModulenav=false;">
                            <i class="modulenav-icon material-icons">dashboard</i>
                            <span class="modulenav-label">
                                <?= Module::t('menu_dashboard'); ?>
                            </span>
                        </span>
                    </li>
                </ul>
            </div>
            <div class="modulenav-group" ng-repeat="item in items" class="submenu-group">
                <span class="modulenav-group-title">{{item.name}}</span>
                <ul class="modulenav-list">
                    <li class="modulenav-item" ng-repeat="sub in item.items" ng-if="!sub.hiddenInMenu">
                        <span class="modulenav-link" ng-click="click(sub)"  ng-class="{'modulenav-link-active' : sub.route == currentItem.route }">
                            <i class="modulenav-icon material-icons">{{sub.icon}}</i>
                            <span class="modulenav-label">
                                {{sub.alias}}
                            </span>
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="luya-content" ui-view>

    </div>
</div>