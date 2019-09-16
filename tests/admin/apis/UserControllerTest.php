<?php

namespace luya\admin\tests\admin\apis;

use Yii;
use admintests\AdminModelTestCase;
use luya\admin\apis\UserController;
use luya\admin\components\Auth;
use luya\admin\Module;
use luya\testsuite\scopes\PermissionScope;

class UserControllerTest extends AdminModelTestCase
{
    public function testActionSessionUpdate()
    {
        PermissionScope::run($this->app, function(PermissionScope $scope) {
            
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
        }, function(PermissionScope $config) {
            $config->userFixtureData = [
                'title' => 1,
                'email' => 'before@test.com',
            ];
        });
    }
}