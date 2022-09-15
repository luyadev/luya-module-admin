<?php

namespace luya\admin\tests\admin\aws;

use luya\admin\apis\ApiUserController;
use luya\admin\aws\ApiOverviewActiveWindow;
use luya\admin\controllers\ApiUserController as LuyaApiUserController;
use luya\admin\models\ApiUser;
use luya\admin\tests\data\controllers\TestController;
use luya\testsuite\cases\NgRestTestCase;

class ApiOverviewActiveWindowTest extends NgRestTestCase
{
    public $modelClass = ApiUser::class;
    public $apiClass = ApiUserController::class;
    public $controllerClass = LuyaApiUserController::class;

    public function getConfigArray()
    {
        return [
            'id' => 'id',
            'basePath' => dirname(__FILE__),
        ];
    }

    protected function getWindow()
    {
        return new ApiOverviewActiveWindow([
            'ngRestModelClass' => $this->modelClass,
            'itemId' => 1,
        ]);
    }

    public function testRender()
    {
        $this->assertNotEmpty($this->getWindow()->index());
    }

    public function testCallbackReplaceToken()
    {
        $this->assertSame(1, $this->getWindow()->callbackReplaceToken());
    }

    public function testApiEndpointList()
    {
        $list = $this->app->auth->getPermissionApiEndpointsTable();
        $this->assertNotEmpty($list);

        $this->assertTrue($this->app->auth->isInApiEndpointPermissionTable('api-admin-apiuser'));
        $this->assertFalse($this->app->auth->isInApiEndpointPermissionTable('api-admin-user'));
    }

    public function testExtraActions()
    {
        $this->app->getModule('admin')->controllerMap = [
            'foobar' => [
                'class' => TestController::class,
                'module' => $this->app->getModule('admin'),
            ]
        ];

        $w = $this->getWindow();

        $r = $this->invokeMethod($w, 'getAvailableApiEndpoints');

        $this->assertNotEmpty($w->index());

        $this->assertNotEmpty($r['generic']);
    }
}
