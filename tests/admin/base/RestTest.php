<?php
declare(strict_types=1);

namespace luya\admin\tests\admin\base;

error_reporting(E_ALL);

use luya\admin\tests\NgRestTestCase;
use luya\admin\models\ApiUser;
use luya\admin\controllers\ApiUserController;
use luya\admin\apis\ApiUserController as LuyaApiUserController;
use Lcobucci\JWT\Token;
use luya\admin\tests\data\models\JwtModel;
use yii\helpers\ArrayHelper;

class RestTest extends NgRestTestCase
{
    public $modelClass = ApiUser::class;

    public $controllerClass = ApiUserController::class;

    public $apiClass = LuyaApiUserController::class;

    public function getConfigArray()
    {
        $config = parent::getConfigArray();

        return ArrayHelper::merge($config, [
            'modules' => [
                'admin' => [
                    'jwtSecret' => 'xyz',
                    'jwtAuthModel' => [
                        'class' => 'luya\admin\tests\data\models\JwtModel',
                    ],
                ]
            ]
        ]);

        return $config;
    }

    public function testRequestWithJwt()
    {
        $this->app->getModule('admin')->jwtApiUserEmail = $this->userFixture->getModel('user1')->email;
        $this->app->getModule('admin')->registerComponents();

        $this->apiCanList(true);
        $this->controllerCanAccess('index', true);

        $token = $this->userFixture->getModel('user1')->auth_token;
        $this->app->request->setQueryParams(['access-token' => $token]);

        $r = $this->runControllerAction($this->api, 'list');
        $this->assertTrue(is_array($r));

        $token = (new Token(['alg' => 'none'], [], null, [false, false]));
        $this->assertNull($this->api->authJwtUser($token, 'athMethod'));
        $token = (new Token(['alg' => 'none'], [], null, [true, true]));

        $user = $this->api->authJwtUser($token, 'athMethod');
        $this->assertSame('John', $user->firstname);

        $newUser = new JwtModel();
        $this->assertNotEmpty($this->app->jwt->generateToken($newUser));
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

    public function testMisconfiguredJwtUser()
    {
        $token = (new Token(['alg' => 'none'], [], null, [1, 1]));
        $this->app->getModule('admin')->jwtSecret = 'xyz';
        $this->app->getModule('admin')->jwtApiUserEmail = 'notfound@luya.io';

        $this->expectException('yii\base\InvalidConfigException');
        $r = $this->api->authJwtUser($token, 'athMethod');
    }
}