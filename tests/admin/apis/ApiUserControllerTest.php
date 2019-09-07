<?php

namespace luya\admin\tests\admin\apis;

use admintests\AdminModelTestCase;
use luya\admin\tests\data\apis\StubApiUserApiController;
use luya\testsuite\scopes\PermissionScope;

class ApiUserControllerTest extends AdminModelTestCase
{
    public function testDeleteNoPermission()
    {
        $api = new StubApiUserApiController('apiuser', $this->app);
        PermissionScope::run($this->app, function(PermissionScope $scope) use ($api) {
            $this->expectException('yii\web\ForbiddenHttpException');
            $scope->runControllerAction($api, 'delete', ['id' => 1], 'DELETE');
        });
    }

    public function testNoAuth()
    {
        $api = new StubApiUserApiController('apiuser', $this->app);
        PermissionScope::run($this->app, function(PermissionScope $scope) use ($api) {
            $scope->setQueryAuthToken(false);
            $this->expectException('yii\web\UnauthorizedHttpException');
            $api->runAction('delete');
        });
    }

    public function testAddApiAuthRouteButDoNotGrant()
    {
        $api = new StubApiUserApiController('apiuser', $this->app);
        PermissionScope::run($this->app, function(PermissionScope $scope) use ($api) {
            $scope->createApi('apiuser');
            $this->expectException('yii\web\ForbiddenHttpException');
            $scope->runControllerAction($api, 'delete', ['id' => 1], 'DELETE');
        });
    }

    public function testAddApiButDoNotAllowDeleteAction()
    {
        $api = new StubApiUserApiController('apiuser', $this->app);
        PermissionScope::run($this->app, function(PermissionScope $scope) use ($api) {
            $scope->createApi('apiuser');
            $scope->allowApi('apiuser');

            // this will actually delete the login user.
            $r = $scope->runControllerAction($api, 'delete', ['id' => 1], 'DELETE');

            $this->assertNull($r);

            $this->assertSame(204, $this->app->response->statusCode);
        });
    }
}