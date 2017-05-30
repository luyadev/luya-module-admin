<?php
use luya\admin\Module as Admin;
use luya\helpers\Url;
use yii\helpers\Markdown;

$user = Yii::$app->adminuser->getIdentity();
$this->beginPage()
?><!DOCTYPE html>
<html ng-app="zaa" ng-controller="LayoutMenuController">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title><?= Yii::$app->siteTitle; ?> &rsaquo; {{currentItem.alias}}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <base href="<?= Url::base(true); ?>/admin" />
    <style type="text/css">
        [ng\:cloak], [ng-cloak], [data-ng-cloak], [x-ng-cloak], .ng-cloak, .x-ng-cloak {
  			display: none !important;
		}
		
		.dragover {
		    border: 5px dashed #2196F3;
		}
    </style>
    <?php $this->head(); ?>
</head>
<body ng-cloak flow-prevent-drop>
<?php $this->beginBody(); ?>
<?= $this->render('_angulardirectives'); ?>
<div class="luya">
    <div class="luya__mainnav">
            
        <div class="mainnav mainnav--small">
        
            <div class="mainnav__static">
        
                <ul class="mainnav__list">
        
                    <li class="mainnav__entry">
                        <a class="mainnav__link" href="#">
                            <i class="mainnav__icon material-icons">search</i>
                            <span class="mainnav__label">
                                Search
                            </span>
                        </a>
                    </li>
        
                    <li class="mainnav__entry">
                        <a class="mainnav__link" ng-href="#">
                            <i class="mainnav__icon material-icons">home</i>
                            <span class="mainnav__label">
                                Dashboard
                            </span>
                        </a>
                    </li>
        
                </ul>
        
            </div>
        
            <div class="mainnav__modules">
        
                <ul class="mainnav__list">
        
                    <li class="mainnav__entry" ng-repeat="item in items">
                        <a class="mainnav__link"  ng-class="{'mainnav__link--active' : isActive(item) }" ng-click="click(item)">
                            <i class="mainnav__icon material-icons">{{item.icon}}</i>
                            <span class="mainnav__label">
                                {{item.alias}}
                            </span>
                        </a>
                    </li>
        
                </ul>
        
            </div>
        
            <div class="mainnav__static mainnav__static--bottom">
        
                <ul class="mainnav__list">
        
                    <li class="mainnav__entry">
                        <a class="mainnav__link" href="#">
                            <i class="mainnav__icon material-icons">refresh</i>
                            <span class="mainnav__label">
                                Clear cache
                            </span>
                        </a>
                    </li>
        
                    <li class="mainnav__entry">
                        <a class="mainnav__link" href="#">
                            <i class="mainnav__icon material-icons">face</i>
                            <span class="mainnav__label">
                                Account
                            </span>
                        </a>
                    </li>
        
                    <li class="mainnav__entry">
                        <a class="mainnav__link" href="http://luya.io" target="_blank">
                            <span class="mainnav__icon">
                                <img class="mainnav__image-icon" src="images/luya-logo-small.png" />
                            </span>
                            <span class="mainnav__label">
                                LUYA
                            </span>
                        </a>
                    </li>
        
                </ul>
        
            </div>
        
        </div>
            
    </div>
    <div class="luya__main" ui-view>
    </div>
</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>