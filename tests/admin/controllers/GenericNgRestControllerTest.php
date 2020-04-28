<?php

namespace admintests\admin\controllers;

use admintests\AdminModelTestCase;
use luya\admin\controllers\ApiUserController;
use luya\admin\controllers\ConfigController;
use luya\admin\controllers\EffectController;
use luya\admin\controllers\FilterController;
use luya\admin\controllers\GroupController;
use luya\admin\controllers\LangController;
use luya\admin\controllers\LoggerController;
use luya\admin\controllers\NgrestLogController;
use luya\admin\controllers\ProxyBuildController;
use luya\admin\controllers\ProxyMachineController;
use luya\admin\controllers\QueueLogController;
use luya\admin\controllers\QueueLogErrorController;
use luya\admin\controllers\TagController;
use luya\admin\controllers\UserController;
use Yii;

class GenericNgRestControllerTest extends AdminModelTestCase
{
    public $controllers = [
        NgrestLogController::class,
        ApiUserController::class,
        EffectController::class,
        FilterController::class,
        GroupController::class,
        LangController::class,
        LoggerController::class,
        ProxyBuildController::class,
        ProxyMachineController::class,
        QueueLogController::class,
        QueueLogErrorController::class,
        TagController::class,
        UserController::class,
        ConfigController::class,
    ];

    public function testControllerGenericMethodsForCoverage()
    {
        $this->createAdminLangFixture();

        foreach ($this->controllers as $ctrl) {

            $ctrl = Yii::createObject(['class' => $ctrl], ['id', $this->app]);

            $ctrl->setDescription('foo');
            $this->assertNotNull($ctrl->getDescription()); 
        }
    }
}