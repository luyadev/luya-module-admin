<?php

namespace admintests\admin\controllers;

use admintests\AdminModelTestCase;
use luya\admin\components\AdminMenu;
use luya\admin\components\Auth;
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
use luya\admin\controllers\StorageImageController;
use luya\admin\controllers\TagController;
use luya\admin\controllers\UserController;
use luya\admin\models\QueueLog;
use luya\admin\models\QueueLogError;
use luya\testsuite\fixtures\NgRestModelFixture;
use luya\testsuite\scopes\PermissionScope;
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
        StorageImageController::class,
    ];

    public function testControllerGenericMethodsForCoverage()
    {
        foreach ($this->controllers as $ctrl) {
            $ctrlObject = Yii::createObject(['class' => $ctrl], ['foo', $this->app]);
            $this->app->clear('adminmenu');
            $this->app->clear('auth');



            PermissionScope::run($this->app, function (PermissionScope $scope) use ($ctrl, $ctrlObject) {
                $class = $ctrlObject->modelClass;
                $ctrl = Yii::createObject(['class' => $ctrl], [$class::ngRestApiEndpoint(), $this->app]);
                $this->app->setComponents(['adminmenu' => ['class' => AdminMenu::class]]);
                $this->app->setComponents(['auth' => ['class' => Auth::class]]);

                if ($ctrl->modelClass == QueueLog::class) {
                    $scope->createAndAllowApi(QueueLogError::ngRestApiEndpoint());
                }
                $scope->createAndAllowApi($class::ngRestApiEndpoint());

                new NgRestModelFixture([
                    'modelClass' => $ctrl->modelClass,
                ]);

                $ctrl->setDescription('foo');
                $this->assertNotNull($ctrl->getDescription());

                $scope->runControllerAction($ctrl, 'index');
            });
        }
    }
}
