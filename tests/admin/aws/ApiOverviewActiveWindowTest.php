<?php

namespace luya\admin\tests\admin\aws;

use luya\admin\aws\ApiOverviewActiveWindow;
use admintests\AdminModelTestCase;
use luya\testsuite\cases\NgRestTestCase;
use luya\admin\models\ApiUser;
use luya\admin\apis\ApiUserController;
use luya\admin\controllers\ApiUserController as LuyaApiUserController;

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
}