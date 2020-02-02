<?php

namespace luya\admin\tests\admin\apis;

use admintests\AdminModelTestCase;
use luya\admin\apis\UserController;
use luya\admin\tests\data\apis\StubApiUserApiController;
use luya\testsuite\scopes\PermissionScope;
use luya\testsuite\traits\AdminDatabaseTableTrait;

class ApiUserControllerTest extends AdminModelTestCase
{
    use AdminDatabaseTableTrait;

    public function testDeleteNoPermission()
    {
        $api = new StubApiUserApiController('apiuser', $this->app);
        PermissionScope::run($this->app, function (PermissionScope $scope) use ($api) {
            $this->expectException('yii\web\ForbiddenHttpException');
            $scope->runControllerAction($api, 'delete', ['id' => 1], 'DELETE');
        });
    }

    public function testNoAuth()
    {
        $api = new StubApiUserApiController('apiuser', $this->app);
        PermissionScope::run($this->app, function (PermissionScope $scope) use ($api) {
            $scope->setQueryAuthToken(false);
            $this->expectException('yii\web\UnauthorizedHttpException');
            $api->runAction('delete');
        });
    }

    public function testAddApiAuthRouteButDoNotGrant()
    {
        $api = new StubApiUserApiController('apiuser', $this->app);
        PermissionScope::run($this->app, function (PermissionScope $scope) use ($api) {
            $scope->createApi('apiuser');
            $this->expectException('yii\web\ForbiddenHttpException');
            $scope->runControllerAction($api, 'delete', ['id' => 1], 'DELETE');
        });
    }

    public function testAddApiButDoNotAllowDeleteAction()
    {
        $api = new StubApiUserApiController('apiuser', $this->app);
        PermissionScope::run($this->app, function (PermissionScope $scope) use ($api) {
            $scope->createApi('apiuser');
            $scope->allowApi('apiuser');

            // this will actually delete the login user.
            $r = $scope->runControllerAction($api, 'delete', ['id' => 1], 'DELETE');

            $this->assertNull($r);

            $this->assertSame(204, $this->app->response->statusCode);
        });
    }

    public function testHasOpenEmailValidation()
    {
        $ctrl = new UserController('user', $this->app->getModule('admin'));

        $user = $this->createAdminUserFixture([
            'no' => [
                'id' => 1,
                'firstname' => 'john',
                'lastname' => 'doe',
                'email' => 'john@luya.io',
                'email_verification_token' => '123',
                'email_verification_token_timestamp' => 123,
                'is_deleted' => 0,
                'is_api_user' => 0,
            ],
            'yes' => [
                'id' => 2,
                'firstname' => 'john',
                'lastname' => 'doe',
                'email' => 'john2@luya.io',
                'email_verification_token' => '123',
                'email_verification_token_timestamp' => time(),
                'is_deleted' => 0,
                'is_api_user' => 0,
            ]
        ]);

        $this->assertFalse($this->invokeMethod($ctrl, 'hasOpenEmailValidation', [$user->getModel('no')]));


        $this->assertTrue($this->invokeMethod($ctrl, 'hasOpenEmailValidation', [$user->getModel('yes')]));
    }
}
