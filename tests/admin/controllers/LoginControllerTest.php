<?php

namespace luya\admin\tests\admin\controllers;

use admintests\AdminModelTestCase;
use luya\admin\controllers\LoginController;
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
        $this->createUserOnlineFixture();
        $this->createUserFixture();
        $login = new LoginController('login', $this->app->getModule('admin'));
        $r = $login->actionIndex();
        $this->assertNotNull($r);
        $r = $login->actionAsync();
        $this->assertNotNull($r);
        $r = $login->actionAsyncToken();
        $this->assertNotNull($r);
        $r = $login->actionTwofaToken();
        $this->assertNotNull($r);
    }
}