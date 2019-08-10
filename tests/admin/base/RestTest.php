<?php

namespace luya\admin\tests\admin\base;

use luya\admin\tests\NgRestTestCase;
use luya\admin\models\ApiUser;
use luya\admin\controllers\ApiUserController;
use luya\admin\apis\ApiUserController as LuyaApiUserController;
use Lcobucci\JWT\Token;

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


        $token = (new Token(['alg' => 'none'], [], null, [false, false]));
        $this->assertFalse($this->api->authJwtUser($token, 'athMethod'));
        $token = (new Token(['alg' => 'none'], [], null, [true, true]));
        $this->assertSame('John', $this->api->authJwtUser($token, 'athMethod')->firstname);
    }

    public function testExceptionModel()
    {
        $token = (new Token(['alg' => 'none'], [], null, [false, false]));
        $this->app->getModule('admin')->jwtSecret = 'xyz';
        $this->app->getModule('admin')->jwtApiUserEmail = $this->userFixture->getModel('user1')->email;
        $this->app->getModule('admin')->jwtAuthModel = 'luya\admin\models\User';

        $this->expectException('yii\base\InvalidConfigException');
        $this->api->authJwtUser($token, 'athMethod');
    }
}