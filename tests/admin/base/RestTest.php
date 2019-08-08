<?php

namespace luya\admin\tests\admin\base;

use luya\admin\tests\NgRestTestCase;
use luya\admin\models\ApiUser;
use luya\admin\controllers\ApiUserController;
use luya\admin\apis\ApiUserController as LuyaApiUserController;

class RestTest extends NgRestTestCase
{
    public $modelClass = ApiUser::class;

    public $controllerClass = ApiUserController::class;

    public $apiClass = LuyaApiUserController::class;

    public function testRequestWithJwt()
    {
        $this->app->getModule('admin')->jwtSecret = 'xyz';
        $this->app->getModule('admin')->jwtApiUserEmail = $this->userFixture->getModel('user1')->email;
        $this->app->getModule('admin')->jwtAuthModel = 'luya\admin\tests\data\models\JwtModel';

        $this->apiCanList(true);
        $this->controllerCanAccess('index', true);

        $token = $this->userFixture->getModel('user1')->auth_token;
        $this->app->request->setQueryParams(['access-token' => $token]);

        $r = $this->runControllerAction($this->api, 'list');
        $this->assertTrue(is_array($r));

        $this->assertSame('John', $this->api->authJwtUser('token', 'athMethod')->firstname);
    }
}