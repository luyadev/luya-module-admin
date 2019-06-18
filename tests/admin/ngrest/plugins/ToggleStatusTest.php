<?php

namespace admintests\admin\ngrest\plugins;

use admintests\AdminTestCase;
use luya\admin\ngrest\plugins\ToggleStatus;
use luya\admin\ngrest\render\RenderCrud;
use luya\admin\tests\NgRestTestCase;
use luya\admin\models\User;
use luya\admin\apis\UserController;
use luya\admin\controllers\UserController as LuyaUserController;

class ToggleStatusTest extends NgRestTestCase
{
    public $modelClass = User::class;
    public $apiClass = UserController::class;
    public $controllerClass = LuyaUserController::class; 

    public function testDefaultOption()
    {
        $context = new RenderCrud();
        $plugin = new ToggleStatus([
            'renderContext' => $context,
            'alias' => 'test',
            'name' => 'test',
            'i18n' => false,
            
        ]);
        $this->assertSame([
            0 => '<i class="material-icons" ng-if="model == 1">check</i>',
            1 => '<i class="material-icons" ng-if="model == 0">close</i>',
        ], $plugin->renderList(1, 'model'));
    }

    /*
    public function testSchedulingOption()
    {
        $context = new RenderCrud();
        $plugin = new ToggleStatus([
            'renderContext' => $context,
            'alias' => 'test',
            'name' => 'test',
            'i18n' => false,
            'scheduling' => true,
        ]);
        $this->assertSame([
            0 => '<i class="material-icons" ng-if="model == 1">check</i>',
            1 => '<i class="material-icons" ng-if="model == 0">close</i>',
        ], $plugin->renderList(1, 'model'));
    }
    */
}