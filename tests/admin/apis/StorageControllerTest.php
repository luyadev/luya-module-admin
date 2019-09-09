<?php

namespace luya\admin\tests\admin\apis;

use admintests\AdminModelTestCase;
use luya\admin\apis\StorageController;
use luya\testsuite\scopes\PermissionScope;

class StorageControllerTest extends AdminModelTestCase
{
    public function testFlushApiCache()
    {
        $ctrl = new StorageController('id', $this->app);

        $this->assertEmpty($this->invokeMethod($ctrl, 'flushApiCache'));
    }

    public function testPermissionBasedAction()
    {
        $ctrl = new StorageController('id', $this->app);

        PermissionScope::run($this->app, function (PermissionScope $scope) use ($ctrl) {
            $scope->createAndAllowRoute(StorageController::PERMISSION_ROUTE);
            $this->assertNotEmpty($scope->runControllerAction($ctrl, 'images-upload'));
        });

        PermissionScope::run($this->app, function (PermissionScope $scope) use ($ctrl) {
            $scope->createRoute(StorageController::PERMISSION_ROUTE);
            $this->expectException('yii\web\ForbiddenHttpException');
            $scope->runControllerAction($ctrl, 'images-upload');
        });
    }
}
