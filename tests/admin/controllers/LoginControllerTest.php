<?php

namespace luya\admin\tests\admin\controllers;

use admintests\AdminModelTestCase;
use luya\admin\controllers\LoginController;
use luya\admin\models\UserLoginLockout;
use luya\testsuite\fixtures\NgRestModelFixture;
use luya\testsuite\traits\AdminDatabaseTableTrait;

class LoginControllerTest extends AdminModelTestCase
{
    use AdminDatabaseTableTrait;

    public function testSendArray()
    {
        $login = new LoginController('login', $this->app->getModule('admin'));

        $reponse = $this->invokeMethod($login, 'sendArray', [
            true, ['foo' => 'bar'], true
        ]);

        unset($reponse['time']);

        $this->assertSame([
            'refresh' => true,
            'message' => null,
            'errors' => [
                0 => [
                    'field' => 'foo',
                    'message' => 'bar',
                ],
            ],
            'enterSecureToken' => true,
            'enterTwoFaToken' => false,
        ], $reponse);
    }

    public function testLogin()
    {
        $this->createAdminUserOnlineFixture();
        $this->createAdminUserFixture();
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        new NgRestModelFixture(['modelClass' => UserLoginLockout::class]);

        $module = $this->app->getModule('admin');
        $module->resetPassword = true;
        $login = new LoginController('login', $module);
        $this->app->controller = $login;

        $r = $login->actionIndex();
        $this->assertNotNull($r);
        $r = $login->actionAsync();
        $this->assertNotNull($r);
        $r = $login->actionAsyncToken();
        $this->assertNotNull($r);
        $r = $login->actionTwofaToken();
        $this->assertNotNull($r);


        $r = $login->actionReset();
        $this->assertNotNull($r);

        $r = $login->actionPasswordReset('token', 1);
        $this->assertNotNull($r);
    }
}
