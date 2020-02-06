<?php

namespace luya\admin\tests\admin\apis;

use Yii;
use admintests\AdminModelTestCase;
use luya\admin\apis\UserController;
use luya\admin\components\Auth;
use luya\admin\models\UserDevice;
use luya\admin\Module;
use luya\testsuite\fixtures\NgRestModelFixture;
use luya\testsuite\scopes\PermissionScope;
use yii\web\NotFoundHttpException;

class UserControllerTest extends AdminModelTestCase
{
    public function testActionSessionUpdate()
    {
        PermissionScope::run($this->app, function (PermissionScope $scope) {
            $scope->createAndAllowApi('user');

            $module = new Module('admin', $this->app, [
                'emailVerification' => 1,
            ]);

            $user = new UserController('user', $module);
            $user->addActionPermission(Auth::CAN_VIEW, 'session-update');

            Yii::$app->request->bodyParams = [
                'email' => 'test@test.com',
            ];

            $update = $scope->runControllerAction($user, 'session-update');

            $this->assertSame([
                [
                    'field' => 'email',
                    'message' => 'account_changeemail_tokensenterror',
                ]
            ], $update);
        }, function (PermissionScope $config) {
            $config->userFixtureData = [
                'title' => 1,
                'email' => 'before@test.com',
            ];
        });
    }

    public function testSessionData()
    {
        PermissionScope::run($this->app, function (PermissionScope $scope) {
            new NgRestModelFixture(['modelClass' => UserDevice::class]);
            $scope->createAndAllowApi('user');
            $user = new UserController('user', $this->app->getModule('admin'));
            $user->addActionPermission(Auth::CAN_VIEW, 'session');
            $data = $scope->runControllerAction($user, 'session');

            $this->assertArrayHasKey('packages', $data);
            $this->assertArrayHasKey('user', $data);
            $this->assertArrayHasKey('activities', $data);
            $this->assertArrayHasKey('settings', $data);
            $this->assertArrayHasKey('vendor_install_timestamp', $data);
            $this->assertArrayHasKey('devices', $data);
            $this->assertArrayHasKey('twoFa', $data);
        });
    }

    public function testDeviceManager()
    {
        PermissionScope::run($this->app, function (PermissionScope $scope) {
            new NgRestModelFixture(['modelClass' => UserDevice::class]);
            $scope->createAndAllowApi('user');
            $user = new UserController('user', $this->app->getModule('admin'));
            $user->addActionPermission(Auth::CAN_VIEW, 'remove-device');
            $this->expectException(NotFoundHttpException::class);
            $data = $scope->runControllerAction($user, 'remove-device');
        });
    }

    public function testTwoFa()
    {
        PermissionScope::run($this->app, function (PermissionScope $scope) {
            new NgRestModelFixture(['modelClass' => UserDevice::class]);
            $scope->createAndAllowApi('user');
            $user = new UserController('user', $this->app->getModule('admin'));
            $user->addActionPermission(Auth::CAN_VIEW, 'disable-twofa');
            $data = $scope->runControllerAction($user, 'disable-twofa');
            $this->assertsame([], $data);
        });

        PermissionScope::run($this->app, function (PermissionScope $scope) {
            new NgRestModelFixture(['modelClass' => UserDevice::class]);
            $scope->createAndAllowApi('user');
            $user = new UserController('user', $this->app->getModule('admin'));
            $user->addActionPermission(Auth::CAN_VIEW, 'register-twofa');
            $this->app->request->setBodyParams(['verification' => '123123', 'secret' => '27UZSNVXEA5W7FQC']);
            $data = $scope->runControllerAction($user, 'register-twofa');
            $this->assertsame([
                [
                    'field' => 'verificaton',
                    'message' => 'user_register_2fa_verification_error'
                ]
            ], $data);
        });
    }
}
