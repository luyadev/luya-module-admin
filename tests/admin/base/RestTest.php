<?php
declare(strict_types=1);

namespace luya\admin\tests\admin\base;

error_reporting(E_ALL);

use luya\admin\tests\NgRestTestCase;
use luya\admin\models\ApiUser;
use luya\admin\controllers\ApiUserController;
use Lcobucci\JWT\Token;
use luya\admin\components\Auth;
use luya\admin\tests\data\apis\StubApiUserApiController;
use luya\admin\tests\data\controllers\StubApiUserControllerController;
use luya\admin\tests\data\models\JwtModel;
use yii\helpers\ArrayHelper;

class RestTest extends NgRestTestCase
{
    public $modelClass = ApiUser::class;

    public $controllerClass = StubApiUserControllerController::class;

    public $apiClass = StubApiUserApiController::class;

    public function getConfigArray()
    {
        $config = parent::getConfigArray();

        return ArrayHelper::merge($config, [
            'components' => [
                'jwt' => [
                    'class' => 'luya\admin\components\Jwt',
                    'key' => 'xyz',
                    'apiUserEmail' => 'foo@bar.com',
                    'identityClass' => [
                        'class' => 'luya\admin\tests\data\models\JwtModel',
                    ],
                ]
            ]
        ]);

        return $config;
    }

    public function testControllerCustomActionRoute()
    {
        // as the route of the controller is not in permission system, this is visible to not api users:
        $this->assertSame('adminmodeltest/stubapiuser/foo-bar', $this->runControllerAction($this->controller, 'foo-bar'));
        
        $this->controllerCanAccess('foo-bar', false);
        $this->runControllerAction($this->controller, 'foo-bar');
    }

    public function testGetActionPermissions()
    {
        $r = $this->api->getActionPermissions();

        $this->assertSame($r['foo-bar'], Auth::CAN_DELETE);

        $this->apiCanDelete(true);

        $this->assertSame('test!', $this->runControllerAction($this->api, 'foo-bar'));
    }

    public function testInvalidType()
    {
        $this->expectException('yii\base\InvalidConfigException');
        $this->api->isActionAllowed('invalid');
    }

    public function testActionPermissionNoAccess()
    {
        $this->expectException('yii\web\ForbiddenHttpException');
        $this->runControllerAction($this->api, 'foo-bar');
    }

    public function testRequestWithJwt()
    {
        $this->app->jwt->apiUserEmail = $this->userFixture->getModel('user1')->email;
        $this->app->getModule('admin')->registerComponents();

        $this->apiCanList(true);
        $this->controllerCanAccess('index', true);

        $token = $this->userFixture->getModel('user1')->auth_token;
        $this->app->request->setQueryParams(['access-token' => $token]);

        $r = $this->runControllerAction($this->api, 'list');
        $this->assertTrue(is_array($r));

        $token = (new Token(['alg' => 'none'], [], null, [false, false]));
        $this->assertNull($this->app->jwt->authenticateUser($token, 'athMethod'));
        $token = (new Token(['alg' => 'none'], [], null, [true, true]));

        $user = $this->app->jwt->authenticateUser($token, 'athMethod');
        $this->assertSame('John', $user->firstname);

        $newUser = new JwtModel();
        $this->assertNotEmpty($this->app->jwt->generateToken($newUser));
    }

    public function testExceptionModel()
    {
        $token = (new Token(['alg' => 'none'], [], null, [false, false]));
        $this->app->jwt->key = 'xyz';
        $this->app->jwt->apiUserEmail = $this->userFixture->getModel('user1')->email;
        $this->app->jwt->identityClass = 'luya\admin\models\User';

        $this->expectException('yii\base\InvalidConfigException');
        $this->app->jwt->authenticateUser($token, 'athMethod');
    }

    public function testMisconfiguredJwtUser()
    {
        $token = (new Token(['alg' => 'none'], [], null, [1, 1]));
        
        $this->app->jwt->key = 'xyz';
        $this->app->jwt->apiUserEmail = 'notfound@luya.io';

        $this->expectException('yii\base\InvalidConfigException');
        $this->app->jwt->authenticateUser($token, 'athMethod');
    }
}