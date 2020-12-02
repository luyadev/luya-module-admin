<?php

namespace luya\admin\tests\admin\base;

use luya\admin\models\ApiUser;
use luya\admin\ngrest\base\actions\IndexAction;
use luya\admin\tests\data\apis\StubApiUserApiController;
use luya\admin\tests\data\controllers\StubApiUserControllerController;
use luya\admin\tests\data\controllers\TestController;
use luya\admin\tests\NgRestTestCase;

class RestControllerTest extends NgRestTestCase
{
    public $modelClass = ApiUser::class;

    public $controllerClass = StubApiUserControllerController::class;

    public $apiClass = StubApiUserApiController::class;

    public function testPermissionRouteTest()
    {
        $ctrl = new TestController('controllerid', $this->app);

        $action = new IndexAction('actionid', $ctrl, ['modelClass' => 'foobar']);

        $this->assertSame('adminmodeltest/controllerid/actionid', $ctrl->permissionRoute($action));
    }

    public function testAccessNoPermissionBasedRoute()
    {
        $ctrl = new TestController('controllerid', $this->app);

        $this->app->getModule('admin')->apiUserAllowActionsWithoutPermissions = true;
        $this->assertTrue($this->runControllerAction($ctrl, 'bar-foo'));
    }

    public function testNoPermissionProtection()
    {
        $this->app->db->createCommand()->insert('admin_auth', [
            'alias_name' => 'testpermission',
            'route' => 'adminmodeltest/controllerid/bar-foo'
        ])->execute();

        $ctrl = new TestController('controllerid', $this->app);
        $this->app->getModule('admin')->apiUserAllowActionsWithoutPermissions = true;

        $this->expectException('yii\web\ForbiddenHttpException');
        $this->assertTrue($this->runControllerAction($ctrl, 'bar-foo'));
    }

    public function testOptionalAuthController()
    {
        $ctrl = new TestController('controllerid', $this->app);

        $this->assertTrue($this->runControllerAction($ctrl, 'no-auth', [], false));
    }
}
