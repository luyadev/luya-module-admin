<?php

namespace luya\admin\tests\admin\apis;

use admintests\AdminModelTestCase;
use luya\admin\apis\StorageController;
use luya\admin\models\StorageFile;
use luya\testsuite\fixtures\NgRestModelFixture;
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

    public function testPermissionLessDataAction()
    {
        

        $ctrl = new StorageController('id', $this->app);

        PermissionScope::run($this->app, function(PermissionScope $scope) use ($ctrl) {
            $fixture = new NgRestModelFixture([
                'modelClass' => StorageFile::class,
            ]);
            $data = $scope->runControllerAction($ctrl, 'data-files');
            $this->assertSame([], $data);
            $fixture->cleanup();
        }, function(PermissionScope $config) {
            $config->userFixtureData = [
                'is_api_user' => 0,
            ];
        });
    }
}
