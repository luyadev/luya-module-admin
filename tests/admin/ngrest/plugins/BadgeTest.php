<?php

namespace admintests\admin\ngrest\plugins;

use admintests\AdminTestCase;
use luya\admin\ngrest\plugins\Badge;

class BadgeTest extends AdminTestCase
{
    public function testHtmlNewLine()
    {
        $raw = new Badge(['name' => 'badge', 'alias' => 'raw', 'i18n' => false, 'variations' => [1 => 'warning']]);
        $this->assertSame('<div><span ng-switch="model"><span ng-switch-when="1" class="badge  badge-warning" ng-bind="model"></span><span ng-switch-default class="badge  badge-secondary" ng-bind="model"></span></span></div>', $raw->renderList('id', 'model'));


        $raw = new Badge(['name' => 'badge', 'alias' => 'raw', 'i18n' => false, 'variations' => [1 => 'warning'], 'pill' => true]);
        $this->assertSame('<div><span ng-switch="model"><span ng-switch-when="1" class="badge badge-pill badge-warning" ng-bind="model"></span><span ng-switch-default class="badge badge-pill badge-secondary" ng-bind="model"></span></span></div>', $raw->renderList('id', 'model'));
    }

    public function testCreate()
    {
        $raw = new Badge(['name' => 'badge', 'alias' => 'raw', 'i18n' => false, 'variations' => [1 => 'warning']]);

        $this->expectException('luya\Exception');
        $raw->renderCreate('id', 'model');
    }

    public function testupdate()
    {
        $raw = new Badge(['name' => 'badge', 'alias' => 'raw', 'i18n' => false, 'variations' => [1 => 'warning']]);

        $this->expectException('luya\Exception');
        $raw->renderUpdate('id', 'model');
    }
}
