<?php

namespace luya\admin\tests\admin\apis;

use admintests\AdminModelTestCase;
use InvalidArgumentException;
use luya\admin\apis\StorageController;
use luya\admin\models\StorageFile;
use luya\testsuite\fixtures\NgRestModelFixture;
use luya\testsuite\scopes\PermissionScope;
use luya\testsuite\traits\AdminDatabaseTableTrait;

class StorageControllerTest extends AdminModelTestCase
{
    use AdminDatabaseTableTrait;

    public function testFlushApiCache()
    {
        $this->createAdminLangFixture();
        $ctrl = new StorageController('id', $this->app);

        $this->assertEmpty($this->invokeMethod($ctrl, 'flushApiCache'));
    }

    public function testPermissionBasedAction()
    {
        $this->createAdminLangFixture();
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
        $this->createAdminLangFixture();
        $ctrl = new StorageController('id', $this->app);

        PermissionScope::run($this->app, function (PermissionScope $scope) use ($ctrl) {
            $fixture = new NgRestModelFixture([
                'modelClass' => StorageFile::class,
            ]);
            $data = $scope->runControllerAction($ctrl, 'data-files');
            $this->assertSame([], $data);
            $fixture->cleanup();
        }, function (PermissionScope $config) {
            $config->userFixtureData = [
                'is_api_user' => 0,
            ];
        });
    }

    public function testFileCrop()
    {
        PermissionScope::run($this->app, function (PermissionScope $scope) {
            new NgRestModelFixture([
                'modelClass' => StorageFile::class,
            ]);

            $scope->createAndAllowRoute(StorageController::PERMISSION_ROUTE);

            $ctrl = new StorageController('id', $this->app);

            $this->expectException(InvalidArgumentException::class);
            $data = $scope->runControllerAction($ctrl, 'file-crop');
        });
    }

    public function testFileReplace()
    {
        PermissionScope::run($this->app, function (PermissionScope $scope) {
            new NgRestModelFixture([
                'modelClass' => StorageFile::class,
            ]);

            $scope->createAndAllowRoute(StorageController::PERMISSION_ROUTE);

            $ctrl = new StorageController('id', $this->app);

            $data = $scope->runControllerAction($ctrl, 'file-replace');

            $this->assertFalse($data);
        });
    }
}
