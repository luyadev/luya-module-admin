<?php

namespace admintests\admin\controllers;

use admintests\AdminModelTestCase;
use luya\admin\apis\ApiUserController;
use luya\admin\apis\ConfigController;
use luya\admin\apis\EffectController;
use luya\admin\apis\FilterController;
use luya\admin\apis\GroupController;
use luya\admin\apis\LangController;
use luya\admin\apis\LoggerController;
use luya\admin\apis\NgrestLogController;
use luya\admin\apis\ProxyBuildController;
use luya\admin\apis\ProxyMachineController;
use luya\admin\apis\QueueLogController;
use luya\admin\apis\QueueLogErrorController;
use luya\admin\apis\StorageImageController;
use luya\admin\apis\TagController;
use luya\admin\apis\UserController;
use luya\testsuite\fixtures\NgRestModelFixture;
use Yii;
use yii\db\ActiveQuery;

class GenericApiControllerTest extends AdminModelTestCase
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
        StorageImageController::class,
    ];

    public function testControllerGenericMethodsForCoverage()
    {
        $this->createAdminLangFixture();
        $this->createAdminUserAuthNotificationTable();
        foreach ($this->controllers as $ctrl) {
            $ctrl = Yii::createObject(['class' => $ctrl], ['id', $this->app]);

            new NgRestModelFixture([
                'modelClass' => $ctrl->modelClass,
            ]);

            $this->assertInstanceOf(ActiveQuery::class, $ctrl->prepareListQuery());
            $this->assertInstanceOf(ActiveQuery::class, $ctrl->prepareIndexQuery());
            $this->assertTrue(is_array($ctrl->getWithRelation('index')));
            $this->assertTrue(is_array($ctrl->actions()));
            $this->assertNull($ctrl->getDataFilter());
            $this->assertNotNull($ctrl->getModel());
        }
    }
}
